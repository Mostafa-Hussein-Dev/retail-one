@extends('layouts.app')

@section('title', 'تفاصيل الإرجاع')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>تفاصيل الإرجاع #{{ $return->return_number }}</h1>
    <div style="display: flex; gap: 0.5rem;">
        @if(!$return->is_voided)
            <a href="{{ route('returns.receipt', $return) }}"
               style="padding: 12px 24px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.95rem;"
               target="_blank">
                طباعة الإيصال
            </a>
            @if(auth()->user()->role === 'manager')
                <button onclick="voidReturn()"
                        style="padding: 12px 24px; background-color: #e74c3c; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 0.95rem;">
                    إلغاء الإرجاع
                </button>
            @endif
        @else
            <span style="padding: 12px 24px; background-color: #95a5a6; color: white; border-radius: 6px; font-weight: 600;">
                ملغي
            </span>
        @endif
    </div>
</div>

<!-- Return Info Card -->
@if($return->is_voided)
    <div class="card" style="margin-bottom: 2rem; opacity: 0.7;">
@else
    <div class="card" style="margin-bottom: 2rem;">
@endif
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div>
                <p style="margin: 0.5rem 0; color: #7f8c8d;"><strong>التاريخ:</strong> {{ $return->return_date->format('Y-m-d H:i') }}</p>
                <p style="margin: 0.5rem 0; color: #7f8c8d;"><strong>المستخدم:</strong> {{ $return->user->name }}</p>
                <p style="margin: 0.5rem 0; color: #7f8c8d;">
                    <strong>البيع الأصلي:</strong>
                    <a href="{{ route('sales.show', $return->sale) }}"
                       style="color: #1abc9c; text-decoration: none; font-weight: 600;">
                        {{ $return->sale->receipt_number }}
                    </a>
                </p>
                @if($return->sale->customer)
                    <p style="margin: 0.5rem 0; color: #7f8c8d;"><strong>العميل:</strong> {{ $return->sale->customer->name }}</p>
                @endif
            </div>
            <div>
                <p style="margin: 0.5rem 0; color: #7f8c8d;">
                    <strong>إجمالي الإرجاع:</strong>
                    <span style="color: #e74c3c; font-size: 1.3rem; font-weight: 700; margin-right: 0.5rem;">
                        ${{ number_format($return->total_return_amount, 2) }}
                    </span>
                </p>
                <p style="margin: 0.5rem 0; color: #7f8c8d;"><strong>طريقة الاسترداد:</strong> {{ $return->getPaymentMethodText() }}</p>
                <p style="margin: 0.5rem 0; color: #7f8c8d;">
                    <strong>الاسترداد النقدي:</strong>
                    <span style="color: #27ae60; font-weight: 600;">${{ number_format($return->cash_refund_amount, 2) }}</span>
                </p>
                <p style="margin: 0.5rem 0; color: #7f8c8d;">
                    <strong>تخفيض الدين:</strong>
                    <span style="color: #3498db; font-weight: 600;">${{ number_format($return->debt_reduction_amount, 2) }}</span>
                </p>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid #dee2e6; margin: 1.5rem 0;">

        <div>
            <p style="margin-bottom: 0.5rem; color: #7f8c8d;"><strong>السبب:</strong></p>
            <p style="padding: 1rem; background-color: #f8f9fa; border-radius: 6px; margin: 0;">{{ $return->reason }}</p>
        </div>

        @if($return->is_voided)
            <div style="margin-top: 1.5rem; padding: 1rem; background-color: #fff3cd; border-radius: 6px; border-left: 4px solid #ffc107;">
                <p style="margin: 0.5rem 0;"><strong>تم الإلغاء بواسطة:</strong> {{ $return->voidedBy->name }}</p>
                <p style="margin: 0.5rem 0;"><strong>تاريخ الإلغاء:</strong> {{ $return->voided_at->format('Y-m-d H:i') }}</p>
                <p style="margin: 0.5rem 0;"><strong>سبب الإلغاء:</strong> {{ $return->void_reason }}</p>
            </div>
        @endif
    </div>

    <!-- Returned Items -->
    <div class="card">
        <h4 style="margin-bottom: 1.5rem;">العناصر المرتجعة</h4>
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
                @foreach($return->returnItems as $item)
                <tr>
                    <td>{{ $item->product->display_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td style="font-weight: 600;">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: 700;">الإجمالي:</td>
                    <td style="text-align: right; font-weight: 700; color: #e74c3c; font-size: 1.2rem;">
                        ${{ number_format($return->total_return_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if(auth()->user()->role === 'manager' && !$return->is_voided)
<script>
async function voidReturn() {
    const reason = await showPromptDialog({
        type: 'warning',
        title: 'سبب الإلغاء',
        message: 'الرجاء إدخال سبب إلغاء هذا الإرجاع:',
        placeholder: 'سبب الإلغاء...'
    });

    if (!reason || reason.trim() === '') {
        await showAlertDialog({
            type: 'warning',
            title: 'تحذير',
            message: 'يجب إدخال سبب الإلغاء'
        });
        return;
    }

    const confirmed = await showConfirmDialog({
        type: 'error',
        title: 'تأكيد الإلغاء',
        message: 'هل أنت متأكد من إلغاء هذا الإرجاع؟ سيتم عكس جميع التغييرات.',
        confirmText: 'تأكيد الإلغاء',
        cancelText: 'إلغاء'
    });

    if (!confirmed) return;

    try {
        const response = await fetch('{{ route("returns.void", $return) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: reason.trim() })
        });

        // Get response as text first to debug
        const responseText = await response.text();
        console.log('Response status:', response.status);
        console.log('Response text (first 500 chars):', responseText.substring(0, 500));

        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            console.error('Full response:', responseText);
            throw new Error('الخادم أرجع استجابة غير صحيحة. تحقق من سجل الأخطاء.');
        }

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
                message: data.message || 'حدث خطأ أثناء الإلغاء'
            });
        }
    } catch (error) {
        console.error('Error voiding return:', error);
        await showAlertDialog({
            type: 'error',
            title: 'خطأ',
            message: 'حدث خطأ أثناء الإلغاء: ' + error.message
        });
    }
}
</script>
@endif
@endsection
