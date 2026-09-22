<?php

namespace App\Modules\Contracts;

/**
 * Kumpulan `MahasiswaDTO` yang immutable — pasangan batch dari
 * `DosenDTOList`, supaya kontrak tidak mengembalikan array polos yang mudah
 * dimutasi di luar modul pemiliknya.
 */
final readonly class MahasiswaDTOList
{
    /**
     * @param  list<MahasiswaDTO>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @return list<MahasiswaDTO>
     */
    public function all(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Profil milik satu akun user, atau null bila akun tidak punya profil
     * akademik — dipakai konsumen untuk memetakan hasil batch per baris
     * tanpa query kedua.
     */
    public function byUserId(int $userId): ?MahasiswaDTO
    {
        foreach ($this->items as $mahasiswa) {
            if ($mahasiswa->userId === $userId) {
                return $mahasiswa;
            }
        }

        return null;
    }
}
