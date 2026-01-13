<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'RetailOne') }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('styles')
</head>

<body>
<header class="topbar">
    <h1>RetailOne</h1>

    @auth
        <nav>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">الرئيسية</a>
                @if(auth()->user()->role === 'manager')
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">نقطة البيع</a>
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">المبيعات</a>
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">المنتجات</a>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">الفئات</a>
                    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers*') ? 'active' : '' }}">العملاء</a>
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers*') ? 'active' : '' }}">الموردين</a>
                    <a href="{{ route('purchases.index') }}" class="{{ request()->routeIs('purchases*') ? 'active' : '' }}">المشتريات</a>
                    <a href="#" class="{{ request()->is('returns*') ? 'active' : '' }}">المرتجعات</a>
                    <a href="#" class="{{ request()->is('reports*') ? 'active' : '' }}">التقارير</a>
                    <a href="#" class="{{ request()->is('settings*') ? 'active' : '' }}">الإعدادات</a>
                @else
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">نقطة البيع</a>
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">مبيعاتي</a>
                    <a href="#" class="{{ request()->is('returns*') ? 'active' : '' }}">المرتجعات</a>
                @endif
        </nav>

        <div class="user-section">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <span class="user-role">({{ auth()->user()->role === 'manager' ? 'مدير' : 'أمين صندوق' }})</span>
            <form method="POST" action="{{ route('logout') }}" style="display: inline; margin-right: 15px;">
                @csrf
                <button type="submit" class="logout-btn">تسجيل خروج</button>
            </form>
        </div>
    @endauth
</header>

<main class="container">
    @yield('content')
</main>

<footer class="foot">RetailOne © {{ date('Y') }} </footer>

@stack('scripts')
<script>
    // Convert Laravel flash messages to popup notifications
    @if(session('success'))
    showMessage('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
    showMessage('{{ session('error') }}', 'error');
    @endif

    @if(session('warning'))
    showMessage('{{ session('warning') }}', 'warning');
    @endif

    function showMessage(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#fff3cd'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#856404'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#ffeaa7'};
        padding: 15px 25px;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        font-family: "Inter", "Cairo", "Tajawal", sans-serif;
        font-weight: 800;
        max-width: 400px;
        font-size: 18px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

        toast.textContent = message;
        document.body.appendChild(toast);

        // Slide in from right
        setTimeout(() => toast.style.transform = 'translateX(0)', 100);

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
</body>
</html>
