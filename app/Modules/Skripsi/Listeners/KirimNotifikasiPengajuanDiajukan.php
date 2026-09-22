<?php

namespace App\Modules\Skripsi\Listeners;

use App\Models\User;
use App\Modules\Skripsi\Events\PengajuanDiajukan;
use App\Modules\Skripsi\Notifications\PengajuanDiajukan as PengajuanDiajukanNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke semua admin saat pengajuan dibuat (§5.4).
 *
 * Penerima role admin diresolusi via `User::role('admin')` — `App\Models\User`
 * shared kernel yang boleh dipakai modul (PRD §5.4).
 *
 * Listener ini DI-QUEUE (PRD ketahanan-teknis §3.1): request aksi tidak lagi
 * memikul kerja notifikasi.
 */
class KirimNotifikasiPengajuanDiajukan implements ShouldQueue
{
    /**
     * Notifikasi terkirim hanya setelah transaksi pembungkus event commit —
     * rollback membatalkan pengiriman (PRD ketahanan-teknis §3.1, keputusan
     * pelaksanaan §8 #2).
     */
    public bool $afterCommit = true;

    public function handle(PengajuanDiajukan $event): void
    {
        $admins = User::query()->role('admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new PengajuanDiajukanNotification($event->pengajuan));
    }
}
