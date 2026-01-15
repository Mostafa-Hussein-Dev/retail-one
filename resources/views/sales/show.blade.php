@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تفاصيل البيع</h1>
        <div style="display: flex; gap: 1rem;">

            <a href="{{ route('sales.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Sale Header Information -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Sale Info -->
            <div>
                <h3 style="margin-bottom: 1rem; color: #2c3e50;">معلومات البيع</h3>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 0.5rem 1rem; margin-bottom: 1rem;">
                    <strong>رقم الإيصال:</strong>
                    <span>{{ $sale->receipt_number }}</span>

                    <strong>تاريخ البيع:</strong>
                    <span>{{ $sale->sale_date->format('H:i:s Y-m-d') }}</span>

                    <strong>الكاشير:</strong>
                    <span>{{ $sale->user->name }}</span>

                    <strong>العميل:</strong>
                    @if($sale->customer)
                        <a style="color: #1abc9c" href="{{ route('dashboard') }}">{{ $sale->customer->name}}</a>
                    @else
                        <span >غير مسجل</span>
                    @endif

                    @if($sale->notes)
                        <strong>ملاحظات:</strong>
                        <span>{{ $sale->notes }}</span>
                    @endif

                </div>


            </div>

            <!-- Payment Info -->
            <div>
                <h3 style="margin-bottom: 1rem; color: #2c3e50;">معلومات الدفع</h3>

                <!-- Payment Status -->
                <div style="text-align: center; margin-top: 1rem;">
                    @if($sale->payment_method === 'cash')
                        <div style="background: #e8f5e8; border: 1px solid #27ae60; padding: 1rem; border-radius: 6px; border-left: 4px solid #27ae60;">
                            <strong style="color: #155724;">نقدي</strong>
                        </div>
                    @elseif($sale->payment_method === 'debt')
                        <div style="background: #fdf7e8; border: 1px solid #f39c12; padding: 1rem; border-radius: 6px; border-left: 4px solid #f39c12;">
                            <strong style="color: #856404;">دين</strong>
                        </div>
                    @endif
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    @if($sale->is_voided)
                        <div style="background: #fdeaea; border: 1px solid #e74c3c; padding: 1rem; border-radius: 6px; border-left: 4px solid #e74c3c;">
                            <strong style="color: #c0392b;">تم إلغاء هذا البيع</strong>
                            @if($sale->void_reason || $sale->voidedBy || $sale->voided_at)
                                <div style="font-size: 0.85rem; color: #c0392b; margin-top: 0.5rem;">
                                    @if($sale->void_reason)
                                        السبب: {{ $sale->void_reason }}
                                    @endif

                                    @if($sale->void_reason && ($sale->voidedBy || $sale->voided_at))
                                        |
                                    @endif

                                    @if($sale->voidedBy)
                                        بواسطة: {{ $sale->voidedBy->name }}
                                    @endif

                                    @if($sale->voidedBy && $sale->voided_at)
                                        |
                                    @endif

                                    @if($sale->voided_at)
                                        عند: {{ $sale->voided_at->format('H:i:s Y-m-d') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    @elseif($sale->isFullyReturned())
                        <div style="background: #fdf7e8; border: 1px solid #f39c12; padding: 1rem; border-radius: 6px; border-left: 4px solid #f39c12;">
                            <strong style="color: #856404;">مرتجع بالكامل</strong>
                        </div>
                    @elseif($sale->payment_method === 'debt' && $sale->debt_amount > 0)
                        @php
                            $paidAmount = $sale->getTotalPaid();
                            // Calculate debt reduction from returns
                            $debtReductionFromReturns = $sale->returns()
                                ->where('is_voided', false)
                                ->sum('debt_reduction_amount');
                            // Calculate remaining debt
                            $originalDebt = $sale->total_amount;
                            $remainingDebt = $sale->debt_amount;
                        @endphp
                        <div style="background: #fdf7e8; border: 1px solid #f39c12; padding: 1rem; border-radius: 6px; border-left: 4px solid #f39c12;">
                            @if($paidAmount > 0 || $debtReductionFromReturns > 0)
                                <strong style="color: #856404;">
                                    الدين الأصلي: ${{ number_format($originalDebt, 2) }}
                                    @if($debtReductionFromReturns > 0)
                                        (قيمة المرتجعات: ${{ number_format($debtReductionFromReturns, 2) }})
                                    @endif
                                    <br>
                                    الباقي المستحق: ${{ number_format($remainingDebt, 2) }}
                                </strong>
                            @else
                                <strong style="color: #856404;">دين مستحق: ${{ number_format($remainingDebt, 2) }}</strong>
                            @endif
                        </div>
                    @else
                        <div style="background: #e8f5e8; border: 1px solid #27ae60; padding: 1rem; border-radius: 6px; border-left: 4px solid #27ae60;">
                            <strong style="color: #155724;">تم الدفع كاملاً</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Items -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">تفاصيل المنتجات</h3>

        <table class="table">
            <thead>
            <tr>
                <th style="text-align: center; vertical-align: middle;">المنتج</th>
                <th style="text-align: center; vertical-align: middle;">الفئة</th>
                <th style="text-align: center; vertical-align: middle;">الكمية</th>
                <th style="text-align: center; vertical-align: middle;">سعر الوحدة</th>
                <th style="text-align: center; vertical-align: middle;">المجموع الفرعي</th>
                <th style="text-align: center; vertical-align: middle;">الخصم</th>
                <th style="text-align: center; vertical-align: middle;">الإجمالي</th>
                @if(auth()->user()->role === 'manager')
                <th style="text-align: center; vertical-align: middle;">الربح</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td style="text-align: center; vertical-align: middle;">
                        <div style="font-weight: 600;">{{ $item->product->display_name }}</div>
                        @if($item->product->barcode)
                            <div style="font-size: 0.85rem; color: #7f8c8d;">{{ $item->product->barcode }}</div>
                        @endif
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($item->product->category)
                            <span style="padding: 4px 8px; font-size: 1rem; display: inline-block;">
                                {{ $item->product->category->display_name }}
                            </span>
                        @else
                            <span style="color: #7f8c8d;">-</span>
                        @endif
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <strong>{{ number_format($item->quantity, 2) }}</strong>
                        <div style="font-size: 0.85rem; color: #7f8c8d;">{{ $item->product->unit_display }}</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <strong>${{ number_format($item->unit_price, 2) }}</strong>
                        <div style="font-size: 0.85rem; color: #7f8c8d;">{{ number_format($item->unit_price * 89500) }} ل.ل.</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @php
                            $subtotal = $item->unit_price * $item->quantity;
                        @endphp
                        <strong>${{ number_format($subtotal, 2) }}</strong>
                        <div style="font-size: 0.85rem; color: #7f8c8d;">{{ number_format($subtotal * 89500) }} ل.ل.</div>
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        @if($item->discount_amount > 0)
                            <div style="color: #e74c3c;">
                                ${{ number_format($item->discount_amount, 2) }}
                                @if($item->discount_percentage > 0)
                                    <div style="font-size: 0.8rem;">({{ number_format($item->discount_percentage, 2) }}%)</div>
                                @endif
                            </div>
                        @else
                            <span style="color: #7f8c8d;">0%</span>
                        @endif
                    </td>
                    <td style="text-align: center; vertical-align: middle;">
                        <strong>${{ number_format($item->total_price, 2) }}</strong>
                        <div style="font-size: 0.8rem; color: #7f8c8d;">
                            {{ number_format($item->total_price * 89500) }} ل.ل.
                        </div>
                    </td>
                    @if(auth()->user()->role === 'manager')
                    <td style="text-align: center; vertical-align: middle;">
                        <div style="color: {{ $item->profit >= 0 ? '#27ae60' : '#e74c3c' }}; font-weight: 600;">
                            ${{ number_format($item->profit, 2) }}
                        </div>

                        <div style="font-size: 0.8rem; color: #7f8c8d;">
                            {{ $item->unit_cost > 0 ? number_format((($item->unit_price - $item->unit_cost) / $item->unit_cost) * 100, 2) : 0 }}%
                        </div>
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- Sale Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Financial Summary -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #1abc9c; margin-bottom: 1rem;">الملخص المالي</h3>
            @if($sale->discount_amount > 0)
                <div style="margin-bottom: 0.75rem; color: #e74c3c;">
                    <strong>إجمالي الخصم:</strong><br>
                    ${{ number_format($sale->discount_amount, 2) }}
                </div>
            @endif

            @php
                $totalReturned = $sale->getTotalReturned();
                $netTotal = $sale->total_amount - $totalReturned;
            @endphp

            @if($totalReturned > 0)
                <div style="margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">
                    <strong>الإجمالي الأصلي:</strong><br>
                    <span style="text-decoration: line-through;">${{ number_format($sale->total_amount, 2) }}</span>
                </div>
                <div style="margin-bottom: 0.5rem; color: #e74c3c; font-size: 0.95rem;">
                    <strong>ناقص المرتجع:</strong><br>
                    -${{ number_format($totalReturned, 2) }}
                </div>
            @endif

            <div style="font-size: 1.5rem; font-weight: bold; color: #1abc9c; padding-top: {{ $totalReturned > 0 ? '0.5rem' : '0.5rem' }}; border-top: {{ $totalReturned > 0 ? '2px solid #ecf0f1' : 'none' }}; margin-top: {{ $totalReturned > 0 ? '0.5rem' : '0' }};">
                <strong>الإجمالي{{ $totalReturned > 0 ? ' النهائي' : '' }}:</strong><br>
                ${{ number_format($netTotal, 2) }}
            </div>
            <div style="color: #7f8c8d; font-size: 0.9rem; margin-top: 0.5rem;">
                {{ number_format($netTotal * 89500) }} ل.ل.
            </div>
        </div>

        @if(auth()->user()->role === 'manager')
            <!-- Profit Summary -->
            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 1rem;">ملخص الربح</h3>
                @php
                    $totalProfit = $sale->saleItems->sum('profit');
                    $profitMargin = $sale->subtotal > 0 ? ($totalProfit / $sale->subtotal) * 100 : 0;
                @endphp
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c; margin-bottom: 0.5rem;">
                    ${{ number_format($totalProfit, 2) }}
                </div>
                <div style="color: #7f8c8d; margin-bottom: 1rem;">
                    {{ number_format($totalProfit * 89500) }} ل.ل.
                </div>
                <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px; border: 1px solid #eee;">
                    <strong>هامش الربح:</strong> {{ number_format($profitMargin, 1) }}%
                </div>
            </div>
        @endif

        <!-- Sale Statistics -->
        <div class="card" style="text-align: center;">
            <h3 style="color: #1abc9c; margin-bottom: 1rem;">إحصائيات البيع</h3>
            <div style="margin-bottom: 0.75rem;">
                <strong>المجموع الفرعي:</strong><br>
                <span style="font-size: 1.2rem; color: #2c3e50;">${{ number_format($sale->subtotal, 2) }}</span>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <strong>عدد الأصناف:</strong> {{ $sale->saleItems->count() }}
            </div>
            <div>
                <strong>وقت البيع:</strong> {{ $sale->sale_date->format('H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Payment Transactions -->
    @if($sale->customer && $sale->debtTransactions()->count() > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">سجل المدفوعات</h3>

            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">التاريخ والوقت</th>
                    <th style="text-align: center; vertical-align: middle;">نوع المعاملة</th>
                    <th style="text-align: center; vertical-align: middle;">المبلغ</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $transactions = $sale->debtTransactions()->orderBy('created_at', 'asc')->get();
                @endphp
                @foreach($transactions as $transaction)
                    @if(!$transaction->isVoided())
                        <tr>
                            <td style="text-align: center; vertical-align: middle;">
                                {{ $transaction->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <span class="badge" style="background: {{ $transaction->getTypeColor() }}; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">
                                    {{ $transaction->getTypeText() }}
                                </span>
                            </td>
                            <td style="text-align: center; vertical-align: middle;">
                                <span style="color: {{ $transaction->amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                    ${{ number_format(abs($transaction->amount), 2) }}
                                </span>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if($sale->debt_amount > 0)
                    <tr style="background: #f8f9fa; font-weight: 600;">
                        <td colspan="2" style="text-align: center; vertical-align: middle; padding: 1rem;">
                            المتبقي:
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding: 1rem;">
                            <span style="color: #e74c3c; font-size: 1.1rem;">
                                ${{ number_format($sale->debt_amount, 2) }}
                            </span>
                        </td>
                    </tr>
                @else
                    <tr style="background: #e8f5e8; font-weight: 600;">
                        <td colspan="2" style="text-align: center; vertical-align: middle; padding: 1rem;">
                            تم السداد بالكامل:
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding: 1rem;">
                            <span style="color: #27ae60; font-size: 1.1rem;">
                                $0.00
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @endif

    <!-- Returns Section -->
    @if($sale->returns()->where('is_voided', false)->count() > 0)
        <div class="card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">المرتجعات</h3>

            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">رقم الإرجاع</th>
                    <th style="text-align: center; vertical-align: middle;">التاريخ</th>
                    <th style="text-align: center; vertical-align: middle;">المنتجات</th>
                    <th style="text-align: center; vertical-align: middle;">المبلغ</th>
                    <th style="text-align: center; vertical-align: middle;">طريقة الاسترداد</th>
                    <th style="text-align: center; vertical-align: middle;">المعالج بواسطة</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sale->returns()->where('is_voided', false)->orderBy('created_at', 'desc')->get() as $return)
                    <tr @if($return->is_voided) style="opacity: 0.6; background-color: #f8f9fa;" @endif>
                        <td style="text-align: center; vertical-align: middle;">
                            <a href="{{ route('returns.show', $return) }}"
                               style="color: #3498db; text-decoration: none; font-weight: 600; transition: color 0.3s ease;"
                               onmouseover="this.style.color='#2980b9'"
                               onmouseout="this.style.color='#3498db'">
                                {{ $return->return_number }}
                            </a>
                            @if($return->is_voided)
                                <div style="font-size: 0.75rem; color: #e74c3c;">ملغي</div>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            {{ $return->return_date->format('Y-m-d H:i') }}
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @foreach($return->returnItems as $item)
                                <div style="font-size: 0.9rem;">
                                    {{ $item->product->display_name }} × {{ number_format($item->quantity, 2) }}
                                </div>
                            @endforeach
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="color: #e74c3c; font-weight: 600;">
                                ${{ number_format($return->total_return_amount, 2) }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span class="badge" style="background: {{ $return->getStatusColor() }}; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; display: inline-block;">
                                {{ $return->getPaymentMethodText() }}
                            </span>
                            @if($return->cash_refund_amount > 0)
                                <div style="font-size: 0.75rem; color: #27ae60; margin-top: 4px;">
                                    استرداد: ${{ number_format($return->cash_refund_amount, 2) }}
                                </div>
                            @endif
                            @if($return->debt_reduction_amount > 0)
                                <div style="font-size: 0.75rem; color: #3498db; margin-top: 4px;">
                                    تخفيض دين: ${{ number_format($return->debt_reduction_amount, 2) }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            {{ $return->user->name }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa; font-weight: 600;">
                        <td colspan="3" style="text-align: center; vertical-align: middle; padding: 1rem;">
                            إجمالي الاسترداد:
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding: 1rem;">
                            <span style="color: #e74c3c; font-size: 1.1rem;">
                                ${{ number_format($sale->getTotalReturned(), 2) }}
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <!-- Action Buttons -->
    @if(auth()->user()->role === 'manager' && !$sale->is_voided)
        <div class="card" style="text-align: center;">
            <h3 style="margin-bottom: 1.5rem; color: #2c3e50;">عمليات الإدارة</h3>
            <a href="{{ route('sales.receipt', $sale) }}"
               target="_blank"
               style="display: inline-block; background-color: transparent; color: #3498db; padding: 12px 100px; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                طباعة الإيصال
            </a>
            @if($sale->payment_method === 'debt' && $sale->debt_amount > 0 && $sale->customer)
                <a href="{{ route('debt.payment-form', [$sale->customer, $sale]) }}"
                   style="display: inline-block; background-color: transparent; color: #27ae60; padding: 12px 100px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
                   onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                   onmouseout="this.style.backgroundColor='transparent'">
                    تسجيل دفعة
                </a>
            @endif
            <button onclick="voidSale({{ $sale->id }})"
                    style="background-color: transparent; color: #e74c3c; padding: 12px 100px; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                إلغاء البيع
            </button>
            <div style="margin-top: 1rem; font-size: 0.9rem; color: #7f8c8d;">
                تحذير: إلغاء البيع سيعيد المنتجات للمخزون ولا يمكن التراجع عن هذا الإجراء
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        async function voidSale(saleId) {
            const reason = await showPromptDialog({
                type: 'warning',
                title: 'سبب الإلغاء',
                message: 'الرجاء إدخال سبب إلغاء هذا البيع:',
                placeholder: 'سبب الإلغاء...'
            });

            if (!reason || reason.trim() === '') {
                await showAlertDialog({
                    type: 'error',
                    title: 'خطأ',
                    message: 'يجب إدخال سبب الإلغاء'
                });
                return;
            }

            const confirmed = await showConfirmDialog({
                type: 'error',
                title: 'تأكيد الإلغاء',
                message: 'هل أنت متأكد من إلغاء هذا البيع؟ سيتم إعادة جميع المنتجات للمخزون وهذا الإجراء لا يمكن التراجع عنه.',
                confirmText: 'تأكيد الإلغاء',
                cancelText: 'إلغاء'
            });

            if (!confirmed) return;

            fetch(`/sales/${saleId}/void`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ reason: reason.trim() })
            })
                .then(response => response.json())
                .then(async data => {
                    if (data.success) {
                        await showAlertDialog({
                            type: 'success',
                            title: 'نجاح',
                            message: data.message
                        });
                        location.reload();
                    } else {
                        await showAlertDialog({
                            type: 'error',
                            title: 'خطأ',
                            message: data.message
                        });
                    }
                })
                .catch(async error => {
                    console.error('Error:', error);
                    await showAlertDialog({
                        type: 'error',
                        title: 'خطأ',
                        message: 'حدث خطأ في إلغاء البيع'
                    });
                });
        }
    </script>
@endpush
