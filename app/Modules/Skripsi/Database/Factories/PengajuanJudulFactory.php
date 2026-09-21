<?php

namespace App\Modules\Skripsi\Database\Factories;

use App\Models\User;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengajuanJudul>
 */
class PengajuanJudulFactory extends Factory
{
    protected $model = PengajuanJudul::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // `mahasiswa_id` merujuk profil Akademik TANPA FK (boundary rule #2)
        // sehingga factory tidak perlu membuat profil nyata — test yang butuh
        // keterkaitan nyata membuat profil Akademik langsung dari namespace
        // Tests (tidak discan Pest Arch) lalu menyimpan lewat service.
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'mahasiswa_id' => fake()->numberBetween(1, 999999),
            'berkas_path' => 'pengajuan/'.fake()->uuid().'.pdf',
            'berkas_original_name' => 'surat-pengajuan.pdf',
            'status' => StatusPengajuan::Diajukan,
            'submitted_at' => now(),
        ];
    }

    /**
     * Pengajuan yang sudah diverifikasi admin + ditugaskan validator —
     * menunggu putusan validator (status = diverifikasi_admin, §4).
     */
    public function diverifikasi(int $validatorId): static
    {
        return $this->state(fn (): array => [
            'status' => StatusPengajuan::DiverifikasiAdmin,
            'validator_id' => $validatorId,
            'verified_at' => now(),
        ]);
    }

    /**
     * Pengajuan yang telah diputuskan validator: satu judul disetujui —
     * judul-judulnya boleh diberi penugasan pembimbing/penguji (PRD §3.5).
     */
    public function disetujui(?int $validatorId = null): static
    {
        return $this->state(fn (): array => [
            'status' => StatusPengajuan::Disetujui,
            'validator_id' => $validatorId,
            'verified_at' => now(),
            'decided_at' => now(),
        ]);
    }

    /**
     * Pengajuan yang ditolak admin.
     */
    public function ditolakAdmin(string $catatan = 'Berkas tidak lengkap.'): static
    {
        return $this->state(fn (): array => [
            'status' => StatusPengajuan::DitolakAdmin,
            'catatan_admin' => $catatan,
            'verified_at' => now(),
        ]);
    }
}
