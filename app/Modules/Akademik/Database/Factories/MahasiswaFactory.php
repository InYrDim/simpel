<?php

namespace App\Modules\Akademik\Database\Factories;

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Nama model eksplisit — tebakan namespace Laravel tidak cocok untuk
     * factory modul di luar `Database\Factories`.
     */
    protected $model = Mahasiswa::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama' => fake()->name(),
            'nim' => fake()->unique()->numerify('21#########'),
            'dosen_pa_id' => Dosen::factory(),
            'prodi' => fake()->randomElement(['Teknik Informatika', 'Sistem Informasi']),
            'angkatan' => fake()->numberBetween(2020, 2025),
        ];
    }
}
