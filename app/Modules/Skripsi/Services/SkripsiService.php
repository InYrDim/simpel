<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Contracts\SkripsiContract;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;

/**
 * Implementasi `SkripsiContract` — pintu baca-saja bagi modul lain.
 */
class SkripsiService implements SkripsiContract
{
    /**
     * @param  list<int>  $mahasiswaIds
     * @return list<int>
     */
    public function mahasiswaIdsDenganPengajuan(array $mahasiswaIds): array
    {
        if ($mahasiswaIds === []) {
            return [];
        }

        return array_values(
            PengajuanJudul::query()
                ->whereIn('mahasiswa_id', $mahasiswaIds)
                ->distinct()
                ->pluck('mahasiswa_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all(),
        );
    }

    /**
     * @param  list<int>  $dosenIds
     * @return list<int>
     */
    public function dosenIdsDenganPenugasan(array $dosenIds): array
    {
        if ($dosenIds === []) {
            return [];
        }

        $dirujuk = PengajuanJudul::query()
            ->whereIn('validator_id', $dosenIds)
            ->pluck('validator_id')
            ->all();

        foreach (['dosen_pembimbing_1', 'dosen_pembimbing_2', 'dosen_penguji_1', 'dosen_penguji_2'] as $kolom) {
            $dirujuk = [
                ...$dirujuk,
                ...JudulPengajuan::query()->whereIn($kolom, $dosenIds)->pluck($kolom)->all(),
            ];
        }

        return array_values(array_unique(array_map(intval(...), $dirujuk)));
    }
}
