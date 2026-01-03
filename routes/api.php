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
// Tambahkan Controller lain di sini jika sudah dibuat (misal: ReportController)

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =========================================================================
// PUBLIC ROUTES (No Authentication Required)
// =========================================================================
Route::middleware(['throttle:6,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register'); // Pendaftaran Klien B2B

    // Fitur Reset Password (jika diimplementasikan)
    // Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    // Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// =========================================================================
// PROTECTED ROUTES (Requires Bearer Token)
// =========================================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Actions
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user(); // Cek user yang sedang login
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN ROLE & PERMISSION (Admin Super)
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola roles')->group(function () {
        Route::apiResource('roles', RoleController::class);
        // Tambahan fitur add permission ke role
        Route::post('roles/{id}/permissions', [RoleController::class, 'addPermission']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN PERMISSION (Admin Super)
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola roles')->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN USER (Admin)
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola users')->group(function () {
        Route::apiResource('users', UserController::class);
        // Update status user (Aktif/Non Aktif)
        Route::patch('users/{id}/status', [UserController::class, 'updateStatus']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN CLIENT B2B (Admin & Sales)
    // [Ref Proposal: Mengelola data klien B2B terpusat]
    // ---------------------------------------------------------------------
    Route::middleware('permission:mengelola clients')->group(function () {
        Route::apiResource('clients', ClientController::class);

        // Verifikasi Akun Klien (Fitur Kunci Proposal)
        Route::patch('clients/{id}/verify', [ClientController::class, 'verify']);

        // Analisis Pasar (Laporan Segmentasi)
        Route::get('clients-market-analysis', [ClientController::class, 'marketAnalysis']);
    });

    // ---------------------------------------------------------------------
    // MANAJEMEN PRODUK / KATALOG (Admin & Gudang)
    // [Ref Proposal: Mengelola informasi produk dan harga grosir]
    // ---------------------------------------------------------------------
    // Note: 'index' dan 'show' mungkin perlu dibuka untuk Klien juga tanpa permission 'mengelola'
    // Tapi untuk 'store', 'update', 'destroy' wajib permission ini.
    Route::middleware('permission:mengelola products')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{id}', [ProductController::class, 'update']);
        Route::delete('products/{id}', [ProductController::class, 'destroy']);
    });

    // Public Access untuk Katalog (Authenticated Users Only - misal Klien)
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);

    // ---------------------------------------------------------------------
    // MANAJEMEN PESANAN / ORDER (Admin, Klien, Gudang)
    // [Ref Proposal: Verifikasi & Ubah Status Pesanan]
    // ---------------------------------------------------------------------

    // Route Khusus Klien (Membuat Pesanan)
    Route::post('orders', [OrderController::class, 'store']); // Klien bikin order
    Route::get('my-orders', [OrderController::class, 'myOrders']); // Riwayat order saya

    // Route Khusus Admin (Mengelola Pesanan)
    Route::middleware('permission:mengelola orders')->group(function () {
        Route::get('orders', [OrderController::class, 'index']); // Lihat semua order
        Route::get('orders/{id}', [OrderController::class, 'show']); // Detail order
        Route::put('orders/{id}', [OrderController::class, 'update']); // Admin update (verifikasi/ubah status)
        Route::delete('orders/{id}', [OrderController::class, 'destroy']);

        // Fitur Laporan Transaksi
        Route::get('orders-report', [OrderController::class, 'report']);

        // Update Status Spesifik (Shortcut)
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    });

});
