<?php

namespace App\Modules\Akademik\Database\Seeders;

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Data contoh untuk pengujian end-to-end alur pengajuan judul (lokal/dev).
 *
 * Membuat dosen dengan akun login (role validator), satu dosen referensi
 * tanpa akun, dan profil mahasiswa yang terhubung akun user. Idempotent —
 * aman dijalankan berulang (updateOrCreate by email/nip/user_id).
 *
 * Akun yang tersedia setelah seeder ini:
 *
 * | Email                   | Password     | Role      |
 * |-------------------------|--------------|-----------|
 * | admin@simpel.com        | admin123     | admin     |
 * | validator1@simpel.com   | validator123 | validator |
 * | validator2@simpel.com   | validator123 | validator |
 * | validator3@simpel.com   | validator123 | validator |
 * | mahasiswa@simpel.com    | mahasiswa123 | mahasiswa |
 */
class SampleDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Pastikan role dasar ada — seeder ini juga bisa dijalankan sendiri.
        foreach (['mahasiswa', 'validator', 'admin'] as $role) {
            Role::findOrCreate($role);
        }

        // --- Dosen dengan akun login (role validator) --------------------
        $dosenData = [
            [
                'nama' => 'Dr. Andi Saputra, M.Kom',
                'nip' => '198001012005011001',
                'bidang' => 'Rekayasa Perangkat Lunak',
                'email' => 'validator1@simpel.com',
            ],
            [
                'nama' => 'Prof. Siti Rahayu, M.T.',
                'nip' => '197505102003122002',
                'bidang' => 'Sistem Cerdas',
                'email' => 'validator2@simpel.com',
            ],
            [
                'nama' => 'Dr. Bambang Wibowo, M.Kom',
                'nip' => '198203202009041003',
                'bidang' => 'Jaringan Komputer',
                'email' => 'validator3@simpel.com',
            ],
        ];

        foreach ($dosenData as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['nama'], 'password' => 'validator123', 'email_verified_at' => now()],
            );
            $user->assignRole('validator');

            Dosen::query()->updateOrCreate(
                ['nip' => $data['nip']],
                ['nama' => $data['nama'], 'bidang' => $data['bidang'], 'user_id' => $user->id],
            );
        }

        // Satu dosen tanpa akun — realistis sebagai referensi penugasan.
        Dosen::query()->updateOrCreate(
            ['nip' => '196912122000031004'],
            ['nama' => 'Drs. Hendra Gunawan, M.Si.', 'bidang' => 'Sistem Informasi', 'user_id' => null],
        );

        // --- Mahasiswa ----------------------------------------------------
        $mahasiswaUser = User::query()->updateOrCreate(
            ['email' => 'mahasiswa@simpel.com'],
            ['name' => 'Dewi Lestari', 'password' => 'mahasiswa123', 'email_verified_at' => now()],
        );
        $mahasiswaUser->assignRole('mahasiswa');

        $dosenPa = Dosen::query()->where('nip', '198001012005011001')->firstOrFail();

        Mahasiswa::query()->updateOrCreate(
            ['user_id' => $mahasiswaUser->id],
            [
                'nama' => 'Dewi Lestari',
                'nim' => '2110512001',
                'dosen_pa_id' => $dosenPa->id,
                'prodi' => 'Teknik Informatika',
                'angkatan' => 2021,
            ],
        );
    }
}
