<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDirevisi;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action minta revisi oleh admin saat verifikasi (alur revisi, sesi 3).
 *
 * Dari `diajukan` → `direvisi` + catatan wajib — pengajuan dikembalikan ke
 * mahasiswa untuk diperbaiki, kemudian resubmit pada pengajuan yang SAMA
 * (bukan pengajuan baru, berbeda dari penolakan §8 keputusan #1).
 */
class MintaRevisiAdmin
{
    /**
     * @throws ValidationException
     */
    public function handle(PengajuanJudul $pengajuan, ?string $catatan = null, ?User $aktor = null): PengajuanJudul
    {
        if ($pengajuan->status !== StatusPengajuan::Diajukan) {
            throw ValidationException::withMessages([
                'status' => "Pengajuan berstatus {$pengajuan->status->label()}, bukan diajukan.",
            ]);
        }

        if (filled($catatan) === false) {
            throw ValidationException::withMessages([
                'catatan_admin' => 'Catatan wajib diisi saat meminta revisi.',
            ]);
        }

        return DB::transaction(function () use ($pengajuan, $catatan, $aktor): PengajuanJudul {
            $pengajuan->update([
                'status' => StatusPengajuan::Direvisi->value,
                'catatan_admin' => $catatan,
            ]);

            PengajuanDirevisi::dispatch($pengajuan->refresh(), StatusPengajuan::Diajukan, $aktor);

            return $pengajuan;
        });
    }
}
