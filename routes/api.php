<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ActivityLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// PUBLIC ROUTES (No Authentication Required / Bisa Diakses Tamu)
// =========================================================================
Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});
    // --- PERBAIKAN: Route Produk Dipindah Kesini ---
    // Siapapun (Tamu/User) bisa melihat daftar & detail produk
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/most-ordered', [ProductController::class, 'mostOrdered']);
    Route::get('products/{id}', [ProductController::class, 'show']);
});

// =========================================================================
// PROTECTED ROUTES (Requires Bearer Token / Wajib Login)
// =========================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Actions
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        if ($user) {
            // Load relasi yang diperlukan termasuk Client
            $user->load(['roles', 'client']);
        }
        return $user;
    });

    // ---------------------------------------------------------------------
    // DASHBOARD
    // ---------------------------------------------------------------------
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData']);

    // ---------------------------------------------------------------------
    // MANAJEMEN ROLE & PERMISSION
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola roles')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{id}/permissions', [RoleController::class, 'addPermission']);
    });

    Route::middleware('permission:mengelola roles')->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // ---------------------------------------------------------------------
    // ACTIVITY LOG
    // ---------------------------------------------------------------------
    Route::middleware('permission:melihat activity log')->group(function () {
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
    });

    Route::middleware('permission:mengekspor activity log')->group(function () {
        Route::get('activity-logs/export', [ActivityLogController::class, 'export']);
    });

    // Hidden feature: Clear activity logs (Super Admin only)
    // This route is intentionally not documented and requires Super Admin role
    Route::delete('activity-logs/clear', [ActivityLogController::class, 'clear']);

    // ---------------------------------------------------------------------
    // MANAJEMEN USER
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola users')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{id}/status', [UserController::class, 'updateStatus']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN CLIENT B2B
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola clients')->group(function () {
        Route::apiResource('clients', ClientController::class);
        Route::patch('clients/{id}/verify', [ClientController::class, 'verify']);
        Route::get('clients-market-analysis', [ClientController::class, 'marketAnalysis']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN PRODUK (Create/Update/Delete Wajib Login & Permission)
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola products')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
    });

    // (Note: Route GET products sudah dipindah ke atas agar public)

    // ---------------------------------------------------------------------
    // MANAJEMEN PESANAN / ORDER
    // ---------------------------------------------------------------------

    // Route Khusus Klien
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('my-orders', [OrderController::class, 'myOrders']);

    // Route Khusus Admin
    Route::middleware('permission:mengelola orders')->group(function () {
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::put('orders/{id}', [OrderController::class, 'update']);
        Route::delete('orders/{id}', [OrderController::class, 'destroy']);
        Route::get('orders-report', [OrderController::class, 'report']);
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN ORDER DETAIL (Rincian Item Pesanan)
    // ---------------------------------------------------------------------
    
    // Route untuk melihat order details (bisa berdasarkan order_id atau product_id)
    Route::get('order-details', [OrderDetailController::class, 'index']);
    Route::get('order-details/{id}', [OrderDetailController::class, 'show']);

    // Route untuk membuat multiple order details sekaligus
    Route::post('order-details/many', [OrderDetailController::class, 'storeMany']);

    // Route Khusus Admin (Create/Update/Delete)
    Route::middleware('permission:mengelola orders')->group(function () {
        Route::post('order-details', [OrderDetailController::class, 'store']);
        Route::put('order-details/{id}', [OrderDetailController::class, 'update']);
        Route::delete('order-details/{id}', [OrderDetailController::class, 'destroy']);
    });

});
