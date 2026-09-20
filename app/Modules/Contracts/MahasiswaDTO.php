<?php

namespace App\Modules\Contracts;

/**
 * Data mahasiswa yang melintasi batas modul — bentuk datar, tanpa perilaku.
 *
 * `userId` merujuk tabel core `users`; `dosenPaId` merujuk dosen milik
 * Akademik (resolusi namanya via `AkademikContract::dosenById()`).
 */
final readonly class MahasiswaDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $nama,
        public string $nim,
        public int $dosenPaId,
        public ?string $prodi,
        public ?int $angkatan,
    ) {}
}
