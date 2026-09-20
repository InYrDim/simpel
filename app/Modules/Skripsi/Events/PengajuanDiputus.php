<?php

namespace App\Modules\Skripsi\Events;

use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event: validator baru saja memutuskan — judul tertentu disetujui
 * atau pengajuan ditolak dengan catatan (§5.4).
 */
class PengajuanDiputus
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PengajuanJudul $pengajuan,
        public readonly ?JudulPengajuan $judulDisetujui = null,
    ) {}
}
