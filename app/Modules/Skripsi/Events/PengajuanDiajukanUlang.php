<?php

namespace App\Modules\Skripsi\Events;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event: mahasiswa mengirim ulang pengajuan yang diminta revisi —
 * pengajuan yang SAMA kembali ke `diajukan` (alur revisi, sesi 3).
 */
class PengajuanDiajukanUlang
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PengajuanJudul $pengajuan,
    ) {}
}
