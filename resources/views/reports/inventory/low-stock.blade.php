@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">تنبيهات المخزون المنخفض</h1>

    <p style="color: #7f8c8d; margin-bottom: 2rem;">
        عرض المنتجات التي تقل كميتها عن الحد المحدد: <strong>{{ $threshold }}</strong>
    </p>

    <!-- Export Buttons -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <a href="{{ route('reports.inventory.low-stock.export') }}"
           style="display: inline-block; background-color: #27ae60; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='#229954'"
           onmouseout="this.style.backgroundColor='#27ae60'">
            تصدير Excel
        </a>
    </div>

    <div class="card">
        <div style="padding: 1.5rem;">
            @if($products->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ecf0f1;">
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المنتج</th>
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">الفئة</th>
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المورد</th>
                            <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الكمية الحالية</th>
                            <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">سعر التكلفة</th>
                            <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">سعر البيع</th>
                            <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr style="border-bottom: 1px solid #ecf0f1;">
                            <td style="padding: 0.75rem;">{{ $product->name }}</td>
                            <td style="padding: 0.75rem;">{{ $product->category?->name ?? '-' }}</td>
                            <td style="padding: 0.75rem;">{{ $product->supplier?->name ?? '-' }}</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                @if($product->quantity < $threshold / 2)
                                    <span style="background: #e74c3c; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">{{ $product->quantity }}</span>
                                @else
                                    <span style="background: #f39c12; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">{{ $product->quantity }}</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem; text-align: left;">${{ number_format($product->cost_price, 2) }}</td>
                            <td style="padding: 0.75rem; text-align: left;">${{ number_format($product->selling_price, 2) }}</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <a href="{{ route('products.show', $product) }}"
                                   style="display: inline-block; background-color: transparent; color: #3498db; padding: 0.5rem 1rem; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                                   onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; color: #7f8c8d; padding: 2rem;">لا توجد منتجات بمخزون منخفض</div>
            @endif
        </div>
    </div>

@endsection
