@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">قيمة المخزون</h1>

    <!-- Summary -->
    <div class="card" style="margin-bottom: 2rem; text-align: center;">
        <div style="padding: 2rem;">
            <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي قيمة المخزون</div>
            <div style="font-size: 2.5rem; font-weight: bold; color: #1abc9c;">${{ number_format($totalStockValue, 2) }}</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
        <!-- Value by Category -->
        <div class="card">
            <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50;">القيمة حسب الفئة</h3>
            </div>
            <div style="padding: 1.5rem;">
                @foreach($valueByCategory as $category => $value)
                <div style="display: flex; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid #ecf0f1;">
                    <span>{{ $category ?? 'غير مصنف' }}</span>
                    <strong>${{ number_format($value, 2) }}</strong>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top 10 Products -->
        <div class="card">
            <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50;">أعلى 10 منتجات قيمة</h3>
            </div>
            <div style="padding: 1.5rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ecf0f1;">
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المنتج</th>
                            <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الكمية</th>
                            <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">القيمة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($valuableProducts as $product)
                        <tr style="border-bottom: 1px solid #ecf0f1;">
                            <td style="padding: 0.75rem;">{{ $product->name }}</td>
                            <td style="padding: 0.75rem; text-align: center;">{{ $product->quantity }}</td>
                            <td style="padding: 0.75rem; text-align: left;"><strong>${{ number_format($product->quantity * $product->cost_price, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
