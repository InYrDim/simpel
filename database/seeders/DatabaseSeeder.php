<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
        // Akademik, Skripsi) saat development. updateOrCreate agar seeder
        // idempotent (aman dijalankan berulang).
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@simpel.com'],
            ['name' => 'Admin Simpel', 'password' => 'admin123', 'email_verified_at' => now()],
        );
        $admin->assignRole('admin');

        // Kemudahan development: akun admin lokal diberi SEMUA permission
        // secara langsung agar dapat mengetes seluruh alur peran dari satu
        // akun, tanpa mengubah mapping role ↔ permission di
        // RolePermissionSeeder. syncPermissions menjaga ini idempotent dan
        // mengikuti set permission terbaru saat seeder dijalankan ulang.
        $admin->syncPermissions(Permission::all());

        // Data contoh E2E (dosen + akun validator + profil mahasiswa) ada di
        // seeder milik modul Akademik — core tidak boleh menyentuh model
        // modul. Jalankan terpisah:
        // php artisan db:seed --class=App\\Modules\\Akademik\\Database\\Seeders\\SampleDataSeeder
    }
}
