<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'simpan'])->name('kategori.simpan');
    Route::post('/kategori/hapus', [KategoriController::class, 'hapus'])->name('kategori.hapus');

    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::post('/produk', [ProdukController::class, 'simpan'])->name('produk.simpan');
    Route::post('/produk/hapus', [ProdukController::class, 'hapus'])->name('produk.hapus');

    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::post('/penjualan', [PenjualanController::class, 'simpan'])->name('penjualan.simpan');
    Route::post('/penjualan/hapus', [PenjualanController::class, 'hapus'])->name('penjualan.hapus');
});
