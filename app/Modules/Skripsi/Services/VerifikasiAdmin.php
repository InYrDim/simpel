<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiverifikasi;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action verifikasi admin (PRD §5.3, §6.5, §6.6).
 *
 * Dari `diajukan`:
 * - setujui → `diverifikasi_admin` + wajib memilih validator (ID dosen
 *   Akademik, keberadaannya dicek via AkademikContract);
 * - tolak → `ditolak_admin` + catatan wajib (§6.6).
 */
class VerifikasiAdmin
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * @param  ?User  $aktor  admin pelaksana — dicatat di jejak audit
     *
     * @throws ValidationException
     */
    public function handle(PengajuanJudul $pengajuan, bool $disetujui, ?int $dosenValidatorId = null, ?string $catatan = null, ?User $aktor = null): PengajuanJudul
    {
        if ($pengajuan->status !== StatusPengajuan::Diajukan) {
            throw ValidationException::withMessages([
                'status' => "Pengajuan berstatus {$pengajuan->status->label()}, bukan diajukan.",
            ]);
        }

        $validator = null;

        if ($disetujui) {
            $validator = $dosenValidatorId === null ? null : $this->akademik->dosenById($dosenValidatorId);

            if ($validator === null) {
                throw ValidationException::withMessages([
                    'validator_id' => 'Validator wajib dipilih dari daftar dosen.',
                ]);
            }
        }

        if (! $disetujui && filled($catatan) === false) {
            throw ValidationException::withMessages([
                'catatan_admin' => 'Catatan wajib diisi saat menolak pengajuan.',
            ]);
        }

        return DB::transaction(function () use ($pengajuan, $disetujui, $dosenValidatorId, $catatan, $aktor): PengajuanJudul {
            $pengajuan->update([
                'status' => $disetujui
                    ? StatusPengajuan::DiverifikasiAdmin->value
                    : StatusPengajuan::DitolakAdmin->value,
                'validator_id' => $disetujui ? $dosenValidatorId : $pengajuan->validator_id,
                'catatan_admin' => $catatan,
                'verified_at' => now(),
            ]);

            PengajuanDiverifikasi::dispatch($pengajuan->refresh(), $aktor);

            return $pengajuan;
        });
    }
}
