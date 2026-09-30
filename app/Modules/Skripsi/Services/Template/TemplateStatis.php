<?php

namespace App\Modules\Skripsi\Services\Template;

use App\Models\User;

/**
 * Implementasi awal: mengembalikan template kosong apa adanya. Diganti
 * implementasi pengisi otomatis kelak tanpa mengubah controller/frontend.
 */
class TemplateStatis implements PembuatTemplatePengajuan
{
    public function buat(User $user, array $juduls): string
    {
        return 'template/template-pengajuan.docx';
    }
}
