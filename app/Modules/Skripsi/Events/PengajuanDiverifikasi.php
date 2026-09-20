<?php

namespace App\Modules\Skripsi\Events;

use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event: admin baru saja memverifikasi pengajuan — disetujui
 * (validator ditugaskan) atau ditolak dengan catatan (§5.4).
 *
 * `$aktor` adalah admin pelaksana verifikasi, untuk jejak audit (PR 1
 * sesi 3). Nullable demi kompatibilitas dengan pemanggil lama.
 */
class PengajuanDiverifikasi
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PengajuanJudul $pengajuan,
        public readonly ?User $aktor = null,
    ) {}
}
