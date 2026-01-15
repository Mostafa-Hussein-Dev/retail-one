@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل المنتج</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('products.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="form">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Right Column -->
                <div>
                    <!-- Product Names -->
                    <label>اسم المنتج (بالإنجليزية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $product->name) }}"
                           required
                           placeholder="Product Name">

                    <label>اسم المنتج (بالعربية)</label>
                    <input type="text"
                           name="name_ar"
                           value="{{ old('name_ar', $product->name_ar) }}"
                           placeholder="اسم المنتج">

                    <!-- Barcode -->
                    <label>الباركود</label>
                    <input type="text"
                           name="barcode"
                           value="{{ old('barcode', $product->barcode) }}">

                    <!-- Category -->
                    <label>الفئة</label>
                    <select name="category_id">
                        <option value="">اختر الفئة</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->display_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Supplier -->
                    <label>المورد <span style="color: #e74c3c;">*</span></label>
                    <select name="supplier_id" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->display_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Description -->
                    <label>الوصف</label>
                    <textarea name="description"
                              rows="5"
                              placeholder="وصف المنتج">{{ old('description', $product->description) }}</textarea>
                </div>

                <!-- Left Column -->
                <div>
                    <!-- Current Image -->
                    @if($product->image_path)
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الصورة الحالية</label>
                            <img src="{{ asset('storage/' . $product->image_path) }}"
                                 alt="{{ $product->name }}"
                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #eee;">
                        </div>
                    @endif

                    <!-- Prices -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>سعر التكلفة (دولار) <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   name="cost_price"
                                   value="{{ old('cost_price', $product->cost_price) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                        <div>
                            <label>سعر البيع (دولار) <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   name="selling_price"
                                   value="{{ old('selling_price', $product->selling_price) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                    </div>

                    <!-- Unit -->
                    <label>الوحدة <span style="color: #e74c3c;">*</span></label>
                    <select name="unit" required>
                        @foreach($units as $value => $label)
                            <option value="{{ $value }}" {{ old('unit', $product->unit) == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Minimum Quantity -->
                    <label>الكمية الأدنى <span style="color: #e74c3c;">*</span></label>
                    <input type="number"
                           name="minimum_quantity"
                           value="{{ old('minimum_quantity', $product->minimum_quantity) }}"
                           min="0"
                           required>

                    <!-- Product Image -->
                    <label>تغيير صورة المنتج</label>
                    <input type="file"
                           name="image"
                           accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px dashed #ccc; border-radius: 6px; background: #f8f9fa;">
                    <small style="color: #7f8c8d; font-size: 0.85rem;">
                        الحد الأقصى: 2 ميجابايت. الصيغ المدعومة: JPG, PNG, GIF
                        <br>اترك فارغاً للحفاظ على الصورة الحالية
                    </small>
                </div>
            </div>
            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit"
                        style="background-color: transparent; color: #f39c12; padding: 12px 100px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    تحديث المنتج
                </button>
                <button type="button" onclick="window.location.href='{{ route('products.show', $product) }}'"
                        style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    إلغاء
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const costInput = document.querySelector('input[name="cost_price"]');
                const sellInput = document.querySelector('input[name="selling_price"]');
                const quantityInput = document.querySelector('input[name="quantity"]');
                const quickQuantityInput = document.getElementById('quick-quantity');
                const profitDisplay = document.getElementById('profit-display');

                function calculateProfit() {
                    const cost = parseFloat(costInput.value) || 0;
                    const sell = parseFloat(sellInput.value) || 0;

                    if (cost === 0) {
                        profitDisplay.innerHTML = '0% ربح • $0.00 ربح لكل وحدة';
                        return;
                    }

                    const profit = sell - cost;
                    const margin = ((profit / cost) * 100);

                    profitDisplay.innerHTML = `${margin.toFixed(1)}% ربح • $${profit.toFixed(2)} ربح لكل وحدة`;

                    // Change color based on profit margin
                    if (margin < 10) {
                        profitDisplay.style.color = '#e74c3c';
                    } else if (margin < 25) {
                        profitDisplay.style.color = '#f39c12';
                    } else {
                        profitDisplay.style.color = '#27ae60';
                    }
                }

                window.adjustQuantity = function(amount) {
                    const currentQty = parseFloat(quickQuantityInput.value) || 0;
                    const newQty = Math.max(0, currentQty + amount);
                    quickQuantityInput.value = newQty;
                };

                window.syncQuantity = function() {
                    quantityInput.value = quickQuantityInput.value;
                };

                costInput.addEventListener('input', calculateProfit);
                sellInput.addEventListener('input', calculateProfit);

                // Sync quick quantity with main quantity
                quantityInput.addEventListener('input', function() {
                    quickQuantityInput.value = this.value;
                });

                // Calculate on page load
                calculateProfit();
            });
        </script>
    @endpush

@endsection
