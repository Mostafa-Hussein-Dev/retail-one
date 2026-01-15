@extends('layouts.app')

@section('content')

    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1.75rem;">التقارير</h1>
        <p style="margin: 0; color: #7f8c8d;">اختر التقرير المناسب لعرض وتحليل البيانات</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 2rem;">

        <!-- Sales Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #3498db; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #3498db; border-radius: 2px; display: inline-block;"></span>
                تقارير المبيعات
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.sales') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#3498db'; this.style.boxShadow='0 2px 8px rgba(52, 152, 219, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #3498db;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">ملخص المبيعات</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تقرير شامل للمبيعات مع فلاتر متعددة</p>
                </a>
                <a href="{{ route('reports.sales.by-period') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#3498db'; this.style.boxShadow='0 2px 8px rgba(52, 152, 219, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #3498db;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">المبيعات زمنياً</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تحليل المبيعات حسب الفترة الزمنية</p>
                </a>
            </div>
        </div>

        <!-- Profit Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #27ae60; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #27ae60; border-radius: 2px; display: inline-block;"></span>
                تقارير الأرباح
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.profit') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#27ae60'; this.style.boxShadow='0 2px 8px rgba(39, 174, 96, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #27ae60;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">تحليل الأرباح</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">الأرباح والهوامش حسب المنتج</p>
                </a>
            </div>
        </div>

        <!-- Inventory Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #f39c12; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #f39c12; border-radius: 2px; display: inline-block;"></span>
                تقارير المخزون
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.inventory.low-stock') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#f39c12'; this.style.boxShadow='0 2px 8px rgba(243, 156, 18, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #f39c12;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">المخزون المنخفض</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تنبيهات المنتجات منخفضة الكمية</p>
                </a>
                <a href="{{ route('reports.inventory.stock-value') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#f39c12'; this.style.boxShadow='0 2px 8px rgba(243, 156, 18, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #f39c12;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">قيمة المخزون</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">القيمة الإجمالية للمخزون</p>
                </a>
            </div>
        </div>

        <!-- Customers Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #e74c3c; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #e74c3c; border-radius: 2px; display: inline-block;"></span>
                تقارير العملاء
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.customers.debt') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#e74c3c'; this.style.boxShadow='0 2px 8px rgba(231, 76, 60, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #e74c3c;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">ديون العملاء</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">إجمالي الديون لكل عميل</p>
                </a>
                <a href="{{ route('reports.customers.debt-aging') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#e74c3c'; this.style.boxShadow='0 2px 8px rgba(231, 76, 60, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #e74c3c;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">أعمار ديون العملاء</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تصنيف الديون حسب الفترة</p>
                </a>
            </div>
        </div>

        <!-- Suppliers Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #9b59b6; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #9b59b6; border-radius: 2px; display: inline-block;"></span>
                تقارير الموردين
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.suppliers.debt') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#9b59b6'; this.style.boxShadow='0 2px 8px rgba(155, 89, 182, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #9b59b6;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">ديون الموردين</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">إجمالي الديون لكل مورد</p>
                </a>
                <a href="{{ route('reports.suppliers.debt-aging') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#9b59b6'; this.style.boxShadow='0 2px 8px rgba(155, 89, 182, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #9b59b6;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">أعمار ديون الموردين</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تصنيف الديون زمنياً</p>
                </a>
            </div>
        </div>

        <!-- Returns Section -->
        <div>
            <h2 style="margin: 0 0 1rem 0; color: #e67e22; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                <span style="width: 4px; height: 20px; background: #e67e22; border-radius: 2px; display: inline-block;"></span>
                تقارير المرتجعات
            </h2>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                <a href="{{ route('reports.returns') }}"
                   style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
                   onmouseover="this.style.borderColor='#e67e22'; this.style.boxShadow='0 2px 8px rgba(230, 126, 34, 0.15)';"
                   onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
                    <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #e67e22;"></div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">ملخص المرتجعات</h3>
                    <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تقرير شامل للمرتجعات</p>
                </a>
            </div>
        </div>

    </div>

@endsection
