<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDebtController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierDebtController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // Redirect root to dashboard (must be inside auth middleware)
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product Management Routes
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::patch('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
    Route::get('/api/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
    Route::post('/api/products/search-barcode', [ProductController::class, 'searchByBarcode'])->name('products.search-barcode');

    // Category Management Routes
    Route::resource('categories', CategoryController::class);
    Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('/api/categories', [CategoryController::class, 'apiIndex'])->name('categories.api');

    // POS Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/search-products', [PosController::class, 'searchProducts'])->name('search-products');
        Route::post('/search-barcode', [PosController::class, 'searchByBarcode'])->name('search-barcode');
        Route::post('/add-to-cart', [PosController::class, 'addToCart'])->name('add-to-cart');
        Route::post('/update-cart-item', [PosController::class, 'updateCartItem'])->name('update-cart-item');
        Route::post('/apply-discount', [PosController::class, 'applyDiscount'])->name('apply-discount');
        Route::post('/remove-from-cart', [PosController::class, 'removeFromCart'])->name('remove-from-cart');
        Route::post('/clear-cart', [PosController::class, 'clearCart'])->name('clear-cart');
        Route::get('/get-cart', [PosController::class, 'getCart'])->name('get-cart');
        Route::post('/search-customers', [PosController::class, 'searchCustomers'])->name('search-customers');
        Route::post('/process-sale', [PosController::class, 'processSale'])->name('process-sale');
    });

    // Sales Management Routes
    Route::resource('sales', SaleController::class)->only(['index', 'show']);
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::get('/api/sales/today-summary', [SaleController::class, 'todaysSummary'])->name('sales.today-summary');
    Route::get('/api/sales/analytics', [SaleController::class, 'analytics'])->name('sales.analytics');
    Route::post('/sales/{sale}/void', [SaleController::class, 'void'])->name('sales.void');
    Route::get('/sales/export', [SaleController::class, 'export'])->name('sales.export');
    Route::post('/sales/lookup-payment', [SaleController::class, 'lookupPayment'])->name('sales.lookup-payment');

    // Customer Management Routes
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');


    // Customer Debt & Payment Routes
    Route::get('/customers/{customer}/sales/{sale}/payment', [CustomerDebtController::class, 'showPaymentForm'])->name('debt.payment-form');
    Route::post('/customers/{customer}/sales/{sale}/payment', [CustomerDebtController::class, 'recordPayment'])->name('debt.record-payment');

    // ===== SUPPLIER MANAGEMENT ROUTES =====
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

    // ===== PURCHASE ROUTES =====
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/purchases/{purchase}/receipt', [PurchaseController::class, 'receipt'])->name('purchases.receipt');
    Route::post('/purchases/{purchase}/void', [PurchaseController::class, 'void'])->name('purchases.void');

    // ===== SUPPLIER DEBT & PAYMENT ROUTES =====
    Route::get('/suppliers/{supplier}/purchases/{purchase}/payment', [SupplierDebtController::class, 'showPaymentForm'])->name('supplier-debt.payment-form');
    Route::post('/suppliers/{supplier}/purchases/{purchase}/payment', [SupplierDebtController::class, 'recordPayment'])->name('supplier-debt.record-payment');

    // ===== RETURNS ROUTES =====
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns/search-sale', [ReturnController::class, 'searchSale'])->name('returns.search-sale');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');
    Route::get('/returns/{return}/receipt', [ReturnController::class, 'receipt'])->name('returns.receipt');
    Route::post('/returns/{return}/void', [ReturnController::class, 'void'])->name('returns.void');

    // ===== REPORTS ROUTES =====
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('/sales/by-period', [\App\Http\Controllers\ReportController::class, 'salesByPeriod'])->name('sales.by-period');
        Route::get('/sales/export', [\App\Http\Controllers\ReportController::class, 'exportSales'])->name('sales.export');
        Route::get('/sales/pdf', [\App\Http\Controllers\ReportController::class, 'exportSalesPdf'])->name('sales.pdf');
        Route::get('/profit', [\App\Http\Controllers\ReportController::class, 'profit'])->name('profit');
        Route::get('/profit/export', [\App\Http\Controllers\ReportController::class, 'exportProfit'])->name('profit.export');
        Route::get('/inventory/low-stock', [\App\Http\Controllers\ReportController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('/inventory/low-stock/export', [\App\Http\Controllers\ReportController::class, 'exportLowStock'])->name('inventory.low-stock.export');
        Route::get('/inventory/stock-value', [\App\Http\Controllers\ReportController::class, 'stockValue'])->name('inventory.stock-value');
        Route::get('/customers/debt', [\App\Http\Controllers\ReportController::class, 'customerDebt'])->name('customers.debt');
        Route::get('/customers/debt-aging', [\App\Http\Controllers\ReportController::class, 'customerDebtAging'])->name('customers.debt-aging');
        Route::get('/customers/debt-aging/export', [\App\Http\Controllers\ReportController::class, 'exportCustomerDebtAging'])->name('customers.debt-aging.export');
        Route::get('/suppliers/debt', [\App\Http\Controllers\ReportController::class, 'supplierDebt'])->name('suppliers.debt');
        Route::get('/suppliers/debt-aging', [\App\Http\Controllers\ReportController::class, 'supplierDebtAging'])->name('suppliers.debt-aging');
        Route::get('/suppliers/debt-aging/export', [\App\Http\Controllers\ReportController::class, 'exportSupplierDebtAging'])->name('suppliers.debt-aging.export');
        Route::get('/returns', [\App\Http\Controllers\ReportController::class, 'returns'])->name('returns');
        Route::get('/returns/export', [\App\Http\Controllers\ReportController::class, 'exportReturns'])->name('returns.export');
        Route::get('/returns/pdf', [\App\Http\Controllers\ReportController::class, 'exportReturnsPdf'])->name('returns.pdf');
    });

    // ===== SETTINGS ROUTES =====
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SettingsController::class, 'index'])->name('index');

        // Profile (all authenticated users)
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

        // Manager-only routes
        Route::middleware('role:manager')->group(function () {
            // Store settings
            Route::get('/store', [\App\Http\Controllers\SettingsController::class, 'store'])->name('store');
            Route::post('/store', [\App\Http\Controllers\SettingsController::class, 'updateStore'])->name('store.update');

            // Exchange rate
            Route::get('/exchange-rate', [\App\Http\Controllers\SettingsController::class, 'exchangeRate'])->name('exchange-rate');
            Route::post('/exchange-rate', [\App\Http\Controllers\SettingsController::class, 'updateExchangeRate'])->name('exchange-rate.update');

            // Preferences
            Route::get('/preferences', [\App\Http\Controllers\SettingsController::class, 'preferences'])->name('preferences');
            Route::post('/preferences', [\App\Http\Controllers\SettingsController::class, 'updatePreferences'])->name('preferences.update');

            // User management
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::post('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::delete('/users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');

            // Backup
            Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'index'])->name('backup');
            Route::post('/backup/create', [\App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
            Route::get('/backup/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
            Route::delete('/backup/delete', [\App\Http\Controllers\BackupController::class, 'destroy'])->name('backup.destroy');
            Route::post('/backup/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');
            Route::post('/backup/upload', [\App\Http\Controllers\BackupController::class, 'uploadAndRestore'])->name('backup.upload');
        });
    });
});
