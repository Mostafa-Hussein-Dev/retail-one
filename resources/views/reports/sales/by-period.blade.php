@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">المبيعات حسب الفترة</h1>

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
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">التجميع حسب</label>
                        <select name="group_by"
                                style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                            <option value="day" {{ $groupBy === 'day' ? 'selected' : '' }}>يومي</option>
                            <option value="week" {{ $groupBy === 'week' ? 'selected' : '' }}>أسبوعي</option>
                            <option value="month" {{ $groupBy === 'month' ? 'selected' : '' }}>شهري</option>
                            <option value="year" {{ $groupBy === 'year' ? 'selected' : '' }}>سنوي</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit"
                                style="width: 100%; background-color: #1abc9c; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#16a085'"
                                onmouseout="this.style.backgroundColor='#1abc9c'">
                            عرض
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">الفترة</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">عدد المبيعات</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesByPeriod as $period)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $period->period }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ $period->count }}</td>
                        <td style="padding: 0.75rem; text-align: left;"><strong>${{ number_format($period->total, 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
