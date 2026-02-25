<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\AdvancedSalesController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\BarcodeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Root route - redirect to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Language Switcher
Route::get('/language/{locale}', [LanguageController::class, 'switchLang'])->name('language.switch');

// Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    // Password Reset Routes
    Route::get('/password/reset', [LoginController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [LoginController::class, 'reset'])->name('password.update');
});

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Product Inventory
    Route::get('/inventory', [ProductController::class, 'index'])->name('inventory');
    Route::get('/products', function () { return redirect('/inventory'); });
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/search-barcode', [ProductController::class, 'searchByBarcode'])->name('products.search-barcode');
    Route::get('/products-reorder', [ProductController::class, 'purchaseSuggestions'])->name('products.purchase-suggestions');
    Route::post('/products/batch-update', [ProductController::class, 'batchUpdate'])->name('products.batch-update');

    // Batch Management Routes
    Route::prefix('products/{product}')->group(function () {
        Route::get('batches', [BatchController::class, 'index'])->name('products.batches.index');
        Route::get('batches/create', [BatchController::class, 'create'])->name('products.batches.create');
        Route::post('batches', [BatchController::class, 'store'])->name('products.batches.store');
        Route::get('batches/{batch}/edit', [BatchController::class, 'edit'])->name('products.batches.edit');
        Route::put('batches/{batch}', [BatchController::class, 'update'])->name('products.batches.update');
        Route::delete('batches/{batch}', [BatchController::class, 'destroy'])->name('products.batches.destroy');
    });

    // Categories (Admin Only)
    Route::middleware(['admin'])->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
    });

    // Categories (View for All Authenticated Users)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::delete('/sales/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');

    // Clients
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Reports (Admin Only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    });

    // User Management (Admin Only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/sales-performance', [UserController::class, 'salesPerformance'])->name('users.sales-performance');
        Route::patch('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('users.toggle-role');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // User Profile (All Authenticated Users)
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/activities', [UserController::class, 'activities'])->name('users.activities');
    Route::get('/users/{user}/change-password', [UserController::class, 'changePassword'])->name('users.change-password');
    Route::put('/users/{user}/update-password', [UserController::class, 'updatePassword'])->name('users.update-password');

    // Notifications
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/latest', [NotificationsController::class, 'getLatest'])->name('notifications.latest');
    Route::post('/notifications/mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::put('/notifications/{notification}/mark-read', [NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{notification}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');

    // Advanced Sales Routes
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/advanced', [AdvancedSalesController::class, 'index'])->name('advanced');
        Route::post('/search-products', [AdvancedSalesController::class, 'searchProducts'])->name('search-products');
        Route::post('/scan-barcode', [AdvancedSalesController::class, 'scanBarcode'])->name('scan-barcode');
        Route::post('/calculate-cart', [AdvancedSalesController::class, 'calculateCart'])->name('calculate-cart');
        Route::post('/process-advanced', [AdvancedSalesController::class, 'processAdvancedSale'])->name('process-advanced');
        Route::get('/receipt/{sale}', [AdvancedSalesController::class, 'generateReceipt'])->name('generate-receipt');
        Route::get('/receipt/{receipt}/print', [AdvancedSalesController::class, 'printReceipt'])->name('print-receipt');
        Route::post('/receipt/{receipt}/email', [AdvancedSalesController::class, 'emailReceipt'])->name('email-receipt');
    });

    // Customer Management Routes
    Route::resource('customers', CustomerController::class);
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/search', [CustomerController::class, 'search'])->name('search');
        Route::get('/{customer}/purchase-history', [CustomerController::class, 'purchaseHistory'])->name('purchase-history');
        Route::post('/{customer}/add-loyalty-points', [CustomerController::class, 'addLoyaltyPoints'])->name('add-loyalty-points');
        Route::post('/{customer}/redeem-loyalty-points', [CustomerController::class, 'redeemLoyaltyPoints'])->name('redeem-loyalty-points');
        Route::get('/export', [CustomerController::class, 'export'])->name('export');
    });

    // Promotion Management Routes
    Route::resource('promotions', PromotionController::class);
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::post('/{promotion}/toggle-status', [PromotionController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/validate-code', [PromotionController::class, 'validateCode'])->name('validate-code');
        Route::get('/active', [PromotionController::class, 'getActivePromotions'])->name('active');
        Route::get('/{promotion}/statistics', [PromotionController::class, 'statistics'])->name('statistics');
        Route::post('/{promotion}/duplicate', [PromotionController::class, 'duplicate'])->name('duplicate');
    });

    // POS System Routes
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::post('/calculate-total', [POSController::class, 'calculateTotal'])->name('calculate-total');
        Route::post('/apply-quick-discount', [POSController::class, 'applyQuickDiscount'])->name('apply-quick-discount');
        Route::get('/receipt/{receipt}', [POSController::class, 'showReceipt'])->name('receipt');
        Route::get('/receipt/{receipt}/download', [POSController::class, 'downloadReceipt'])->name('download-receipt');
        Route::post('/sync-offline', [POSController::class, 'syncOfflineSales'])->name('sync-offline');
        Route::get('/offline-status', [POSController::class, 'offlineStatus'])->name('offline-status');
        Route::get('/low-stock-alerts', [POSController::class, 'getLowStockAlerts'])->name('low-stock-alerts');
        Route::post('/email-receipt', [POSController::class, 'emailReceipt'])->name('email-receipt');
        Route::post('/sms-receipt', [POSController::class, 'smsReceipt'])->name('sms-receipt');
        Route::post('/search-products', [POSController::class, 'searchProducts'])->name('search-products');
        Route::post('/scan-barcode', [POSController::class, 'scanBarcode'])->name('scan-barcode');
        Route::get('/product/{product}', [POSController::class, 'getProduct'])->name('get-product');
    });

    // Barcode System Routes
    Route::prefix('barcode')->name('barcode.')->group(function () {
        Route::post('/scan', [BarcodeController::class, 'scan'])->name('scan');
        Route::get('/search', [BarcodeController::class, 'search'])->name('search');
        Route::post('/generate', [BarcodeController::class, 'generate'])->name('generate');
        Route::get('/scan-history', [BarcodeController::class, 'scanHistory'])->name('scan-history');
        Route::post('/validate', [BarcodeController::class, 'validateBarcode'])->name('validate');
    });
});
