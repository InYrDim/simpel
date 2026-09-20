<?php

namespace App\Modules\Akademik\Services;

use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Contracts\DosenDTO;
use App\Modules\Contracts\DosenDTOList;
use App\Modules\Contracts\MahasiswaDTO;

/**
 * Implementasi `AkademikContract` — satu-satunya jembatan dari modul lain
 * ke data Akademik. Kelas ini berada DI DALAM modul Akademik sehingga
 * boleh memakai model internalnya sendiri.
 */
class AkademikService implements AkademikContract
{
    public function mahasiswaByUserId(int $userId): ?MahasiswaDTO
    {
        $mahasiswa = Mahasiswa::query()->where('user_id', $userId)->first();

        return $mahasiswa === null ? null : $this->toMahasiswaDTO($mahasiswa);
    }

    public function mahasiswaByNim(string $nim): ?MahasiswaDTO
    {
        $mahasiswa = Mahasiswa::query()->where('nim', $nim)->first();

        return $mahasiswa === null ? null : $this->toMahasiswaDTO($mahasiswa);
    }

    public function daftarDosen(): DosenDTOList
    {
        $dosen = Dosen::query()->orderBy('nama')->get();

        $items = array_values(
            $dosen
                ->map(fn (Dosen $d): DosenDTO => $this->toDosenDTO($d))
                ->all()
        );

        return new DosenDTOList($items);
    }

    public function dosenById(int $id): ?DosenDTO
    {
        $dosen = Dosen::query()->find($id);

        return $dosen === null ? null : $this->toDosenDTO($dosen);
    }

    private function toMahasiswaDTO(Mahasiswa $m): MahasiswaDTO
    {
        return new MahasiswaDTO(
            id: $m->id,
            userId: $m->user_id,
            nama: $m->nama,
            nim: $m->nim,
            dosenPaId: $m->dosen_pa_id,
            prodi: $m->prodi,
            angkatan: $m->angkatan,
        );
    }

    private function toDosenDTO(Dosen $d): DosenDTO
    {
        return new DosenDTO(
            id: $d->id,
            nama: $d->nama,
            nip: $d->nip,
            bidang: $d->bidang,
        );
    }
}
