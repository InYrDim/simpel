<?php

namespace App\Modules\Contracts;

/**
 * Data dosen yang melintasi batas modul — bentuk datar, tanpa perilaku.
 *
 * `userId` adalah akun login dosen (biasanya role `validator`), null bila
 * dosen belum punya akun — dipakai modul lain untuk resolusi penerima
 * notifikasi tanpa menyentuh modul Akademik.
 */
final readonly class DosenDTO
{
    public function __construct(
        public int $id,
        public string $nama,
        public string $nip,
        public string $bidang,
        public ?int $userId = null,
    ) {}
}
