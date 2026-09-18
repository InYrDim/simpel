<?php

use App\Http\Controllers\Manajemen\PenggunaController;
use App\Http\Controllers\ManajemenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('manajemen')->name('manajemen.')->group(function (): void {
    Route::get('/', ManajemenController::class)->name('index');
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
});
