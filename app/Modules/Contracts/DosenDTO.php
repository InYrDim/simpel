<?php

namespace App\Modules\Contracts;

/**
 * Data dosen yang melintasi batas modul — bentuk datar, tanpa perilaku.
 */
final readonly class DosenDTO
{
    public function __construct(
        public int $id,
        public string $nama,
        public string $nip,
        public string $bidang,
    ) {}
}
