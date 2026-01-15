@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>أعمار ديون العملاء</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('reports.customers.debt-aging.export') }}"
               style="display: inline-block; background-color: #27ae60; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
               onmouseover="this.style.backgroundColor='#229954'"
               onmouseout="this.style.backgroundColor='#27ae60'">
                تصدير Excel
            </a>
            <a href="{{ route('reports.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للرئيسية
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="border-right: 4px solid #27ae60; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">0-30 يوم</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #27ae60;">${{ number_format($agingData->sum('aging.0-30'), 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #f39c12; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">31-60 يوم</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #f39c12;">${{ number_format($agingData->sum('aging.31-60'), 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #e67e22; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">61-90 يوم</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #e67e22;">${{ number_format($agingData->sum('aging.61-90'), 2) }}</div>
            </div>
        </div>
        <div class="card" style="border-right: 4px solid #e74c3c; text-align: center;">
            <div style="padding: 1.5rem;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">+90 يوم</div>
                <div style="font-size: 1.75rem; font-weight: bold; color: #e74c3c;">${{ number_format($agingData->sum('aging.90+'), 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Aging Table -->
    <div class="card">
        <div style="padding: 1.5rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #ecf0f1;">
                        <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">العميل</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem; background: #d4edda;">0-30</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem; background: #fff3cd;">31-60</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem; background: #ffe5cc;">61-90</th>
                        <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem; background: #f8d7da;">+90</th>
                        <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agingData as $item)
                    <tr style="border-bottom: 1px solid #ecf0f1;">
                        <td style="padding: 0.75rem;">{{ $item['customer']->name }}</td>
                        <td style="padding: 0.75rem; text-align: center; color: #27ae60; font-weight: 500;">${{ number_format($item['aging']['0-30'], 2) }}</td>
                        <td style="padding: 0.75rem; text-align: center; color: #f39c12; font-weight: 500;">${{ number_format($item['aging']['31-60'], 2) }}</td>
                        <td style="padding: 0.75rem; text-align: center; color: #e67e22; font-weight: 500;">${{ number_format($item['aging']['61-90'], 2) }}</td>
                        <td style="padding: 0.75rem; text-align: center; color: #e74c3c; font-weight: 500;">${{ number_format($item['aging']['90+'], 2) }}</td>
                        <td style="padding: 0.75rem; text-align: left;"><strong>${{ number_format($item['total'], 2) }}</strong></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 1rem; text-align: center; color: #7f8c8d;">لا توجد ديون</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
