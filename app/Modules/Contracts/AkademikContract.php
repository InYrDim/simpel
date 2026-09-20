<?php

namespace App\Modules\Contracts;

/**
 * Kontrak publik modul Akademik — satu-satunya cara modul lain mengakses
 * data mahasiswa & dosen.
 *
 * Modul lain DILARANG mengimpor model Eloquent milik Akademik
 * (`App\Modules\Akademik\*`); pelanggaran digagalkan oleh
 * `tests/Feature/Architecture/ModuleBoundaryTest.php`. Data dikembalikan
 * sebagai DTO sederhana yang juga didefinisikan di namespace bersama ini.
 */
interface AkademikContract
{
    /**
     * Profil akademik milik akun user, atau null bila tidak terdaftar.
     */
    public function mahasiswaByUserId(int $userId): ?MahasiswaDTO;

    /**
     * Profil akademik berdasarkan NIM, atau null bila tidak ditemukan.
     */
    public function mahasiswaByNim(string $nim): ?MahasiswaDTO;

    /**
     * Daftar dosen lengkap — dipakai admin untuk menugaskan validator,
     * pembimbing, dan penguji (PRD §5.3, §3.2 `bidang`).
     */
    public function daftarDosen(): DosenDTOList;

    /**
     * Detail satu dosen by ID, atau null bila tidak ada.
     */
    public function dosenById(int $id): ?DosenDTO;

    /**
     * Dosen pemilik akun user tertentu, atau null bila akun bukan dosen /
     * belum terhubung — dipakai modul lain untuk resolusi validator dari
     * akun login tanpa menyentuh internal Akademik.
     */
    public function dosenByUserId(int $userId): ?DosenDTO;
}
