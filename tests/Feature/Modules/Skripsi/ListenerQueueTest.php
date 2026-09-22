<?php

use App\Modules\Skripsi\Listeners\CatatRiwayatPengajuanDiajukan;
use App\Modules\Skripsi\Listeners\CatatRiwayatPengajuanDiajukanUlang;
use App\Modules\Skripsi\Listeners\CatatRiwayatPengajuanDiputus;
use App\Modules\Skripsi\Listeners\CatatRiwayatPengajuanDirevisi;
use App\Modules\Skripsi\Listeners\CatatRiwayatPengajuanDiverifikasi;
use App\Modules\Skripsi\Listeners\KirimNotifikasiPengajuanDiajukan;
use App\Modules\Skripsi\Listeners\KirimNotifikasiPengajuanDiajukanUlang;
use App\Modules\Skripsi\Listeners\KirimNotifikasiPengajuanDiputus;
use App\Modules\Skripsi\Listeners\KirimNotifikasiPengajuanDirevisi;
use App\Modules\Skripsi\Listeners\KirimNotifikasiPengajuanDiverifikasi;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Menjaga keputusan PRD ketahanan-teknis §3.1: kerja notifikasi antri,
 * sedangkan jejak audit tetap sinkron agar atomik dengan transisi status.
 */
test('listener notifikasi pengajuan antri dengan afterCommit', function (string $listener) {
    $reflection = new ReflectionClass($listener);

    expect($reflection->implementsInterface(ShouldQueue::class))->toBeTrue();
    expect($reflection->getDefaultProperties()['afterCommit'] ?? null)->toBeTrue();
})->with([
    KirimNotifikasiPengajuanDiajukan::class,
    KirimNotifikasiPengajuanDiverifikasi::class,
    KirimNotifikasiPengajuanDiputus::class,
    KirimNotifikasiPengajuanDirevisi::class,
    KirimNotifikasiPengajuanDiajukanUlang::class,
]);

test('listener jejak audit tetap sinkron (bukan ShouldQueue)', function (string $listener) {
    expect((new ReflectionClass($listener))->implementsInterface(ShouldQueue::class))->toBeFalse();
})->with([
    CatatRiwayatPengajuanDiajukan::class,
    CatatRiwayatPengajuanDiverifikasi::class,
    CatatRiwayatPengajuanDiputus::class,
    CatatRiwayatPengajuanDirevisi::class,
    CatatRiwayatPengajuanDiajukanUlang::class,
]);
