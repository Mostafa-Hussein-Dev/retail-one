<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
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
});
