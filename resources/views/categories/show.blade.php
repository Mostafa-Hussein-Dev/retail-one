@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تفاصيل الفئة</h1>
        <div style="display: flex; gap: 1rem;">
            <form method="POST" action="{{ route('categories.toggle-status', $category) }}" style="display: inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                        style="background-color: transparent; color: {{ $category->is_active ? '#9BB3CC' : '#27ae60' }}; padding: 12px 100px; border: 2px solid {{ $category->is_active ? '#9BB3CC' : '#27ae60' }}; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;  line-height: normal"
                        onmouseover="this.style.backgroundColor='{{ $category->is_active ? 'rgba(155, 179, 204, 0.1)' : 'rgba(39, 174, 96, 0.1)' }}'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    {{ $category->is_active ? 'إلغاء التفعيل' : 'تفعيل الفئة' }}
                </button>
            </form>
            <a href="{{ route('categories.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Category Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div>
            <!-- Name, Description, Dates -->
            <div style="display: grid; grid-template-columns: auto 1fr auto; gap: 2rem; align-items: center; margin-bottom: 3rem; margin-top: 3rem;">
                <!-- Left: Name -->
                <div style="text-align: center;">
                    <h2 style="margin: 0; color: #2c3e50;">
                        {{ $category->name_ar }}{{ $category->name_ar && $category->name ? ' • ' : '' }}{{ $category->name }}
                    </h2>
                </div>

                <!-- Center: Description -->
                <div style="text-align: center;">
                    @if($category->description)
                        <div>
                            <strong style="color: #2c3e50; font-size: 1.1rem;">الوصف:</strong>
                            <span style="color: #7f8c8d; font-size: 1rem;">{{ $category->description }}</span>
                        </div>
                    @endif
                </div>

                <!-- Right: Dates -->
                <div style="text-align: center; min-width: 200px;">
                    <div style="color: #7f8c8d; font-size: 1rem;">
                        <div><strong>تاريخ الإنشاء:</strong> {{ $category->created_at->format('d-m-Y') }}</div>
                        @if($category->updated_at->ne($category->created_at))
                            <div><strong>آخر تحديث:</strong> {{ $category->updated_at->format('d-m-Y') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Badge - spans full width below (like product header) -->
            <div style="background: {{ $category->is_active ? '#27ae60' : '#9BB3CC' }}; color: white; padding: 0.7rem; border-radius: 6px; font-weight: 600; text-align: center; width: 100%; margin: 0 auto; display: block;">
                {{ $category->is_active ? 'نشط' : 'غير نشط' }}
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

        <div class="card" style="text-align: center;">
            <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي المنتجات</h3>
            <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                {{ $stats['total_products'] }}
            </div>
        </div>

        <div class="card" style="text-align: center;">
            <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">المنتجات النشطة</h3>
            <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                {{ $stats['active_products'] }}
            </div>
        </div>

        <div class="card" style="text-align: center;">
            <h3 style="color: #f39c12; margin-bottom: 0.5rem;">مخزون منخفض</h3>
            <div style="font-size: 2rem; font-weight: bold; color: #f39c12;">
                {{ $stats['low_stock_products'] }}
            </div>
        </div>

        <div class="card" style="text-align: center;">
            <h3 style="color: #e74c3c; margin-bottom: 0.5rem;">نفد المخزون</h3>
            <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                {{ $stats['out_of_stock_products'] }}
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    @if($category->products->count() > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>آخر 10 منتجات</h2>
                <a href="{{ route('products.index', ['category' => $category->id]) }}"
                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 8px 16px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                   onmouseout="this.style.backgroundColor='transparent'">
                    عرض جميع المنتجات
                </a>
            </div>

            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="text-align: center; vertical-align: middle;">الاسم</th>
                        <th style="text-align: center; vertical-align: middle;">المورد</th>
                        <th style="text-align: center; vertical-align: middle;">السعر</th>
                        <th style="text-align: center; vertical-align: middle;">المخزون</th>
                        <th style="text-align: center; vertical-align: middle;">الحالة</th>
                        <th style="text-align: center; vertical-align: middle;">العمليات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($category->products as $product)
                        <tr>
                            <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }}">
                                <div>
                                    <strong>{{ $product->name_ar }}{{ $product->name_ar && $product->name ? ' • ' : '' }}{{ $product->name }}</strong>
                                    <div style="color: #7f8c8d; font-size: 0.9rem;">{{ $product->barcode }}</div>
                                </div>
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
                            <td style="text-align: center; vertical-align: middle; {{ !$product->is_active ? 'opacity: 0.6;' : '' }};">
                                <strong>${{ number_format($product->selling_price, 2) }}</strong>
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
                                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
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
            </div>

            @if($stats['total_products'] > 10)
                <div style="text-align: center; margin-top: 1rem; color: #7f8c8d;">
                    عرض 10 من أصل {{ $stats['total_products'] }} منتج.
                    <a href="{{ route('products.index', ['category' => $category->id]) }}" style="color: #1abc9c;">
                        عرض الجميع
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="card" style="text-align: center; padding: 3rem; color: #7f8c8d;">
            <h2>لا توجد منتجات في هذه الفئة</h2>
            <h4>ابدأ بإضافة منتجات لهذه الفئة</h4>
            <a href="{{ route('products.create', ['category' => $category->id]) }}"
               style="display: inline-block; margin-top: 1rem; background-color: transparent; color: #1abc9c; padding: 12px 24px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                إضافة منتج جديد
            </a>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="card">
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <button type="button" onclick="deleteCategory({{ $category->id }})"
                    style="background-color: transparent; color: #e74c3c; padding: 12px 100px; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                حذف الفئة
            </button>
            <button type="button" onclick="window.location.href='{{ route('categories.edit', $category) }}'"
                    style="background-color: transparent; color: #f39c12; padding: 12px 100px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'; this.style.color='#f39c12'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f39c12'">
                تعديل الفئة
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteCategory(categoryId) {
                fetch(`/categories/${categoryId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'error');

                        if (data.success) {
                            // Redirect to categories list after successful deletion
                            setTimeout(() => {
                                window.location.href = '/categories';
                            }, 1500);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('حدث خطأ أثناء الحذف', 'error');
                    });
            }
        </script>
    @endpush

@endsection
