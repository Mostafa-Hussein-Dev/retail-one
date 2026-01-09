@extends('layouts.app')

@section('content')

    <!-- Welcome Section -->
    <div class="card" style="margin-bottom: 2rem;">
        <h2>مرحباً، {{ auth()->user()->name }}</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div>
                <strong>الدور:</strong> {{ auth()->user()->role === 'manager' ? 'مدير' : 'أمين صندوق' }}
            </div>
            <div>
                <strong>آخر تسجيل دخول:</strong> {{ auth()->user()->last_login_at?->format('Y-m-d H:i') ?? 'لم يتم تسجيل الدخول من قبل' }}
            </div>
            <div>
                <strong>حالة النظام:</strong> <span style="color: #1abc9c;">متصل</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card" style="margin-bottom: 2rem;">
        <h3>إجراءات سريعة</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
            <a href="#" style="background: #1abc9c; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                بيع جديد
            </a>
            @if(auth()->user()->role === 'manager')
                <a href="#" style="background: #3498db; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                    إضافة منتج
                </a>
                <a href="#" style="background: #9b59b6; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                    إدارة العملاء
                </a>
                <a href="#" style="background: #f39c12; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                    التقارير
                </a>
            @endif
            <a href="#" style="background: #e67e22; color: white; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none;">
                إرجاع منتج
            </a>
        </div>
    </div>

    @if(auth()->user()->role === 'manager')
        <!-- Statistics Cards -->
        <div class="cards" style="margin-bottom: 2rem;">
            <!-- Today's Sales -->
            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">مبيعات اليوم</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: #1abc9c; margin-bottom: 0.5rem;">
                    $0.00
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">0 عملية بيع</div>
                <div style="color: #27ae60; font-size: 0.85rem; margin-top: 0.5rem;">
                    الربح: $0.00
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">مبيعات الشهر</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: #3498db; margin-bottom: 0.5rem;">
                    $0.00
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">0 عملية بيع</div>
                <div style="color: #2980b9; font-size: 0.85rem; margin-top: 0.5rem;">
                    الربح: $0.00
                </div>
            </div>

            <!-- Low Stock -->
            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">تنبيهات المخزون</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: #e74c3c; margin-bottom: 0.5rem;">
                    0
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">منتج بمخزون منخفض</div>
                <div style="color: #c0392b; font-size: 0.85rem; margin-top: 0.5rem;">
                    نفد المخزون: 0
                </div>
            </div>

            <!-- Customer Debts -->
            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">ديون العملاء</h3>
                <div style="font-size: 2.5rem; font-weight: bold; color: #f39c12; margin-bottom: 0.5rem;">
                    $0.00
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">0 عميل مدين</div>
                <div style="color: #d68910; font-size: 0.85rem; margin-top: 0.5rem;">
                    متأخر: $0.00
                </div>
            </div>
        </div>

        <!-- Recent Activity & System Info -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Recent Activity -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">النشاط الأخير</h3>
                <div style="color: #7f8c8d; text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 6px;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                    <div>لا توجد عمليات حديثة</div>
                    <div style="font-size: 0.9rem; margin-top: 0.5rem;">ابدأ بعملية بيع جديدة</div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card">
                <h3 style="margin-bottom: 1rem;">حالة النظام</h3>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: #e8f5e8; border-radius: 4px;">
                        <span>قاعدة البيانات</span>
                        <span style="color: #27ae60; font-weight: 600;">متصلة</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: #fff3cd; border-radius: 4px;">
                        <span>النسخة الاحتياطية</span>
                        <span style="color: #f39c12; font-weight: 600;">لم يتم</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                        <span>إجمالي المنتجات</span>
                        <span style="color: #6c757d; font-weight: 600;">0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: #f8f9fa; border-radius: 4px;">
                        <span>إجمالي العملاء</span>
                        <span style="color: #6c757d; font-weight: 600;">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manager Tools -->
        <div class="card">
            <h3 style="margin-bottom: 1rem;">أدوات الإدارة</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="border: 1px solid #e1e8ed; padding: 1rem; border-radius: 6px;">
                    <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">إدارة المخزون</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">تتبع المنتجات والكميات</p>
                    <a href="#" style="background: #3498db; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                        إدارة المنتجات
                    </a>
                </div>
                <div style="border: 1px solid #e1e8ed; padding: 1rem; border-radius: 6px;">
                    <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">التقارير المالية</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">تحليل المبيعات والأرباح</p>
                    <a href="#" style="background: #f39c12; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                        عرض التقارير
                    </a>
                </div>
                <div style="border: 1px solid #e1e8ed; padding: 1rem; border-radius: 6px;">
                    <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">النسخ الاحتياطي</h4>
                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 1rem;">حماية البيانات المالية</p>
                    <a href="#" style="background: #e74c3c; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem;">
                        إنشاء نسخة احتياطية
                    </a>
                </div>
            </div>
        </div>

    @else
        <!-- Cashier View -->
        <div class="cards" style="margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">عملياتي اليوم</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c; margin-bottom: 0.5rem;">
                    0
                </div>
                <div style="color: #7f8c8d;">عملية بيع</div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #2c3e50; margin-bottom: 1rem;">إجمالي المبيعات</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #3498db; margin-bottom: 0.5rem;">
                    $0.00
                </div>
                <div style="color: #7f8c8d;">اليوم</div>
            </div>
        </div>

        <!-- Cashier Recent Activity -->
        <div class="card">
            <h3 style="margin-bottom: 1rem;">عملياتي الأخيرة</h3>
            <div style="color: #7f8c8d; text-align: center; padding: 2rem;">
                لا توجد عمليات بيع اليوم
            </div>
        </div>
    @endif

@endsection
