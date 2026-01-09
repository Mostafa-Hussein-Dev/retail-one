<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::withCount(['products', 'activeProducts']);
        $activeProductsCount = \App\Models\Product::where('is_active', true)->count();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->latest()->paginate(20);

        return view('categories.index', compact('categories', 'activeProductsCount' ));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'name_ar' => 'required|string|max:255|unique:categories,name_ar',
            'description' => 'nullable|string|max:1000',
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'تم إضافة الفئة بنجاح');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['products' => function ($query) {
            $query->latest()->take(10);
        }]);

        $stats = [
            'total_products' => $category->products_count,
            'active_products' => $category->active_products_count,
            'low_stock_products' => $category->products()->lowStock()->count(),
            'out_of_stock_products' => $category->products()->outOfStock()->count(),
        ];

        return view('categories.show', compact('category', 'stats'));
    }

    /**
     * Show the form for editing the category
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'name_ar' => 'required|string|max:255|unique:categories,name_ar,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')
            ->with('success', 'تم تحديث الفئة بنجاح');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفئة لأنها تحتوي على منتجات'
            ]);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفئة بنجاح'
        ]);
    }

    /**
     * Toggle category status (active/inactive)
     */
    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';

        return redirect()->back()
            ->with('success', $status . ' الفئة بنجاح');
    }

    /**
     * Get categories for API/AJAX requests
     */
    public function apiIndex()
    {
        $categories = Category::active()->get(['id', 'name', 'name_ar']);

        return response()->json($categories);
    }
}
