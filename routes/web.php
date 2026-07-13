<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KasirDashboardController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\KasirFnbController;
use App\Http\Controllers\OwnerFnbController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\OwnerUserController;

// ==========================================
// AUTH ROUTES (PUBLIK)
// ==========================================
Route::get('/', function () { return redirect()->route('login'); });
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

// [HYGIENE] Tambahkan middleware throttle:5,1 (Maksimal 5x coba login gagal dalam 1 menit)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ==========================================
// PROTECTED ROUTES (HARUS LOGIN)
// ==========================================
Route::middleware(['auth'])->group(function () {
    
    // Rute Global (Bisa diakses Kasir & Owner)
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');

    // ------------------------------------------
    // GRUP KHUSUS KASIR
    // ------------------------------------------
    Route::middleware(['role:kasir'])->group(function () {
        // Denah & Sesi Sewa
        Route::get('/dashboard-kasir', [KasirDashboardController::class, 'index'])->name('kasir.dashboard');
        Route::post('/sewa/mulai', [KasirDashboardController::class, 'mulaiSesi'])->name('sewa.mulai');
        Route::post('/sewa/selesai/{id_sewa}', [KasirDashboardController::class, 'selesaiSesi'])->name('sewa.selesai');
        
        // POS F&B Kasir
        Route::get('/kasir/fnb', [KasirFnbController::class, 'index'])->name('kasir.fnb.index');
        Route::post('/kasir/fnb/checkout', [KasirFnbController::class, 'checkout'])->name('kasir.fnb.checkout');
    });

    // ------------------------------------------
    // GRUP KHUSUS OWNER
    // ------------------------------------------
    Route::middleware(['role:owner'])->group(function () {
        // Dashboard Analitik
        Route::get('/dashboard-owner', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
        
        // Manajemen Admin F&B
        Route::get('/owner/fnb', [OwnerFnbController::class, 'index'])->name('owner.fnb.index');
        Route::post('/owner/fnb/store', [OwnerFnbController::class, 'store'])->name('owner.fnb.store');
        Route::post('/owner/fnb/toggle/{id}', [OwnerFnbController::class, 'toggleStatus'])->name('owner.fnb.toggle');
        Route::post('/owner/fnb/delete/{id}', [OwnerFnbController::class, 'destroy'])->name('owner.fnb.delete');
        Route::post('/owner/fnb/update/{id}', [OwnerFnbController::class, 'update'])->name('owner.fnb.update');

        Route::post('/maintenance/selesai/{id}', [App\Http\Controllers\MaintenanceController::class, 'selesaiServis'])->name('maintenance.selesai');
        // Manajemen Akun (CRUD Users)
        Route::get('/owner/users', [OwnerUserController::class, 'index'])->name('owner.users.index');
        Route::post('/owner/users/store', [OwnerUserController::class, 'store'])->name('owner.users.store');
        Route::post('/owner/users/update/{id}', [OwnerUserController::class, 'update'])->name('owner.users.update');
        Route::post('/owner/users/delete/{id}', [OwnerUserController::class, 'destroy'])->name('owner.users.delete');
    });

});