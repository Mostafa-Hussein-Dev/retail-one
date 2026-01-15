@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>ملخص ديون العملاء</h1>
        <a href="{{ route('reports.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للرئيسية
        </a>
    </div>

    <!-- Summary Card -->
    <div class="card" style="margin-bottom: 2rem; text-align: center;">
        <div style="padding: 2rem;">
            <h3 style="margin: 0 0 1rem 0; color: #2c3e50;">إجمالي الديون</h3>
            <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c;">${{ number_format($totalDebt, 2) }}</div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">العميل</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">إجمالي المشتريات</th>
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المدفوع</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">المتبقي</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">آخر دفع</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $customer->name }}</td>
                        <td style="padding: 0.75rem;">${{ number_format($customer->total_purchases, 2) }}</td>
                        <td style="padding: 0.75rem;">${{ number_format($customer->total_paid, 2) }}</td>
                        <td style="padding: 0.75rem; text-align: left;"><strong class="text-danger">${{ number_format($customer->total_debt, 2) }}</strong></td>
                        <td style="padding: 0.75rem; text-align: center; color: #7f8c8d;">{{ $customer->last_payment_date?->format('Y-m-d') ?? '-' }}</td>
                        <td style="padding: 0.75rem; text-align: center;">
                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                <a href="{{ route('customers.show', $customer) }}"
                                   style="display: inline-block; background-color: transparent; color: #3498db; padding: 0.5rem 1rem; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                                   onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد ديون</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
