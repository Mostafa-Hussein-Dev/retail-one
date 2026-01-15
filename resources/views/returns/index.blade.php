@extends('layouts.app')

@section('title', 'المرتجعات')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>المرتجعات</h1>
    <a href="{{ route('returns.create') }}"
       style="display: inline-block; background-color: transparent; color: #f39c12; padding: 12px 100px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
       onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
       onmouseout="this.style.backgroundColor='transparent'">
        إرجاع جديد
    </a>
</div>

<!-- Filters Card -->
<div class="card" style="margin-bottom: 2rem;">
    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
        <div>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">من تاريخ</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"
                   style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">إلى تاريخ</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"
                   style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
        </div>
        <div>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">طريقة الاسترداد</label>
            <select name="payment_method" class="form-control"
                    style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                <option value="">الكل</option>
                <option value="cash_refund" {{ request('payment_method') == 'cash_refund' ? 'selected' : '' }}>استرداد نقدي</option>
                <option value="debt_reduction" {{ request('payment_method') == 'debt_reduction' ? 'selected' : '' }}>تخفيض دين</option>
                <option value="mixed" {{ request('payment_method') == 'mixed' ? 'selected' : '' }}>مختلط</option>
            </select>
        </div>
        <div>
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الحالة</label>
            <select name="status" class="form-control"
                    style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                <option value="">الكل</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>ملغي</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit"
                    style="background-color: transparent; color: #1abc9c; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                    onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                    onmouseout="this.style.backgroundColor='transparent'">
                بحث
            </button>
            <a href="{{ route('returns.index') }}"
               style="display: inline-block; padding: 13px 20px; border: 2px solid #ccc; border-radius: 6px; font-weight: 600; text-decoration: none; text-align: center; color: #666; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.borderColor='#999'"
               onmouseout="this.style.borderColor='#ccc'">
                مسح
            </a>
        </div>
    </form>
</div>

<!-- Statistics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <h3 style="color: #f39c12; margin-bottom: 0.5rem;">إجمالي المرتجعات</h3>
        <div style="font-size: 2rem; font-weight: bold; color: #f39c12;">
            {{ $totalReturnsCount }}
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <h3 style="color: #e74c3c; margin-bottom: 0.5rem;">إجمالي المبلغ</h3>
        <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
            ${{ number_format($totalReturnAmount, 2) }}
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <h3 style="color: #27ae60; margin-bottom: 0.5rem;">الاسترداد النقدي</h3>
        <div style="font-size: 2rem; font-weight: bold; color: #27ae60;">
            ${{ number_format($totalCashRefund, 2) }}
        </div>
    </div>

    <div class="card" style="text-align: center;">
        <h3 style="color: #3498db; margin-bottom: 0.5rem;">تخفيض الدين</h3>
        <div style="font-size: 2rem; font-weight: bold; color: #3498db;">
            ${{ number_format($totalDebtReduction, 2) }}
        </div>
    </div>
</div>

<!-- Returns Table -->
<div class="card">
    @if($returns->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>رقم الإرجاع</th>
                    <th>رقم البيع</th>
                    <th>التاريخ</th>
                    <th>المبلغ</th>
                    <th>طريقة الاسترداد</th>
                    <th>الحالة</th>
                    <th>المستخدم</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returns as $return)
                <tr style="{{ $return->is_voided ? 'opacity: 0.5; text-decoration: line-through;' : '' }}">
                    <td>
                        <a href="{{ route('returns.show', $return) }}"
                           style="color: #f39c12; text-decoration: none; font-weight: 600;">
                            {{ $return->return_number }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('sales.show', $return->sale) }}"
                           style="color: #1abc9c; text-decoration: none;">
                            {{ $return->sale->receipt_number }}
                        </a>
                    </td>
                    <td>{{ $return->return_date->format('Y-m-d H:i') }}</td>
                    <td style="color: #e74c3c; font-weight: 600;">
                        ${{ number_format($return->total_return_amount, 2) }}
                    </td>
                    <td>
                        <span style="padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;
                                      {{ $return->payment_method === 'cash_refund' ? 'background-color: #d4edda; color: #155724;' :
                                         ($return->payment_method === 'debt_reduction' ? 'background-color: #d1ecf1; color: #0c5460;' :
                                         'background-color: #fff3cd; color: #856404;') }}">
                            {{ $return->getPaymentMethodText() }}
                        </span>
                    </td>
                    <td>
                        <span style="padding: 4px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;
                                      background-color: {{ $return->getStatusColor() }}20; color: {{ $return->getStatusColor() }};">
                            {{ $return->getStatusText() }}
                        </span>
                    </td>
                    <td>{{ $return->user->name }}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('returns.show', $return) }}"
                               style="padding: 6px 12px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                عرض
                            </a>
                            @if(!$return->is_voided)
                                <a href="{{ route('returns.receipt', $return) }}"
                                   style="padding: 6px 12px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 0.85rem; font-weight: 600;"
                                   target="_blank">
                                    طباعة
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{ $returns->links() }}
    @else
        <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
            <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">لا توجد مرتجعات</p>
            <p style="font-size: 0.9rem;">ابدأ بإنشاء إرجاع جديد</p>
        </div>
    @endif
</div>
@endsection
