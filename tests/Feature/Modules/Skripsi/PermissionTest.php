<?php

use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('aksi skripsi ditolak bila permission dicabut dari peran', function (string $role, string $permission, string $routeName) {
    Role::findByName($role)->revokePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);
    $pengajuan = PengajuanJudul::factory()->create();

    $parameters = $routeName === 'skripsi.pengajuan.store' ? [] : [$pengajuan];

    $this->actingAs($user)
        ->post(route($routeName, $parameters))
        ->assertForbidden();
})->with([
    'submit' => ['mahasiswa', 'skripsi.pengajuan.submit', 'skripsi.pengajuan.store'],
    'resubmit' => ['mahasiswa', 'skripsi.pengajuan.submit', 'skripsi.pengajuan.resubmit'],
    'putusan' => ['validator', 'skripsi.pengajuan.decide', 'skripsi.putusan.store'],
    'revisi validator' => ['validator', 'skripsi.pengajuan.revise', 'skripsi.putusan.revisi'],
]);
