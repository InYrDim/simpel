<?php

use App\Models\User;
use App\Modules\Manajemen\Database\Seeders\JurusanSeeder;
use App\Modules\Manajemen\Models\Jurusan;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function manajemenAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin dapat melihat profil jurusan', function () {
    Jurusan::factory()->create([
        'nama' => 'Teknik Informatika',
        'ketua_nama' => 'Prof. Andi',
    ]);

    $this->actingAs(manajemenAdmin())
        ->get(route('manajemen.jurusan.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manajemen/jurusan/index')
            ->where('jurusan.nama', 'Teknik Informatika')
            ->where('jurusan.ketua_nama', 'Prof. Andi'));
});

test('profil jurusan belum ada saat tabel kosong', function () {
    $this->actingAs(manajemenAdmin())
        ->get(route('manajemen.jurusan.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manajemen/jurusan/index')
            ->where('jurusan', null));
});

test('admin dapat memperbarui profil jurusan', function () {
    Jurusan::factory()->create(['nama' => 'Lama']);

    $this->actingAs(manajemenAdmin())
        ->put(route('manajemen.jurusan.update'), [
            'nama' => 'Teknik Informatika',
            'ketua_nama' => 'Prof. Andi',
            'ketua_nip' => '197001012000031001',
            'sekretaris_nama' => null,
            'sekretaris_nip' => null,
        ])
        ->assertRedirect(route('manajemen.jurusan.edit'));

    expect(Jurusan::query()->count())->toBe(1);
    $jurusan = Jurusan::query()->sole();
    expect($jurusan->nama)->toBe('Teknik Informatika');
    expect($jurusan->ketua_nama)->toBe('Prof. Andi');
});

test('menyimpan profil membuat jurusan bila belum ada', function () {
    $this->actingAs(manajemenAdmin())
        ->put(route('manajemen.jurusan.update'), [
            'nama' => 'Sistem Informasi',
        ])
        ->assertRedirect(route('manajemen.jurusan.edit'));

    expect(Jurusan::query()->where('nama', 'Sistem Informasi')->exists())->toBeTrue();
});

test('nama jurusan wajib diisi', function () {
    Jurusan::factory()->create();

    $this->actingAs(manajemenAdmin())
        ->put(route('manajemen.jurusan.update'), ['nama' => ''])
        ->assertSessionHasErrors('nama');
});

test('non-admin tidak bisa melihat atau mengubah profil jurusan', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');

    $this->actingAs($user)
        ->get(route('manajemen.jurusan.edit'))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('manajemen.jurusan.update'), ['nama' => 'X'])
        ->assertForbidden();

    expect(Jurusan::query()->count())->toBe(0);
});

test('JurusanSeeder membuat tepat satu jurusan dan idempotent', function () {
    $this->seed(JurusanSeeder::class);
    $this->seed(JurusanSeeder::class);

    expect(Jurusan::query()->count())->toBe(1);
    expect(Jurusan::query()->sole()->nama)->toBe('Teknik Informatika');
});
