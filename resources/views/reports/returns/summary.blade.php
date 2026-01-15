@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">ملخص المرتجعات</h1>

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
                            <option value="cash_refund" {{ request('payment_method') == 'cash_refund' ? 'selected' : '' }}>استرداد نقدي</option>
                            <option value="debt_reduction" {{ request('payment_method') == 'debt_reduction' ? 'selected' : '' }}>تخفيض دين</option>
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
        <a href="{{ route('reports.returns.export', request()->all()) }}"
           style="display: inline-block; background-color: #27ae60; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#229954'"
           onmouseout="this.style.backgroundColor='#27ae60'">
            تصدير Excel
        </a>
        <a href="{{ route('reports.returns.pdf', request()->all()) }}"
           style="display: inline-block; background-color: #e74c3c; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#c0392b'"
           onmouseout="this.style.backgroundColor='#e74c3c'">
            تصدير PDF
        </a>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="border-right: 4px solid #3498db; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">عدد المرتجعات</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #3498db;">{{ $totalReturns }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #9b59b6; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي المبلغ</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #9b59b6;">${{ number_format($totalReturnAmount, 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #16a085; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">استرداد نقدي</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #16a085;">${{ number_format($totalCashRefund, 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #e67e22; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">تخفيض ديون</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #e67e22;">${{ number_format($totalDebtReduction, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">رقم المرتج</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">التاريخ</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإجمالي</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">الطريقة</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">السبب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $return->return_number }}</td>
                        <td style="padding: 0.75rem;">{{ $return->return_date->format('Y-m-d H:i') }}</td>
                        <td style="padding: 0.75rem; text-align: left;"><strong>${{ number_format($return->total_return_amount, 2) }}</strong></td>
                        <td style="padding: 0.75rem;">{{ $return->getPaymentMethodText() }}</td>
                        <td style="padding: 0.75rem;">{{ $return->reason }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد مرتجعات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $returns->links() }}
        </div>
    </div>

    <!-- Top Returned Products -->
    @if($topReturnedProducts->count() > 0)
    <div class="card">
        <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">المنتجات الأكثر إرجاعاً</h3>
        </div>
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المنتج</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الكمية</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">عدد مرات الإرجاع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topReturnedProducts as $product)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $product->name }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $product->total_quantity }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $product->return_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@endsection
