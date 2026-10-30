<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\KategoriController;
use App\Http\Controllers\API\AlatController;
use App\Http\Controllers\API\PeminjamanController;
use App\Http\Controllers\API\PengembalianController;
use App\Http\Controllers\API\LogAktivitasController;
use App\Http\Controllers\API\LaporanController;

// ==========================================
// PUBLIC ROUTES
// Tidak membutuhkan token
// ==========================================

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');


// ==========================================
// PROTECTED ROUTES
// Membutuhkan Bearer Token Sanctum
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // Data user yang sedang login
    Route::get('/me', [AuthController::class, 'me']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/me', [AuthController::class, 'updateProfile']);
    Route::put('/me/password', [AuthController::class, 'updatePassword']);

    // Katalog: semua role yang login
    Route::get('/katalog', [AlatController::class, 'katalog']);
    // Detail, ubah, batalkan pengajuan (cek kepemilikan di controller)
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
    Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
    Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);

    // Admin + Petugas
    Route::middleware('role.staff')->group(function () {
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::post('/peminjaman/{peminjaman}/reject', [PeminjamanController::class, 'reject']);
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);
        Route::post('/pengembalian', [PengembalianController::class, 'store']);
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
    
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);
        Route::get('/laporan-peminjaman/export', [LaporanController::class, 'export']);
    });


    // ==========================================
    // ADMIN
    // ==========================================

    Route::middleware('role.admin')->group(function () {

        // CRUD User
        Route::apiResource('users', UserController::class);

        // CRUD Kategori
        Route::apiResource('kategori', KategoriController::class);

        // CRUD Alat
        Route::apiResource('alat', AlatController::class);
        // Peminjaman
        
        //Pengembalian
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);
        //Log aktivitas observer
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
    });


    // ==========================================
    // PETUGAS
    // ==========================================

    Route::middleware('role.petugas')->group(function () {
    //pengembalian
    });


    // ==========================================
    // PEMINJAM
    // ==========================================

    Route::middleware('role.peminjam')->group(function () {

        // Katalog alat
        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);
        Route::post('/peminjaman/{peminjaman}/ajukan-kembali', [PeminjamanController::class, 'ajukanKembali']);
        Route::delete('/peminjaman/{peminjaman}/ajukan-kembali', [PeminjamanController::class, 'batalAjukanKembali']);

    });

});