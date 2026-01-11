@extends('layouts.app')

@section('title', 'تعديل عميل')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل بيانات العميل</h1>
        <a href="{{ route('customers.show', $customer) }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للتفاصيل
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="form">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Right Column -->
                <div>
                    <label>الاسم <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $customer->name) }}"
                           required
                           placeholder="اسم العميل">

                    <label>الهاتف</label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone', $customer->phone) }}"
                           placeholder="رقم الهاتف">

                    <label>العنوان</label>
                    <textarea name="address"
                              rows="6"
                              placeholder="العنوان الكامل">{{ old('address', $customer->address) }}</textarea>
                </div>

                <!-- Left Column -->
                <div>
                    <label>حد الائتمان ($) <span style="color: #e74c3c;">*</span></label>
                    <input type="number"
                           name="credit_limit"
                           value="{{ old('credit_limit', $customer->credit_limit) }}"
                           step="0.01"
                           min="0"
                           required
                           placeholder="الحد الأقصى للديون">

                    <label>الحالة</label>
                    <select name="is_active">
                        <option value="1" {{ $customer->is_active ? 'selected' : '' }}>نشط</option>
                        <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>غير نشط</option>
                    </select>

                    <!-- Current Debt Display -->
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 1rem;">
                        <strong>معلومات الحساب:</strong>
                        <div style="margin-top: 0.5rem;">
                            المديونية الحالية:
                            <span style="color: {{ $customer->total_debt > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                ${{ number_format($customer->total_debt, 2) }}
                            </span>
                        </div>
                        <div style="color: #7f8c8d; font-size: 0.9rem; margin-top: 0.25rem;">
                            نسبة الاستخدام: {{ number_format($customer->getCreditUtilizationPercentage(), 1) }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit" style="background-color: transparent; color:#1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    تحديث البيانات
                </button>
                <button type="button" onclick="window.location.href='{{ route('customers.show', $customer) }}'"
                        style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    إلغاء
                </button>
            </div>
        </form>
    </div>

@endsection
