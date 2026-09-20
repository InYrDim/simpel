<?php

namespace App\Modules\Skripsi\Services;

use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiputus;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action putusan validator (PRD §5.3, §6.5, §6.6).
 *
 * Status sebelum putusan adalah `diverifikasi_admin` — admin menugaskan
 * validator saat verifikasi. Validator:
 * - setujui → `disetujui`, wajib memilih SATU judul dari 3 judul (§1);
 * - tolak → `ditolak_validator` + catatan wajib (§6.6).
 *
 * Penugasan pembimbing/penguji (§3.5) diisi admin kemudian lewat
 * `AssignPenugasan` hanya pada judul yang disetujui.
 */
class PutusanValidator
{
    public function handle(PengajuanJudul $pengajuan, bool $disetujui, ?int $judulId = null, ?string $catatan = null): PengajuanJudul
    {
        if ($pengajuan->status !== StatusPengajuan::DiverifikasiAdmin) {
            throw ValidationException::withMessages([
                'status' => "Pengajuan berstatus {$pengajuan->status->label()}, bukan menunggu putusan validator.",
            ]);
        }

        $judulDisetujui = null;

        if ($disetujui) {
            $judulDisetujui = $judulId === null
                ? null
                : JudulPengajuan::query()->where('pengajuan_judul_id', $pengajuan->id)->find($judulId);

            if ($judulDisetujui === null) {
                throw ValidationException::withMessages([
                    'judul_id' => 'Judul yang disetujui wajib dipilih dari 3 judul pengajuan.',
                ]);
            }
        }

        if (! $disetujui && filled($catatan) === false) {
            throw ValidationException::withMessages([
                'catatan_validator' => 'Catatan wajib diisi saat menolak pengajuan.',
            ]);
        }

        return DB::transaction(function () use ($pengajuan, $disetujui, $judulDisetujui, $catatan): PengajuanJudul {
            $pengajuan->update([
                'status' => $disetujui
                    ? StatusPengajuan::Disetujui->value
                    : StatusPengajuan::DitolakValidator->value,
                'catatan_validator' => $catatan,
                'decided_at' => now(),
            ]);

            PengajuanDiputus::dispatch($pengajuan->refresh(), $judulDisetujui);

            return $pengajuan;
        });
    }
}
