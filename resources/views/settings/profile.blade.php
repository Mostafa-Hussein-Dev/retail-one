@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>ملفي الشخصي</h1>
        <a href="{{ route('settings.index') }}"
           style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 12px 40px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة للإعدادات
        </a>
    </div>

    <div style="max-width: 800px;">
        <div class="card">
            <div style="padding: 2rem;">
                <h3 style="margin: 0 0 1.5rem 0; color: #2c3e50; font-size: 1.25rem;">معلومات الحساب</h3>

                <form method="POST" action="{{ route('settings.profile.update') }}">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Name -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                الاسم
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('name')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                اسم المستخدم
                            </label>
                            <input type="text"
                                   name="username"
                                   value="{{ old('username', $user->username) }}"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('username')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h4 style="margin: 2rem 0 1rem 0; color: #2c3e50; font-size: 1.1rem; border-bottom: 2px solid #ecf0f1; padding-bottom: 0.5rem;">
                        تغيير كلمة المرور
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                        <!-- Current Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                كلمة المرور الحالية
                            </label>
                            <input type="password"
                                   name="current_password"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('current_password')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                كلمة المرور الجديدة
                            </label>
                            <input type="password"
                                   name="new_password"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('new_password')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                تأكيد كلمة المرور الجديدة
                            </label>
                            <input type="password"
                                   name="new_password_confirmation"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                        </div>
                    </div>

                    <!-- Account Info Display -->
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 6px; margin-bottom: 2rem;">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الدور</div>
                                <div style="color: #2c3e50; font-weight: 600;">{{ $user->role_display }}</div>
                            </div>
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الحالة</div>
                                <div style="color: {{ $user->status_color }}; font-weight: 600;">{{ $user->status_display }}</div>
                            </div>
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">آخر تسجيل دخول</div>
                                <div style="color: #2c3e50; font-weight: 600;">{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'لم يسجل دخوله بعد' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="submit"
                                style="background-color: #3498db; color: white; padding: 0.75rem 2rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#2980b9'"
                                onmouseout="this.style.backgroundColor='#3498db'">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
