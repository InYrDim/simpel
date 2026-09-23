<?php

use App\Modules\Skripsi\Controllers\BebanDosenController;
use App\Modules\Skripsi\Controllers\DaftarJudulController;
use App\Modules\Skripsi\Controllers\ExportController;
use App\Modules\Skripsi\Controllers\MonitoringController;
use App\Modules\Skripsi\Controllers\PengajuanJudulController;
use App\Modules\Skripsi\Controllers\PutusanValidatorController;
use App\Modules\Skripsi\Controllers\RiwayatPengajuanController;
use App\Modules\Skripsi\Controllers\StatistikController;
use App\Modules\Skripsi\Controllers\VerifikasiAdminController;
use Illuminate\Support\Facades\Route;

// Mahasiswa (§5.1): panel status + submit pengajuan + template DOCX.
// Resubmit (alur revisi, sesi 3) mengirim ulang pengajuan yang sama saat
// statusnya `direvisi`. Submit & resubmit dibatasi rate limiter
// `pengajuan-submit` (5/menit per akun, PRD ketahanan-teknis §3.3).
Route::middleware(['auth', 'verified', 'role:mahasiswa'])->prefix('skripsi/pengajuan')->name('skripsi.pengajuan.')->group(function (): void {
    Route::get('/', [PengajuanJudulController::class, 'status'])->name('status');
    Route::post('/', [PengajuanJudulController::class, 'store'])
        ->middleware('throttle:pengajuan-submit')
        ->name('store');
    Route::post('/{pengajuan}/resubmit', [PengajuanJudulController::class, 'resubmit'])
        ->middleware('throttle:pengajuan-submit')
        ->name('resubmit');
    Route::get('/template', [PengajuanJudulController::class, 'template'])->name('template');
});

// Admin (§5.3): verifikasi pengajuan masuk + pilih validator / tolak /
// minta revisi (alur revisi, sesi 3).
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('skripsi/verifikasi')->name('skripsi.verifikasi.')->group(function (): void {
    Route::get('/', [VerifikasiAdminController::class, 'index'])->name('index');
    Route::post('/{pengajuan}', [VerifikasiAdminController::class, 'store'])->name('store');
    Route::post('/{pengajuan}/revisi', [VerifikasiAdminController::class, 'revisi'])->name('revisi');
});

// Validator (§5.3): penugasan review + putusan setujui/tolak/minta revisi.
Route::middleware(['auth', 'verified', 'role:validator'])->prefix('skripsi/putusan')->name('skripsi.putusan.')->group(function (): void {
    Route::get('/', [PutusanValidatorController::class, 'index'])->name('index');
    Route::post('/{pengajuan}', [PutusanValidatorController::class, 'store'])->name('store');
    Route::post('/{pengajuan}/revisi', [PutusanValidatorController::class, 'revisi'])->name('revisi');
});

// Admin & validator (§5.2): daftar judul; penugasan (assign) hanya admin.
// Catatan: multi-role Spatie memakai PIPE (role:admin|validator) — koma
// akan dibaca sebagai argumen guard kedua.
Route::middleware(['auth', 'verified', 'role:admin|validator'])->prefix('skripsi/daftar-judul')->name('skripsi.daftar-judul.')->group(function (): void {
    Route::get('/', [DaftarJudulController::class, 'index'])->name('index');

    Route::post('/{judul}/assign', [DaftarJudulController::class, 'assign'])
        ->middleware('role:admin')
        ->name('assign');
});

// Admin (PR 3 sesi 3 — dipecah jadi menu Laporan): statistik ringkasan,
// monitoring per pengajuan, dan sebaran beban dosen.
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('skripsi')->name('skripsi.')->group(function (): void {
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/beban-dosen', [BebanDosenController::class, 'index'])->name('beban-dosen.index');
    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export.csv', [ExportController::class, 'download'])->name('export.csv');
});

// Admin & mahasiswa (PR 4 sesi 3): riwayat pengajuan — daftar baca-saja
// dengan timeline detail di dialog. Catatan: multi-role Spatie memakai
// PIPE (role:admin|mahasiswa) — koma akan dibaca sebagai argumen guard
// kedua.
Route::middleware(['auth', 'verified', 'role:admin|mahasiswa'])->prefix('skripsi/riwayat')->name('skripsi.riwayat.')->group(function (): void {
    Route::get('/', [RiwayatPengajuanController::class, 'index'])->name('index');
    Route::get('/{pengajuan}/berkas', [RiwayatPengajuanController::class, 'berkas'])->name('berkas');
});
