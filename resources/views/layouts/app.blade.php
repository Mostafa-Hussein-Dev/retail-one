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
                    <a href="{{ route('returns.index') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}">المرتجعات</a>
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">التقارير</a>
                    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">الإعدادات</a>
                @else
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">نقطة البيع</a>
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">مبيعاتي</a>
                    <a href="{{ route('returns.index') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}">المرتجعات</a>
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

<!-- Custom Modal System -->
<div id="customModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div id="customModalBox" style="background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); max-width: 500px; width: 90%; animation: modalSlideIn 0.3s ease;">
        <div id="customModalHeader" style="padding: 1.5rem; border-bottom: 1px solid #ecf0f1; display: flex; align-items: center; gap: 1rem;">
            <span id="customModalIcon" style="font-size: 2rem;"></span>
            <h3 id="customModalTitle" style="margin: 0; color: #2c3e50; font-size: 1.3rem;"></h3>
        </div>
        <div id="customModalBody" style="padding: 1.5rem; color: #34495e; font-size: 1rem; line-height: 1.6; white-space: pre-line;"></div>
        <div id="customModalFooter" style="padding: 1rem 1.5rem; border-top: 1px solid #ecf0f1; display: flex; gap: 0.75rem; justify-content: flex-end;"></div>
    </div>
</div>

<style>
@keyframes modalSlideIn {
    from {
        transform: scale(0.9) translateY(-20px);
        opacity: 0;
    }
    to {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}
</style>

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

    // Custom Modal System
    function showAlertDialog(options) {
        return new Promise((resolve) => {
            const overlay = document.getElementById('customModalOverlay');
            const title = document.getElementById('customModalTitle');
            const icon = document.getElementById('customModalIcon');
            const body = document.getElementById('customModalBody');
            const footer = document.getElementById('customModalFooter');

            // Set content based on type
            const types = {
                success: { icon: '✓', color: '#27ae60', title: 'نجاح' },
                error: { icon: '✕', color: '#e74c3c', title: 'خطأ' },
                warning: { icon: '⚠', color: '#f39c12', title: 'تحذير' },
                info: { icon: 'ℹ', color: '#3498db', title: 'معلومات' }
            };

            const type = types[options.type] || types.info;
            const customTitle = options.title || type.title;

            title.textContent = customTitle;
            title.style.color = type.color;
            icon.textContent = type.icon;
            body.textContent = options.message;

            // Create OK button
            footer.innerHTML = '';
            const okBtn = document.createElement('button');
            okBtn.textContent = options.confirmText || 'موافق';
            okBtn.style.cssText = `
                background: ${type.color};
                color: white;
                border: none;
                padding: 12px 32px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.3s ease;
            `;
            okBtn.onmouseover = () => okBtn.style.opacity = '0.8';
            okBtn.onmouseout = () => okBtn.style.opacity = '1';
            okBtn.onclick = () => {
                overlay.style.display = 'none';
                resolve(true);
            };
            footer.appendChild(okBtn);

            // Show modal
            overlay.style.display = 'flex';
        });
    }

    function showConfirmDialog(options) {
        return new Promise((resolve) => {
            const overlay = document.getElementById('customModalOverlay');
            const title = document.getElementById('customModalTitle');
            const icon = document.getElementById('customModalIcon');
            const body = document.getElementById('customModalBody');
            const footer = document.getElementById('customModalFooter');

            const type = options.type || 'warning';
            const types = {
                success: { icon: '✓', color: '#27ae60', title: 'تأكيد' },
                error: { icon: '✕', color: '#e74c3c', title: 'تأكيد الحذف' },
                warning: { icon: '⚠', color: '#f39c12', title: 'تأكيد' },
                info: { icon: 'ℹ', color: '#3498db', title: 'تأكيد' }
            };

            const config = types[type] || types.warning;

            title.textContent = options.title || config.title;
            title.style.color = config.color;
            icon.textContent = config.icon;
            body.textContent = options.message;

            // Create buttons
            footer.innerHTML = '';

            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = options.cancelText || 'إلغاء';
            cancelBtn.style.cssText = `
                background: transparent;
                color: #95a5a6;
                border: 2px solid #95a5a6;
                padding: 12px 32px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.3s ease;
            `;
            cancelBtn.onmouseover = () => cancelBtn.style.backgroundColor = 'rgba(149, 165, 166, 0.1)';
            cancelBtn.onmouseout = () => cancelBtn.style.backgroundColor = 'transparent';
            cancelBtn.onclick = () => {
                overlay.style.display = 'none';
                resolve(false);
            };

            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = options.confirmText || 'تأكيد';
            confirmBtn.style.cssText = `
                background: ${config.color};
                color: white;
                border: none;
                padding: 12px 32px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.3s ease;
            `;
            confirmBtn.onmouseover = () => confirmBtn.style.opacity = '0.8';
            confirmBtn.onmouseout = () => confirmBtn.style.opacity = '1';
            confirmBtn.onclick = () => {
                overlay.style.display = 'none';
                resolve(true);
            };

            footer.appendChild(cancelBtn);
            footer.appendChild(confirmBtn);

            // Show modal
            overlay.style.display = 'flex';
        });
    }

    function showPromptDialog(options) {
        return new Promise((resolve) => {
            const overlay = document.getElementById('customModalOverlay');
            const title = document.getElementById('customModalTitle');
            const icon = document.getElementById('customModalIcon');
            const body = document.getElementById('customModalBody');
            const footer = document.getElementById('customModalFooter');

            const type = options.type || 'info';
            const types = {
                success: { icon: '✓', color: '#27ae60', title: 'إدخال' },
                error: { icon: '✕', color: '#e74c3c', title: 'إدخال مطلوب' },
                warning: { icon: '⚠', color: '#f39c12', title: 'إدخال' },
                info: { icon: 'ℹ', color: '#3498db', title: 'إدخال' }
            };

            const config = types[type] || types.info;

            title.textContent = options.title || config.title;
            title.style.color = config.color;
            icon.textContent = config.icon;

            // Create input field in body
            body.innerHTML = '';
            const message = document.createElement('div');
            message.textContent = options.message;
            message.style.marginBottom = '1rem';
            body.appendChild(message);

            const input = document.createElement('input');
            input.type = options.inputType || 'text';
            input.placeholder = options.placeholder || '';
            input.value = options.defaultValue || '';
            input.style.cssText = `
                width: 100%;
                padding: 12px;
                border: 2px solid #bdc3c7;
                border-radius: 6px;
                font-size: 1rem;
                font-family: inherit;
                box-sizing: border-box;
            `;
            input.onkeypress = (e) => {
                if (e.key === 'Enter') confirmBtn.click();
            };
            body.appendChild(input);

            // Create buttons
            footer.innerHTML = '';

            const cancelBtn = document.createElement('button');
            cancelBtn.textContent = options.cancelText || 'إلغاء';
            cancelBtn.style.cssText = `
                background: transparent;
                color: #95a5a6;
                border: 2px solid #95a5a6;
                padding: 12px 32px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.3s ease;
            `;
            cancelBtn.onmouseover = () => cancelBtn.style.backgroundColor = 'rgba(149, 165, 166, 0.1)';
            cancelBtn.onmouseout = () => cancelBtn.style.backgroundColor = 'transparent';
            cancelBtn.onclick = () => {
                overlay.style.display = 'none';
                resolve(null);
            };

            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = options.confirmText || 'تأكيد';
            confirmBtn.style.cssText = `
                background: ${config.color};
                color: white;
                border: none;
                padding: 12px 32px;
                border-radius: 6px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                font-family: inherit;
                transition: all 0.3s ease;
            `;
            confirmBtn.onmouseover = () => confirmBtn.style.opacity = '0.8';
            confirmBtn.onmouseout = () => confirmBtn.style.opacity = '1';
            confirmBtn.onclick = () => {
                overlay.style.display = 'none';
                resolve(input.value);
            };

            footer.appendChild(cancelBtn);
            footer.appendChild(confirmBtn);

            // Show modal
            overlay.style.display = 'flex';

            // Focus and select input
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        });
    }

    // Close modal on overlay click (outside modal box)
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('customModalOverlay');
        const modalBox = document.getElementById('customModalBox');

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });

        // Dropdown functionality
        const dropdownToggle = document.querySelector('.dropdown-toggle');
        const dropdownContent = document.querySelector('.dropdown-content');

        if (dropdownToggle && dropdownContent) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-content').forEach(content => {
                    if (content !== dropdownContent) {
                        content.style.display = 'none';
                    }
                });

                // Toggle current dropdown
                if (dropdownContent.style.display === 'block') {
                    dropdownContent.style.display = 'none';
                } else {
                    dropdownContent.style.display = 'block';
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdownContent.style.display = 'none';
                }
            });

            // Hover effect for dropdown items
            const dropdownItems = dropdownContent.querySelectorAll('a');
            dropdownItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f1f1f1';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = 'white';
                });
            });
        }
    });

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
