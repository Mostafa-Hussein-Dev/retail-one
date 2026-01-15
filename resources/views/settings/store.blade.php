@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">إعدادات المتجر</h1>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div style="padding: 2rem;">
            <form method="POST" enctype="multipart/form-data">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">اسم المتجر *</label>
                        <input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">الهاتف</label>
                        <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}"
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">العنوان</label>
                    <textarea name="store_address" rows="3"
                              style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">{{ $settings['store_address'] ?? '' }}</textarea>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">شعار المتجر</label>
                    <input type="file" name="store_logo" accept="image/*"
                           style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    @if(isset($settings['store_logo']))
                        <br>
                        <img src="{{ $settings['store_logo'] }}" alt="Store Logo" style="max-height: 100px; margin-top: 0.5rem;">
                    @endif
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">تذييل الإيصال</label>
                    <textarea name="receipt_footer" rows="2"
                              style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">{{ $settings['receipt_footer'] ?? '' }}</textarea>
                </div>

                <div style="padding: 1.5rem 0; border-top: 2px solid #ecf0f1; border-bottom: 2px solid #ecf0f1; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <input type="checkbox" name="receipt_show_logo" value="1" {{ ($settings['receipt_show_logo'] ?? 0) ? 'checked' : '' }}
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <label style="cursor: pointer; margin: 0;">عرض الشعار على الإيصال</label>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="receipt_auto_print" value="1" {{ ($settings['receipt_auto_print'] ?? 0) ? 'checked' : '' }}
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <label style="cursor: pointer; margin: 0;">طباعة تلقائية للإيصال</label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit"
                            style="flex: 1; background-color: #1abc9c; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#16a085'"
                            onmouseout="this.style.backgroundColor='#1abc9c'">
                        حفظ الإعدادات
                    </button>
                    <a href="{{ route('settings.index') }}"
                       style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 0.75rem 2rem; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                       onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                       onmouseout="this.style.backgroundColor='transparent'">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>

@endsection
