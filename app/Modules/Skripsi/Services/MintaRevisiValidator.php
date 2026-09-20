<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDirevisi;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action minta revisi oleh validator saat putusan (alur revisi, sesi 3).
 *
 * Dari `diverifikasi_admin` → `direvisi` + catatan wajib — pengajuan
 * dikembalikan ke mahasiswa untuk diperbaiki, kemudian resubmit pada
 * pengajuan yang SAMA (bukan pengajuan baru, berbeda dari penolakan §8
 * keputusan #1).
 */
class MintaRevisiValidator
{
    /**
     * @throws ValidationException
     */
    public function handle(PengajuanJudul $pengajuan, ?string $catatan = null, ?User $aktor = null): PengajuanJudul
    {
        if ($pengajuan->status !== StatusPengajuan::DiverifikasiAdmin) {
            throw ValidationException::withMessages([
                'status' => "Pengajuan berstatus {$pengajuan->status->label()}, bukan menunggu putusan validator.",
            ]);
        }

        if (filled($catatan) === false) {
            throw ValidationException::withMessages([
                'catatan_validator' => 'Catatan wajib diisi saat meminta revisi.',
            ]);
        }

        return DB::transaction(function () use ($pengajuan, $catatan, $aktor): PengajuanJudul {
            $pengajuan->update([
                'status' => StatusPengajuan::Direvisi->value,
                'catatan_validator' => $catatan,
            ]);

            PengajuanDirevisi::dispatch($pengajuan->refresh(), StatusPengajuan::DiverifikasiAdmin, $aktor);

            return $pengajuan;
        });
    }
}
