<?php

use App\Http\Controllers\Manajemen\PenggunaController;
use App\Http\Controllers\ManajemenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('manajemen')->name('manajemen.')->group(function (): void {
    Route::get('/', ManajemenController::class)->name('index');
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{pengguna}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
});
