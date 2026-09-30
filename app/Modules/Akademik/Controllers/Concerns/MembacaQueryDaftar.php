<?php

namespace App\Modules\Akademik\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Pembacaan query string daftar admin (urutan dan ukuran halaman). Nilai tak
 * sah jatuh ke default, bukan galat validasi.
 */
trait MembacaQueryDaftar
{
    /**
     * @return list<int>
     */
    protected function perPageOptions(): array
    {
        return [10, 25, 50];
    }

    protected function perPage(Request $request): int
    {
        $options = $this->perPageOptions();
        $requested = (int) $request->input('per_page');

        return in_array($requested, $options, true) ? $requested : $options[0];
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function sortColumn(Request $request, array $allowed, string $default): string
    {
        $requested = $request->input('sort');

        return is_string($requested) && in_array($requested, $allowed, true) ? $requested : $default;
    }

    /**
     * @return 'asc'|'desc'
     */
    protected function sortDirection(Request $request): string
    {
        return $request->input('direction') === 'desc' ? 'desc' : 'asc';
    }
}
