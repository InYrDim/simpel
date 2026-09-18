<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guests are redirected to the login page', function () {
    $this->get(route('manajemen.index'))->assertRedirect(route('login'));
});

test('admin can visit the manajemen pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('manajemen.index'))->assertOk();
    $this->actingAs($admin)->get(route('manajemen.pengguna.index'))->assertOk();
});

test('users without the admin role are forbidden', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('manajemen.index'))->assertForbidden();
    $this->actingAs($user)
        ->get(route('manajemen.pengguna.index'))
        ->assertForbidden();
})->with(['mahasiswa', 'validator']);

test('users without the admin role cannot update or delete accounts', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    $victim = User::factory()->create();

    $this->actingAs($user)
        ->put(route('manajemen.pengguna.update', $victim), [
            'name' => 'Diubah',
            'email' => 'diubah@example.com',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('manajemen.pengguna.destroy', $victim))
        ->assertForbidden();

    expect($victim->refresh()->name)->not->toBe('Diubah');
    expect(User::find($victim->id))->not->toBeNull();
});

test('admin cannot delete their own account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->delete(route('manajemen.pengguna.destroy', $admin))
        ->assertRedirect(route('manajemen.pengguna.index'));

    expect(User::find($admin->id))->not->toBeNull();
});

test('admin can update and delete another account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $other = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('manajemen.pengguna.update', $other), [
            'name' => 'Nama Baru',
            'email' => 'baru@example.com',
        ])
        ->assertRedirect(route('manajemen.pengguna.index'));

    expect($other->refresh()->name)->toBe('Nama Baru');

    $this->actingAs($admin)
        ->delete(route('manajemen.pengguna.destroy', $other))
        ->assertRedirect(route('manajemen.pengguna.index'));

    expect(User::find($other->id))->toBeNull();
});
