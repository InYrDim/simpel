<?php

namespace App\Modules\Skripsi\Notifications;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke mahasiswa: pengajuannya diminta revisi (admin/validator)
 * dan harus diperbaiki lalu dikirim ulang (alur revisi, sesi 3).
 */
class PengajuanDirevisi extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PengajuanJudul $pengajuan,
        private readonly string $catatan,
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
            'jenis' => 'pengajuan_direvisi',
            'judul' => 'Pengajuan diminta revisi',
            'pesan' => 'Pengajuan Anda diminta revisi: '.$this->catatan,
            'pengajuan_id' => $this->pengajuan->id,
        ]);
    }
}
