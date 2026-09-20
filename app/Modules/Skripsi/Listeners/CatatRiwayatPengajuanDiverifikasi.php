<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiverifikasi;
use App\Modules\Skripsi\Models\PengajuanRiwayat;

/**
 * Catat transisi `diajukan → diverifikasi_admin | ditolak_admin` di jejak
 * audit (PR 1 sesi 3). Aktor = admin pelaksana (diteruskan via event).
 */
class CatatRiwayatPengajuanDiverifikasi
{
    public function handle(PengajuanDiverifikasi $event): void
    {
        $pengajuan = $event->pengajuan;

        PengajuanRiwayat::create([
            'pengajuan_judul_id' => $pengajuan->id,
            'dari_status' => StatusPengajuan::Diajukan->value,
            'ke_status' => $pengajuan->status->value,
            'aksi' => $pengajuan->status === StatusPengajuan::DitolakAdmin
                ? 'verifikasi_tolak'
                : 'verifikasi_setuju',
            'aktor_id' => $event->aktor?->id,
            'catatan' => $pengajuan->catatan_admin,
        ]);
    }
}
