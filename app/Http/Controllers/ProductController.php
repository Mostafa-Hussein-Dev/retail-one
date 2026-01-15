<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
                case 'active':
                    $query->active();
                    break;
            }
        }

        $products = $query->latest()->paginate(20);

        // Get categories for filter dropdown
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();

        // Get low stock count for alerts
        $lowStockCount = Product::lowStock()->active()->count();
        $outOfStockCount = Product::outOfStock()->active()->count();

        return view('products.index', compact(
            'products',
            'categories',
            'suppliers',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * Show the form for creating a new product
     */
    public function create(Request $request)
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();
        $units = Product::getUnits();

        $selectedCategoryId = $request->get('category');
        $fromCategoryPage = !is_null($selectedCategoryId); // Check if coming from category page

        return view('products.create', compact('categories', 'suppliers', 'units', 'selectedCategoryId', 'fromCategoryPage'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_quantity' => 'required|integer|min:0',
            'unit' => 'required|in:piece,kg,gram,liter,meter',
            'barcode' => 'nullable|string|unique:products,barcode',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image_path'] = $imagePath;
        }

        // Generate barcode if not provided
        if (empty($data['barcode'])) {
            $data['barcode'] = $this->generateBarcode();
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the product
     */
    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        $suppliers = Supplier::active()->get();
        $units = Product::getUnits();

        return view('products.edit', compact('product', 'categories', 'suppliers', 'units'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_quantity' => 'required|integer|min:0',
            'unit' => 'required|in:piece,kg,gram,liter,meter',
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('image');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $imagePath = $request->file('image')->store('products', 'public');
            $data['image_path'] = $imagePath;
        }

        $product->update($data);

        return redirect()->route('products.show', $product)
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {

        // Delete image
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return redirect()->back()
            ->with('success', $status . ' المنتج بنجاح');
    }

    /**
     * Adjust product stock
     */
    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldQuantity = $product->quantity;
        $product->update(['quantity' => $request->quantity]);

        return redirect()->route('products.show', $product)
            ->with('success', 'تم تعديل الكمية من ' . $oldQuantity . ' إلى ' . $request->quantity);
    }

    /**
     * Get low stock products for dashboard
     */
    public function lowStock()
    {
        $products = Product::lowStock()->active()->with('category')->get();

        return response()->json($products);
    }

    /**
     * Generate unique barcode
     */
    private function generateBarcode(): string
    {
        do {
            $barcode = str_pad(rand(1, 9999999999999), 13, '0', STR_PAD_LEFT);
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Search products by barcode (for POS)
     */
    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::findByBarcode($request->barcode);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'المنتج غير موجود'
            ]);
        }

        return response()->json([
            'success' => true,
            'product' => $product->load('category')
        ]);
    }
}
