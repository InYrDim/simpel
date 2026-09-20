<?php

namespace App\Modules\Skripsi\Database\Factories;

use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JudulPengajuan>
 */
class JudulPengajuanFactory extends Factory
{
    protected $model = JudulPengajuan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengajuan_judul_id' => PengajuanJudul::factory(),
            'judul' => fake()->sentence(6),
            'deskripsi' => fake()->paragraph(),
            'topik' => fake()->randomElement([
                'Sistem Informasi',
                'Machine Learning',
                'Mobile Computing',
                'Rekayasa Perangkat Lunak',
            ]),
            'urutan' => fake()->numberBetween(1, 3),
        ];
    }
}
