<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDirevisi;
use App\Modules\Skripsi\Notifications\PengajuanDirevisi as PengajuanDirevisiNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke mahasiswa saat pengajuannya diminta
 * revisi (alur revisi, sesi 3). Catatan yang dikutip sesuai asal aksi:
 * admin (dari `diajukan`) memakai `catatan_admin`, validator (dari
 * `diverifikasi_admin`) memakai `catatan_validator`.
 *
 * Listener ini DI-QUEUE (PRD ketahanan-teknis §3.1).
 */
class KirimNotifikasiPengajuanDirevisi implements ShouldQueue
{
    /**
     * Notifikasi terkirim hanya setelah transaksi pembungkus event commit —
     * rollback membatalkan pengiriman (PRD ketahanan-teknis §3.1).
     */
    public bool $afterCommit = true;

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
