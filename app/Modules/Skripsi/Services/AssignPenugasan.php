<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\JudulPengajuan;
use Illuminate\Validation\ValidationException;

/**
 * Action penugasan pembimbing & penguji oleh admin (PRD §3.5).
 *
 * Hanya boleh pada judul milik pengajuan berstatus `disetujui`, hanya pada
 * judul yang ditetapkan validator (bila sudah ditetapkan). Semua ID dosen
 * divalidasi via AkademikContract — tanpa menyentuh internal Akademik.
 */
class AssignPenugasan
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * @param  array{dosen_pembimbing_1?: int|null, dosen_pembimbing_2?: int|null, dosen_penguji_1?: int|null, dosen_penguji_2?: int|null}  $penugasan
     *
     * @throws ValidationException
     */
    public function handle(JudulPengajuan $judul, array $penugasan): JudulPengajuan
    {
        $pengajuan = $judul->pengajuan;

        if ($pengajuan->status !== StatusPengajuan::Disetujui) {
            throw ValidationException::withMessages([
                'status' => 'Penugasan hanya bisa diisi pada pengajuan yang sudah disetujui.',
            ]);
        }

        foreach ($penugasan as $dosenId) {
            if ($dosenId !== null && $this->akademik->dosenById($dosenId) === null) {
                throw ValidationException::withMessages([
                    'penugasan' => 'Dosen tidak ditemukan.',
                ]);
            }
        }

        $judul->update($penugasan);

        return $judul;
    }
}
