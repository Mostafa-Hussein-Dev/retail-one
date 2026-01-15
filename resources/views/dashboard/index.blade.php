@extends('layouts.app')

@section('content')

    <!-- Welcome Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2 style="margin: 0 0 1rem 0; color: #2c3e50; font-size: 1.5rem;">مرحباً، {{ auth()->user()->name }}</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div style="text-align: right;">
                <strong style="color: #7f8c8d;">الدور:</strong>
                <span style="color: #2c3e50;">{{ auth()->user()->role_display }}</span>
            </div>
            <div style="text-align: center;">
                <strong style="color: #7f8c8d;">آخر تسجيل دخول:</strong>
                <span style="color: #2c3e50;">{{ auth()->user()->last_login_at?->format('Y-m-d H:i') ?? 'لم يتم تسجيل الدخول من قبل' }}</span>
            </div>
            <div style="text-align: left;">
                <strong style="color: #7f8c8d;">حالة النظام:</strong>
                <span style="color: #27ae60; font-weight: 600;">متصل</span>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Today's Sales -->
        <div class="card" style="border-right: 4px solid #1abc9c;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">مبيعات اليوم</div>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c; margin-bottom: 0.25rem;">
                    ${{ number_format($todaySalesAmount, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.85rem;">{{ $todaySales }} عملية</div>
            </div>
        </div>

        <!-- This Month Sales -->
        <div class="card" style="border-right: 4px solid #3498db;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">مبيعات الشهر</div>
                <div style="font-size: 2rem; font-weight: bold; color: #3498db; margin-bottom: 0.25rem;">
                    ${{ number_format($thisMonthSalesAmount, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.85rem;">{{ $thisMonthSales }} عملية</div>
            </div>
        </div>

        <!-- Customer Debt -->
        <div class="card" style="border-right: 4px solid #e74c3c;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">ديون العملاء</div>
                <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                    ${{ number_format($customerDebt, 2) }}
                </div>
            </div>
        </div>

        <!-- Supplier Debt -->
        <div class="card" style="border-right: 4px solid #f39c12;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">ديون الموردين</div>
                <div style="font-size: 2rem; font-weight: bold; color: #f39c12;">
                    ${{ number_format($supplierDebt, 2) }}
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'manager')
    <!-- Cash Flow (Full Width) -->
    @if($cashFlow !== 0 || $cashIn > 0 || $cashOut > 0)
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 1.25rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">💰 التدفق النقدي اليوم</h3>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                <div style="padding: 1rem; background: #e8f5e8; border-radius: 6px;">
                    <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.5rem;">نقد داخِل</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">${{ number_format($cashIn, 2) }}</div>
                </div>
                <div style="padding: 1rem; background: #ffeaea; border-radius: 6px;">
                    <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.5rem;">نقد خارج</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: #e74c3c;">${{ number_format($cashOut, 2) }}</div>
                </div>
                <div style="padding: 1rem; background: {{ $cashFlow >= 0 ? '#e8f5e8' : '#ffeaea' }}; border-radius: 6px; border: 2px solid {{ $cashFlow >= 0 ? '#27ae60' : '#e74c3c' }};">
                    <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.5rem;">صافي التدفق</div>
                    <div style="font-size: 1.5rem; font-weight: bold; color: {{ $cashFlow >= 0 ? '#27ae60' : '#e74c3c' }};">
                        ${{ number_format($cashFlow, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Low Stock & Top Products -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Low Stock Alerts -->
        <div class="card">
            <div style="padding: 1.25rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50; display: flex; align-items: center; gap: 0.5rem;">
                    ⚠️ تنبيهات المخزون المنخفض
                    @if($lowStockCount > 0)
                    <span style="background: #f39c12; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem;">
                        {{ $lowStockCount }}
                    </span>
                    @endif
                </h3>
            </div>
            <div style="padding: 1.25rem;">
                @if($lowStockProducts->count() > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($lowStockProducts as $product)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8f9fa; border-radius: 6px;">
                            <span style="font-weight: 500;">{{ $product->name }}</span>
                            <span style="background: #e74c3c; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem;">
                                {{ $product->quantity }} متبقي
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; color: #7f8c8d; padding: 1rem;">لا توجد منتجات بمخزون منخفض</div>
                @endif
            </div>
        </div>

        <!-- Top Products -->
        <div class="card">
            <div style="padding: 1.25rem; border-bottom: 2px solid #ecf0f1;">
                <h3 style="margin: 0; color: #2c3e50;">📊 أكثر المنتجات مبيعاً</h3>
            </div>
            <div style="padding: 1.25rem;">
                @if($topProducts->count() > 0)
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ecf0f1;">
                                <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">المنتج</th>
                                <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الكمية</th>
                                <th style="padding: 0.75rem; text-align: left; color: #7f8c8d; font-size: 0.9rem;">الإيرادات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $product)
                            <tr style="border-bottom: 1px solid #ecf0f1;">
                                <td style="padding: 0.75rem;">{{ $product->name }}</td>
                                <td style="padding: 0.75rem; text-align: center;">{{ $product->total_quantity }}</td>
                                <td style="padding: 0.75rem; text-align: left;">${{ number_format($product->total_revenue, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="text-align: center; color: #7f8c8d; padding: 1rem;">لا توجد بيانات</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="card">
        <div style="padding: 1.25rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">📝 النشاط الأخير</h3>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; flex-direction: column; gap: 0.75rem; max-height: 400px; overflow-y: auto;">
                @foreach($recentActivity as $activity)
                    <div style="padding: 0.75rem; background: #f8f9fa; border-radius: 6px; border-right: 3px solid #3498db;">
                        <div style="color: #2c3e50; font-weight: 600; font-size: 0.95rem;">{{ $activity->description }}</div>
                        <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @else
    <!-- Cashier View - Simplified -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="border-right: 4px solid #1abc9c;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">عملياتي اليوم</div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #1abc9c;">
                    {{ $todaySales }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.85rem;">عملية بيع</div>
            </div>
        </div>

        <div class="card" style="border-right: 4px solid #3498db;">
            <div style="padding: 1.5rem; text-align: center;">
                <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 0.5rem;">إجمالي المبيعات</div>
                <div style="font-size: 2.5rem; font-weight: bold; color: #3498db;">
                    ${{ number_format($todaySalesAmount, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.85rem;">اليوم</div>
            </div>
        </div>
    </div>

    <!-- Cashier Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="card">
        <div style="padding: 1.25rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">📝 عملياتي الأخيرة</h3>
        </div>
        <div style="padding: 1.25rem;">
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @foreach($recentActivity->take(10) as $activity)
                    <div style="padding: 0.75rem; background: #f8f9fa; border-radius: 6px; border-right: 3px solid #3498db;">
                        <div style="color: #2c3e50; font-weight: 600; font-size: 0.95rem;">{{ $activity->description }}</div>
                        <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">{{ $activity->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif

@endsection
