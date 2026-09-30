<?php

namespace App\Modules\Skripsi\Database\Factories;

use App\Modules\Skripsi\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kategori>
 */
class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn (): array => ['aktif' => false]);
    }
}
