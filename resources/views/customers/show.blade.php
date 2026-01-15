@extends('layouts.app')

@section('title', 'تفاصيل العميل')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>{{ $customer->name }}</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('customers.edit', $customer) }}"
               style="display: inline-block; background-color: transparent; color: #f39c12; padding: 12px 40px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                تعديل
            </a>
            <form id="deleteCustomerForm" method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline-block;">
                @csrf
                @method('DELETE')
            </form>
            <button type="button" onclick="confirmDeleteCustomer()"
                    style="background-color: transparent; color: #e74c3c; padding: 12px 40px; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                حذف
            </button>
            <a href="{{ route('customers.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Customer Info Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; padding: 1.5rem;">
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">الهاتف</div>
                <div style="font-weight: 600; font-size: 1.1rem;">{{ $customer->phone ?? '-' }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">العنوان</div>
                <div style="font-weight: 600; font-size: 1.1rem;">{{ $customer->address ?? '-' }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">حد الائتمان</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: #1abc9c;">${{ number_format($customer->credit_limit, 2) }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">المديونية الحالية</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: {{ $currentDebt > 0 ? '#e74c3c' : '#27ae60' }};">
                    ${{ number_format($currentDebt, 2) }}
                </div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">نسبة الاستخدام</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: {{ $creditUtilization > 80 ? '#e74c3c' : '#27ae60' }};">
                    {{ number_format($creditUtilization, 1) }}%
                </div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">الحالة</div>
                <span style="background: {{ $customer->is_active ? '#27ae60' : '#9BB3CC' }}; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center;">
                    {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Sales with Debt -->
    @if($salesWithDebt->count() > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="padding: 1.5rem 1.5rem 0.5rem; margin: 0;">المبيعات غير المسددة</h3>
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">رقم الإيصال</th>
                    <th style="text-align: center; vertical-align: middle;">التاريخ</th>
                    <th style="text-align: center; vertical-align: middle;">الإجمالي</th>
                    <th style="text-align: center; vertical-align: middle;">المتبقي</th>
                    <th style="text-align: center; vertical-align: middle;">الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($salesWithDebt as $sale)
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            <a href="{{ route('sales.show', $sale) }}" style="color: #1abc9c; text-decoration: none; font-weight: 600;">
                                {{ $sale->receipt_number }}
                            </a>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            {{ $sale->sale_date->format('Y-m-d') }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            ${{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="color: #e74c3c; font-weight: 600;">
                                ${{ number_format($sale->debt_amount, 2) }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: center;">
                                <a href="{{ route('sales.show', $sale) }}"
                                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                                <a href="{{ route('debt.payment-form', [$customer, $sale]) }}"
                                   style="display: inline-block; background-color: transparent; color: #27ae60; padding: 6px 8px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    دفع
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Fully Paid Debt Sales -->
    @if($fullyPaidSales->count() > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="padding: 1.5rem 1.5rem 0.5rem; margin: 0;">المبيعات المسددة بالكامل</h3>
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">رقم الإيصال</th>
                    <th style="text-align: center; vertical-align: middle;">التاريخ</th>
                    <th style="text-align: center; vertical-align: middle;">الإجمالي</th>
                    <th style="text-align: center; vertical-align: middle;">المدفوع</th>
                    <th style="text-align: center; vertical-align: middle;">الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($fullyPaidSales as $sale)
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            <a href="{{ route('sales.show', $sale) }}" style="color: #1abc9c; text-decoration: none; font-weight: 600;">
                                {{ $sale->receipt_number }}
                            </a>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            {{ $sale->sale_date->format('Y-m-d') }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            ${{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="color: #27ae60; font-weight: 600;">
                                ${{ number_format($sale->getTotalPaid(), 2) }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <a href="{{ route('sales.show', $sale) }}"
                               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                               onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                               onmouseout="this.style.backgroundColor='transparent'">
                                عرض
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Transaction History -->
    <div class="card">
        <h3 style="padding: 1.5rem 1.5rem 0.5rem; margin: 0;">سجل المعاملات</h3>

        <!-- Transaction Filter -->
        <div style="padding: 0 1.5rem 1rem;">
            <form method="GET" style="display: flex; align-items: center; gap: 1rem;">
                <label style="font-weight: 600;">تصفية حسب البيع:</label>
                <select name="sale_filter" style="padding: 8px 13px; border: 1px solid #ccc; border-radius: 6px;" onchange="this.form.submit()">
                    <option value="">جميع المبيعات</option>
                    @foreach($salesWithDebt as $sale)
                        <option value="{{ $sale->id }}" {{ $saleFilter == $sale->id ? 'selected' : '' }}>
                            {{ $sale->receipt_number }} - {{ number_format($sale->total_amount, 2) }}$
                        </option>
                    @endforeach
                    @foreach($fullyPaidSales as $sale)
                        <option value="{{ $sale->id }}" {{ $saleFilter == $sale->id ? 'selected' : '' }}>
                            {{ $sale->receipt_number }} - {{ number_format($sale->total_amount, 2) }}$ (مدفوع)
                        </option>
                    @endforeach
                    @foreach($customer->sales()->where('is_voided', true)->get() as $voidedSale)
                        <option value="{{ $voidedSale->id }}" {{ $saleFilter == $voidedSale->id ? 'selected' : '' }}>
                            {{ $voidedSale->receipt_number }} - {{ number_format($voidedSale->total_amount, 2) }}$ (ملغي)
                        </option>
                    @endforeach
                </select>
                @if($saleFilter)
                    <a href="{{ route('customers.show', $customer) }}"
                       style="color: #e74c3c; text-decoration: none; font-weight: 600;">
                        ✕ إلغاء التصفية
                    </a>
                @endif
            </form>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th style="text-align: center; vertical-align: middle;">البيع</th>
                <th style="text-align: center; vertical-align: middle;">التاريخ والوقت</th>
                <th style="text-align: center; vertical-align: middle;">النوع</th>
                <th style="text-align: center; vertical-align: middle;">المبلغ</th>
                <th style="text-align: center; vertical-align: middle;">الرصيد</th>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr style="{{ $transaction->isVoided() ? 'opacity: 0.5; position: relative;' : '' }}" class="{{ $transaction->isVoided() ? 'voided-transaction' : '' }}">
                    <td style="text-align: center; vertical-align: middle;">
                        @if($transaction->sale)
                            <a href="{{ route('sales.show', $transaction->sale) }}" style="color: #1abc9c; text-decoration: none;">
                                {{ $transaction->sale->receipt_number }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        {{ $transaction->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <span class="badge" style="background: {{ $transaction->getTypeColor() }};color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">
                            {{ $transaction->getTypeText() }}
                        </span>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <span style="color: {{ $transaction->amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                            ${{ number_format(abs($transaction->amount), 2) }}
                        </span>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($transaction->isVoided())
                            <span class="badge badge-secondary">ملغي</span>
                        @else
                            <span style="color: {{ $transaction->running_balance > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                ${{ number_format($transaction->running_balance, 2) }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <style>
        .voided-transaction::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 2px;
            background: #7f8c8d;
            z-index: 10;
            pointer-events: none;
        }
    </style>

@push('scripts')
    <script>
        async function confirmDeleteCustomer() {
            const confirmed = await showConfirmDialog({
                type: 'error',
                title: 'تأكيد الحذف',
                message: 'هل أنت متأكد من حذف هذا العميل؟ لا يمكن التراجع عن هذا الإجراء.',
                confirmText: 'نعم، احذف',
                cancelText: 'إلغاء'
            });

            if (confirmed) {
                document.getElementById('deleteCustomerForm').submit();
            }
        }
    </script>
@endpush

@endsection
