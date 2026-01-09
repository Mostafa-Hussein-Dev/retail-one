@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إضافة فئة جديدة</h1>
        <a href="{{ route('categories.index') }}"
           style="display: inline-block; background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للقائمة
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('categories.store') }}" class="form">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Left Column -->
                <div>
                    <label>اسم الفئة (بالإنجليزية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           placeholder="Category Name">
                    @error('name')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <label>وصف الفئة</label>
                    <textarea name="description"
                              rows="5"
                              placeholder="وصف مختصر عن هذه الفئة ونوع المنتجات التي تحتويها">{{ old('description') }}</textarea>
                    @error('description')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Right Column -->
                <div>
                    <label>اسم الفئة (بالعربية) <span style="color: #e74c3c;">*</span></label>
                    <input type="text"
                           name="name_ar"
                           value="{{ old('name_ar') }}"
                           required
                           placeholder="اسم الفئة">
                    @error('name_ar')
                    <div style="color: #e74c3c; font-size: 0.9rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror

                    <!-- Category Preview -->
                    <div style="background: #f8f9fa; border-radius: 6px;">
                        <strong>معاينة الفئة:</strong>
                        <div style="margin-top: 0.75rem; padding: 0.75rem; background: white; border-radius: 4px; border: 1px solid #ddd; min-height: 107px">
                            <div id="preview-name" style="font-weight: 600; color: #2c3e50;">اسم الفئة</div>
                            <div id="preview-name-ar" style="color: #7f8c8d; font-size: 0.9rem;">الاسم بالعربية</div>
                            <div id="preview-description" style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">الوصف...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Examples Section -->
            <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 6px; margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem; color: #2c3e50;">أمثلة على الفئات:</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div style="background: white; padding: 1rem; border-radius: 6px;">
                        <strong>Electronics</strong> • إلكترونيات<br>
                        <small style="color: #7f8c8d;">هواتف، أجهزة كمبيوتر، كاميرات</small>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: 6px;">
                        <strong>Food</strong> • مواد غذائية<br>
                        <small style="color: #7f8c8d;">طعام، مشروبات، حلويات</small>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: 6px;">
                        <strong>Clothing</strong> • ملابس<br>
                        <small style="color: #7f8c8d;">قمصان، بناطيل، أحذية</small>
                    </div>
                    <div style="background: white; padding: 1rem; border-radius: 6px;">
                        <strong>Home</strong> • أدوات منزلية<br>
                        <small style="color: #7f8c8d;">أثاث، أدوات منزلية</small>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <button type="submit"
                        style="background-color: transparent; color: #1abc9c; padding: 12px 100px; border: 2px solid #1abc9c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='rgba(26, 188, 156, 0.1)'"
                        onmouseout="this.style.backgroundColor='transparent'">
                    حفظ الفئة
                </button>
                <button type="button" onclick="window.location.href='{{ route('categories.index') }}'"
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
