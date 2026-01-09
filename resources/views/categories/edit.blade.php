@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل الفئة</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('categories.index') }}"
               style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-decoration: none; text-align: center; transition: all 0.3s ease; line-height: normal;"
               onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للقائمة
            </a>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('categories.update', $category) }}" class="form">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Left Column -->
                <div>
                    <label>اسم الفئة (بالإنجليزية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $category->name) }}"
                           required
                           placeholder="Category Name">
                    @error('name')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <label>وصف الفئة</label>
                    <textarea name="description"
                              rows="5"
                              placeholder="وصف مختصر عن هذه الفئة ونوع المنتجات التي تحتويها">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Right Column -->
                <div>
                    <label>اسم الفئة (بالعربية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name_ar"
                           value="{{ old('name_ar', $category->name_ar) }}"
                           required
                           placeholder="اسم الفئة">
                    @error('name_ar')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <!-- Category Preview -->
                    <div style="background: #f8f9fa; border-radius: 6px;">
                        <strong>معاينة الفئة:</strong>
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: white; border-radius: 4px; border: 1px solid #ddd; min-height: 107px;">
                            <div id="preview-name" style="font-weight: 600; color: #2c3e50;">{{ $category->name }}</div>
                            <div id="preview-name-ar" style="color: #7f8c8d; font-size: 0.9rem;">{{ $category->name_ar }}</div>
                            <div id="preview-description" style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">{{ $category->description ?: 'الوصف...' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Statistics -->
            <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 6px; margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #2c3e50;">إحصائيات الفئة:</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                    <div style="background: white; padding: 1rem; border-radius: 6px; text-align: center;">
                        <div style="color: #1abc9c; font-weight: bold; font-size: 1.5rem;">{{ $category->products_count ?? 0 }}</div>
                        <div style="color: #7f8c8d; font-size: 0.9rem;">إجمالي المنتجات</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: 6px; text-align: center;">
                        <div style="color: #1abc9c; font-weight: bold; font-size: 1.5rem;">{{ $category->active_products_count ?? 0 }}</div>
                        <div style="color: #7f8c8d; font-size: 0.9rem;">منتجات نشطة</div>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: 6px; text-align: center;">
                        <div style="color: #1abc9c; font-weight: bold; font-size: 1.5rem;">{{ $category->created_at->format('M Y') }}</div>
                        <div style="color: #7f8c8d; font-size: 0.9rem;">تاريخ الإنشاء</div>
                    </div>
                </div>
            </div>

            <!-- Warning if has products -->
            @if($category->products_count > 0)
                <div style="background: #fdf7e8; border: 1px solid #f39c12; padding: 1rem; border-radius: 6px; margin-top: 1rem; border-left: 4px solid #f39c12;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div>
                            <strong>تنبيه:</strong> هذه الفئة تحتوي على {{ $category->products_count }} منتج.
                            تعديل اسم الفئة سيؤثر على عرض جميع المنتجات المرتبطة بها.
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit"
                        style="background-color: transparent; color: #f39c12; padding: 12px 100px; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    تحديث الفئة
                </button>
                <button type="button" onclick="window.location.href='{{ route('categories.show', $category) }}'"
                        style="background-color: transparent; color: #95a5a6; padding: 12px 100px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    إلغاء
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const nameInput = document.querySelector('input[name="name"]');
                const nameArInput = document.querySelector('input[name="name_ar"]');
                const descriptionInput = document.querySelector('textarea[name="description"]');

                const previewName = document.getElementById('preview-name');
                const previewNameAr = document.getElementById('preview-name-ar');
                const previewDescription = document.getElementById('preview-description');

                function updatePreview() {
                    previewName.textContent = nameInput.value || 'اسم الفئة';
                    previewNameAr.textContent = nameArInput.value || 'الاسم بالعربية';
                    previewDescription.textContent = descriptionInput.value || 'الوصف...';
                }

                nameInput.addEventListener('input', updatePreview);
                nameArInput.addEventListener('input', updatePreview);
                descriptionInput.addEventListener('input', updatePreview);
            });
        </script>
    @endpush

@endsection
