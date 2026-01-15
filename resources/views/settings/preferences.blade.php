@extends('layouts.app')

@section('content')

    <h1 style="margin-bottom: 2rem;">تفضيلات النظام</h1>

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div style="padding: 2rem;">
            <form method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">حد تنبيه المخزون المنخفض *</label>
                        <input type="number" name="low_stock_threshold"
                               value="{{ $settings['low_stock_threshold'] ?? 10 }}"
                               min="1" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                        <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">عندما تقل الكمية عن هذا الرقم، سيظهر تنبيه</div>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">عدد العناصر في الصفحة *</label>
                        <input type="number" name="pagination_per_page"
                               value="{{ $settings['pagination_per_page'] ?? 20 }}"
                               min="5" max="100" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">مهلة الجلسة (بالدقائق) *</label>
                        <input type="number" name="session_timeout"
                               value="{{ $settings['session_timeout'] ?? 120 }}"
                               min="30" max="1440" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">الاحتفاظ بالنسخ لمدة (أيام) *</label>
                        <input type="number" name="backup_retention_days"
                               value="{{ $settings['backup_retention_days'] ?? 30 }}"
                               min="7" max="365" required
                               style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">وقت النسخ الاحتياطي التلقائي</label>
                    <input type="time" name="backup_time"
                           value="{{ $settings['backup_time'] ?? '02:00' }}"
                           style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                </div>

                <div style="padding: 1.5rem 0; border-top: 2px solid #ecf0f1; margin-bottom: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <input type="checkbox" name="backup_enabled" value="1" {{ ($settings['backup_enabled'] ?? 1) ? 'checked' : '' }}
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <label style="cursor: pointer; margin: 0;">تفعيل النسخ الاحتياطي التلقائي</label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit"
                            style="flex: 1; background-color: #1abc9c; color: white; padding: 0.75rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#16a085'"
                            onmouseout="this.style.backgroundColor='#1abc9c'">
                        حفظ التفضيلات
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
