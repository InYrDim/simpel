<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Events\PengajuanDiputus;
use App\Modules\Skripsi\Notifications\PengajuanDiputus as PengajuanDiputusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke mahasiswa pemilik pengajuan setelah
 * validator memutuskan — disetujui (judul tertentu) atau ditolak (§5.4).
 *
 * Listener ini DI-QUEUE (PRD ketahanan-teknis §3.1).
 */
class KirimNotifikasiPengajuanDiputus implements ShouldQueue
{
    /**
     * Notifikasi terkirim hanya setelah transaksi pembungkus event commit —
     * rollback membatalkan pengiriman (PRD ketahanan-teknis §3.1).
     */
    public bool $afterCommit = true;

    public function handle(PengajuanDiputus $event): void
    {
        Notification::send(
            $event->pengajuan->user,
            new PengajuanDiputusNotification($event->pengajuan, $event->judulDisetujui),
        );
    }
}
