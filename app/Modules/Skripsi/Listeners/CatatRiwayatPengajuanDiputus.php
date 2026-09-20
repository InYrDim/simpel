<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiputus;
use App\Modules\Skripsi\Models\PengajuanRiwayat;

/**
 * Catat transisi `diverifikasi_admin → disetujui | ditolak_validator` di
 * jejak audit (PR 1 sesi 3). Aktor = validator pelaksana (diteruskan via
 * event).
 */
class CatatRiwayatPengajuanDiputus
{
    public function handle(PengajuanDiputus $event): void
    {
        $pengajuan = $event->pengajuan;

        PengajuanRiwayat::create([
            'pengajuan_judul_id' => $pengajuan->id,
            'dari_status' => StatusPengajuan::DiverifikasiAdmin->value,
            'ke_status' => $pengajuan->status->value,
            'aksi' => $pengajuan->status === StatusPengajuan::Disetujui
                ? 'putusan_setuju'
                : 'putusan_tolak',
            'aktor_id' => $event->aktor?->id,
            'catatan' => $pengajuan->catatan_validator,
        ]);
    }
}
