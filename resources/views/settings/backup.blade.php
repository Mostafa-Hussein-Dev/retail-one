@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة النسخ الاحتياطي</h1>
        <a href="{{ route('settings.index') }}"
           style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 12px 40px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للإعدادات
        </a>
    </div>

    <!-- Create Backup -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: center;">
                <div>
                    <h3 style="margin: 0 0 1rem 0; color: #2c3e50;">إنشاء نسخة احتياطية</h3>
                    @if($latestBackup)
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="color: #7f8c8d;"><strong>التاريخ:</strong> {{ $latestBackup['created_at'] }}</div>
                            <div style="color: #7f8c8d;"><strong>الحجم:</strong> {{ $latestBackup['size_formatted'] }}</div>
                        </div>
                    @else
                        <div style="color: #f39c12;">لا توجد نسخ احتياطية</div>
                    @endif
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <form method="POST" action="{{ route('settings.backup.create') }}">
                        @csrf
                        <button type="submit"
                                style="background-color: #27ae60; color: white; padding: 1rem 2rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#229954'"
                                onmouseout="this.style.backgroundColor='#27ae60'">
                            إنشاء نسخة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Backup -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="padding: 2rem;">
            <h3 style="margin: 0 0 1rem 0; color: #2c3e50;">استعادة من ملف</h3>
            <p style="color: #7f8c8d; margin-bottom: 1rem;">اختر ملف نسخة احتياطية من جهازك لاستعادته</p>
            <form method="POST" action="{{ route('settings.backup.upload') }}" enctype="multipart/form-data" style="display: flex; gap: 1rem; align-items: end;">
                @csrf
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #7f8c8d; font-size: 0.9rem;">اختر ملف SQL</label>
                    <input type="file" name="backup_file" accept=".sql" required
                           style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit;">
                </div>
                <button type="submit"
                        style="background-color: #f39c12; color: white; padding: 0.75rem 2rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                        onmouseover="this.style.backgroundColor='#e67e22'"
                        onmouseout="this.style.backgroundColor='#f39c12'">
                    رفع واستعادة
                </button>
            </form>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card">
        <div style="padding: 1.5rem; border-bottom: 2px solid #ecf0f1;">
            <h3 style="margin: 0; color: #2c3e50;">قائمة النسخ الاحتياطية</h3>
        </div>
        <div style="padding: 1.5rem;">
            @if(count($backups) > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ecf0f1;">
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">الملف</th>
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">التاريخ</th>
                            <th style="padding: 0.75rem; text-align: right; color: #7f8c8d; font-size: 0.9rem;">الحجم</th>
                            <th style="padding: 0.75rem; text-align: center; color: #7f8c8d; font-size: 0.9rem;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                        <tr style="border-bottom: 1px solid #ecf0f1;">
                            <td style="padding: 0.75rem; font-family: monospace;">{{ $backup['filename'] }}</td>
                            <td style="padding: 0.75rem;">{{ $backup['created_at'] }}</td>
                            <td style="padding: 0.75rem;">{{ $backup['size_formatted'] }}</td>
                            <td style="padding: 0.75rem; text-align: center;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <a href="{{ route('settings.backup.download', ['filename' => $backup['filename']]) }}"
                                       style="display: inline-block; background-color: transparent; color: #3498db; padding: 0.5rem 1rem; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                                       onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
                                       onmouseout="this.style.backgroundColor='transparent'">
                                        تحميل
                                    </a>
                                    <button onclick="confirmRestore('{{ $backup['filename'] }}')"
                                            style="background-color: transparent; color: #f39c12; padding: 0.5rem 1rem; border: 2px solid #f39c12; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; transition: all 0.3s ease;"
                                            onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
                                            onmouseout="this.style.backgroundColor='transparent'">
                                        استعادة
                                    </button>
                                    <form method="POST"
                                          action="{{ route('settings.backup.destroy', ['filename' => $backup['filename']]) }}"
                                          style="display: inline;"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه النسخة؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                style="background-color: transparent; color: #e74c3c; padding: 0.5rem 1rem; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.9rem; transition: all 0.3s ease;"
                                                onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                                                onmouseout="this.style.backgroundColor='transparent'">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; color: #7f8c8d; padding: 2rem;">لا توجد نسخ احتياطية</div>
            @endif
        </div>
    </div>

    <script>
        function confirmRestore(filename) {
            showConfirmDialog({
                title: 'تأكيد استعادة النسخة الاحتياطية',
                message: 'سيتم استعادة قاعدة البيانات من هذه النسخة الاحتياطية. سيتم إنشاء نسخة احتياطية احتياطية قبل الاستعادة. هل أنت متأكد؟',
                type: 'warning',
                confirmText: 'استعادة',
                cancelText: 'إلغاء'
            }).then((confirmed) => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("settings.backup.restore") }}';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    form.appendChild(csrfInput);

                    const filenameInput = document.createElement('input');
                    filenameInput.type = 'hidden';
                    filenameInput.name = 'filename';
                    filenameInput.value = filename;
                    form.appendChild(filenameInput);

                    const confirmInput = document.createElement('input');
                    confirmInput.type = 'hidden';
                    confirmInput.name = 'confirm';
                    confirmInput.value = '1';
                    form.appendChild(confirmInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

@endsection
