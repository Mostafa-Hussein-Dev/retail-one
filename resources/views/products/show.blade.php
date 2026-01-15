@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تفاصيل المنتج</h1>
        <div style="display: flex; gap: 1rem;">
            <form method="POST" action="{{ route('products.toggle-status', $product) }}" style="display: inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                        style="background-color: transparent; color: {{ $product->is_active ? '#9BB3CC' : '#27ae60' }}; padding: 12px 100px; border: 2px solid {{ $product->is_active ? '#9BB3CC' : '#27ae60' }}; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;  line-height: normal"
                        onmouseover="this.style.backgroundColor='{{ $product->is_active ? 'rgba(155, 179, 204, 0.1)' : 'rgba(39, 174, 96, 0.1)' }}'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    {{ $product->is_active ? 'إلغاء التفعيل' : 'تفعيل المنتج' }}
                </button>
            </form>
            <a href="{{ route('products.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Product Header -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: auto 1fr; gap: 2rem; align-items: start;">
            <!-- Product Image -->
            <div>
                @if($product->image_path)
                    <img src="{{ asset('storage/' . $product->image_path) }}"
                         alt="{{ $product->name }}"
                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 12px; border: 3px solid #eee;">
                @else
                    <div style="width: 150px; height: 150px; background: #ecf0f1; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 3rem; color: #7f8c8d;">
                        📦
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div>
                <h2 style="margin: 0 0 0.5rem 0; color: #2c3e50;">{{ $product->name_ar }}{{ $product->name_ar && $product->name ? ' • ' : '' }}{{ $product->name }}</h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <!-- First Column -->
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                        <!-- Barcode -->
                        <div style="background: #f8f9fa; border-radius: 6px; width: 100%; text-align: right;">
                            <strong>الباركود:</strong> {{ $product->barcode ?? 'غير محدد' }}
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div style="= background: #f8f9fa; border-radius: 6px; width: 100%; text-align: right;">

                                <div style="margin-top: 0.25rem; text-align: right;"><strong>الوصف: </strong>{{ $product->description }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Second Column -->
                    <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.5rem;">
                        <!-- Category -->
                        <div style="= background: #f8f9fa; border-radius: 6px; width: 100%; text-align: right;">
                            <strong>الفئة:</strong>
                            @if($product->category)
                                <span style="padding: 2px 6px; border-radius: 4px; font-size: 0.9rem; display: inline-block;">
                    {{ $product->category->display_name }}
                </span>
                            @else
                                <span style="color: #7f8c8d;">غير مصنف</span>
                            @endif
                        </div>

                        <!-- Supplier -->
                        <div style="=background: #f8f9fa; border-radius: 6px; width: 100%; text-align: right;">
                                <strong style="white-space: nowrap;">المورد:</strong>
                                @if($product->supplier)
                                    <span style="padding: 2px 6px; border-radius: 4px; font-size: 0.9rem; white-space: nowrap; display: inline-block;">
                        {{ $product->supplier->display_name }}
                    </span>
                                @else
                                    <span style="color: #7f8c8d; white-space: nowrap;">غير محدد</span>
                                @endif
                        </div>
                    </div>
                </div>

                <!-- Status Badge -->
                <div style="background: {{ $product->getStockStatusColor() }}; color: white; padding: 0.5rem; border-radius: 6px; font-weight: 600; text-align: center; width: 100%; margin-top: 0.5rem;">
                    {{ $product->getStockStatusText() }}
                </div>
                @if(!$product->is_active)
                    <div style="background: #9BB3CC; color: white; padding: 0.5rem; border-radius: 6px;  font-weight: 600; text-align: center; width: 100%; margin-top: 0.25rem;">
                        غير نشط
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

        <!-- Pricing Info -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">الأسعار</h3>
            <div style="margin-bottom: 0.5rem;">
                <div style="color: #e74c3c; font-weight: 600;">تكلفة: ${{ number_format($product->cost_price, 2) }}</div>
            </div>
            <div style="margin-bottom: 0.5rem;">
                <div style="color: #27ae60; font-weight: 600; font-size: 1.2rem;">بيع: ${{ number_format($product->selling_price, 2) }}</div>
            </div>
            <div style="color: #3498db; font-size: 0.9rem;">
                ربح: ${{ number_format($product->selling_price - $product->cost_price, 2) }}
                ({{ number_format($product->profit_margin, 1) }}%)
            </div>
        </div>

        <!-- Stock Info -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">المخزون</h3>
            <div style="font-size: 2rem; font-weight: bold; color: {{ $product->getStockStatusColor() }}; margin-bottom: 0.5rem;">
                {{ number_format($product->quantity, 2) }}
            </div>
            <div style="color: #7f8c8d; font-size: 1rem;">
                الكمية الأدنى: {{ $product->minimum_quantity }}
            </div>
        </div>

        <!-- Unit Info -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">نوع الوحدة</h3>
            <div style="font-size: 1.5rem; font-weight: bold; color: {{ $product->getStockStatusColor() }}; margin-bottom: 1rem;">
                {{ $product->unit_display }}
            </div>
            <div style="color: #7f8c8d; font-size: 1rem;">
                @switch($product->unit)
                    @case('piece')
                        منتج يُباع بالقطعة
                        @break
                    @case('kg')
                        منتج يُباع بالكيلوغرام
                        @break
                    @case('gram')
                        منتج يُباع بالغرام
                        @break
                    @case('liter')
                        منتج يُباع بالليتر
                        @break
                    @case('meter')
                        منتج يُباع بالمتر
                        @break
                    @default
                        نوع وحدة غير محدد
                @endswitch
            </div>
        </div>

        <!-- Value Info -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">قيمة المخزون</h3>
            <div style="margin-bottom: 0.5rem;">
                <div style="color: #e74c3c; font-weight: 600;">
                    تكلفة: ${{ number_format($product->cost_price * $product->quantity, 2) }}
                </div>
            </div>
            <div style="margin-bottom: 0.5rem;">
                <div style="color: #27ae60; font-weight: 600;">
                    قيمة البيع: ${{ number_format($product->selling_price * $product->quantity, 2) }}
                </div>
            </div>
            <div style="color: #3498db; font-size: 0.9rem;">
                ربح محتمل: ${{ number_format(($product->selling_price - $product->cost_price) * $product->quantity, 2) }}
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card">
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <form id="deleteProductForm" method="POST" action="{{ route('products.destroy', $product) }}" style="display: inline;">
                @csrf
                @method('DELETE')
            </form>
            <button type="button" onclick="confirmDeleteProduct()"
                    style="background-color: transparent; color: #e74c3c; padding: 12px 100px; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                حذف المنتج
            </button>
            <button type="button" onclick="window.location.href='{{ route('products.edit', $product) }}'"
                    style="background-color: transparent; color: #f39c12; padding: 12px 100px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'; this.style.color='#f39c12'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f39c12'">
                تعديل المنتج
            </button>
        </div>
    </div>


    @push('scripts')
        <script>
            function quickAdjust(adjustment) {
                const quantityInput = document.querySelector('input[name="quantity"]');
                const currentQuantity = parseFloat(quantityInput.value) || 0;
                const newQuantity = Math.max(0, currentQuantity + adjustment);
                quantityInput.value = newQuantity;
            }

            async function confirmQuantityAdjustment() {
                const confirmed = await showConfirmDialog({
                    type: 'warning',
                    title: 'تأكيد التعديل',
                    message: 'هل أنت متأكد من تعديل الكمية؟',
                    confirmText: 'نعم، عدل',
                    cancelText: 'إلغاء'
                });

                if (confirmed) {
                    document.getElementById('adjustQuantityForm').submit();
                }
            }

            async function confirmDeleteProduct() {
                const confirmed = await showConfirmDialog({
                    type: 'error',
                    title: 'تأكيد الحذف',
                    message: 'هل أنت متأكد من حذف المنتج؟ هذا الإجراء لا يمكن التراجع عنه.',
                    confirmText: 'نعم، احذف',
                    cancelText: 'إلغاء'
                });

                if (confirmed) {
                    document.getElementById('deleteProductForm').submit();
                }
            }
        </script>
    @endpush

@endsection
