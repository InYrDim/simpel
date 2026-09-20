<?php

namespace App\Modules\Skripsi\Policies;

use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;

/**
 * Mahasiswa hanya boleh melihat & mengubah pengajuannya sendiri (§6.7).
 * Akses per-role ditangani middleware `role:` di routes.
 */
class PengajuanJudulPolicy
{
    public function view(User $user, PengajuanJudul $pengajuanJudul): bool
    {
        return $user->id === $pengajuanJudul->user_id;
    }

    public function update(User $user, PengajuanJudul $pengajuanJudul): bool
    {
        return $user->id === $pengajuanJudul->user_id;
    }
}
