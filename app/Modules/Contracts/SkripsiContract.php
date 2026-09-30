<?php

namespace App\Modules\Contracts;

/**
 * Kontrak publik modul Skripsi — satu-satunya cara modul lain bertanya
 * tentang data pengajuan skripsi.
 *
 * Modul lain DILARANG mengimpor model Eloquent milik Skripsi
 * (`App\Modules\Skripsi\*`); pelanggaran digagalkan oleh
 * `tests/Feature/Architecture/ModuleBoundaryTest.php`.
 */
interface SkripsiContract
{
    /**
     * Subset dari `$mahasiswaIds` yang masih punya pengajuan skripsi (status
     * apa pun) — satu query `whereIn`, bukan satu panggilan per mahasiswa.
     * Daftar kosong tidak menembak query sama sekali.
     *
     * @param  list<int>  $mahasiswaIds
     * @return list<int>
     */
    public function mahasiswaIdsDenganPengajuan(array $mahasiswaIds): array;
}
