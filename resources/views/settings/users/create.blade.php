@extends('layouts.app')

@section('content')

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>إضافة مستخدم جديد</h1>
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
                <form method="POST" action="{{ route('settings.users.store') }}">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Name -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                الاسم <span style="color: #e74c3c;">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
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
                                   value="{{ old('username') }}"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('username')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <!-- Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                كلمة المرور <span style="color: #e74c3c;">*</span>
                            </label>
                            <input type="password"
                                   name="password"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                            @error('password')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #2c3e50; font-weight: 600;">
                                تأكيد كلمة المرور <span style="color: #e74c3c;">*</span>
                            </label>
                            <input type="password"
                                   name="password_confirmation"
                                   required
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #ecf0f1; border-radius: 6px; font-family: inherit; font-size: 1rem;"
                                   onfocus="this.style.borderColor='#3498db'"
                                   onblur="this.style.borderColor='#ecf0f1'">
                        </div>

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
                                <option value="cashier">أمين صندوق</option>
                                <option value="manager">مدير</option>
                            </select>
                            @error('role')
                                <div style="color: #e74c3c; font-size: 0.85rem; margin-top: 0.25rem;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div style="margin-bottom: 2rem;">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', '1') ? 'checked' : '' }}
                                   style="width: 20px; height: 20px; cursor: pointer;">
                            <span style="color: #2c3e50; font-weight: 600;">حساب نشط</span>
                        </label>
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
                                style="background-color: #27ae60; color: white; padding: 0.75rem 2rem; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-family: inherit; font-size: 1rem; transition: all 0.3s ease;"
                                onmouseover="this.style.backgroundColor='#229954'"
                                onmouseout="this.style.backgroundColor='#27ae60'">
                            إنشاء المستخدم
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
