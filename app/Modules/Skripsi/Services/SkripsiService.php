<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Contracts\SkripsiContract;
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
}
