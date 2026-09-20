<?php

namespace App\Modules\Skripsi\Notifications;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke admin: pengajuan yang diminta revisi telah dikirim ulang
 * mahasiswa dan menunggu verifikasi ulang (alur revisi, sesi 3).
 */
class PengajuanDiajukanUlang extends Notification
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
            'jenis' => 'pengajuan_diajukan_ulang',
            'judul' => 'Pengajuan direvisi — menunggu verifikasi ulang',
            'pesan' => "Pengajuan telah direvisi mahasiswa dan menunggu verifikasi ulang (dikirim {$this->pengajuan->submitted_at?->translatedFormat('d F Y H:i')}).",
            'pengajuan_id' => $this->pengajuan->id,
        ]);
    }
}
