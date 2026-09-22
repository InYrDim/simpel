<?php

namespace App\Modules\Skripsi\Listeners;

use App\Models\User;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Events\PengajuanDiverifikasi;
use App\Modules\Skripsi\Notifications\PengajuanDiverifikasi as PengajuanDiverifikasiNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Kirim database notification setelah admin verifikasi (§5.4):
 * - selalu ke mahasiswa pemilik pengajuan (via `user_id`);
 * - bila disetujui, ke validator terkait — akun login diresolusi dari
 *   dosen penugasan via `AkademikContract`, tanpa menyentuh internal Akademik.
 *
 * Listener ini DI-QUEUE (PRD ketahanan-teknis §3.1).
 */
class KirimNotifikasiPengajuanDiverifikasi implements ShouldQueue
{
    /**
     * Notifikasi terkirim hanya setelah transaksi pembungkus event commit —
     * rollback membatalkan pengiriman (PRD ketahanan-teknis §3.1).
     */
    public bool $afterCommit = true;

    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    public function handle(PengajuanDiverifikasi $event): void
    {
        $pengajuan = $event->pengajuan;

        Notification::send(
            $pengajuan->user,
            new PengajuanDiverifikasiNotification($pengajuan, untukValidator: false),
        );

        if ($pengajuan->validator_id !== null) {
            $dosen = $this->akademik->dosenById($pengajuan->validator_id);

            if ($dosen?->userId !== null) {
                Notification::send(
                    User::find($dosen->userId),
                    new PengajuanDiverifikasiNotification($pengajuan, untukValidator: true),
                );
            }
        }
    }
}
