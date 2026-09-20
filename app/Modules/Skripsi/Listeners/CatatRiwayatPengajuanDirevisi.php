<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDirevisi;
use App\Modules\Skripsi\Models\PengajuanRiwayat;

/**
 * Catat transisi `diajukan | diverifikasi_admin → direvisi` di jejak audit
 * (alur revisi, sesi 3). Aksi mengikuti asal aksi: dari `diajukan` berarti
 * verifikasi admin, dari `diverifikasi_admin` berarti putusan validator.
 */
class CatatRiwayatPengajuanDirevisi
{
    public function handle(PengajuanDirevisi $event): void
    {
        $pengajuan = $event->pengajuan;

        PengajuanRiwayat::create([
            'pengajuan_judul_id' => $pengajuan->id,
            'dari_status' => $event->dariStatus->value,
            'ke_status' => StatusPengajuan::Direvisi->value,
            'aksi' => $event->dariStatus === StatusPengajuan::Diajukan
                ? 'verifikasi_revisi'
                : 'putusan_revisi',
            'aktor_id' => $event->aktor?->id,
            'catatan' => $event->dariStatus === StatusPengajuan::Diajukan
                ? $pengajuan->catatan_admin
                : $pengajuan->catatan_validator,
        ]);
    }
}
