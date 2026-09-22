<?php

namespace App\Modules\Skripsi\Listeners;

use App\Models\User;
use App\Modules\Skripsi\Events\PengajuanDiajukanUlang;
use App\Modules\Skripsi\Notifications\PengajuanDiajukanUlang as PengajuanDiajukanUlangNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke semua admin saat pengajuan yang diminta
 * revisi dikirim ulang mahasiswa (alur revisi, sesi 3).
 *
 * Penerima role admin diresolusi via `User::role('admin')` — `App\Models\User`
 * shared kernel yang boleh dipakai modul (PRD §5.4).
 *
 * Listener ini DI-QUEUE (PRD ketahanan-teknis §3.1).
 */
class KirimNotifikasiPengajuanDiajukanUlang implements ShouldQueue
{
    /**
     * Notifikasi terkirim hanya setelah transaksi pembungkus event commit —
     * rollback membatalkan pengiriman (PRD ketahanan-teknis §3.1).
     */
    public bool $afterCommit = true;

    public function handle(PengajuanDiajukanUlang $event): void
    {
        $admins = User::query()->role('admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new PengajuanDiajukanUlangNotification($event->pengajuan));
    }
}
