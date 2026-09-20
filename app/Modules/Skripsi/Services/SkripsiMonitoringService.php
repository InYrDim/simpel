<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;

/**
 * Agregasi statistik pengajuan untuk dashboard monitoring admin (PR 3
 * sesi 3). Hanya membaca tabel milik modul Skripsi — nama dosen di-resolusi
 * lewat AkademikContract, tanpa query lintas modul (§7.2).
 */
class SkripsiMonitoringService
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * Ringkasan statistik: total pengajuan, jumlah per status (semua status
     * selalu hadir — termasuk yang nol — agar urutan UI stabil), beban
     * penugasan aktif per validator, dan jumlah pengajuan bulan berjalan.
     *
     * @return array{total: int, per_status: list<array{status: string, status_label: string, jumlah: int}>, per_validator: list<array{dosen_id: int, dosen_nama: string, beban: int}>, bulan_ini: int}
     */
    public function ringkasan(): array
    {
        $statusCount = PengajuanJudul::query()
            ->pluck('status')
            ->countBy();

        $perStatus = collect(StatusPengajuan::cases())
            ->map(fn (StatusPengajuan $status): array => [
                'status' => $status->value,
                'status_label' => $status->label(),
                'jumlah' => (int) ($statusCount[$status->value] ?? 0),
            ])
            ->all();

        // Beban validator = pengajuan yang SEDANG ditugaskan review
        // (`diverifikasi_admin`) per dosen; nama via kontrak Akademik.
        $perValidator = PengajuanJudul::query()
            ->where('status', StatusPengajuan::DiverifikasiAdmin->value)
            ->whereNotNull('validator_id')
            ->get(['validator_id'])
            ->countBy('validator_id')
            ->map(fn (int $beban, int|string $dosenId): array => [
                'dosen_id' => (int) $dosenId,
                'dosen_nama' => $this->akademik->dosenById((int) $dosenId)->nama ?? '-',
                'beban' => $beban,
            ])
            ->sortByDesc('beban')
            ->values()
            ->all();

        return [
            'total' => PengajuanJudul::query()->count(),
            'per_status' => array_values($perStatus),
            'per_validator' => array_values($perValidator),
            'bulan_ini' => PengajuanJudul::query()
                ->where('submitted_at', '>=', now()->startOfMonth())
                ->count(),
        ];
    }
}
