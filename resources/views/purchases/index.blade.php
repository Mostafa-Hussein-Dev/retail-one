@extends('layouts.app')

@section('title', 'المشتريات')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>المشتريات</h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('purchases.create') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 50px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                شراء جديد
            </a>
            <a href="{{ route('suppliers.index') }}"
               style="display: inline-block; background-color: transparent; color: #3498db; padding: 12px 50px; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                الموردين
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">تاريخ من</label>
                <input type="date"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">تاريخ إلى</label>
                <input type="date"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">المورد</label>
                <select name="supplier_id" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الموردين</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الحالة</label>
                <select name="status" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الحالات</option>
                    <option value="with_debt" {{ request('status') == 'with_debt' ? 'selected' : '' }}>غير مدفوع بالكامل</option>
                    <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>ملغاة</option>
                </select>
            </div>

            <div>
                <button type="submit"
                        style="background-color: transparent; color: #1abc9c; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease; width: 100%;"
                        onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    @if($totalPurchasesCount > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي المشتريات</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $totalPurchasesCount }}
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي المبالغ</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    ${{ number_format($totalAmount, 2) }}
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الديون</h3>
                <div style="font-size: 2rem; font-weight: bold; color: {{ $totalDebt > 0 ? '#e74c3c' : '#27ae60' }};">
                    ${{ number_format($totalDebt, 2) }}
                </div>
            </div>
        </div>
    @endif

    <!-- Purchases Table -->
    <div class="card">
        @if($purchases->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th>رقم الشراء</th>
                    <th>المورد</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>الديون</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($purchases as $purchase)
                    <tr style="{{ $purchase->is_voided ? 'opacity: 0.5;' : '' }}">
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}" style="color: #3498db; text-decoration: none; font-weight: 600;">
                                {{ $purchase->purchase_number }}
                            </a>
                            @if($purchase->is_voided)
                                <span style="background: #95a5a6; color: white; padding: 2px 8px; border-radius: 8px; font-size: 0.75rem; margin-right: 0.5rem;">ملغي</span>
                            @endif
                        </td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                        <td>${{ number_format($purchase->total_amount, 2) }}</td>
                        <td style="color: {{ $purchase->debt_amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                            ${{ number_format($purchase->debt_amount, 2) }}
                        </td>
                        <td>
                            <span style="background: {{ $purchase->getStatusColor() }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
                                {{ $purchase->getStatusText() }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase) }}"
                               style="color: #3498db; text-decoration: none; margin-left: 0.5rem;">عرض</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $purchases->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <p style="font-size: 1.2rem;">لا يوجد مشتريات</p>
                <p style="margin-top: 0.5rem;">ابدأ بإنشاء شراء جديد</p>
            </div>
        @endif
    </div>

@endsection
