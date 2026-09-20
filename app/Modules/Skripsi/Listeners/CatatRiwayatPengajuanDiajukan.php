<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Events\PengajuanDiajukan;
use App\Modules\Skripsi\Models\PengajuanRiwayat;

/**
 * Catat transisi awal `→ diajukan` di jejak audit (PR 1 sesi 3).
 *
 * Aktor = mahasiswa penyusun pengajuan (pengajuan.user_id) — tidak perlu
 * diteruskan dari event karena memang identik dengan pemilik pengajuan.
 */
class CatatRiwayatPengajuanDiajukan
{
    public function handle(PengajuanDiajukan $event): void
    {
        PengajuanRiwayat::create([
            'pengajuan_judul_id' => $event->pengajuan->id,
            'dari_status' => null,
            'ke_status' => $event->pengajuan->status->value,
            'aksi' => 'submit',
            'aktor_id' => $event->pengajuan->user_id,
        ]);
    }
}
