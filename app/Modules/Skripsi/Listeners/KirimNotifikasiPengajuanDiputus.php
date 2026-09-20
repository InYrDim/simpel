<?php

namespace App\Modules\Skripsi\Listeners;

use App\Modules\Skripsi\Events\PengajuanDiputus;
use App\Modules\Skripsi\Notifications\PengajuanDiputus as PengajuanDiputusNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke mahasiswa pemilik pengajuan setelah
 * validator memutuskan — disetujui (judul tertentu) atau ditolak (§5.4).
 */
class KirimNotifikasiPengajuanDiputus
{
    public function handle(PengajuanDiputus $event): void
    {
        Notification::send(
            $event->pengajuan->user,
            new PengajuanDiputusNotification($event->pengajuan, $event->judulDisetujui),
        );
    }
}
