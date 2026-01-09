<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول - RetailOne</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>

<body style="background: #f4f6f8; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<div style="background: white; padding: 3rem; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); width: 100%; max-width: 400px;">

    <h1 style="text-align: center; font-size: 1.8rem; font-weight: 700; color: #2c3e50; margin-bottom: 2rem;">
        تسجيل الدخول
    </h1>

    @if (session('success'))
        <div class="message" style="margin-bottom: 1rem;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="error" style="margin-bottom: 1rem;">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <label for="username">اسم المستخدم</label>
        <input type="text"
               id="username"
               name="username"
               value="{{ old('username') }}"
               required
               autofocus>

        <label for="password">كلمة المرور</label>
        <input type="password"
               id="password"
               name="password"
               required>

        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
            <input type="checkbox"
                   id="remember"
                   name="remember"
                   {{ old('remember') ? 'checked' : '' }}
                   style="width: 18px; height: 18px; margin: 0;">
            <label for="remember" style="margin: 0;">تذكرني</label>
        </div>

        <button type="submit" style="width: 100%; padding: 12px; font-size: 1rem;">
            دخول
        </button>
    </form>

    <div style="text-align: center; margin-top: 2rem; color: #7f8c8d; font-size: 0.9rem;">
        RetailOne © {{ date('Y') }}
    </div>
</div>
</body>
</html>
