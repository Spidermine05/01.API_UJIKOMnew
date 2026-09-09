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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ==========================================
// PROTECTED ROUTES
// Membutuhkan Bearer Token Sanctum
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // Data user yang sedang login
    Route::get('/me', [AuthController::class, 'me']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);


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
        // katalog
         Route::get('/katalog', [AlatController::class, 'katalog']);
        // Peminjaman
        Route::get('/peminjaman', [PeminjamanController::class, 'index']);
        Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
        Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
        Route::put('/peminjaman/{peminjaman}', [PeminjamanController::class, 'update']);
        Route::delete('/peminjaman/{peminjaman}', [PeminjamanController::class, 'destroy']);
        //Pengembalian
        Route::get('/pengembalian', [PengembalianController::class, 'index']);
        Route::get('/pengembalian/{pengembalian}', [PengembalianController::class, 'show']);
        Route::put('/pengembalian/{pengembalian}', [PengembalianController::class, 'update']);
        Route::delete('/pengembalian/{pengembalian}', [PengembalianController::class, 'destroy']);
        //Log aktivitas observer
        Route::get('/log-aktivitas', [LogAktivitasController::class, 'index']);
        //Laporan
        Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);

    });


    // ==========================================
    // PETUGAS
    // ==========================================

    Route::middleware('role.petugas')->group(function () {
    Route::post('/peminjaman/{peminjaman}/approve', [PeminjamanController::class, 'approve']);
    //pengembalian
    Route::post('/pengembalian', [PengembalianController::class, 'store']);
    //Laporan
    Route::get('/laporan-peminjaman', [LaporanController::class, 'index']);

    });


    // ==========================================
    // PEMINJAM
    // ==========================================

    Route::middleware('role.peminjam')->group(function () {

        // Katalog alat
        Route::get('/katalog', [AlatController::class, 'katalog']);
        Route::post('/peminjaman', [PeminjamanController::class, 'store']);
        Route::get('/riwayat-pinjam', [PeminjamanController::class, 'riwayat']);

    });

});