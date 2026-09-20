<?php

namespace App\Modules\Skripsi\Services;

use App\Models\User;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Events\PengajuanDiajukan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action submit pengajuan baru (PRD §6.1–§6.5).
 *
 * Aturan yang dijaga DI SERVER, bukan hanya UI:
 * - tepat 3 judul (validasi array di controller + di sini);
 * - berkas PDF maks 5 MB, disimpan ke disk privat dengan nama acak;
 * - satu pengajuan aktif per mahasiswa — dicek ulang di sini (§6.3, §6.4);
 * - transisi awal `diajukan` di-guard sebelum insert.
 */
class SubmitPengajuan
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * @param  array<int, array{judul: string, deskripsi: string, topik: string}>  $juduls
     * @param  array<string, mixed>  $attributes  user_id, mahasiswa_id, submitted_at
     *
     * @throws ValidationException
     */
    public function handle(User $user, array $juduls, UploadedFile $berkas, array $attributes = []): PengajuanJudul
    {
        if (count($juduls) !== 3) {
            throw ValidationException::withMessages([
                'juduls' => 'Pengajuan harus berisi tepat 3 judul.',
            ]);
        }

        $mahasiswa = $this->akademik->mahasiswaByUserId($user->id);

        if ($mahasiswa === null) {
            throw ValidationException::withMessages([
                'user_id' => 'Anda belum terdaftar sebagai mahasiswa. Hubungi admin akademik.',
            ]);
        }

        $punyaPengajuanAktif = PengajuanJudul::query()
            ->where('user_id', $user->id)
            ->whereIn('status', array_column(StatusPengajuan::aktif(), 'value'))
            ->exists();

        if ($punyaPengajuanAktif) {
            throw ValidationException::withMessages([
                'status' => 'Anda masih memiliki pengajuan yang sedang berjalan.',
            ]);
        }

        $path = $berkas->store('pengajuan', 'local');

        return DB::transaction(function () use ($user, $mahasiswa, $juduls, $berkas, $path, $attributes): PengajuanJudul {
            $pengajuan = PengajuanJudul::create([
                ...$attributes,
                'user_id' => $user->id,
                'mahasiswa_id' => $mahasiswa->id,
                'berkas_path' => $path,
                'berkas_original_name' => $berkas->getClientOriginalName(),
                'status' => StatusPengajuan::Diajukan->value,
                'submitted_at' => $attributes['submitted_at'] ?? now(),
            ]);

            foreach (array_values($juduls) as $index => $judul) {
                $pengajuan->juduls()->create([
                    ...$judul,
                    'urutan' => $index + 1,
                ]);
            }

            PengajuanDiajukan::dispatch($pengajuan);

            return $pengajuan;
        });
    }
}
