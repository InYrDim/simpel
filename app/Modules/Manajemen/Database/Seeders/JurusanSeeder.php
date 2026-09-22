<?php

namespace App\Modules\Manajemen\Database\Seeders;

use App\Modules\Manajemen\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Data awal Profil Jurusan (lokal/dev).
 *
 * Aplikasi melayani SATU jurusan: seeder ini hanya mengisi data saat tabel
 * masih kosong — nama placeholder bisa langsung diganti lewat halaman
 * Profil Jurusan. Tidak pernah membuat record kedua.
 *
 * Jalankan terpisah:
 * php artisan db:seed --class=App\\Modules\\Manajemen\\Database\\Seeders\\JurusanSeeder
 */
class JurusanSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Jurusan::query()->exists()) {
            return;
        }

        Jurusan::create([
            'nama' => 'Teknik Informatika',
            'ketua_nama' => null,
            'ketua_nip' => null,
            'sekretaris_nama' => null,
            'sekretaris_nip' => null,
        ]);
    }
}
