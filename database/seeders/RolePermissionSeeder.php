<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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
     * Permission per modul dan role pemiliknya.
     *
     * Modul baru mendaftarkan permission-nya ke role di sini — satu blok per
     * modul, seperti contoh blok Skripsi di bawah.
     *
     * @var array<string, array<string, list<string>>>
     */
    private const PERMISSIONS = [
        'skripsi' => [
            'skripsi.pengajuan.submit' => ['mahasiswa'],
            'skripsi.pengajuan.verify' => ['admin'],
            'skripsi.pengajuan.decide' => ['validator'],
        ],
    ];

    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role);
        }

        foreach (self::PERMISSIONS as $modulePermissions) {
            foreach ($modulePermissions as $permission => $roles) {
                $permission = Permission::findOrCreate($permission);

                foreach ($roles as $role) {
                    Role::findByName($role)->givePermissionTo($permission);
                }
            }
        }
    }
}
