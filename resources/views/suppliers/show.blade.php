@extends('layouts.app')

@section('title', 'تفاصيل المورد')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>{{ $supplier->name }}</h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('suppliers.edit', $supplier) }}"
               style="display: inline-block; background-color: transparent; color: #f39c12; padding: 12px 50px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                تعديل
            </a>
            <a href="{{ route('suppliers.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 50px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Supplier Info Card -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body" style="padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <h3 style="color: #1abc9c; margin-bottom: 1rem; font-size: 1.1rem;">معلومات المورد</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>الشخص المسؤول:</strong> {{ $supplier->contact_person ?? '-' }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>الهاتف:</strong> {{ $supplier->phone ?? '-' }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>العنوان:</strong> {{ $supplier->address ?? '-' }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>الحالة:</strong>
                        @if($supplier->is_active)
                            <span style="background: #27ae60; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">نشط</span>
                        @else
                            <span style="background: #95a5a6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">غير نشط</span>
                        @endif
                    </p>
                </div>

                <div>
                    <h3 style="color: #1abc9c; margin-bottom: 1rem; font-size: 1.1rem;">معلومات الحساب</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>المديونية الحالية:</strong>
                        <span style="color: {{ $currentDebt > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 700; font-size: 1.3rem;">
                            ${{ number_format($currentDebt, 2) }}
                        </span>
                    </p>
                    <p style="margin-bottom: 0.5rem;"><strong>إجمالي المشتريات:</strong> ${{ number_format($supplier->getTotalPurchases(), 2) }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>حالة المديونية:</strong>
                        <span style="color: {{ $supplier->getDebtStatusColor() }}; font-weight: 600;">
                            {{ $supplier->getDebtStatusText() }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchases with Debt -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">المشتريات غير المسددة</h3>
        </div>
        <div style="padding: 1.5rem;">
            @if($purchasesWithDebt->count() > 0)
                <table class="table">
                    <thead>
                    <tr>
                        <th>رقم الشراء</th>
                        <th>التاريخ</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الإجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchasesWithDebt as $purchase)
                        <tr>
                            <td><a href="{{ route('purchases.show', $purchase) }}" style="color: #3498db; text-decoration: none; font-weight: 600;">{{ $purchase->purchase_number }}</a></td>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>${{ number_format($purchase->total_amount, 2) }}</td>
                            <td>${{ number_format($purchase->paid_amount, 2) }}</td>
                            <td style="color: #e74c3c; font-weight: 600;">
                                ${{ number_format($purchase->debt_amount, 2) }}
                            </td>
                            <td>
                                <a href="{{ route('supplier-debt.payment-form', [$supplier, $purchase]) }}"
                                   style="background: #27ae60; color: white; padding: 6px 20px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                                    دفع
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 2rem; color: #27ae60;">
                    <p style="font-size: 1.1rem;">✓ لا توجد مشتريات غير مسددة</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Paid Purchases -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">المشتريات المسددة</h3>
        </div>
        <div style="padding: 1.5rem;">
            @if($paidPurchases->count() > 0)
                <table class="table">
                    <thead>
                    <tr>
                        <th>رقم الشراء</th>
                        <th>التاريخ</th>
                        <th>الإجمالي</th>
                        <th>المبلغ المدفوع</th>
                        <th>تاريخ الدفع</th>
                        <th>الإجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($paidPurchases as $purchase)
                        <tr>
                            <td><a href="{{ route('purchases.show', $purchase) }}" style="color: #3498db; text-decoration: none; font-weight: 600;">{{ $purchase->purchase_number }}</a></td>
                            <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                            <td>${{ number_format($purchase->total_amount, 2) }}</td>
                            <td style="color: #27ae60; font-weight: 600;">
                                ${{ number_format($purchase->paid_amount, 2) }}
                            </td>
                            <td>
                                @if($purchase->debtTransactions->where('transaction_type', 'payment')->whereNull('voided_at')->count() > 0)
                                    {{ $purchase->debtTransactions->where('transaction_type', 'payment')->whereNull('voided_at')->sortByDesc('created_at')->first()->created_at->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}"
                                   style="color: #3498db; text-decoration: none; font-size: 0.9rem;">
                                    عرض
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    <p style="font-size: 1.1rem;">لا توجد مشتريات مسددة</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">سجل المعاملات</h3>
        </div>
        <div style="padding: 1.5rem;">
            @if($transactions->count() > 0)
                <table class="table">
                    <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>الشراء</th>
                        <th>الرصيد</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($transactions as $transaction)
                        <tr style="{{ $transaction->isVoided() ? 'text-decoration: line-through; opacity: 0.5;' : '' }}">
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span style="background: {{ $transaction->getTypeColor() }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
                                    {{ $transaction->getTypeText() }}
                                </span>
                                @if($transaction->isVoided())
                                    <span style="background: #95a5a6; color: white; padding: 4px 8px; border-radius: 8px; font-size: 0.75rem; margin-right: 0.5rem;">ملغي</span>
                                @endif
                            </td>
                            <td style="color: {{ $transaction->amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600; font-size: 1.05rem;">
                                {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>
                                @if($transaction->purchase)
                                    <a href="{{ route('purchases.show', $transaction->purchase) }}" style="color: #3498db; text-decoration: none;">{{ $transaction->purchase->purchase_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="font-weight: 600; color: #1abc9c;">
                                ${{ number_format($transaction->running_balance ?? 0, 2) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                    <p style="font-size: 1.1rem;">لا يوجد سجل معاملات</p>
                </div>
            @endif
        </div>
    </div>

@endsection
