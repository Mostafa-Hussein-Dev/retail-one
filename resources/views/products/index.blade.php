@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة المنتجات</h1>
        <a href="{{ route('products.create') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            إضافة منتج جديد
        </a>
    </div>

    <!-- Stock Alerts -->
    @if($lowStockCount > 0 || $outOfStockCount > 0)
        <div style="background: #fdf7e8; border: 1px solid #f39c12; padding: 1rem; border-radius: 6px; margin-top: 1rem; border-left: 4px solid #f39c12;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div>
                    <strong>تنبيهات المخزون:</strong>
                    @if($lowStockCount > 0)
                        <span style="color: #f39c12;">{{ $lowStockCount }} منتج بمخزون منخفض</span>
                    @endif
                    @if($outOfStockCount > 0)
                        @if($lowStockCount > 0) • @endif
                        <span style="color: #e74c3c;">{{ $outOfStockCount }} منتج نفد من المخزون</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">البحث</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="البحث بالاسم أو الباركود"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الفئة</label>
                <select name="category" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الفئات</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الحالة</label>
                <select name="status" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>مخزون منخفض</option>
                    <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>نفد المخزون</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit"
                        style="background-color: transparent; color: #1abc9c; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    بحث
                </button>

            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="card">
        @if($products->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">الصورة</th>
                    <th style="text-align: center; vertical-align: middle;">المعلومات</th>
                    <th style="text-align: center; vertical-align: middle;">الفئة</th>
                    <th style="text-align: center; vertical-align: middle;">المورد</th>
                    <th style="text-align: center; vertical-align: middle;">المخزون</th>
                    <th style="text-align: center; vertical-align: middle;">الحالة</th>
                    <th style="text-align: center; vertical-align: middle;">العمليات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                    <tr>
                        <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }}">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}"
                                     alt="{{ $product->name }}"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                            @else
                                <div style="width: 50px; height: 50px; background: #ecf0f1;  border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #7f8c8d;">
                                    📦
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }}">
                            <div>
                                <strong>{{ $product->name_ar }}{{ $product->name_ar && $product->name ? ' • ' : '' }}{{ $product->name }}</strong>
                                <div style="color: #7f8c8d; font-size: 0.9rem;">{{ $product->barcode }}</div>
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }}">
                            @if($product->category)
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">
                                    {{ $product->category->display_name }}
                                </span>
                            @else
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">غير محدد</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }} ">
                            @if($product->supplier)
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">
                                    {{ $product->supplier->name }}
                                </span>
                            @else
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; display: inline-block;">غير محدد</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }}">
                            <div>
                                <strong>{{ number_format($product->quantity, 2) }} {{ $product->unit_display }}</strong>
                            </div>

                        </td>
                        <td style="text-align: center; vertical-align: middle; min-width: 120px">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                                <span style="background: {{ $product->getStockStatusColor() }}; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">
                                    {{ $product->getStockStatusText() }}
                                </span>
                                @if(!$product->is_active)
                                    <span style="background: #9BB3CC; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">
                                        غير نشط
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="{{ route('products.show', $product) }}"
                                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $products->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <h2>لا توجد منتجات</h2>
                <h4>إبدأ بإضافة منتجاتك</h4>
            </div>
        @endif
    </div>

@endsection
