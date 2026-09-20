<?php

namespace App\Modules\Skripsi\Enums;

/**
 * Status pengajuan judul (PRD §3.3, §4; revisi = PRD §5.1 diperluas, sesi 3).
 *
 * Transisi sah:
 *
 *   [tidak ada pengajuan aktif] → diajukan (submit mahasiswa)
 *   diajukan → diverifikasi_admin | ditolak_admin | direvisi   (admin)
 *   diverifikasi_admin → disetujui | ditolak_validator | direvisi (validator)
 *   direvisi → diajukan                                        (resubmit mahasiswa
 *                                                                pada pengajuan SAMA)
 *
 * Submit ulang setelah DITOLAK = pengajuan baru (§8 keputusan #1); resubmit
 * setelah DIMINTA REVISI tetap pada pengajuan yang sama.
 */
enum StatusPengajuan: string
{
    case Diajukan = 'diajukan';
    case Direvisi = 'direvisi';
    case DiverifikasiAdmin = 'diverifikasi_admin';
    case DitolakAdmin = 'ditolak_admin';
    case DiverifikasiValidator = 'diverifikasi_validator';
    case Disetujui = 'disetujui';
    case DitolakValidator = 'ditolak_validator';

    /**
     * Status yang menghalangi pengajuan baru (§6.3). `direvisi` ikut
     * menghalangi karena resubmit tetap pada pengajuan yang sama.
     *
     * @return list<self>
     */
    public static function aktif(): array
    {
        return [self::Diajukan, self::Direvisi, self::DiverifikasiAdmin, self::DiverifikasiValidator];
    }

    /**
     * Status yang sah sebagai titik awal transisi `$this`.
     */
    public function bolehTransisiKe(self $target): bool
    {
        return match ($this) {
            self::Diajukan => in_array($target, [self::DiverifikasiAdmin, self::DitolakAdmin, self::Direvisi], true),
            self::DiverifikasiAdmin => in_array($target, [self::Disetujui, self::DitolakValidator, self::Direvisi], true),
            self::Direvisi => $target === self::Diajukan,
            self::DiverifikasiValidator,
            self::DitolakAdmin,
            self::Disetujui,
            self::DitolakValidator => false, // titik akhir; submit ulang = pengajuan baru
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Direvisi => 'Direvisi',
            self::DiverifikasiAdmin => 'Diverifikasi Admin',
            self::DitolakAdmin => 'Ditolak Admin',
            self::DiverifikasiValidator => 'Diverifikasi Validator',
            self::Disetujui => 'Disetujui',
            self::DitolakValidator => 'Ditolak Validator',
        };
    }
}
