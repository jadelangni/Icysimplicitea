<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\QrAuthController;
use App\Http\Controllers\PinAuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PermissionOverrideController;
use App\Http\Controllers\ProductInventoryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RecipeController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// QR Code Scanner (public access for time tracking station)
Route::get('/qr-scanner', [QrAuthController::class, 'scanner'])->name('qr.scanner');
Route::post('/qr-scanner/process', [QrAuthController::class, 'process'])->name('qr.process');

// PIN Quick Login (public access for cashier switching)
Route::get('/pin-login', [PinAuthController::class, 'showPinLogin'])->name('pin.login');
Route::post('/pin-login/authenticate', [PinAuthController::class, 'authenticatePin'])->name('pin.authenticate');

// Attendance Terminal (public access for kiosk mode)
Route::get('/attendance-terminal', [AttendanceController::class, 'terminal'])->name('attendance.terminal');
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
Route::get('/attendance/status', [AttendanceController::class, 'getStatus'])->name('attendance.status');
Route::get('/attendance/users', [AttendanceController::class, 'getUsers'])->name('attendance.users');

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/recent-sales', [DashboardController::class, 'getRecentSales'])->name('dashboard.recent-sales');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/dashboard/live-sales', [DashboardController::class, 'getLiveSales'])->name('dashboard.live-sales');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // PIN Setup (for current user)
    Route::get('/setup-pin', [PinAuthController::class, 'showSetupPin'])->name('pin.setup');
    Route::post('/setup-pin', [PinAuthController::class, 'savePin'])->name('pin.save');
    Route::delete('/remove-pin', [PinAuthController::class, 'removePin'])->name('pin.remove');

    // My Attendance
    Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.my-attendance');
    Route::get('/attendance/selfie/{attendance}', [AttendanceController::class, 'viewSelfie'])->name('attendance.selfie');

    // QR Code routes (for authenticated users)
    Route::get('/my-qrcode', [QrAuthController::class, 'showMyQrCode'])->name('qr.my-qrcode');
    Route::get('/my-qrcode/regenerate', [QrAuthController::class, 'regenerateQrCode'])->name('qr.regenerate');
    Route::get('/my-qrcode/image', [QrAuthController::class, 'getQrCodeImage'])->name('qr.image');
    
    // POS System
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('receipt');
        Route::post('/void/{sale}', [POSController::class, 'voidSale'])->name('void');
    });

    // Permission Override Requests
    Route::prefix('permission-override')->name('permission-override.')->group(function () {
        Route::post('/request', [PermissionOverrideController::class, 'request'])->name('request');
        Route::get('/check', [PermissionOverrideController::class, 'check'])->name('check');
        Route::post('/quick-approve', [PermissionOverrideController::class, 'quickApprove'])->name('quick-approve');
    });

    // Products Management (Admins only)
    Route::middleware(['role:admin'])->group(function () {
        // Employee Management
        Route::resource('employees', EmployeeController::class);
        Route::patch('/employees/{employee}/branch', [EmployeeController::class, 'updateBranch'])->name('employees.update-branch');
        Route::patch('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
        
    Route::resource('products', ProductController::class);
    // Toggle availability without requiring full update payload
    Route::post('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
        Route::resource('inventory', InventoryController::class);
        Route::post('/inventory/{inventory}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
        Route::post('/inventory/update-ingredient-branches', [InventoryController::class, 'updateIngredientBranches'])->name('inventory.update-ingredient-branches');
        
        // Ingredient Management (Global)
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');
        
        // Recipe Management (BOM - Bill of Materials)
        Route::prefix('recipes')->name('recipes.')->group(function () {
            Route::get('/', [RecipeController::class, 'index'])->name('index');
            Route::get('/{product}', [RecipeController::class, 'show'])->name('show');
            Route::put('/{product}', [RecipeController::class, 'update'])->name('update');
            Route::delete('/{product}', [RecipeController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-update', [RecipeController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/{product}/estimates', [RecipeController::class, 'getServingEstimates'])->name('estimates');
        });
        
        // Product Inventory Management (Global prices + Branch stocks)
        Route::prefix('product-inventory')->name('product-inventory.')->group(function () {
            Route::get('/', [ProductInventoryController::class, 'index'])->name('index');
            Route::get('/low-stock-alerts', [ProductInventoryController::class, 'getLowStockAlerts'])->name('low-stock-alerts');
            Route::get('/sync-status', [ProductInventoryController::class, 'getSyncStatus'])->name('sync-status');
            Route::get('/{product}', [ProductInventoryController::class, 'show'])->name('show');
            Route::post('/{product}/price', [ProductInventoryController::class, 'updatePrice'])->name('update-price');
            Route::post('/{product}/stock', [ProductInventoryController::class, 'updateStock'])->name('update-stock');
            Route::post('/{product}/all-stocks', [ProductInventoryController::class, 'updateAllBranchStock'])->name('update-all-stocks');
        });
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
            Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        });

        // Activity Logs (Admin only)
        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityLogController::class, 'userActivity'])->name('user');
        });

        // Attendance Management
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        // Permission Override Management
        Route::prefix('permission-overrides')->name('permission-override.')->group(function () {
            Route::get('/', [PermissionOverrideController::class, 'index'])->name('index');
            Route::get('/pending', [PermissionOverrideController::class, 'getPending'])->name('pending');
            Route::post('/{override}/approve', [PermissionOverrideController::class, 'approve'])->name('approve');
            Route::post('/{override}/deny', [PermissionOverrideController::class, 'deny'])->name('deny');
        });

        // QR Code Management for Users (Admin only)
        Route::get('/user/{user}/qrcode', [QrAuthController::class, 'showUserQrCode'])->name('qr.user-qrcode');
        Route::get('/user/{user}/qrcode/regenerate', [QrAuthController::class, 'regenerateUserQrCode'])->name('qr.user-regenerate');

        // Admin PIN Management (Admin only)
        Route::post('/user/{user}/set-pin', [PinAuthController::class, 'adminSetPin'])->name('pin.admin-set');
    });
});
