<?php

namespace App\Modules\Skripsi\Notifications;

use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke mahasiswa & validator setelah admin memverifikasi (§5.4):
 * - mahasiswa tahu pengajuannya diverifikasi / ditolak admin (dengan catatan);
 * - validator terkait tahu ada penugasan review.
 */
class PengajuanDiverifikasi extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PengajuanJudul $pengajuan,
        private readonly bool $untukValidator,
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
        if ($this->untukValidator) {
            return new DatabaseMessage([
                'jenis' => 'pengajuan_ditugaskan',
                'judul' => 'Penugasan review judul',
                'pesan' => 'Anda ditugaskan mereview pengajuan judul skripsi.',
                'pengajuan_id' => $this->pengajuan->id,
            ]);
        }

        $ditolak = $this->pengajuan->status->value === 'ditolak_admin';

        return new DatabaseMessage([
            'jenis' => 'pengajuan_diverifikasi_admin',
            'judul' => $ditolak ? 'Pengajuan ditolak admin' : 'Pengajuan diverifikasi admin',
            'pesan' => $ditolak
                ? 'Pengajuan Anda ditolak admin: '.(string) $this->pengajuan->catatan_admin
                : 'Pengajuan Anda telah diverifikasi admin dan diteruskan ke validator.',
            'pengajuan_id' => $this->pengajuan->id,
        ]);
    }
}
