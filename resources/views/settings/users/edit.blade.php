@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>تعديل المستخدم</h1>
        <a href="{{ route('settings.users.index') }}"
           style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 12px 40px; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
           onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
           onmouseout="this.style.backgroundColor='transparent'">
            العودة لقائمة المستخدمين
        </a>
    </div>

    <div style="max-width: 800px;">
        <div class="card">
            <div style="padding: 2rem;">
                <form method="POST" action="{{ route('settings.users.update', $user) }}">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Name -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                الاسم <span style="color: #e74c3c;">*</span>
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
                                اسم المستخدم <span style="color: #e74c3c;">*</span>
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- New Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                كلمة المرور الجديدة
                            </label>
                            <input type="password"
                                   name="password"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 0.25rem;">اتركه فارغاً للإبقاء على كلمة المرور الحالية</div>
                            @error('password')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                تأكيد كلمة المرور الجديدة
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Role -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                الدور <span style="color: #e74c3c;">*</span>
                            </label>
                            <select name="role"
                                    required
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem; background-color: white;"
                                    onfocus="this.style.borderColor='#3498db'"
                                    onblur="this.style.borderColor='#ecf0f1'">
                                <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>أمين صندوق</option>
                                <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>مدير</option>
                            </select>
                            @error('role')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                الحالة
                            </label>
                            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0;">
                                <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                                    <input type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                           style="width: 20px; height: 20px; cursor: pointer;">
                                    <span style="color: #2c3e50; font-weight: 600;">حساب نشط</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- User Info Display -->
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 6px; margin-bottom: 2rem;">
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">تاريخ الإنشاء</div>
                                <div style="color: #2c3e50; font-weight: 600;">{{ $user->created_at->format('Y-m-d') }}</div>
                            </div>
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">آخر تسجيل دخول</div>
                                <div style="color: #2c3e50; font-weight: 600;">{{ $user->last_login_at?->format('Y-m-d H:i') ?? 'لم يسجل دخوله بعد' }}</div>
                            </div>
                            <div>
                                <div style="color: #7f8c8d; font-size: 0.85rem; margin-bottom: 0.25rem;">الحالة الحالية</div>
                                <div style="color: {{ $user->status_color }}; font-weight: 600;">{{ $user->status_display }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="{{ route('settings.users.index') }}"
                           style="display: inline-block; background-color: transparent; color: #95a5a6; padding: 0.75rem 2rem; border: 2px solid #95a5a6; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; text-align: center; transition: all 0.3s ease; line-height: normal; text-decoration: none;"
                           onmouseover="this.style.backgroundColor='rgba(149, 165, 166, 0.1)'"
                           onmouseout="this.style.backgroundColor='transparent'">
                            إلغاء
                        </a>
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
