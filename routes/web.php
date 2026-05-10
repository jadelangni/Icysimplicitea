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
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\CrewSessionController;
use App\Http\Controllers\EmployeeInventoryController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// QR Code Scanner (public access for time tracking station)
Route::get('/qr-scanner', [QrAuthController::class, 'scanner'])->name('qr.scanner');
Route::post('/qr-scanner/process', [QrAuthController::class, 'process'])->name('qr.process');

// PIN Quick Login removed - employees now log in independently with branch sessions
// Route::get('/pin-login', [PinAuthController::class, 'showPinLogin'])->name('pin.login');
// Route::post('/pin-login/authenticate', [PinAuthController::class, 'authenticatePin'])->name('pin.authenticate');


Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
Route::get('/attendance/status', [AttendanceController::class, 'getStatus'])->name('attendance.status');
Route::get('/attendance/users', [AttendanceController::class, 'getUsers'])->name('attendance.users');

// Authentication routes
require __DIR__.'/auth.php';

// Password Change (for first login)
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

// CSRF token endpoint - needs to be accessible even when session expires
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');

// Protected routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\ForcePasswordChange::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/recent-sales', [DashboardController::class, 'getRecentSales'])->name('dashboard.recent-sales');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
    Route::get('/dashboard/live-sales', [DashboardController::class, 'getLiveSales'])->name('dashboard.live-sales');
    Route::get('/dashboard/cashier-data', [DashboardController::class, 'getCashierDashboardData'])->name('dashboard.cashier-data');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // PIN Setup (for current user)
    Route::get('/setup-pin', [PinAuthController::class, 'showSetupPin'])->name('pin.setup');
    Route::post('/setup-pin', [PinAuthController::class, 'savePin'])->name('pin.save');
    Route::delete('/remove-pin', [PinAuthController::class, 'removePin'])->name('pin.remove');

    // Employee Inventory Overview (read-only)
    Route::get('/employee-inventory', [EmployeeInventoryController::class, 'index'])->name('employee-inventory.index');

    // My Attendance
    Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.my-attendance');
    Route::get('/attendance/selfie/{attendance}', [AttendanceController::class, 'viewSelfie'])->name('attendance.selfie');

    // QR Code routes (for authenticated users)
    Route::get('/my-qrcode', [QrAuthController::class, 'showMyQrCode'])->name('qr.my-qrcode');
    Route::get('/my-qrcode/regenerate', [QrAuthController::class, 'regenerateQrCode'])->name('qr.regenerate');
    Route::get('/my-qrcode/image', [QrAuthController::class, 'getQrCodeImage'])->name('qr.image');

    // QR Switch User removed - employees now log in independently with branch sessions
    // Route::post('/switch-user/qr', [QrAuthController::class, 'switchByQr'])->name('switch-user.qr');
    
    // POS System
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/live-data', [POSController::class, 'liveData'])->name('live-data');
        Route::post('/gcash/create-qr', [POSController::class, 'createGcashQr'])->name('gcash.create-qr');
        Route::post('/gcash/check-status', [POSController::class, 'checkGcashStatus'])->name('gcash.check-status');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('receipt');
        Route::get('/receipt/{sale}/print', [POSController::class, 'printReceipt'])->name('receipt.print');
        Route::get('/receipt/{sale}/direct-print', [POSController::class, 'directPrintReceipt'])->name('receipt.direct-print');
        Route::get('/receipt/{sale}/raw', [POSController::class, 'getRawReceiptData'])->name('receipt.raw');
        Route::post('/void/{sale}', [POSController::class, 'voidSale'])->name('void');
    });

    // Cashier Daily Report (accessible by all authenticated users for logout flow)
    Route::get('/reports/daily/print', [ReportController::class, 'printDailyReceipt'])->name('reports.daily.print');
    Route::get('/reports/cashier-logout-report', [ReportController::class, 'cashierLogoutReport'])->name('reports.cashier-logout-report');

    // Crew Session Management (check-in/out on shared device)
    Route::middleware(['role:cashier,admin'])->prefix('crew-session')->name('crew-session.')->group(function () {
        Route::post('/check-in', [CrewSessionController::class, 'checkIn'])->name('check-in');
        Route::post('/check-out', [CrewSessionController::class, 'checkOut'])->name('check-out');
        Route::get('/active', [CrewSessionController::class, 'activeCrew'])->name('active');
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
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::patch('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::patch('/branches/{branch}/archive', [BranchController::class, 'archive'])->name('branches.archive');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::post('/branches/{branchId}/restore', [BranchController::class, 'restore'])->name('branches.restore');
        Route::get('/branches/archived', [BranchController::class, 'archived'])->name('branches.archived');
        
        Route::resource('products', ProductController::class);
        // Toggle availability without requiring full update payload
        Route::post('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
        Route::resource('inventory', InventoryController::class);
        Route::post('/inventory/{inventory}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
        Route::post('/inventory/update-ingredient-branches', [InventoryController::class, 'updateIngredientBranches'])->name('inventory.update-ingredient-branches');
        Route::get('/ingredients/{ingredient}/recommended-recipe-unit', [InventoryController::class, 'getRecommendedRecipeUnit'])->name('ingredients.recommended-recipe-unit');
        
        // Ingredient Management (Global)
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::delete('/ingredients/{ingredient}', [IngredientController::class, 'destroy'])->name('ingredients.destroy');
        
        // Recipe Management (BOM - Bill of Materials)
        Route::prefix('recipes')->name('recipes.')->group(function () {
            Route::get('/', [RecipeController::class, 'index'])->name('index');
            Route::post('/bulk-update', [RecipeController::class, 'bulkUpdate'])->name('bulk-update');
            Route::post('/convert-quantity', [RecipeController::class, 'convertQuantity'])->name('convert-quantity');
            Route::get('/ingredients/{ingredient}/compatible-units', [RecipeController::class, 'getCompatibleUnits'])->name('compatible-units');
            Route::get('/{product}', [RecipeController::class, 'show'])->name('show');
            Route::put('/{product}', [RecipeController::class, 'update'])->name('update');
            Route::delete('/{product}', [RecipeController::class, 'destroy'])->name('destroy');
            Route::get('/{product}/estimates', [RecipeController::class, 'getServingEstimates'])->name('estimates');
        });
        
        // Product Inventory Management (Global prices + Branch stocks)
        Route::prefix('product-inventory')->name('product-inventory.')->group(function () {
            Route::get('/', [ProductInventoryController::class, 'index'])->name('index');
            Route::get('/export', [ProductInventoryController::class, 'exportToExcel'])->name('export');
            Route::get('/low-stock-alerts', [ProductInventoryController::class, 'getLowStockAlerts'])->name('low-stock-alerts');
            Route::get('/sync-status', [ProductInventoryController::class, 'getSyncStatus'])->name('sync-status');
            Route::get('/live-data', [ProductInventoryController::class, 'liveData'])->name('live-data');
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
            Route::get('/forecast', [ReportController::class, 'forecast'])->name('forecast');
            Route::get('/export/restock', [ReportController::class, 'exportRestock'])->name('export.restock');
            Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
            Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');

            
            // Export routes
            Route::get('/export/sales', [ReportController::class, 'exportSales'])->name('export.sales');
            Route::get('/export/inventory', [ReportController::class, 'exportInventory'])->name('export.inventory');
            Route::get('/export/daily', [ReportController::class, 'exportDaily'])->name('export.daily');
            Route::get('/export/monthly', [ReportController::class, 'exportMonthly'])->name('export.monthly');
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
