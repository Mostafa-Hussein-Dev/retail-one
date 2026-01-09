@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إضافة منتج جديد</h1>
        <a href="{{ route('products.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للقائمة
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="form">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Right Column -->
                <div>
                    <!-- Product Names -->
                    <label>اسم المنتج (بالإنجليزية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Product Name">

                    <label>اسم المنتج (بالعربية)<span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name_ar"
                           value="{{ old('name_ar') }}"
                           required
                           placeholder="اسم المنتج">

                    <!-- Barcode -->
                    <label>الباركود</label>
                    <input type="text"
                           name="barcode"
                           value="{{ old('barcode') }}"
                           placeholder="سيتم إنشاؤه تلقائياً إذا ترك فارغاً">

                    <!-- Category -->
                    <label>الفئة<span style="color: #e74c3c;">*</span></label>
                    @if($fromCategoryPage)
                        <!-- Read-only when coming from category page -->
                        <select name="category_id" required disabled style="background: #1abc9c; color: white; opacity: 0.8;">
                            @foreach($categories as $category)
                                @if($category->id == $selectedCategoryId)
                                    <option value="{{ $category->id }}" selected>{{ $category->display_name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <!-- Hidden input to ensure the value is submitted -->
                        <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                    @else
                        <!-- Normal dropdown when accessing directly -->
                        <select name="category_id" required>
                            <option value="">اختر الفئة</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $selectedCategoryId) == $category->id ? 'selected' : '' }}>
                                    {{ $category->display_name }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Description -->
                    <label>الوصف</label>
                    <textarea name="description"
                              rows="10"
                              placeholder="وصف المنتج">{{ old('description') }}</textarea>
                </div>

                <!-- Left Column -->
                <div>
                    <!-- Prices -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>سعر التكلفة (دولار) <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   name="cost_price"
                                   value="{{ old('cost_price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                        <div>
                            <label>سعر البيع (دولار) <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   name="selling_price"
                                   value="{{ old('selling_price', 0) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                    </div>

                    <!-- Quantity and Unit -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div>
                            <label>الكمية <span style="color: #e74c3c;">*</span></label>
                            <input type="number"
                                   name="quantity"
                                   value="{{ old('quantity', 0) }}"
                                   step="0.01"
                                   min="0"
                                   required>
                        </div>
                        <div>
                            <label>الوحدة <span style="color: #e74c3c;">*</span></label>
                            <select name="unit" required>
                                @foreach($units as $value => $label)
                                    <option value="{{ $value }}" {{ old('unit', 'piece') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Minimum Quantity -->
                    <label>الكمية الأدنى <span style="color: #e74c3c;">*</span></label>
                    <input type="number"
                           name="minimum_quantity"
                           value="{{ old('minimum_quantity', 5) }}"
                           min="0"
                           required>

                    <!-- Supplier -->
                    <label>المورد <span style="color: #e74c3c;">*</span></label>
                    <select name="supplier_id" required>
                        <option value="">اختر المورد</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->display_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Product Image -->
                    <label>صورة المنتج</label>
                    <input type="file"
                           name="image"
                           accept="image/*"
                           style="width: 100%; padding: 10px; border: 2px dashed #ccc; border-radius: 6px; background: #f8f9fa;">
                    <small style="color: #7f8c8d; font-size: 0.85rem;">
                        الحد الأقصى: 2 ميجابايت. الصيغ المدعومة: JPG, PNG, GIF
                    </small>

                    <!-- Profit Calculation Display -->
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
                        <strong>معاينة الربح:</strong>
                        <div id="profit-display" style="color: #27ae60; font-weight: 600; margin-top: 0.5rem;">
                            0% ربح • $0.00 ربح لكل وحدة
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit" style="background-color: transparent; color:#1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    حفظ المنتج
                </button>
                <button type="button" onclick="window.location.href='{{ route('products.index') }}'"
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

                costInput.addEventListener('input', calculateProfit);
                sellInput.addEventListener('input', calculateProfit);

                // Calculate on page load
                calculateProfit();
            });
        </script>
    @endpush

@endsection
