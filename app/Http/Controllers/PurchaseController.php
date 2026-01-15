<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierDebtTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display listing of purchases with filters
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'purchaseItems.product']);

        // Date filter
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('purchase_date', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59'
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'voided') {
                $query->where('is_voided', true);
            } elseif ($request->status === 'with_debt') {
                $query->notVoided()->where('debt_amount', '>', 0);
            }
        }

        // User filter (managers can see all, cashiers see only their own)
        if (auth()->user()->role === 'cashier') {
            $query->where('user_id', auth()->id());
        }

        $purchases = $query->latest('purchase_date')->paginate(20);

        // Get suppliers for filter dropdown
        $suppliers = Supplier::active()->get();

        // Calculate totals
        $totalPurchasesCount = (clone $query)->notVoided()->count();
        $totalAmount = (clone $query)->notVoided()->sum('total_amount');
        $totalDebt = (clone $query)->notVoided()->sum('debt_amount');

        return view('purchases.index', compact(
            'purchases',
            'suppliers',
            'totalPurchasesCount',
            'totalAmount',
            'totalDebt'
        ));
    }

    /**
     * Show form for creating new purchase
     */
    public function create()
    {
        $suppliers = Supplier::active()->get();
        $products = Product::where('is_active', true)->with('supplier')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store new purchase
     *
     * Expected JSON structure:
     * {
     *   "supplier_id": 1,
     *   "payment_method": "cash" or "debt",
     *   "notes": "...",
     *   "items": [
     *     {"product_id": 1, "quantity": 10, "unit_cost": 5.50},
     *     {"product_id": 2, "quantity": 20, "unit_cost": 3.25}
     *   ]
     * }
     */
    public function store(Request $request)
    {
        // Manual validation to return JSON on failure
        $validator = \Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method' => 'required|in:cash,debt',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Calculate total amount and prepare items with product costs
            $totalAmount = 0;
            $itemsWithCosts = [];
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitCost = $product->cost_price;
                $totalCost = $item['quantity'] * $unitCost;
                $totalAmount += $totalCost;

                $itemsWithCosts[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];
            }

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'paid_amount' => $request->payment_method === 'cash' ? $totalAmount : 0,
                'debt_amount' => $request->payment_method === 'debt' ? $totalAmount : 0,
                'purchase_date' => now(),
                'notes' => $request->notes,
            ]);

            // Create purchase items and increase stock
            foreach ($itemsWithCosts as $itemWithCost) {
                $product = $itemWithCost['product'];

                // Create purchase item
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $itemWithCost['quantity'],
                    'unit_cost' => $itemWithCost['unit_cost'],
                    'total_cost' => $itemWithCost['total_cost'],
                ]);

                // Increase stock
                $product->increment('quantity', $itemWithCost['quantity']);
            }

            // If debt payment, create debt transaction
            if ($request->payment_method === 'debt') {
                SupplierDebtTransaction::create([
                    'supplier_id' => $request->supplier_id,
                    'purchase_id' => $purchase->id,
                    'transaction_type' => 'debt',
                    'amount' => $totalAmount,
                    'description' => "Purchase #{$purchase->purchase_number}",
                ]);

                // Increase supplier total debt
                $purchase->supplier->increment('total_debt', $totalAmount);
            }

            DB::commit();

            // Check if request expects JSON (from AJAX)
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'id' => $purchase->id,
                    'message' => 'تم إنشاء الشراء بنجاح'
                ]);
            }

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'تم إنشاء الشراء بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => true,
                    'message' => 'حدث خطأ أثناء إنشاء الشراء: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الشراء: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display purchase details
     */
    public function show(Purchase $purchase)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $purchase->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الشراء');
        }

        $purchase->load(['supplier', 'user', 'purchaseItems.product', 'debtTransactions']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Void a purchase (Manager only)
     */
    public function void(Request $request, Purchase $purchase)
    {
        // Check manager permission
        if (auth()->user()->role !== 'manager') {
            return response()->json([
                'success' => false,
                'message' => 'غير مسموح لك بإلغاء المشتريات'
            ], 403);
        }

        // Manual validation to return JSON on failure
        $validator = \Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال سبب الإلغاء'
            ], 422);
        }

        if ($purchase->is_voided) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الشراء ملغي بالفعل'
            ], 400);
        }

        // Get total paid before voiding
        $totalPaid = $purchase->getTotalPaid();

        // Use voidPurchase method
        if ($purchase->voidPurchase($request->reason, auth()->id())) {
            $message = 'تم إلغاء الشراء بنجاح';
            if ($totalPaid > 0) {
                $message .= ". تم دفع مبلغ $" . number_format($totalPaid, 2) . " للمورد سابقاً";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'total_paid' => $totalPaid
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إلغاء الشراء'
        ], 500);
    }

    /**
     * Display purchase receipt
     */
    public function receipt(Purchase $purchase)
    {
        // Check permissions
        if (auth()->user()->role === 'cashier' && $purchase->user_id !== auth()->id()) {
            abort(403, 'غير مسموح لك بعرض هذا الإيصال');
        }

        $purchase->load(['supplier', 'user', 'purchaseItems.product']);

        return view('purchases.receipt', compact('purchase'));
    }
}
