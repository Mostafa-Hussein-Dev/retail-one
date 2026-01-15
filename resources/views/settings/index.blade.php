@extends('layouts.app')

@section('content')

    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1.75rem;">الإعدادات</h1>
        <p style="margin: 0; color: #7f8c8d;">إدارة إعدادات النظام والمتجر</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
        <!-- My Profile (all users) -->
        <a href="{{ route('settings.profile') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#16a085'; this.style.boxShadow='0 2px 8px rgba(22, 160, 133, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #16a085;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">ملفي الشخصي</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تعديل معلومات حسابي</p>
        </a>

        @if(auth()->user()->role === 'manager')
        <!-- User Management (manager only) -->
        <a href="{{ route('settings.users.index') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#8e44ad'; this.style.boxShadow='0 2px 8px rgba(142, 68, 173, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #8e44ad;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">إدارة المستخدمين</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">عرض وإضافة وتعديل المستخدمين</p>
        </a>

        <!-- Store Settings (manager only) -->
        <a href="{{ route('settings.store') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#3498db'; this.style.boxShadow='0 2px 8px rgba(52, 152, 219, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #3498db;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">معلومات المتجر</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">معلومات المتجر، الشعار، إعدادات الإيصال</p>
        </a>

        <!-- Exchange Rate (manager only) -->
        <a href="{{ route('settings.exchange-rate') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#27ae60'; this.style.boxShadow='0 2px 8px rgba(39, 174, 96, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #27ae60;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">سعر الصرف</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">تحديث سعر الصرف بين الدولار والليرة</p>
        </a>

        <!-- Preferences (manager only) -->
        <a href="{{ route('settings.preferences') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#f39c12'; this.style.boxShadow='0 2px 8px rgba(243, 156, 18, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #f39c12;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">التفضيلات</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">إعدادات النظام والمخزون</p>
        </a>
        @endif

        <!-- Backup (all users) -->
        <a href="{{ route('settings.backup') }}"
           style="display: block; padding: 1.25rem; background: white; border: 1px solid #ecf0f1; border-radius: 8px; text-decoration: none; transition: all 0.2s ease; position: relative; overflow: hidden;"
           onmouseover="this.style.borderColor='#e74c3c'; this.style.boxShadow='0 2px 8px rgba(231, 76, 60, 0.15)';"
           onmouseout="this.style.borderColor='#ecf0f1'; this.style.boxShadow='none';">
            <div style="width: 4px; height: 100%; position: absolute; right: 0; top: 0; background: #e74c3c;"></div>
            <h3 style="margin: 0 0 0.5rem 0; color: #2c3e50; font-size: 1rem; font-weight: 600;">النسخ الاحتياطي</h3>
            <p style="margin: 0; color: #7f8c8d; font-size: 0.85rem;">إدارة النسخ الاحتياطية لقاعدة البيانات</p>
        </a>
    </div>

@endsection
