<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiajukanUlang;
use App\Modules\Skripsi\Models\PengajuanRiwayat;

/**
 * Catat resubmit `direvisi → diajukan` di jejak audit (alur revisi, sesi 3).
 *
 * Aktor = mahasiswa pemilik pengajuan (pengajuan.user_id) — identik dengan
 * pola listener submit.
 */
class CatatRiwayatPengajuanDiajukanUlang
{
    public function handle(PengajuanDiajukanUlang $event): void
    {
        PengajuanRiwayat::create([
            'pengajuan_judul_id' => $event->pengajuan->id,
            'dari_status' => StatusPengajuan::Direvisi->value,
            'ke_status' => StatusPengajuan::Diajukan->value,
            'aksi' => 'resubmit',
            'aktor_id' => $event->pengajuan->user_id,
        ]);
    }
}
