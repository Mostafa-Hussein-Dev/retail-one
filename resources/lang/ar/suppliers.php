<?php

return [
    // General
    'suppliers' => 'الموردين',
    'supplier' => 'مورد',
    'add_supplier' => 'إضافة مورد',
    'edit_supplier' => 'تعديل مورد',
    'supplier_details' => 'تفاصيل المورد',
    'supplier_list' => 'قائمة الموردين',

    // Fields
    'name' => 'الاسم',
    'contact_person' => 'الشخص المسؤول',
    'phone' => 'الهاتف',
    'address' => 'العنوان',
    'total_debt' => 'إجمالي المديونية',
    'current_debt' => 'المديونية الحالية',
    'status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',

    // Actions
    'search_supplier' => 'بحث عن مورد',
    'filter_suppliers' => 'تصفية الموردين',
    'all_suppliers' => 'جميع الموردين',
    'active_suppliers' => 'الموردين النشطون',
    'inactive_suppliers' => 'الموردين غير النشطين',
    'suppliers_with_debt' => 'الموردين المدينون',
    'toggle_status' => 'تغيير الحالة',
    'delete_supplier' => 'حذف المورد',
    'record_payment' => 'تسجيل دفعة',
    'pay' => 'دفع',

    // Purchases
    'purchases' => 'المشتريات',
    'purchase' => 'شراء',
    'add_purchase' => 'إضافة شراء',
    'new_purchase' => 'شراء جديد',
    'purchase_number' => 'رقم الشراء',
    'purchase_date' => 'تاريخ الشراء',
    'total_amount' => 'الإجمالي',
    'paid_amount' => 'المدفوع',
    'debt_amount' => 'المتبقي',
    'payment_method' => 'طريقة الدفع',
    'cash' => 'نقدي',
    'debt' => 'دين',
    'notes' => 'ملاحظات',

    // Purchase Items
    'product' => 'المنتج',
    'quantity' => 'الكمية',
    'unit_cost' => 'سعر الوحدة',
    'total_cost' => 'الإجمالي',
    'add_product' => 'إضافة منتج',

    // Debt
    'debt_status' => 'حالة المديونية',
    'no_debt' => 'لا توجد مديونية',
    'we_owe' => 'ندين للمورد',
    'supplier_owes' => 'المورد مدين لنا',
    'purchases_with_debt' => 'المشتريات غير المسددة',
    'transaction_history' => 'سجل المعاملات',
    'payment' => 'دفعة',
    'running_balance' => 'الرصيد الجاري',

    // Status
    'voided' => 'ملغي',
    'paid' => 'مدفوع',
    'partially_paid' => 'مدفوع جزئياً',

    // Messages
    'supplier_added' => 'تم إضافة المورد بنجاح',
    'supplier_updated' => 'تم تحديث بيانات المورد بنجاح',
    'supplier_deleted' => 'تم حذف المورد بنجاح',
    'supplier_activated' => 'تم تفعيل المورد بنجاح',
    'supplier_deactivated' => 'تم إلغاء تفعيل المورد بنجاح',
    'purchase_created' => 'تم إنشاء الشراء بنجاح',
    'purchase_voided' => 'تم إلغاء الشراء بنجاح',
    'payment_recorded' => 'تم تسجيل الدفعة بنجاح',
    'cannot_delete_with_debt' => 'لا يمكن حذف مورد لديه مديونية نشطة',
    'cannot_delete_with_purchases' => 'لا يمكن حذف مورد لديه مشتريات مسجلة',
    'cannot_delete_with_products' => 'لا يمكن حذف مورد لديه منتجات مرتبطة',
    'cannot_inactivate_with_debt' => 'لا يمكن إلغاء تفعيل مورد لديه مديونية نشطة',

    // Validation
    'name_required' => 'الاسم مطلوب',
    'supplier_required' => 'المورد مطلوب',
    'items_required' => 'يجب إضافة منتج واحد على الأقل',
    'quantity_required' => 'الكمية مطلوبة',
    'unit_cost_required' => 'سعر الوحدة مطلوب',
];
