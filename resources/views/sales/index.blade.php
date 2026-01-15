@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة المبيعات</h1>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <form method="GET" action="{{ route('sales.index') }}" style="display: flex; gap: 0.5rem;">
                <input type="text"
                       name="receipt_number"
                       placeholder="البحث برقم الإيصال"
                       style="padding: 12px 20px; border: 2px solid #27ae60; border-radius: 6px; font-size: 1rem; min-width: 250px; height: 49px; box-sizing: border-box;"
                       autofocus>
            </form>
            <a href="{{ route('pos.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal; height: 49px; box-sizing: border-box;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                بيع جديد
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax({{ auth()->user()->role === 'manager' ? '200px' : '300px' }}, 1fr)); gap: 1rem; align-items: end;">

            <!-- Date Range -->
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">من تاريخ</label>
                <input type="date"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">إلى تاريخ</label>
                <input type="date"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <!-- Payment Method -->
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">طريقة الدفع</label>
                <select name="payment_method" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الطرق</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>نقدي</option>
                    <option value="debt" {{ request('payment_method') == 'debt' ? 'selected' : '' }}>دين</option>
                </select>
            </div>

            <!-- Customer -->
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">العميل</label>
                <select name="customer_id" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع العملاء</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cashier -->
            @if(auth()->user()->role === 'manager')
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الكاشير</label>
                    <select name="user_id" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                        <option value="">جميع الكاشيرين</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Receipt Number -->
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">رقم الإيصال</label>
                <input type="text"
                       name="receipt_number"
                       value="{{ request('receipt_number') }}"
                       placeholder="البحث برقم الإيصال"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <!-- Search Button -->
            <div style="display: flex; gap: 0.5rem; align-items: center; width: {{ auth()->user()->role === 'manager' ? 'calc(200% + 1rem)' : '100%' }};">
                <button type="submit"
                        style="background-color: transparent; color: #1abc9c; width: 100%; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    بحث
                </button>
            </div>

        </form>
    </div>

    <!-- Summary Cards -->
    @if(auth()->user()->role === 'manager')
        @if($totalAmount > 0 || $totalProfit > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="card" style="text-align: center;">
                    <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي المبيعات</h3>
                    <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                        ${{ number_format($totalAmount, 2) }}
                    </div>
                    <div style="color: #7f8c8d; font-size: 0.9rem;">
                        {{ number_format($totalAmount * 89500) }} ل.ل.
                    </div>
                </div>

                <div class="card" style="text-align: center;">
                    <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الربح</h3>
                    <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                        ${{ number_format($totalProfit, 2) }}
                    </div>
                    <div style="color: #7f8c8d; font-size: 0.9rem;">
                        {{ number_format($totalProfit * 89500) }} ل.ل.
                    </div>
                    <div style="color: #7f8c8d; font-size: 0.9rem;">
                        {{ $totalAmount > 0 ? number_format(($totalProfit / $totalAmount) * 100, 1) : 0 }}% هامش ربح
                    </div>
                </div>

                <div class="card" style="text-align: center;">
                    <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">عدد المبيعات</h3>
                    <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                        {{ $totalSalesCount }}
                    </div>
                    <div style="color: #7f8c8d; font-size: 0.9rem;">
                        {{ $totalSalesCount > 0 ? number_format($totalAmount / $totalSalesCount, 2) : 0 }}$ متوسط البيع
                    </div>
                    <div style="color: #7f8c8d; font-size: 0.85rem;">
                        {{ $voidedSalesCount }} مبيعات ملغاة
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Sales Table -->
    <div class="card">
        @if($sales->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">رقم الإيصال</th>
                    <th style="text-align: center; vertical-align: middle;">التاريخ</th>
                    <th style="text-align: center; vertical-align: middle;">الكاشير</th>
                    <th style="text-align: center; vertical-align: middle;">العميل</th>
                    <th style="text-align: center; vertical-align: middle;">المبلغ</th>
                    <th style="text-align: center; vertical-align: middle;">طريقة الدفع</th>
                    <th style="text-align: center; vertical-align: middle;">الحالة</th>
                    <th style="text-align: center; vertical-align: middle;">العمليات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sales as $sale)
                    <tr>
                        <td style="text-align: center; vertical-align: middle;">
                            <strong>{{ $sale->receipt_number }}</strong>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div>{{ $sale->sale_date->format('Y-m-d') }}</div>
                            <div style="color: #7f8c8d;">{{ $sale->sale_date->format('H:i:s') }}</div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">{{ $sale->user->name }}</td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if($sale->customer)
                                <div>{{ $sale->customer->name }}</div>
                                @if($sale->customer->phone)
                                    <div style="font-size: 0.85rem; color: #7f8c8d;">{{ $sale->customer->phone }}</div>
                                @endif
                            @else
                                <span style="color: #7f8c8d;">غير محدد</span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="font-weight: 600;">${{ number_format($sale->total_amount, 2) }}</div>
                            <div style="font-size: 0.85rem; color: #7f8c8d;">{{ number_format($sale->total_amount_lbp) }} ل.ل. </div>
                            @if($sale->discount_amount > 0)
                                <div style="font-size: 0.8rem; color: #f39c12;">خصم: ${{ number_format($sale->discount_amount, 2) }}</div>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <span style="color: {{ $sale->payment_method === 'cash' ? '#27ae60' : '#f39c12' }}; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                {{ $sale->payment_method === 'cash' ? 'نقدي' : 'دين' }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            @if($sale->is_voided)
                                <span style="color: #e74c3c; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                    ملغي
                                </span>
                            @elseif($sale->isFullyReturned())
                                <span style="color: #f39c12; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                    مرتجع بالكامل
                                </span>
                            @elseif($sale->payment_method === 'debt')
                                @if($sale->debt_amount > 0)
                                    <span style="color: #f39c12; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                        دين مستحق
                                    </span>
                                @else
                                    <span style="color: #27ae60; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                        مدفوع
                                    </span>
                                @endif
                            @else
                                <span style="color: #27ae60; padding: 6px 13px; border-radius: 6px; font-size: 0.85rem; font-weight: 1000; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                    مكتمل
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="{{ route('sales.show', $sale) }}"
                                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 1px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                                <a href="{{ route('sales.receipt', $sale) }}"
                                   target="_blank"
                                   style="display: inline-block; background-color: transparent; color: #3498db; padding: 6px 8px; border: 1px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(52,152,219,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    إيصال
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            {{ $sales->appends(request()->except('per_page'))->links('pagination.material') }}
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <h2>لا توجد مبيعات</h2>
                <h4>لم يتم العثور على مبيعات تطابق معايير البحث</h4>
                @if(request()->hasAny(['date_from', 'date_to', 'payment_method', 'customer_id', 'user_id', 'receipt_number']))
                    <a href="{{ route('sales.index') }}"
                       style="display: inline-block; margin-top: 1rem; background-color: transparent; color: #1abc9c; padding: 12px 24px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                       onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        عرض جميع المبيعات
                    </a>
                @endif
            </div>
        @endif
    </div>

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
                message: 'هل أنت متأكد من إلغاء هذا البيع؟ هذا الإجراء لا يمكن التراجع عنه.',
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

        // Auto-submit form on date change
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value) {
                        this.form.submit();
                    }
                });
            });
        });
    </script>
@endpush

