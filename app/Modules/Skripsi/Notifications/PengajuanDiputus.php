<?php

namespace App\Modules\Skripsi\Notifications;

use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke mahasiswa setelah validator memutuskan (§5.4): judul
 * tertentu disetujui, atau pengajuan ditolak dengan catatan.
 */
class PengajuanDiputus extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PengajuanJudul $pengajuan,
        private readonly ?JudulPengajuan $judulDisetujui,
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
        $disetujui = $this->judulDisetujui !== null;

        return new DatabaseMessage([
            'jenis' => 'pengajuan_diputus',
            'judul' => $disetujui ? 'Judul skripsi disetujui' : 'Pengajuan ditolak validator',
            'pesan' => $disetujui
                ? "Judul \"{$this->judulDisetujui->judul}\" disetujui. Silakan cek detail penugasan."
                : 'Pengajuan Anda ditolak validator: '.(string) $this->pengajuan->catatan_validator,
            'pengajuan_id' => $this->pengajuan->id,
        ]);
    }
}
