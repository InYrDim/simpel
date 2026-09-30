<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function penggunaAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin dapat membuat akun terverifikasi beserta perannya', function () {
    $this->actingAs(penggunaAdmin())
        ->post(route('manajemen.pengguna.store'), [
            'name' => 'Budi Validator',
            'email' => 'budi@example.com',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
            'role' => 'validator',
        ])
        ->assertRedirect(route('manajemen.pengguna.index'));

    $user = User::query()->where('email', 'budi@example.com')->firstOrFail();
    expect($user->hasExactRoles(['validator']))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
    expect(Hash::check('rahasia-kuat-123', $user->password))->toBeTrue();
});

test('pembuatan akun mewajibkan nama, email, password, dan peran', function () {
    $this->actingAs(penggunaAdmin())
        ->post(route('manajemen.pengguna.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password', 'role']);

    expect(User::query()->count())->toBe(1);
});

test('peran yang tidak terdaftar ditolak', function () {
    $this->actingAs(penggunaAdmin())
        ->post(route('manajemen.pengguna.store'), [
            'name' => 'Siapa',
            'email' => 'siapa@example.com',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
            'role' => 'superuser',
        ])
        ->assertSessionHasErrors('role');

    expect(User::query()->where('email', 'siapa@example.com')->exists())->toBeFalse();
});

test('peran berupa array ditolak karena satu akun hanya satu peran', function () {
    $this->actingAs(penggunaAdmin())
        ->post(route('manajemen.pengguna.store'), [
            'name' => 'Ganda',
            'email' => 'ganda@example.com',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
            'role' => ['admin', 'validator'],
        ])
        ->assertSessionHasErrors('role');

    expect(User::query()->where('email', 'ganda@example.com')->exists())->toBeFalse();
});

test('non-admin tidak dapat membuat akun', function () {
    $user = User::factory()->create();
    $user->assignRole('validator');

    $this->actingAs($user)
        ->post(route('manajemen.pengguna.store'), [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.com',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
            'role' => 'admin',
        ])
        ->assertForbidden();

    expect(User::query()->where('email', 'penyusup@example.com')->exists())->toBeFalse();
});

test('mengganti peran menggantikan peran lama sehingga tetap satu peran', function () {
    $other = User::factory()->create();
    $other->assignRole('mahasiswa');

    $this->actingAs(penggunaAdmin())
        ->put(route('manajemen.pengguna.update', $other), [
            'name' => $other->name,
            'email' => $other->email,
            'role' => 'validator',
        ])
        ->assertRedirect(route('manajemen.pengguna.index'));

    expect($other->refresh()->hasExactRoles(['validator']))->toBeTrue();
});

test('update tanpa field roles tidak mengubah peran', function () {
    $other = User::factory()->create();
    $other->assignRole('mahasiswa');

    $this->actingAs(penggunaAdmin())
        ->put(route('manajemen.pengguna.update', $other), [
            'name' => 'Nama Baru',
            'email' => $other->email,
        ])
        ->assertRedirect(route('manajemen.pengguna.index'));

    expect($other->refresh()->hasExactRoles(['mahasiswa']))->toBeTrue();
});

test('admin tidak dapat mencabut peran admin dari akun sendiri', function () {
    $admin = penggunaAdmin();

    $this->actingAs($admin)
        ->put(route('manajemen.pengguna.update', $admin), [
            'name' => 'Diubah',
            'email' => $admin->email,
            'role' => 'validator',
        ])
        ->assertSessionHasErrors(['role' => 'Anda tidak dapat mencabut peran admin dari akun sendiri.']);

    expect($admin->refresh()->hasExactRoles(['admin']))->toBeTrue();
    expect($admin->name)->not->toBe('Diubah');
});

test('daftar pengguna menyertakan peran dan opsi peran', function () {
    $admin = penggunaAdmin();

    $this->actingAs($admin)
        ->get(route('manajemen.pengguna.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manajemen/pengguna/index')
            ->where('users.data.0.role', 'admin')
            ->where('roleOptions', ['admin', 'mahasiswa', 'validator']));
});
