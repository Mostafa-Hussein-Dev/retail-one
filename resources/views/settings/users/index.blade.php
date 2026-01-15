@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إدارة المستخدمين</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('settings.users.create') }}"
               style="display: inline-block; background-color: #27ae60; color: white; padding: 12px 24px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
               onmouseover="this.style.backgroundColor='#229954'"
               onmouseout="this.style.backgroundColor='#27ae60'">
                إضافة مستخدم جديد
            </a>
            <a href="{{ route('settings.index') }}"
               style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 12px 40px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
               onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
               onmouseout="this.style.backgroundColor='transparent'">
                العودة للإعدادات
            </a>
        </div>
    </div>

    <div class="card">
        <div style="padding: 1.5rem;">
            @if(count($users) > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ecf0f1;">
                            <th style="padding: 1rem; text-align: right; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">الاسم</th>
                            <th style="padding: 1rem; text-align: right; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">اسم المستخدم</th>
                            <th style="padding: 1rem; text-align: right; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">الدور</th>
                            <th style="padding: 1rem; text-align: right; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">الحالة</th>
                            <th style="padding: 1rem; text-align: right; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">تاريخ الإنشاء</th>
                            <th style="padding: 1rem; text-align: center; color: #7f8c8d; font-size: 0.9rem; font-weight: 600;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr style="border-bottom: 1px solid #ecf0f1; {{ $user->trashed() ? 'background-color: #ffeaea;' : '' }}">
                            <td style="padding: 1rem; font-weight: 600;">{{ $user->name }}</td>
                            <td style="padding: 1rem; font-family: monospace;">{{ $user->username }}</td>
                            <td style="padding: 1rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; background-color: {{ $user->role === 'manager' ? 'rgba(52, 152, 219, 0.1)' : 'rgba(149, 165, 166, 0.1)' }}; color: {{ $user->role === 'manager' ? '#3498db' : '#95a5a6' }};">
                                    {{ $user->role_display }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600; background-color: {{ $user->trashed() ? 'rgba(231, 76, 60, 0.1)' : ($user->is_active ? 'rgba(39, 174, 96, 0.1)' : 'rgba(149, 165, 166, 0.1)') }}; color: {{ $user->status_color }};">
                                    {{ $user->status_display }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td style="padding: 1rem; text-align: center;">
                                <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                    @if(!$user->trashed())
                                        <!-- Edit -->
                                        <a href="{{ route('settings.users.edit', $user) }}"
                                           style="display: inline-block; background-color: transparent; color: #3498db; padding: 0.5rem 1rem; border: 2px solid #3498db; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.85rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                                           onmouseover="this.style.backgroundColor='rgba(52, 152, 219, 0.1)'"
                                           onmouseout="this.style.backgroundColor='transparent'">
                                            تعديل
                                        </a>

                                        <!-- Toggle Status -->
                                        @if($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('settings.users.toggle-status', $user) }}"
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit"
                                                    style="background-color: transparent; color: {{ $user->is_active ? '#f39c12' : '#27ae60' }}; padding: 0.5rem 1rem; border: 2px solid {{ $user->is_active ? '#f39c12' : '#27ae60' }}; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.85rem; transition: all 0.3s ease;"
                                                    onmouseover="this.style.backgroundColor='rgba(243, 156, 18, 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                {{ $user->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>
                                        @endif

                                        <!-- Delete -->
                                        @if($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('settings.users.destroy', $user) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="background-color: transparent; color: #e74c3c; padding: 0.5rem 1rem; border: 2px solid #e74c3c; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.85rem; transition: all 0.3s ease;"
                                                    onmouseover="this.style.backgroundColor='rgba(231, 76, 60, 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                حذف
                                            </button>
                                        </form>
                                        @endif
                                    @else
                                        <!-- Restore -->
                                        <form method="POST"
                                              action="{{ route('settings.users.restore', $user) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('هل أنت متأكد من استعادة هذا المستخدم؟')">
                                            @csrf
                                            <button type="submit"
                                                    style="background-color: transparent; color: #27ae60; padding: 0.5rem 1rem; border: 2px solid #27ae60; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.85rem; transition: all 0.3s ease;"
                                                    onmouseover="this.style.backgroundColor='rgba(39, 174, 96, 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                استعادة
                                            </button>
                                        </form>

                                        <!-- Force Delete -->
                                        @if($user->id !== auth()->id())
                                        <form method="POST"
                                              action="{{ route('settings.users.force-delete', $user) }}"
                                              style="display: inline;"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟ لا يمكن التراجع عن هذا الإجراء.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="background-color: transparent; color: #c0392b; padding: 0.5rem 1rem; border: 2px solid #c0392b; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 0.85rem; transition: all 0.3s ease;"
                                                    onmouseover="this.style.backgroundColor='rgba(192, 57, 43, 0.1)'"
                                                    onmouseout="this.style.backgroundColor='transparent'">
                                                حذف نهائي
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; color: #7f8c8d; padding: 2rem;">
                    لا يوجد مستخدمين
                </div>
            @endif
        </div>
    </div>

@endsection
