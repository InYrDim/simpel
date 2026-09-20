<?php

namespace App\Modules\Contracts;

/**
 * Kumpulan `DosenDTO` yang immutable — supaya kontrak tidak mengembalikan
 * array polos yang mudah dimutasi di luar modul pemiliknya.
 */
final readonly class DosenDTOList
{
    /**
     * @param  list<DosenDTO>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @return list<DosenDTO>
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
     * @return list<array{id: int, nama: string, nip: string, bidang: string}>
     */
    public function toOptionList(): array
    {
        return array_map(
            fn (DosenDTO $dosen): array => [
                'id' => $dosen->id,
                'nama' => $dosen->nama,
                'nip' => $dosen->nip,
                'bidang' => $dosen->bidang,
            ],
            $this->items,
        );
    }
}
