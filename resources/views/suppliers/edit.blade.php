@extends('layouts.app')

@section('title', 'تعديل مورد')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل المورد: {{ $supplier->name }}</h1>
        <a href="{{ route('suppliers.show', $supplier) }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للتفاصيل
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="form">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Right Column -->
                <div>
                    <label>الاسم <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $supplier->name) }}"
                           required
                           placeholder="اسم المورد">

                    <label>الشخص المسؤول</label>
                    <input type="text"
                           name="contact_person"
                           value="{{ old('contact_person', $supplier->contact_person) }}"
                           placeholder="الشخص المسؤول عن التواصل">

                    <label>الهاتف</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone', $supplier->phone) }}"
                           placeholder="رقم الهاتف">
                </div>

                <!-- Left Column -->
                <div>
                    <label>العنوان</label>
                    <textarea name="address"
                              rows="6"
                              placeholder="العنوان الكامل">{{ old('address', $supplier->address) }}</textarea>

                    <label>الحالة</label>
                    <select name="is_active">
                        <option value="1" {{ $supplier->is_active ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ !$supplier->is_active ? 'selected' : '' }}>غير نشط</option>
                    </select>

                    <!-- Account Information -->
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
                        <strong>معلومات الحساب:</strong>
                        <div style="color: {{ $supplier->is_active ? '#27ae60' : '#95a5a6' }}; font-weight: 500; margin-top: 0.5rem;">
                            الحالة: {{ $supplier->is_active ? 'نشط' : 'غير نشط' }}
                        </div>
                        <div style="color: {{ $supplier->total_debt > 0 ? '#e74c3c' : '#27ae60' }}; font-size: 0.9rem; margin-top: 0.25rem;">
                            المديونية الحالية: ${{ number_format($supplier->total_debt, 2) }}
                        </div>
                        <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">
                            عدد المنتجات: {{ $supplier->getProductsCount() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit" style="background-color: transparent; color:#1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    حفظ التغييرات
                </button>
                <button type="button" onclick="window.location.href='{{ route('suppliers.show', $supplier) }}'"
                        style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    إلغاء
                </button>
            </div>
        </form>
    </div>

@endsection
