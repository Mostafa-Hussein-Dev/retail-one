@extends('layouts.app')

@section('title', 'الموردين')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>الموردين</h1>
        <a href="{{ route('suppliers.create') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            إضافة مورد جديد
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
                       placeholder="البحث بالاسم أو الهاتف أو الشخص المسؤول"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الفلتر</label>
                <select name="filter" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="all" {{ $filter == 'all' ? 'selected' : '' }}>جميع الموردين</option>
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
    @if($totalSuppliers > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الموردين</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $totalSuppliers }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ $activeSuppliers }} نشط
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الديون</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #e74c3c;">
                    ${{ number_format($totalDebt, 2) }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    ندين للموردين
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">الموردين المدينون لنا</h3>
                <div style="font-size: 2rem; font-weight: bold; color: {{ $suppliersWithDebt > 0 ? '#e74c3c' : '#27ae60' }};">
                    {{ $suppliersWithDebt }}
                </div>
                <div style="color: #7f8c8d; font-size: 0.9rem;">
                    {{ $totalSuppliers > 0 ? number_format(($suppliersWithDebt / $totalSuppliers) * 100, 1) : 0 }}% من الموردين
                </div>
            </div>
        </div>
    @endif

    <!-- Suppliers Table -->
    <div class="card">
        @if($suppliers->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الشخص المسؤول</th>
                    <th>الهاتف</th>
                    <th>المديونية</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($suppliers as $supplier)
                    <tr>
                        <td><strong>{{ $supplier->name }}</strong></td>
                        <td>{{ $supplier->contact_person ?? '-' }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>
                            <span style="color: {{ $supplier->total_debt > 0 ? '#e74c3c' : '#27ae60' }}; font-weight: 600;">
                                ${{ number_format($supplier->total_debt, 2) }}
                            </span>
                        </td>
                        <td>
                            @if($supplier->is_active)
                                <span style="background: #27ae60; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">نشط</span>
                            @else
                                <span style="background: #95a5a6; color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem;">غير نشط</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               style="color: #3498db; text-decoration: none; margin-left: 0.5rem;">عرض</a>
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               style="color: #f39c12; text-decoration: none; margin-left: 0.5rem;">تعديل</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $suppliers->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <p style="font-size: 1.2rem;">لا يوجد موردين</p>
                <p style="margin-top: 0.5rem;">ابدأ بإضافة مورد جديد</p>
            </div>
        @endif
    </div>

@endsection
