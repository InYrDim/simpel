<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDirevisi;
use App\Modules\Skripsi\Notifications\PengajuanDirevisi as PengajuanDirevisiNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke mahasiswa saat pengajuannya diminta
 * revisi (alur revisi, sesi 3). Catatan yang dikutip sesuai asal aksi:
 * admin (dari `diajukan`) memakai `catatan_admin`, validator (dari
 * `diverifikasi_admin`) memakai `catatan_validator`.
 */
class KirimNotifikasiPengajuanDirevisi
{
    public function handle(PengajuanDirevisi $event): void
    {
        $pengajuan = $event->pengajuan;

        $catatan = $event->dariStatus === StatusPengajuan::Diajukan
            ? $pengajuan->catatan_admin
            : $pengajuan->catatan_validator;

        Notification::send(
            $pengajuan->user,
            new PengajuanDirevisiNotification($pengajuan, (string) $catatan),
        );
    }
}
