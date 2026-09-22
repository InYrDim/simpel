<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function peranAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin dapat membuat peran beserta permission-nya', function () {
    $this->actingAs(peranAdmin())
        ->post(route('manajemen.peran.store'), [
            'name' => 'koordinator',
            'permissions' => ['skripsi.pengajuan.verify'],
        ])
        ->assertRedirect(route('manajemen.peran.index'));

    $role = Role::query()->where('name', 'koordinator')->firstOrFail();
    expect($role->permissions->pluck('name'))->toContain('skripsi.pengajuan.verify');
});

test('peran dapat diperbarui dan sync permission menggantikan yang lama', function () {
    $role = Role::create(['name' => 'koordinator']);
    $role->givePermissionTo('skripsi.pengajuan.verify');

    $this->actingAs(peranAdmin())
        ->put(route('manajemen.peran.update', $role), [
            'name' => 'koor',
            'permissions' => ['skripsi.pengajuan.decide'],
        ])
        ->assertRedirect(route('manajemen.peran.index'));

    expect($role->refresh()->name)->toBe('koor');
    expect($role->refresh()->permissions->pluck('name'))->toHaveCount(1);
    expect($role->refresh()->permissions->pluck('name'))->toContain('skripsi.pengajuan.decide');
});

test('nama peran harus unik', function () {
    Role::create(['name' => 'koordinator']);

    $this->actingAs(peranAdmin())
        ->post(route('manajemen.peran.store'), [
            'name' => 'koordinator',
            'permissions' => [],
        ])
        ->assertSessionHasErrors('name');
});

test('permission yang tidak terdaftar ditolak', function () {
    $this->actingAs(peranAdmin())
        ->post(route('manajemen.peran.store'), [
            'name' => 'koordinator',
            'permissions' => ['tidak.ada'],
        ])
        ->assertSessionHasErrors('permissions.0');
});

test('peran admin tidak dapat dihapus', function () {
    $admin = Role::findByName('admin');

    $this->actingAs(peranAdmin())
        ->delete(route('manajemen.peran.destroy', $admin))
        ->assertRedirect(route('manajemen.peran.index'))
        ->assertSessionHas('error');

    expect(Role::findByName('admin'))->not->toBeNull();
});

test('peran yang memiliki pengguna tidak dapat dihapus', function () {
    $role = Role::create(['name' => 'koordinator']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs(peranAdmin())
        ->delete(route('manajemen.peran.destroy', $role))
        ->assertRedirect(route('manajemen.peran.index'))
        ->assertSessionHas('error');

    expect(Role::find($role->id))->not->toBeNull();
});

test('peran kosong dapat dihapus', function () {
    $role = Role::create(['name' => 'koordinator']);

    $this->actingAs(peranAdmin())
        ->delete(route('manajemen.peran.destroy', $role))
        ->assertRedirect(route('manajemen.peran.index'));

    expect(Role::find($role->id))->toBeNull();
});

test('index peran menghadirkan role, permission, dan jumlah pengguna', function () {
    $role = Role::create(['name' => 'koordinator']);
    $role->givePermissionTo('skripsi.pengajuan.verify');
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs(peranAdmin())
        ->get(route('manajemen.peran.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manajemen/peran/index')
            ->has('roles', 4)
            ->where('roles.1.name', 'koordinator')
            ->where('roles.1.permissions', ['skripsi.pengajuan.verify'])
            ->where('roles.1.jumlah_pengguna', 1));
});

test('permission options yang dihadirkan berasal dari tabel permission', function () {
    $this->actingAs(peranAdmin())
        ->get(route('manajemen.peran.index'))
        ->assertInertia(fn ($page) => $page
            ->has('permissionOptions', Permission::count())
            ->where('permissionOptions.0.name', 'skripsi.pengajuan.decide'));
});

test('non-admin tidak bisa memanipulasi peran', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');

    $this->actingAs($user)
        ->post(route('manajemen.peran.store'), [
            'name' => 'hacker',
            'permissions' => [],
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('manajemen.peran.index'))
        ->assertForbidden();
});
