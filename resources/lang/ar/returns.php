<?php

return [
    // General
    'returns' => 'المرتجعات',
    'return' => 'إرجاع',
    'new_return' => 'إرجاع جديد',
    'return_details' => 'تفاصيل الإرجاع',
    'return_list' => 'قائمة المرتجعات',
    'process_return' => 'معالجة الإرجاع',

    // Fields
    'return_number' => 'رقم الإرجاع',
    'return_date' => 'تاريخ الإرجاع',
    'receipt_number' => 'رقم الإيصال',
    'original_sale' => 'البيع الأصلي',
    'reason' => 'السبب',
    'total_return_amount' => 'إجمالي الإرجاع',
    'cash_refund_amount' => 'الاسترداد النقدي',
    'debt_reduction_amount' => 'تخفيض الدين',
    'payment_method' => 'طريقة الاسترداد',

    // Return Wizard Steps
    'step_1' => 'الخطوة 1: البحث عن البيع',
    'step_2' => 'الخطوة 2: اختيار العناصر',
    'step_3' => 'الخطوة 3: التأكيد',
    'enter_receipt_number' => 'أدخل رقم الإيصال',
    'search' => 'بحث',
    'select_items_to_return' => 'اختر العناصر للإرجاع',

    // Items Table
    'product' => 'المنتج',
    'quantity_sold' => 'الكمية المباعة',
    'quantity_returned' => 'تم إرجاعها',
    'quantity_available' => 'المتاحة للإرجاع',
    'quantity_to_return' => 'كمية الإرجاع',
    'unit_price' => 'سعر الوحدة',
    'total' => 'الإجمالي',
    'return_this_item' => 'إرجاع هذا العنصر',

    // Payment Methods
    'cash_refund' => 'استرداد نقدي',
    'debt_reduction' => 'تخفيض دين',
    'mixed' => 'مختلط',

    // Status
    'active' => 'نشط',
    'voided' => 'ملغي',
    'void_return' => 'إلغاء الإرجاع',

    // Actions
    'print_receipt' => 'طباعة إيصال',
    'view_original_sale' => 'عرض البيع الأصلي',
    'void' => 'إلغاء',

    // Messages
    'return_processed' => 'تم معالجة الإرجاع بنجاح',
    'return_voided' => 'تم إلغاء الإرجاع بنجاح',
    'sale_not_found' => 'لم يتم العثور على الإيصال',
    'cannot_return_voided_sale' => 'لا يمكن إرجاع عناصر من بيع ملغي',
    'all_items_returned' => 'جميع العناصر تم إرجاعها بالفعل',
    'no_returnable_items' => 'لا توجد عناصر متاحة للإرجاع',
    'invalid_quantity' => 'الكمية غير صحيحة',
    'stock_restored' => 'تم استرجاع المخزون',
    'debt_reduced' => 'تم تخفيض دين العميل',
    'cash_refunded' => 'تم الاسترداد النقدي',

    // Warnings
    'refund_cash_to_customer' => 'يجب استرداد المبلغ نقداً للعميل',
    'customer_debt_reduced' => 'تم تخفيض دين العميل',
    'void_warning' => 'سيتم عكس جميع التغييرات (المخزون، الدين، الاسترداد)',

    // Reasons (predefined)
    'reason_damaged' => 'منتج تالف',
    'reason_wrong_item' => 'منتج خاطئ',
    'reason_changed_mind' => 'تغيير رأي العميل',
    'reason_defective' => 'منتج معيب',
    'reason_expired' => 'منتج منتهي الصلاحية',
    'reason_other' => 'أخرى',

    // Validation
    'reason_required' => 'السبب مطلوب',
    'items_required' => 'يجب اختيار عنصر واحد على الأقل',
    'receipt_required' => 'رقم الإيصال مطلوب',
];
