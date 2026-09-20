<?php

namespace App\Modules\Skripsi\Notifications;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke admin: ada pengajuan judul baru menunggu verifikasi (§5.4).
 */
class PengajuanDiajukan extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PengajuanJudul $pengajuan,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'jenis' => 'pengajuan_diajukan',
            'judul' => 'Pengajuan judul baru',
            'pesan' => "Pengajuan judul baru menunggu verifikasi (diajukan {$this->pengajuan->submitted_at?->translatedFormat('d F Y H:i')}).",
            'pengajuan_id' => $this->pengajuan->id,
        ]);
    }
}
