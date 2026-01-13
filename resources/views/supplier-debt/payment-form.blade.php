@extends('layouts.app')

@section('title', 'تسجيل دفعة')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تسجيل دفعة للمورد</h1>
        <a href="{{ route('suppliers.show', $supplier) }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للمورد
        </a>
    </div>

    <!-- Purchase Info Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">معلومات الشراء</h3>
        </div>
        <div style="padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <p style="margin-bottom: 0.5rem;"><strong>رقم الشراء:</strong> {{ $purchase->purchase_number }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>المورد:</strong> {{ $supplier->name }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>التاريخ:</strong> {{ $purchase->purchase_date->format('Y-m-d') }}</p>
                </div>
                <div>
                    <p style="margin-bottom: 0.5rem;"><strong>إجمالي الشراء:</strong> <span style="color: #1abc9c; font-weight: 600;">${{ number_format($purchase->total_amount, 2) }}</span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>المدفوع:</strong> <span style="color: #27ae60; font-weight: 600;">${{ number_format($purchase->paid_amount, 2) }}</span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>المتبقي:</strong> <span style="color: #e74c3c; font-weight: 700; font-size: 1.3rem;">${{ number_format($purchase->debt_amount, 2) }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="card">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">تسجيل الدفعة</h3>
        </div>
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('supplier-debt.record-payment', [$supplier, $purchase]) }}">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المبلغ المدفوع ($) <span style="color: #e74c3c;">*</span></label>
                    <input type="number"
                           name="amount"
                           class="form-control @error('amount') is-invalid @enderror"
                           value="{{ old('amount') }}"
                           step="0.01"
                           min="0.01"
                           max="{{ $purchase->debt_amount }}"
                           required
                           autofocus
                           style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px; font-size: 1.1rem;">
                    @error('amount')
                        <div style="color: #e74c3c; margin-top: 0.5rem;">{{ $message }}</div>
                    @enderror
                    <small style="color: #7f8c8d; display: block; margin-top: 0.5rem;">
                        الحد الأقصى: ${{ number_format($purchase->debt_amount, 2) }}
                    </small>
                </div>

                <div style="background: #e8f4f8; border-right: 4px solid #3498db; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
                    <p style="margin: 0; color: #2c3e50;">
                        <strong>ملاحظة:</strong> سيتم خصم المبلغ من مديونية هذا الشراء وتحديث رصيد المورد.
                    </p>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button type="submit"
                            style="background-color: transparent; color: #27ae60; padding: 12px 100px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                        تسجيل الدفعة
                    </button>
                    <button type="button"
                            onclick="window.location.href='{{ route('suppliers.show', $supplier) }}'"
                            style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
