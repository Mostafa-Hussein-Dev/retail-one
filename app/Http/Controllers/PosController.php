<?php


namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PosController extends Controller
{
    /**
     * Display the main POS interface
     */
    public function index()
    {
        // Clear any existing cart session
        $this->clearCart();

        return view('pos.index');
    }

    /**
     * Search products for POS
     */
    public function searchProducts(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:1',
        ]);

        $search = $request->search;

        $products = Product::with(['category', 'supplier'])
            ->where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->display_name,
                'barcode' => $product->barcode,
                'price' => number_format($product->selling_price, 2),
                'price_raw' => $product->selling_price,
                'stock' => number_format($product->quantity, 2),
                'unit' => $product->unit_display,
                'stock_status' => $product->stock_status,
                'stock_color' => $product->getStockStatusColor(),
                'image' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                'category' => $product->category ? $product->category->display_name : null,
                'can_sell' => $product->quantity > 0 && $product->is_active,
            ];
        }));
    }

    /**
     * Search product by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::with(['category', 'supplier'])
            ->where('barcode', $request->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير موجود'
            ]);
        }

        if ($product->quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج نفد من المخزون'
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->display_name,
                'barcode' => $product->barcode,
                'price' => number_format($product->selling_price, 2),
                'price_raw' => $product->selling_price,
                'stock' => number_format($product->quantity, 2),
                'unit' => $product->unit_display,
                'stock_status' => $product->stock_status,
                'can_sell' => true,
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::find($request->product_id);

        if (!$product || !$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير متوفر'
            ]);
        }

        $quantity = $request->quantity;
        $customPrice = $product->selling_price;

        // Check stock availability
        if ($quantity > $product->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'الكمية المطلوبة أكبر من المخزون المتاح'
            ]);
        }

        $cart = Session::get('pos_cart', []);

        // Check if product already in cart
        $existingIndex = null;
        $existingQuantity = 0;
        foreach ($cart as $index => $item) {
            if ($item['product_id'] == $product->id && $item['unit_price'] == $customPrice) {
                $existingIndex = $index;
                $existingQuantity = $item['quantity'];
                break;
            }
        }

        // Check stock availability (existing in cart + new quantity)
        $totalQuantity = $existingQuantity + $quantity;
        if ($totalQuantity > $product->quantity) {
            return response()->json([
                'success' => false,
                'message' => "الكمية المتوفرة: {$product->quantity} | لا يمكن إضافة {$quantity} أخرى"
            ]);
        }

        if ($existingIndex !== null) {
            // Update existing item
            $cart[$existingIndex]['quantity'] = $totalQuantity;
            $cart[$existingIndex]['total_price'] = $cart[$existingIndex]['quantity'] * $cart[$existingIndex]['unit_price'];
        } else {
            // Add new item
            $cartItem = [
                'product_id' => $product->id,
                'product_name' => $product->display_name,
                'product_barcode' => $product->barcode,
                'quantity' => $quantity,
                'unit_price' => $customPrice,
                'unit_cost' => $product->cost_price,
                'unit' => $product->unit_display,
                'total_price' => $quantity * $customPrice,
                'discount_percentage' => 0,
                'discount_amount' => 0,
                'original_price' => $product->selling_price,
                'price_modified' => $customPrice != $product->selling_price,
            ];

            $cart[] = $cartItem;
        }

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المنتج إلى السلة',
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $cart = Session::get('pos_cart', []);
        $index = $request->index;

        if (!isset($cart[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في السلة'
            ]);
        }

        // Check stock availability
        $product = Product::find($cart[$index]['product_id']);
        if ($request->quantity > $product->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'الكمية المطلوبة أكبر من المخزون المتاح'
            ]);
        }

        $cart[$index]['quantity'] = $request->quantity;
        $cart[$index]['total_price'] = $cart[$index]['quantity'] * $cart[$index]['unit_price'] - $cart[$index]['discount_amount'];

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Update cart item price
     */
    public function updateCartPrice(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $cart = Session::get('pos_cart', []);
        $index = $request->index;

        if (!isset($cart[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في السلة'
            ]);
        }

        $cart[$index]['unit_price'] = $request->price;
        $cart[$index]['price_modified'] = true;
        $cart[$index]['total_price'] = $cart[$index]['quantity'] * $cart[$index]['unit_price'] - $cart[$index]['discount_amount'];

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Apply discount to cart item
     */
    public function applyDiscount(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $cart = Session::get('pos_cart', []);
        $index = $request->index;

        if (!isset($cart[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في السلة'
            ]);
        }

        $item = &$cart[$index];
        $subtotal = $item['quantity'] * $item['unit_price'];

        if ($request->discount_type === 'percentage') {
            $item['discount_percentage'] = $request->discount_value;
            $item['discount_amount'] = $subtotal * ($request->discount_value / 100);
        } else {
            $item['discount_amount'] = min($request->discount_value, $subtotal);
            $item['discount_percentage'] = ($item['discount_amount'] / $subtotal) * 100;
        }

        $item['total_price'] = $subtotal - $item['discount_amount'];

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $cart = Session::get('pos_cart', []);
        $index = $request->index;

        if (!isset($cart[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في السلة'
            ]);
        }

        // Remove item and reindex array
        unset($cart[$index]);
        $cart = array_values($cart);

        Session::put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر من السلة',
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        Session::forget('pos_cart');

        return response()->json([
            'success' => true,
            'message' => 'تم مسح السلة',
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Get current cart
     */
    public function getCart()
    {
        return response()->json([
            'success' => true,
            'cart' => $this->getCartSummary()
        ]);
    }

    /**
     * Search customers
     */
    public function searchCustomers(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:1',
        ]);

        $customers = Customer::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'debt' => number_format($customer->total_debt, 2),
                'credit_limit' => number_format($customer->credit_limit, 2),
                'can_debt' => $customer->total_debt < $customer->credit_limit,
            ];
        }));
    }

    /**
     * Process sale
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,debt',
            'customer_id' => 'required_if:payment_method,debt|nullable|exists:customers,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = Session::get('pos_cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'السلة فارغة'
            ]);
        }

        // Check customer credit limit for debt sales
        if ($request->payment_method === 'debt' && $request->customer_id) {
            $customer = Customer::find($request->customer_id);

            if (!$customer || !$customer->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'العميل غير موجود أو غير نشط'
                ]);
            }

            // Calculate sale total
            $saleTotal = 0;
            foreach ($cart as $item) {
                $saleTotal += $item['total_price'];
            }

            // Check if customer has enough credit limit
            if (!$customer->canPurchaseAmount($saleTotal)) {
                $availableCredit = $customer->credit_limit - $customer->total_debt;
                return response()->json([
                    'success' => false,
                    'message' => "حد الائتمان غير كافٍ. المتاح: \${" . number_format($availableCredit, 2) . "، المطلوب: \${" . number_format($saleTotal, 2) . "}"
                ]);
            }
        }

        try {
            DB::beginTransaction();

            // Create sale
            $sale = new Sale();
            $sale->user_id = auth()->id();
            $sale->customer_id = $request->customer_id;
            $sale->payment_method = $request->payment_method;
            $sale->notes = $request->notes;
            $sale->sale_date = now();

            // Calculate totals
            $subtotal = 0;  // Should be BEFORE discount
            $totalDiscount = 0;

            foreach ($cart as $item) {
                $subtotal += ($item['unit_price'] * $item['quantity']);
                $totalDiscount += $item['discount_amount'];
            }

            $sale->subtotal = $subtotal;  // Amount BEFORE discount
            $sale->discount_amount = $totalDiscount;
            $sale->total_amount = $subtotal - $totalDiscount;

            if ($request->payment_method === 'cash') {
                $sale->debt_amount = 0; // Cash = paid in full
            } else {
                $sale->debt_amount = $sale->total_amount; // Debt = all owed
            }

            $sale->save();

            // Create sale items
            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => $item['unit_cost'],
                    'discount_percentage' => $item['discount_percentage'],
                    'discount_amount' => $item['discount_amount'],
                    'total_price' => $item['total_price'],
                    'profit' => ($item['unit_price'] - $item['unit_cost']) * $item['quantity'] - $item['discount_amount'],
                ]);
            }

            // Complete the sale (reduces stock and creates debt transaction if applicable)
            $sale->completeSale();

            DB::commit();

            // Clear cart
            $this->clearCart();

            return response()->json([
                'success' => true,
                'message' => 'تم إتمام البيع بنجاح',
                'sale_id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة البيع: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get cart summary
     */
    private function getCartSummary()
    {
        $cart = Session::get('pos_cart', []);

        // Calculate subtotal BEFORE discount
        $subtotalBeforeDiscount = 0;
        foreach ($cart as $item) {
            $subtotalBeforeDiscount += ($item['unit_price'] * $item['quantity']);
        }

        $summary = [
            'items' => $cart,
            'items_count' => count($cart),
            'total_quantity' => array_sum(array_column($cart, 'quantity')),
            'subtotal' => $subtotalBeforeDiscount,
            'total_discount' => array_sum(array_column($cart, 'discount_amount')),
            'total' => array_sum(array_column($cart, 'total_price')),
        ];

        return $summary;
    }
}
