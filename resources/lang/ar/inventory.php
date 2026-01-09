<?php

return [
    // General
    'products' => 'المنتجات',
    'categories' => 'الفئات',
    'search' => 'بحث',
    'filter' => 'تصفية',
    'actions' => 'العمليات',
    'status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'all' => 'الجميع',
    'create' => 'إنشاء',
    'edit' => 'تعديل',
    'view' => 'عرض',
    'delete' => 'حذف',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'update' => 'تحديث',
    'back_to_list' => 'العودة للقائمة',

    // Products
    'product_management' => 'إدارة المنتجات',
    'add_new_product' => 'إضافة منتج جديد',
    'edit_product' => 'تعديل المنتج',
    'product_details' => 'تفاصيل المنتج',
    'product_name' => 'اسم المنتج',
    'product_name_en' => 'اسم المنتج (بالإنجليزية)',
    'product_name_ar' => 'اسم المنتج (بالعربية)',
    'barcode' => 'الباركود',
    'category' => 'الفئة',
    'cost_price' => 'سعر التكلفة',
    'selling_price' => 'سعر البيع',
    'quantity' => 'الكمية',
    'minimum_quantity' => 'الكمية الأدنى',
    'unit' => 'الوحدة',
    'description' => 'الوصف',
    'image' => 'الصورة',
    'current_image' => 'الصورة الحالية',
    'change_image' => 'تغيير الصورة',
    'profit_margin' => 'هامش الربح',
    'stock_status' => 'حالة المخزون',
    'in_stock' => 'متوفر',
    'low_stock' => 'مخزون منخفض',
    'out_of_stock' => 'نفد من المخزون',
    'auto_generate_barcode' => 'سيتم إنشاؤه تلقائياً إذا ترك فارغاً',

    // Units
    'units' => [
        'piece' => 'قطعة',
        'kg' => 'كيلوغرام',
        'gram' => 'غرام',
        'liter' => 'لتر',
        'meter' => 'متر',
    ],

    // Categories
    'category_management' => 'إدارة الفئات',
    'add_new_category' => 'إضافة فئة جديدة',
    'edit_category' => 'تعديل الفئة',
    'category_details' => 'تفاصيل الفئة',
    'category_name' => 'اسم الفئة',
    'category_name_en' => 'اسم الفئة (بالإنجليزية)',
    'category_name_ar' => 'اسم الفئة (بالعربية)',
    'category_description' => 'وصف الفئة',
    'products_count' => 'عدد المنتجات',
    'active_products' => 'المنتجات النشطة',
    'creation_date' => 'تاريخ الإنشاء',
    'last_update' => 'آخر تحديث',

    // Stock Management
    'stock_alerts' => 'تنبيهات المخزون',
    'low_stock_alert' => 'منتج بمخزون منخفض',
    'out_of_stock_alert' => 'منتج نفد من المخزون',
    'adjust_stock' => 'تعديل المخزون',
    'new_quantity' => 'الكمية الجديدة',
    'adjustment_reason' => 'سبب التعديل',
    'quick_adjustments' => 'تعديلات سريعة',
    'stock_value' => 'قيمة المخزون',
    'total_cost_value' => 'إجمالي قيمة التكلفة',
    'total_selling_value' => 'إجمالي قيمة البيع',
    'potential_profit' => 'الربح المحتمل',

    // Pricing
    'pricing' => 'الأسعار',
    'cost' => 'التكلفة',
    'selling' => 'البيع',
    'profit' => 'الربح',
    'profit_per_unit' => 'ربح لكل وحدة',
    'profit_preview' => 'معاينة الربح',
    'current_stats' => 'الإحصائيات الحالية',

    // Search and Filter
    'search_placeholder' => 'البحث بالاسم أو الباركود',
    'category_filter' => 'تصفية بالفئة',
    'status_filter' => 'تصفية بالحالة',
    'all_categories' => 'جميع الفئات',
    'all_statuses' => 'جميع الحالات',
    'clear_filters' => 'مسح التصفية',

    // Messages
    'messages' => [
        'product_created' => 'تم إضافة المنتج بنجاح',
        'product_updated' => 'تم تحديث المنتج بنجاح',
        'product_deleted' => 'تم حذف المنتج بنجاح',
        'product_activated' => 'تم تفعيل المنتج بنجاح',
        'product_deactivated' => 'تم إلغاء تفعيل المنتج بنجاح',
        'stock_adjusted' => 'تم تعديل الكمية بنجاح',
        'category_created' => 'تم إضافة الفئة بنجاح',
        'category_updated' => 'تم تحديث الفئة بنجاح',
        'category_deleted' => 'تم حذف الفئة بنجاح',
        'category_activated' => 'تم تفعيل الفئة بنجاح',
        'category_deactivated' => 'تم إلغاء تفعيل الفئة بنجاح',
        'category_has_products' => 'لا يمكن حذف الفئة لأنها تحتوي على منتجات',
        'product_not_found' => 'المنتج غير موجود',
        'confirm_delete_product' => 'هل أنت متأكد من حذف المنتج؟ هذا الإجراء لا يمكن التراجع عنه.',
        'confirm_delete_category' => 'هل أنت متأكد من حذف الفئة؟ هذا الإجراء لا يمكن التراجع عنه.',
        'confirm_toggle_status' => 'هل أنت متأكد من تغيير حالة العنصر؟',
        'confirm_adjust_stock' => 'هل أنت متأكد من تعديل الكمية؟',
    ],

    // Validation
    'validation' => [
        'name_required' => 'اسم المنتج مطلوب',
        'name_ar_required' => 'الاسم بالعربية مطلوب',
        'category_name_required' => 'اسم الفئة مطلوب',
        'cost_price_required' => 'سعر التكلفة مطلوب',
        'selling_price_required' => 'سعر البيع مطلوب',
        'quantity_required' => 'الكمية مطلوبة',
        'unit_required' => 'الوحدة مطلوبة',
        'barcode_unique' => 'هذا الباركود موجود مسبقاً',
        'category_name_unique' => 'اسم الفئة موجود مسبقاً',
        'image_max_size' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
        'image_format' => 'صيغة الصورة يجب أن تكون JPG, PNG, أو GIF',
    ],

    // Empty States
    'empty_states' => [
        'no_products' => 'لا توجد منتجات',
        'no_categories' => 'لا توجد فئات',
        'no_products_in_category' => 'لا توجد منتجات في هذه الفئة',
        'start_by_adding' => 'ابدأ بإضافة',
        'add_first_product' => 'ابدأ بإضافة منتجك الأول',
        'add_first_category' => 'ابدأ بإنشاء الفئة الأولى لتنظيم منتجاتك',
    ],

    // Navigation
    'nav' => [
        'products_list' => 'قائمة المنتجات',
        'categories_list' => 'قائمة الفئات',
        'add_product' => 'إضافة منتج',
        'add_category' => 'إضافة فئة',
    ],

    // Dashboard Integration
    'dashboard' => [
        'recent_products' => 'المنتجات الحديثة',
        'total_products' => 'إجمالي المنتجات',
        'total_categories' => 'إجمالي الفئات',
        'active_categories' => 'الفئات النشطة',
        'average_products' => 'متوسط المنتجات',
        'inventory_management' => 'إدارة المخزون',
        'track_products_quantities' => 'تتبع المنتجات والكميات',
        'manage_products' => 'إدارة المنتجات',
    ],
];
