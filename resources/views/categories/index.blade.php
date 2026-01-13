@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة الفئات</h1>
        <a href="{{ route('categories.create') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            إضافة فئة جديدة
        </a>
    </div>


    <!-- Search and Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">البحث</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="البحث بالاسم"
                       style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
            </div>

            <div>
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">الحالة</label>
                <select name="status" style="width: 100%; padding: 13px; border: 1px solid #ccc; border-radius: 6px;">
                    <option value="">جميع الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit"style="background-color: transparent; color: #1abc9c; padding: 6px 40px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    بحث
                </button>
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="card">
        @if($categories->count() > 0)
            <table class="table">
                <thead>
                <tr>
                    <th style="text-align: center; vertical-align: middle;">الاسم</th>
                    <th style="text-align: center; vertical-align: middle;">الاسم بالعربية</th>
                    <th style="text-align: center; vertical-align: middle;">تاريخ الإنشاء</th>
                    <th style="text-align: center; vertical-align: middle;">عدد المنتجات</th>
                    <th style="text-align: center; vertical-align: middle;">المنتجات النشطة</th>
                    <th style="text-align: center; vertical-align: middle;">الحالة</th>
                    <th style="text-align: center; vertical-align: middle;">العمليات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td style="text-align: center; vertical-align: middle; {{ !$category->is_active ? 'opacity: 0.6;' : '' }}">
                            <div>
                                <strong>{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$category->is_active ? 'opacity: 0.6;' : '' }}">{{ $category->name_ar ?: '-' }}</td>
                        <td style="font-size: 1rem; text-align: center; vertical-align: middle; {{ !$category->is_active ? 'opacity: 0.6;' : '' }}">
                            {{ $category->created_at->format('d-m-Y') }}
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$category->is_active ? 'opacity: 0.6;' : '' }}">
                            <span style="
                                color: #435c7a;
                                padding: 4px 10px;
                                border-radius: 7px;
                                font-size: 1.1rem;
                                font-weight: bold;
                            ">
                                {{ $category->products_count }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle; {{ !$category->is_active ? 'opacity: 0.6;' : '' }}">
                            <span style="
                               color: #27ae60;
                                padding: 4px 10px;
                                border-radius: 7px;
                                font-size: 1.1rem;
                                font-weight: bold;
                            ">
                                {{ $category->active_products_count }}
                            </span>
                        </td>
                        <td style="text-align: center; vertical-align: middle; min-width: 80px">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.25rem;">
                                <span style="background: {{ $category->is_active ? '#27ae60' : '#9BB3CC' }}; color: white; padding: 6px 13px;  border-radius: 6px; font-size: 0.85rem; font-weight: 500; display: inline-flex; align-items: center; justify-content: center; min-width: 80px;">
                                    {{ $category->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </div>
                        </td>
                        <td style="text-align: center; vertical-align: middle;  min-width: 90px">
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="{{ route('categories.show', $category) }}"
                                   style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 6px 8px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease;"
                                   onmouseover="this.style.backgroundColor='rgba(26,188,156,0.15)'"
                                   onmouseout="this.style.backgroundColor='transparent'">
                                    عرض
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            {{ $categories->appends(request()->except('per_page'))->links('pagination.material') }}
        @else
            <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
                <h2>لا توجد فئات</h2>
                <h4>ابدأ بإنشاء الفئة الأولى لتنظيم منتجاتك</h4>
            </div>
        @endif
    </div>


    <!-- Categories Stats -->
    @if($categories->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي الفئات</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $categories->total() }}
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">الفئات النشطة</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $categories->where('is_active', true)->count() }}
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">إجمالي المنتجات</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $categories->sum('products_count') }}
                </div>
            </div>

            <div class="card" style="text-align: center;">
                <h3 style="color: #1abc9c; margin-bottom: 0.5rem;">المنتجات النشطة</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #1abc9c;">
                    {{ $activeProductsCount }}
                </div>
            </div>
        </div>
    @endif


@endsection
