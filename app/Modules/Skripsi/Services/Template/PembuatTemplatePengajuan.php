<?php

namespace App\Modules\Skripsi\Services\Template;

use App\Models\User;

/**
 * Kontrak pembuat berkas template pengajuan (DOCX).
 *
 * Implementasi kelak mengisi otomatis nama, NIM, judul, dan dosen dari data
 * draft mahasiswa. Sumber dosen & placeholder template belum diputuskan.
 */
interface PembuatTemplatePengajuan
{
    /**
     * @param  list<array{judul?: string, deskripsi?: string, topik?: string, kategori_id?: int|string|null}>  $juduls  isian draft (boleh belum lengkap)
     * @return string path relatif pada disk `local`
     */
    public function buat(User $user, array $juduls): string;
}
