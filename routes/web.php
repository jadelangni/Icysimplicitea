<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\QrAuthController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// QR Code Scanner (public access for time tracking station)
Route::get('/qr-scanner', [QrAuthController::class, 'scanner'])->name('qr.scanner');
Route::post('/qr-scanner/process', [QrAuthController::class, 'process'])->name('qr.process');

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/recent-sales', [DashboardController::class, 'getRecentSales'])->name('dashboard.recent-sales');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // QR Code routes (for authenticated users)
    Route::get('/my-qrcode', [QrAuthController::class, 'showMyQrCode'])->name('qr.my-qrcode');
    Route::get('/my-qrcode/regenerate', [QrAuthController::class, 'regenerateQrCode'])->name('qr.regenerate');
    Route::get('/my-qrcode/image', [QrAuthController::class, 'getQrCodeImage'])->name('qr.image');
    
    // POS System
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::post('/process-sale', [POSController::class, 'processSale'])->name('process-sale');
        Route::get('/receipt/{sale}', [POSController::class, 'showReceipt'])->name('receipt');
    });

    // Products Management (Supervisors and Owners only)
    Route::middleware(['role:owner,supervisor'])->group(function () {
    Route::resource('products', ProductController::class);
    // Toggle availability without requiring full update payload
    Route::post('/products/{product}/toggle-availability', [ProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
        Route::resource('inventory', IngredientController::class);
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
            Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        });

        // Activity Logs (Owner only)
        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/user/{user}', [ActivityLogController::class, 'userActivity'])->name('user');
        });

        // QR Code Management for Users (Owner only)
        Route::get('/user/{user}/qrcode', [QrAuthController::class, 'showUserQrCode'])->name('qr.user-qrcode');
        Route::get('/user/{user}/qrcode/regenerate', [QrAuthController::class, 'regenerateUserQrCode'])->name('qr.user-regenerate');
    });
});
