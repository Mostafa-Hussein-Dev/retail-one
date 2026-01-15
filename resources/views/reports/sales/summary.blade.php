@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>ملخص المبيعات</h1>
        <a href="{{ route('reports.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للرئيسية
        </a>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 1.5rem;">
            <form method="GET">
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; align-items: end;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">من تاريخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">إلى تاريخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">طريقة الدفع</label>
                        <select name="payment_method"
                                style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                            <option value="">الكل</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                            <option value="debt" {{ request('payment_method') == 'debt' ? 'selected' : '' }}>دين</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                style="width: 100%; background-color: #1abc9c; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#16a085'"
                                onmouseout="this.style.backgroundColor='#1abc9c'">
                            بحث
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Buttons -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <a href="{{ route('reports.sales.export', request()->all()) }}"
           style="display: inline-block; background-color: #27ae60; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#229954'"
           onmouseout="this.style.backgroundColor='#27ae60'">
            تصدير Excel
        </a>
        <a href="{{ route('reports.sales.pdf', request()->all()) }}"
           style="display: inline-block; background-color: #e74c3c; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#c0392b'"
           onmouseout="this.style.backgroundColor='#e74c3c'">
            تصدير PDF
        </a>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">عدد المبيعات</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">{{ $totalSalesCount }}</div>
            </div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الإيرادات</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">${{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">النقد</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">${{ number_format($totalCashCollected, 2) }}</div>
            </div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الديون</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #f39c12;">${{ number_format($totalDebtCreated, 2) }}</div>
            </div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">متوسط البيع</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;">${{ number_format($averageSaleValue, 2) }}</div>
            </div>
        </div>
        <div class="card" style="text-align: center;">
            <div style="padding: 1rem;">
                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الملغاة</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #95a5a6;">{{ $voidedSalesCount }}</div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">رقم الإيصال</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">التاريخ</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">العميل</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإجمالي</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الطريقة</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">المستخدم</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $sale->receipt_number }}</td>
                        <td style="padding: 0.75rem;">{{ $sale->sale_date->format('Y-m-d H:i') }}</td>
                        <td style="padding: 0.75rem;">{{ $sale->customer?->name ?? 'زبون نقدي' }}</td>
                        <td style="padding: 0.75rem; text-align: left;"><strong>${{ number_format($sale->total_amount, 2) }}</strong></td>
                        <td style="padding: 0.75rem; text-align: center;">
                            @if($sale->payment_method == 'cash')
                                <span style="background: #27ae60; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem;">نقدي</span>
                            @else
                                <span style="background: #f39c12; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem;">دين</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $sale->user->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد مبيعات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($sales->hasPages())
            <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                {{ $sales->links() }}

            </div>
            @endif
        </div>
    </div>

@endsection
