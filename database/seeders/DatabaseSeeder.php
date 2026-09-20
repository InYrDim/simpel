<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // User::factory(10)->create();

        // Akun admin lokal — dipakai untuk membuka halaman admin (Manajemen,
        // Akademik, nanti Skripsi) saat development.
        User::factory()->create([
            'name' => 'Admin Simpel',
            'email' => 'admin@simpel.com',
            'password' => 'admin123',
        ])->assignRole('admin');
    }
}
