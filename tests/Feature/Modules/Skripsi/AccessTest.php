<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function loginWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('guests are redirected to login on skripsi pages', function () {
    $this->get(route('skripsi.pengajuan.status'))->assertRedirect(route('login'));
    $this->get(route('skripsi.verifikasi.index'))->assertRedirect(route('login'));
    $this->get(route('skripsi.putusan.index'))->assertRedirect(route('login'));
    $this->get(route('skripsi.daftar-judul.index'))->assertRedirect(route('login'));
});

test('mahasiswa pages are forbidden for non-mahasiswa roles', function (string $role) {
    $user = loginWithRole($role);

    $this->actingAs($user)->get(route('skripsi.pengajuan.status'))->assertForbidden();
    $this->post(route('skripsi.pengajuan.store'), [])->assertForbidden();
})->with(['admin', 'validator']);

test('verifikasi pages are forbidden for non-admin roles', function (string $role) {
    $user = loginWithRole($role);

    $this->actingAs($user)->get(route('skripsi.verifikasi.index'))->assertForbidden();
})->with(['mahasiswa', 'validator']);

test('putusan pages are forbidden for non-validator roles', function (string $role) {
    $user = loginWithRole($role);

    $this->actingAs($user)->get(route('skripsi.putusan.index'))->assertForbidden();
})->with(['mahasiswa', 'admin']);

test('daftar judul is open to admin and validator, closed to mahasiswa', function () {
    $admin = loginWithRole('admin');
    $validator = loginWithRole('validator');
    $mahasiswa = loginWithRole('mahasiswa');

    $this->actingAs($admin)->get(route('skripsi.daftar-judul.index'))->assertOk();
    $this->actingAs($validator)->get(route('skripsi.daftar-judul.index'))->assertOk();
    $this->actingAs($mahasiswa)->get(route('skripsi.daftar-judul.index'))->assertForbidden();
});

test('mahasiswa can view status page', function () {
    $mahasiswa = loginWithRole('mahasiswa');

    $this->actingAs($mahasiswa)->get(route('skripsi.pengajuan.status'))->assertOk();
});

test('template download requires mahasiswa role', function () {
    $this->actingAs(loginWithRole('admin'))->get(route('skripsi.pengajuan.template'))->assertForbidden();
    $this->actingAs(loginWithRole('mahasiswa'))->get(route('skripsi.pengajuan.template'))->assertOk();
});

test('validator sees empty page when account has no dosen link', function () {
    $validator = loginWithRole('validator');
    $dosenTanpaAkun = Dosen::factory()->create();

    $this->actingAs($validator)->get(route('skripsi.putusan.index'))->assertOk();
    expect(PengajuanJudul::count())->toBe(0);
});
