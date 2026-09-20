<?php

namespace App\Modules\Skripsi\Events;

use App\Models\User;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Domain event: admin atau validator meminta revisi — pengajuan
 * dikembalikan ke mahasiswa dengan catatan (alur revisi, sesi 3).
 *
 * `$dariStatus` (diajukan = admin, diverifikasi_admin = validator) diteruskan
 * eksplisit karena status pengajuan sudah berubah menjadi `direvisi` saat
 * listener berjalan — asal transisi hilang dari model.
 */
class PengajuanDirevisi
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PengajuanJudul $pengajuan,
        public readonly StatusPengajuan $dariStatus,
        public readonly ?User $aktor = null,
    ) {}
}
