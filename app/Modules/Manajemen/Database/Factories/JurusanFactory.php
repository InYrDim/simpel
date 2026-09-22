<?php

namespace App\Modules\Manajemen\Database\Factories;

use App\Modules\Manajemen\Models\Jurusan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jurusan>
 */
class JurusanFactory extends Factory
{
    protected $model = Jurusan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->company(),
            'ketua_nama' => fake()->name(),
            'ketua_nip' => fake()->numerify('#########'),
            'sekretaris_nama' => fake()->name(),
            'sekretaris_nip' => fake()->numerify('#########'),
        ];
    }
}
