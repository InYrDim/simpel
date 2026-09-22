<?php

use App\Modules\Manajemen\Controllers\JurusanController;
use App\Modules\Manajemen\Controllers\ManajemenController;
use App\Modules\Manajemen\Controllers\PenggunaController;
use App\Modules\Manajemen\Controllers\PeranController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('manajemen')->name('manajemen.')->group(function (): void {
    Route::get('/', ManajemenController::class)->name('index');

    // Pengguna / Akun
    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{pengguna}/edit', [PenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');

    // Jurusan
    Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan.index');
    Route::post('/jurusan', [JurusanController::class, 'store'])->name('jurusan.store');
    Route::put('/jurusan/{jurusan}', [JurusanController::class, 'update'])->name('jurusan.update');
    Route::delete('/jurusan/{jurusan}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');

    // Peran
    Route::get('/peran', [PeranController::class, 'index'])->name('peran.index');
    Route::post('/peran', [PeranController::class, 'store'])->name('peran.store');
    Route::put('/peran/{peran}', [PeranController::class, 'update'])->name('peran.update');
    Route::delete('/peran/{peran}', [PeranController::class, 'destroy'])->name('peran.destroy');
});
