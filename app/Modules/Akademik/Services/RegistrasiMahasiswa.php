<?php

namespace App\Modules\Akademik\Services;

use App\Models\User;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator as ValidationValidator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Registrasi mandiri Fortify — khusus mahasiswa. Akun mendapat peran
 * `mahasiswa` dan langsung dibuatkan profil akademik (NIM + prodi) tanpa
 * dosen PA; admin mengisi PA belakangan. Akun dosen/admin dibuat admin di
 * Manajemen > Pengguna.
 *
 * Dipasang ke Fortify oleh `AkademikServiceProvider` karena core tidak
 * boleh bergantung pada modul.
 */
class RegistrasiMahasiswa implements CreatesNewUsers
{
    private const DEFAULT_ROLE = 'mahasiswa';

    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'nim' => ['required', 'string', 'max:20', Rule::unique('akademik_mahasiswas', 'nim')],
            'prodi_id' => ['required', 'integer', Rule::exists('akademik_prodis', 'id')],
        ], [
            'nim.unique' => 'NIM sudah terdaftar.',
            'prodi_id.exists' => 'Prodi tidak dikenal.',
        ])->after(function (ValidationValidator $validator) use ($input): void {
            if ($validator->errors()->hasAny(['nim', 'prodi_id'])) {
                return;
            }

            if (Gate::denies('akademik.mahasiswa-kita', [$input['nim'], (int) $input['prodi_id']])) {
                $validator->errors()->add('nim', 'NIM tidak terdaftar sebagai mahasiswa.');
            }
        })->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $user->assignRole(self::DEFAULT_ROLE);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nama' => $input['name'],
                'nim' => $input['nim'],
                'dosen_pa_id' => null,
                'prodi_id' => (int) $input['prodi_id'],
            ]);

            return $user;
        });
    }

    /**
     * Props halaman `auth/register`.
     *
     * @return array{passwordRules: string, prodiOptions: list<array{id: int, nama: string}>}
     */
    public static function registerViewProps(): array
    {
        $prodiOptions = [];

        foreach (Prodi::query()->orderBy('nama')->get(['id', 'nama']) as $prodi) {
            $prodiOptions[] = ['id' => $prodi->id, 'nama' => $prodi->nama];
        }

        return [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'prodiOptions' => $prodiOptions,
        ];
    }
}
