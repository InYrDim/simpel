<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiajukanUlang;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Action resubmit mahasiswa pada pengajuan yang SAMA (alur revisi, sesi 3).
 *
 * Berbeda dari penolakan (§8 keputusan #1 = pengajuan baru): saat admin
 * atau validator meminta revisi (`direvisi`), mahasiswa memperbaiki judul
 * dan berkas pada pengajuan yang sama — judul lama diganti, berkas diganti,
 * dan status kembali ke `diajukan` untuk diverifikasi ulang admin.
 *
 * Aturan yang dijaga DI SERVER:
 * - hanya pemilik pengajuan yang boleh resubmit;
 * - hanya pengajuan berstatus `direvisi`;
 * - tepat 3 judul + berkas PDF maks 5 MB (validasi ulang di sini).
 */
class ResubmitPengajuan
{
    /**
     * @param  array<int, array{judul: string, deskripsi: string, topik: string}>  $juduls
     *
     * @throws ValidationException
     */
    public function handle(User $user, PengajuanJudul $pengajuan, array $juduls, UploadedFile $berkas): PengajuanJudul
    {
        if ($pengajuan->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'pengajuan' => 'Pengajuan ini bukan milik Anda.',
            ]);
        }

        if ($pengajuan->status !== StatusPengajuan::Direvisi) {
            throw ValidationException::withMessages([
                'status' => "Resubmit hanya untuk pengajuan berstatus direvisi, bukan {$pengajuan->status->label()}.",
            ]);
        }

        if (count($juduls) !== 3) {
            throw ValidationException::withMessages([
                'juduls' => 'Pengajuan harus berisi tepat 3 judul.',
            ]);
        }

        $berkasLama = $pengajuan->berkas_path;
        $path = $berkas->store('pengajuan', 'local');

        DB::transaction(function () use ($pengajuan, $juduls, $berkas, $path): void {
            $pengajuan->juduls()->delete();

            foreach (array_values($juduls) as $index => $judul) {
                $pengajuan->juduls()->create([
                    ...$judul,
                    'urutan' => $index + 1,
                ]);
            }

            // Siklus revisi dimulai ulang: catatan & penugasan siklus lama
            // diturunkan (jejaknya tetap tersimpan di tabel riwayat).
            $pengajuan->update([
                'berkas_path' => $path,
                'berkas_original_name' => $berkas->getClientOriginalName(),
                'status' => StatusPengajuan::Diajukan->value,
                'validator_id' => null,
                'catatan_admin' => null,
                'catatan_validator' => null,
                'submitted_at' => now(),
                'verified_at' => null,
                'decided_at' => null,
            ]);

            PengajuanDiajukanUlang::dispatch($pengajuan->refresh());
        });

        // Berkas siklus revisi sebelumnya sudah tidak dirujuk setelah commit.
        Storage::disk('local')->delete($berkasLama);

        return $pengajuan;
    }
}
