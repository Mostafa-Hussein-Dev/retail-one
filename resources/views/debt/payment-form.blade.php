@extends('layouts.app')

@section('title', 'تسجيل دفعة')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تسجيل دفعة</h1>
        <button onclick="history.back()"
                style="background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                onmouseout="this.style.backgroundColor='transparent'">
            رجوع
        </button>
    </div>

    <!-- Sale and Customer Info -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; padding: 1.5rem;">
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">العميل</div>
                <div style="font-weight: 600; font-size: 1.1rem;">{{ $customer->name }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">رقم الإيصال</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: #1abc9c;">
                    <a href="{{ route('sales.show', $sale) }}" style="color: #1abc9c; text-decoration: none;">
                        {{ $sale->receipt_number }}
                    </a>
                </div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">تاريخ البيع</div>
                <div style="font-weight: 600; font-size: 1.1rem;">{{ $sale->sale_date->format('Y-m-d') }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي البيع</div>
                <div style="font-weight: 600; font-size: 1.1rem;">${{ number_format($sale->total_amount, 2) }}</div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">المبلغ المتبقي</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: #e74c3c;">
                    ${{ number_format($sale->debt_amount, 2) }}
                </div>
            </div>
            <div>
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">المدفوع سابقاً</div>
                <div style="font-weight: 600; font-size: 1.1rem; color: #27ae60;">
                    ${{ number_format($sale->getTotalPaid(), 2) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="card">
        <h3 style="padding: 1.5rem 1.5rem 0.5rem; margin: 0;">تسجيل دفعة جديدة</h3>

        @if (session('error'))
            <div style="background: #fadbd8; border: 1px solid #e74c3c; color: #e74c3c; padding: 1rem; border-radius: 6px; margin: 1rem 1.5rem 0;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('debt.record-payment', [$customer, $sale]) }}" style="padding: 1.5rem;">
            @csrf

            <div style="max-width: 600px; margin: 0 auto;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">
                    مبلغ الدفعة ($) <span style="color: #e74c3c;">*</span>
                </label>
                <input type="number"
                       name="amount"
                       step="0.01"
                       min="0.01"
                       max="{{ $sale->debt_amount }}"
                       required
                       value="{{ old('amount') }}"
                       placeholder="أدخل المبلغ"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px; font-size: 1.1rem; margin-bottom: 0.5rem;">

                <div style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>الحد الأقصى للدفعة:</span>
                        <span style="font-weight: 600; color: #e74c3c;">${{ number_format($sale->debt_amount, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>المتبقي بعد الدفعة:</span>
                        <span id="remaining" style="font-weight: 600; color: #27ae60;">
                            ${{ number_format($sale->debt_amount, 2) }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>نسبة السداد:</span>
                        <span id="percentage" style="font-weight: 600;">0%</span>
                    </div>
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <button type="submit" style="background-color: transparent; color:#27ae60; padding: 12px 100px; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                        تأكيد الدفعة
                    </button>
                    <button type="button" onclick="window.location.href='{{ route('customers.show', $customer) }}'"
                            style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                            onmouseout="this.style.backgroundColor='transparent'">
                        إلغاء
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const amountInput = document.querySelector('input[name="amount"]');
                const remainingDisplay = document.getElementById('remaining');
                const percentageDisplay = document.getElementById('percentage');
                const maxAmount = {{ $sale->debt_amount }};

                function updateCalculation() {
                    const amount = parseFloat(amountInput.value) || 0;
                    const remaining = maxAmount - amount;
                    const percentage = (amount / maxAmount) * 100;

                    remainingDisplay.textContent = '$' + remaining.toFixed(2);
                    percentageDisplay.textContent = percentage.toFixed(1) + '%';

                    // Update colors based on remaining
                    if (remaining <= 0) {
                        remainingDisplay.style.color = '#27ae60';
                        percentageDisplay.style.color = '#27ae60';
                    } else if (remaining < maxAmount * 0.5) {
                        remainingDisplay.style.color = '#f39c12';
                        percentageDisplay.style.color = '#f39c12';
                    } else {
                        remainingDisplay.style.color = '#e74c3c';
                        percentageDisplay.style.color = '#e74c3c';
                    }
                }

                amountInput.addEventListener('input', updateCalculation);
                updateCalculation(); // Run on page load
            });
        </script>
    @endpush

@endsection
