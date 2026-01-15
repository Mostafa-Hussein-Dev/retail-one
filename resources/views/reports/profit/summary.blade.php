@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تحليل الأرباح</h1>
        <a href="{{ route('reports.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للرئيسية
        </a>
    </div>

    <!-- Filters -->
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
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">الفئة</label>
                        <select name="category_id"
                                style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                            <option value="">الكل</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
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

    <!-- Export Buttons -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <a href="{{ route('reports.profit.export', request()->all()) }}"
           style="display: inline-block; background-color: #27ae60; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#229954'"
           onmouseout="this.style.backgroundColor='#27ae60'">
            تصدير Excel
        </a>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="border-right: 4px solid #3498db; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي الإيرادات</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #3498db;">${{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #e74c3c; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي التكلفة</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #e74c3c;">${{ number_format($totalCost, 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #27ae60; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">الربح الإجمالي</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #27ae60;">${{ number_format($grossProfit, 2) }}</div>
                <div style="color: #7f8c8d; font-size: 0.85rem;">هامش: {{ number_format($profitMargin, 2) }}%</div>
            </div>
        </div>
    </div>

    <!-- Profit Table -->
    <div class="card">
        <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">تفاصيل الأرباح حسب المنتج</h3>
        </div>
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المنتج</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الكمية</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإيرادات</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">التكلفة</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الربح</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">هامش الربح</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($profitData as $item)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $item->name }}</td>
                        <td style="padding: 0.75rem; text-align: center;">{{ number_format($item->total_quantity, 2) }}</td>
                        <td style="padding: 0.75rem; text-align: left;">${{ number_format($item->total_revenue, 2) }}</td>
                        <td style="padding: 0.75rem; text-align: left;">${{ number_format($item->total_cost, 2) }}</td>
                        <td style="padding: 0.75rem; text-align: left;">
                            <strong style="color: {{ $item->profit >= 0 ? '#27ae60' : '#e74c3c' }};">
                                ${{ number_format($item->profit, 2) }}
                            </strong>
                        </td>
                        <td style="padding: 0.75rem; text-align: center;">{{ number_format($item->margin, 2) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد بيانات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
