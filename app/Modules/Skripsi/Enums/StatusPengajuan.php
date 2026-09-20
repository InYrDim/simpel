<?php

namespace App\Modules\Skripsi\Enums;

/**
 * Status pengajuan judul (PRD §3.3, §4).
 *
 * Transisi sah:
 *
 *   [tidak ada pengajuan aktif] → diajukan (submit mahasiswa)
 *   diajukan → diverifikasi_admin | ditolak_admin          (admin)
 *   diverifikasi_admin → disetujui | ditolak_validator     (validator)
 *
 * Submit ulang setelah ditolak = pengajuan baru (§8 keputusan #1).
 */
enum StatusPengajuan: string
{
    case Diajukan = 'diajukan';
    case DiverifikasiAdmin = 'diverifikasi_admin';
    case DitolakAdmin = 'ditolak_admin';
    case DiverifikasiValidator = 'diverifikasi_validator';
    case Disetujui = 'disetujui';
    case DitolakValidator = 'ditolak_validator';

    /**
     * Status yang menghalangi pengajuan baru (§6.3).
     *
     * @return list<self>
     */
    public static function aktif(): array
    {
        return [self::Diajukan, self::DiverifikasiAdmin, self::DiverifikasiValidator];
    }

    /**
     * Status yang sah sebagai titik awal transisi `$this`.
     */
    public function bolehTransisiKe(self $target): bool
    {
        return match ($this) {
            self::Diajukan => in_array($target, [self::DiverifikasiAdmin, self::DitolakAdmin], true),
            self::DiverifikasiAdmin => in_array($target, [self::Disetujui, self::DitolakValidator], true),
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
            self::DiverifikasiAdmin => 'Diverifikasi Admin',
            self::DitolakAdmin => 'Ditolak Admin',
            self::DiverifikasiValidator => 'Diverifikasi Validator',
            self::Disetujui => 'Disetujui',
            self::DitolakValidator => 'Ditolak Validator',
        };
    }
}
