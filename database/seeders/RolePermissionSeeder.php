<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Daftar role aplikasi.
     *
     * @var list<string>
     */
    private const ROLES = ['mahasiswa', 'validator', 'admin'];

    /**
     * Seed the application's roles and permissions.
     *
     * Modul baru mendaftarkan permission-nya ke role di sini, mis.:
     * Role::findOrCreate('validator')->givePermissionTo('skripsi.pengajuan.validate');
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role);
        }
    }
}
