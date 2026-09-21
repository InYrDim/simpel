<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\JudulPengajuan;
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

    /**
     * Sebaran beban penugasan per dosen (PRD Beban Dosen §4, §6): jumlah
     * penugasan TERSIMPAN per peran — validator aktif (pengajuan status
     * `diverifikasi_admin`) dan pembimbing/penguji (kolom `dosen_*` pada
     * judul milik pengajuan `disetujui`) — bersifat kumulatif, tanpa konsep
     * "selesai".
     *
     * Cakupan: SEMUA dosen Akademik tampil, termasuk yang berban 0 (satu
     * panggilan `daftarDosen()`, tanpa panggilan kontrak per baris); dosen
     * tanpa nama tampil sebagai `-`. Urutan: Total menurun, lalu nama —
     * stabil untuk UI.
     *
     * @return list<array{dosen_id: int, dosen_nama: string, validator_aktif: int, pembimbing_1: int, pembimbing_2: int, penguji_1: int, penguji_2: int, total: int}>
     */
    public function bebanDosen(): array
    {
        // Validator aktif: pengajuan yang SEDANG ditugaskan review per dosen.
        $validatorAktif = PengajuanJudul::query()
            ->where('status', StatusPengajuan::DiverifikasiAdmin->value)
            ->whereNotNull('validator_id')
            ->get(['validator_id'])
            ->countBy('validator_id');

        // Penugasan pembimbing/penguji: hanya judul milik pengajuan
        // `disetujui` (kolom penugasan hanya diisi pada judul disetujui).
        $penugasan = JudulPengajuan::query()
            ->whereHas('pengajuan', fn ($q) => $q->where('status', StatusPengajuan::Disetujui->value))
            ->get(['dosen_pembimbing_1', 'dosen_pembimbing_2', 'dosen_penguji_1', 'dosen_penguji_2']);

        // countBy per kolom peran — baris tanpa penugasan (null) di-skip.
        $perPeran = [
            'pembimbing_1' => $penugasan->map(fn ($j) => $j->dosen_pembimbing_1)->filter()->countBy(),
            'pembimbing_2' => $penugasan->map(fn ($j) => $j->dosen_pembimbing_2)->filter()->countBy(),
            'penguji_1' => $penugasan->map(fn ($j) => $j->dosen_penguji_1)->filter()->countBy(),
            'penguji_2' => $penugasan->map(fn ($j) => $j->dosen_penguji_2)->filter()->countBy(),
        ];

        $namaDosen = [];
        foreach ($this->akademik->daftarDosen()->all() as $dosen) {
            $namaDosen[$dosen->id] = $dosen->nama !== '' ? $dosen->nama : '-';
        }

        // Semua dosen yang muncul di salah satu kolom — termasuk yang tak
        // lagi terdaftar di Akademik (nama `-`), agar totalnya tidak hilang.
        $idsDenganBeban = $validatorAktif->keys()
            ->merge($penugasan->flatMap(fn ($j) => [
                $j->dosen_pembimbing_1,
                $j->dosen_pembimbing_2,
                $j->dosen_penguji_1,
                $j->dosen_penguji_2,
            ]))
            ->filter() // judul tanpa penugasan menyumbang null — buang
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $baris = collect($namaDosen)
            ->keys()
            ->merge($idsDenganBeban)
            ->unique()
            ->values()
            ->map(fn (int $dosenId): array => [
                'dosen_id' => $dosenId,
                'dosen_nama' => $namaDosen[$dosenId] ?? '-',
                'validator_aktif' => (int) ($validatorAktif[$dosenId] ?? 0),
                'pembimbing_1' => (int) ($perPeran['pembimbing_1'][$dosenId] ?? 0),
                'pembimbing_2' => (int) ($perPeran['pembimbing_2'][$dosenId] ?? 0),
                'penguji_1' => (int) ($perPeran['penguji_1'][$dosenId] ?? 0),
                'penguji_2' => (int) ($perPeran['penguji_2'][$dosenId] ?? 0),
            ])
            ->map(function (array $row): array {
                $row['total'] = $row['validator_aktif']
                    + $row['pembimbing_1']
                    + $row['pembimbing_2']
                    + $row['penguji_1']
                    + $row['penguji_2'];

                return $row;
            })
            ->sortBy([
                ['total', 'desc'],
                ['dosen_nama', 'asc'],
            ])
            ->values()
            ->all();

        return array_values($baris);
    }
}
