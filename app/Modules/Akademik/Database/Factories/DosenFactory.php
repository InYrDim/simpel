<?php

namespace App\Modules\Akademik\Database\Factories;

use App\Modules\Akademik\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dosen>
 */
class DosenFactory extends Factory
{
    /**
     * Nama model eksplisit — tebakan namespace Laravel tidak cocok untuk
     * factory modul di luar `Database\Factories`.
     */
    protected $model = Dosen::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nip' => fake()->unique()->numerify('19##########'),
            'bidang' => fake()->randomElement([
                'Rekayasa Perangkat Lunak',
                'Sistem Cerdas',
                'Jaringan Komputer',
                'Sistem Informasi',
            ]),
            'user_id' => null,
        ];
    }
}
