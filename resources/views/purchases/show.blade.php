@extends('layouts.app')

@section('title', 'تفاصيل الشراء')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تفاصيل الشراء</h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('purchases.receipt', $purchase) }}"
               target="_blank"
               style="background-color: transparent; color: #3498db; padding: 12px 50px; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                طباعة الإيصال
            </a>
            @if($purchase->debt_amount > 0 && !$purchase->is_voided)
                <a href="{{ route('supplier-debt.payment-form', [$purchase->supplier, $purchase]) }}"
                   style="background-color: transparent; color: #27ae60; padding: 12px 50px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
                   onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                   onmouseout="this.style.backgroundColor='transparent'">
                    تسديد الدين
                </a>
            @endif
            @if(auth()->user()->role === 'manager' && !$purchase->is_voided)
                <button onclick="showVoidModal()"
                        style="background-color: transparent; color: #e74c3c; padding: 12px 50px; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    إلغاء الشراء
                </button>
            @endif
            <a href="{{ route('purchases.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 50px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
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
                    <p style="margin-bottom: 0.5rem;"><strong>المورد:</strong> {{ $purchase->supplier->name }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>التاريخ:</strong> {{ $purchase->purchase_date->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p style="margin-bottom: 0.5rem;"><strong>الموظف:</strong> {{ $purchase->user->name }}</p>
                    <p style="margin-bottom: 0.5rem;"><strong>الحالة:</strong>
                        <span style="background: {{ $purchase->getStatusColor() }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
                            {{ $purchase->getStatusText() }}
                        </span>
                    </p>
                    @if($purchase->is_voided)
                        <p style="margin-bottom: 0.5rem; color: #e74c3c;"><strong>سبب الإلغاء:</strong> {{ $purchase->void_reason }}</p>
                    @endif
                </div>
                <div>
                    <p style="margin-bottom: 0.5rem;"><strong>الإجمالي:</strong> <span style="color: #1abc9c; font-weight: 600; font-size: 1.1rem;">${{ number_format($purchase->total_amount, 2) }}</span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>المدفوع:</strong> <span style="color: #27ae60; font-weight: 600;">${{ number_format($purchase->paid_amount, 2) }}</span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>المتبقي:</strong> <span style="color: {{ $purchase->debt_amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">${{ number_format($purchase->debt_amount, 2) }}</span></p>
                </div>
            </div>
            @if($purchase->notes)
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
                    <p style="margin-bottom: 0;"><strong>ملاحظات:</strong> {{ $purchase->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0; color: #1abc9c;">المنتجات</h3>
        </div>
        <div style="padding: 1.5rem;">
            <table class="table">
                <thead>
                <tr>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
                </thead>
                <tbody>
                @foreach($purchase->purchaseItems as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>${{ number_format($item->unit_cost, 2) }}</td>
                        <td>${{ number_format($item->total_cost, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr style="background: #f8f9fa; font-weight: 600;">
                    <td colspan="3" style="text-align: left;"><strong>الإجمالي:</strong></td>
                    <td style="color: #1abc9c; font-size: 1.1rem;">${{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Debt Transactions -->
    @if($purchase->debtTransactions->count() > 0)
        <div class="card">
            <div style="background: #f8f9fa; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6;">
                <h3 style="margin: 0; color: #1abc9c;">سجل المعاملات</h3>
            </div>
            <div style="padding: 1.5rem;">
                <table class="table">
                    <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>المبلغ</th>
                        <th>الوصف</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchase->debtTransactions as $transaction)
                        <tr style="{{ $transaction->isVoided() ? 'opacity: 0.5;' : '' }}">
                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span style="background: {{ $transaction->getTypeColor() }}; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">
                                    {{ $transaction->getTypeText() }}
                                </span>
                                @if($transaction->isVoided())
                                    <span style="background: #95a5a6; color: white; padding: 4px 8px; border-radius: 8px; font-size: 0.75rem; margin-right: 0.5rem;">ملغي</span>
                                @endif
                            </td>
                            <td style="color: {{ $transaction->amount > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                {{ $transaction->amount > 0 ? '+' : '' }}${{ number_format($transaction->amount, 2) }}
                            </td>
                            <td>{{ $transaction->description }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Void Modal -->
    @if(auth()->user()->role === 'manager' && !$purchase->is_voided)
        <div id="voidModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
            <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%;">
                <h2 style="color: #e74c3c; margin-bottom: 1rem;">إلغاء الشراء</h2>
                <p style="margin-bottom: 1rem;">هل أنت متأكد من إلغاء الشراء رقم {{ $purchase->purchase_number }}؟</p>
                <p style="margin-bottom: 1rem; color: #e74c3c;"><strong>تحذير:</strong> سيتم خصم الكمية من المخزون وإلغاء جميع المعاملات المرتبطة.</p>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">سبب الإلغاء *</label>
                    <textarea id="void-reason" rows="3" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;" placeholder="أدخل سبب الإلغاء..." required></textarea>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button onclick="confirmVoid()"
                            style="flex: 1; background-color: #e74c3c; color: white; padding: 12px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        تأكيد الإلغاء
                    </button>
                    <button onclick="hideVoidModal()"
                            style="flex: 1; background-color: transparent; color: #95a5a6; padding: 12px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit;">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function showVoidModal() {
            document.getElementById('voidModal').style.display = 'flex';
        }

        function hideVoidModal() {
            document.getElementById('voidModal').style.display = 'none';
        }

        async function confirmVoid() {
            const reason = document.getElementById('void-reason').value;
            if (!reason.trim()) {
                await showAlertDialog({
                    type: 'warning',
                    title: 'تحذير',
                    message: 'يرجى إدخال سبب الإلغاء'
                });
                return;
            }

            fetch('/purchases/{{ $purchase->id }}/void', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(async data => {
                if (data.success) {
                    await showAlertDialog({
                        type: 'success',
                        title: 'نجاح',
                        message: data.message
                    });
                    window.location.href = '/purchases/{{ $purchase->id }}';
                } else {
                    await showAlertDialog({
                        type: 'error',
                        title: 'خطأ',
                        message: 'حدث خطأ: ' + data.message
                    });
                }
            })
            .catch(async error => {
                console.error('Error:', error);
                await showAlertDialog({
                    type: 'error',
                    title: 'خطأ',
                    message: 'حدث خطأ أثناء إلغاء الشراء'
                });
            });
        }

        // Close modal when clicking outside
        document.getElementById('voidModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                hideVoidModal();
            }
        });
    </script>

@endsection
