<?php

namespace App\Modules\Skripsi\Listeners;

use App\Models\User;
use App\Modules\Skripsi\Events\PengajuanDiajukan;
use App\Modules\Skripsi\Notifications\PengajuanDiajukan as PengajuanDiajukanNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification ke semua admin saat pengajuan dibuat (§5.4).
 *
 * Penerima role admin diresolusi via `User::role('admin')` — `App\Models\User`
 * shared kernel yang boleh dipakai modul (PRD §5.4).
 */
class KirimNotifikasiPengajuanDiajukan
{
    public function handle(PengajuanDiajukan $event): void
    {
        $admins = User::query()->role('admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new PengajuanDiajukanNotification($event->pengajuan));
    }
}
