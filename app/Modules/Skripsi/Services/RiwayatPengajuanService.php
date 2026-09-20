<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Models\PengajuanRiwayat;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Daftar pengajuan untuk halaman Riwayat Pengajuan (PR 4 sesi 3) —
 * baca-saja. Hanya membaca tabel milik modul Skripsi; identitas mahasiswa
 * di-resolusi lewat AkademikContract, tanpa query lintas modul (§7.2).
 */
class RiwayatPengajuanService
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * Daftar pengajuan terurut terbaru. Admin melihat semua mahasiswa
     * (`lihatSemua`); mahasiswa hanya pengajuan miliknya. Timeline jejak
     * audit dituang per baris agar dialog detail tidak butuh endpoint
     * terpisah (pola modal Daftar Judul).
     *
     * @return LengthAwarePaginator<int, array{id: int, status: string, status_label: string, submitted_at: string|null, nama_mahasiswa: string, nim: string, jumlah_judul: int, judul_list: list<string>, catatan_admin: string|null, catatan_validator: string|null, riwayat: list<array{aksi: string, dari_status: string|null, dari_status_label: string|null, ke_status: string, ke_status_label: string, aktor_nama: string, catatan: string|null, created_at: string|null}>}>
     */
    public function daftar(User $aktor, bool $lihatSemua): LengthAwarePaginator
    {
        $query = PengajuanJudul::query()
            ->with(['juduls', 'riwayat.aktor'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if (! $lihatSemua) {
            $query->where('user_id', $aktor->id);
        }

        return $query->paginate(10)->withQueryString()->through(fn (PengajuanJudul $p): array => $this->barisPengajuan($p));
    }

    /**
     * @return array{id: int, status: string, status_label: string, submitted_at: string|null, nama_mahasiswa: string, nim: string, jumlah_judul: int, judul_list: list<string>, catatan_admin: string|null, catatan_validator: string|null, riwayat: list<array{aksi: string, dari_status: string|null, dari_status_label: string|null, ke_status: string, ke_status_label: string, aktor_nama: string, catatan: string|null, created_at: string|null}>}
     */
    private function barisPengajuan(PengajuanJudul $p): array
    {
        // Identitas via kontrak — satu panggilan per baris, hasilnya dipakai
        // untuk nama sekaligus NIM.
        $mahasiswa = $this->akademik->mahasiswaByUserId($p->user_id);

        return [
            'id' => $p->id,
            'status' => $p->status->value,
            'status_label' => $p->status->label(),
            'submitted_at' => $p->submitted_at?->toISOString(),
            'nama_mahasiswa' => $mahasiswa->nama ?? '-',
            'nim' => $mahasiswa->nim ?? '-',
            'jumlah_judul' => $p->juduls->count(),
            'judul_list' => array_values($p->juduls->map(fn (JudulPengajuan $j): string => $j->judul)->all()),
            'catatan_admin' => $p->catatan_admin,
            'catatan_validator' => $p->catatan_validator,
            'riwayat' => array_values($p->riwayat->map(fn (PengajuanRiwayat $r): array => [
                'aksi' => $r->aksi,
                'dari_status' => $r->dari_status,
                'dari_status_label' => $r->dari_status === null ? null : (StatusPengajuan::tryFrom($r->dari_status)?->label() ?? $r->dari_status),
                'ke_status' => $r->ke_status,
                'ke_status_label' => StatusPengajuan::tryFrom($r->ke_status)?->label() ?? $r->ke_status,
                'aktor_nama' => $r->aktor_id === null ? 'Sistem' : $r->aktor->name,
                'catatan' => $r->catatan,
                'created_at' => $r->created_at?->toISOString(),
            ])->all()),
        ];
    }
}
