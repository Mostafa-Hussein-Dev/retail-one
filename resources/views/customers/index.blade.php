@extends('layouts.app')

@section('title', 'العملاء')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>العملاء</h1>
        <a href="{{ route('customers.create') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            إضافة عميل جديد
        </a>
    </div>

    <!-- Search and Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">البحث</label>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="البحث بالاسم أو الهاتف"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الفلتر</label>
                <select name="filter" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>جميع العملاء</option>
                    <option value="active" {{ $filter == 'active' ? 'selected' : '' }}>النشطون</option>
                    <option value="inactive" {{ $filter == 'inactive' ? 'selected' : '' }}>غير النشطين</option>
                    <option value="with_debt" {{ $filter == 'with_debt' ? 'selected' : '' }}>المدينون</option>
                </select>
            </div>

            <div>
                <button type="submit"
                        style="background-color: transparent; color: #1abc9c; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    @if($totalCustomers > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي العملاء</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $totalCustomers }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ $activeCustomers }} نشط
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الديون</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                    ${{ number_format($totalDebt, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ number_format($totalDebt * 89500) }} ل.ل.
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">العملاء المدينون</h3>
                <div style="font-size: 2rem; font-weight: bold; color: {{ $customersWithDebt > 0 ? '#e74c3c' : '#27ae60' }};">
                    {{ $customersWithDebt }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ $totalCustomers > 0 ? number_format(($customersWithDebt / $totalCustomers) * 100, 1) : 0 }}% من العملاء
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي حدود الائتمان</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    ${{ number_format($totalCreditLimit, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ $totalCreditLimit > 0 ? number_format(($totalDebt / $totalCreditLimit) * 100, 1) : 0 }}% مستخدم
                </div>
            </div>
        </div>
    @endif

    <!-- Customers Table -->
    <div class="card">
        @if($customers->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">الاسم</th>
                    <th style="text-align: center; vertical-align: middle;">الهاتف</th>
                    <th style="text-align: center; vertical-align: middle;">المديونية</th>
                    <th style="text-align: center; vertical-align: middle;">حد الائتمان</th>
                    <th style="text-align: center; vertical-align: middle;">نسبة الاستخدام</th>
                    <th style="text-align: center; vertical-align: middle;">الحالة</th>
                    <th style="text-align: center; vertical-align: middle;">العمليات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($customers as $customer)
                    <tr>
                        <td style="text-align: center; vertical-align: middle; {{ !$customer->is_active ? 'opacity: 0.6;' : '' }}">
                            <strong>{{ $customer->name }}</strong>
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$customer->is_active ? 'opacity: 0.6;' : '' }}">
                            {{ $customer->phone ?? '-' }}
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$customer->is_active ? 'opacity: 0.6;' : '' }}">
                            <span style="color: {{ $customer->total_debt > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                ${{ number_format($customer->total_debt, 2) }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$customer->is_active ? 'opacity: 0.6;' : '' }}">
                            ${{ number_format($customer->credit_limit, 2) }}
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$customer->is_active ? 'opacity: 0.6;' : '' }}">
                            <span style="color: {{ $customer->getCreditUtilizationPercentage() > 80 ? '#e74c3c' : '#27ae60' }};">
                                {{ number_format($customer->getCreditUtilizationPercentage(), 1) }}%
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle; min-width: 120px">
                            <span style="background: {{ $customer->is_active ? '#27ae60' : '#9BB3CC' }}; color: white; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 120px;">
                                {{ $customer->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="{{ route('customers.show', $customer) }}"
                                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}"
                                   style="display: inline-block; background-color: transparent; color: #f39c12; padding: 6px 8px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    تعديل
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            {{ $customers->appends(request()->except('per_page'))->links('pagination.material') }}
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <h2>لا يوجد عملاء</h2>
                <h4>إبدأ بإضافة عملائك</h4>
            </div>
        @endif
    </div>

@endsection
