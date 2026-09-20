<?php

namespace App\Modules\Skripsi\Events;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event: mahasiswa baru saja mengajukan pengajuan judul (§5.4).
 */
class PengajuanDiajukan
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PengajuanJudul $pengajuan,
    ) {}
}
