<?php

use App\Models\User;
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

test('admin can create, update, and delete a jurusan', function () {
    $this->actingAs(manajemenAdmin())
        ->post(route('manajemen.jurusan.store'), [
            'nama' => 'Informatika',
            'ketua_nama' => 'Prof. Andi',
            'ketua_nip' => '197001012000031001',
            'sekretaris_nama' => 'Dr. Budi',
            'sekretaris_nip' => '197501012000031002',
        ])
        ->assertRedirect(route('manajemen.jurusan.index'));

    $jurusan = Jurusan::query()->where('nama', 'Informatika')->firstOrFail();
    expect($jurusan->ketua_nama)->toBe('Prof. Andi');
    expect($jurusan->sekretaris_nip)->toBe('197501012000031002');

    $this->actingAs(manajemenAdmin())
        ->put(route('manajemen.jurusan.update', $jurusan), [
            'nama' => 'Teknik Informatika',
            'ketua_nama' => null,
            'ketua_nip' => null,
            'sekretaris_nama' => 'Dr. Budi',
            'sekretaris_nip' => '197501012000031002',
        ])
        ->assertRedirect(route('manajemen.jurusan.index'));

    expect($jurusan->refresh()->nama)->toBe('Teknik Informatika');
    expect($jurusan->ketua_nama)->toBeNull();

    $this->actingAs(manajemenAdmin())
        ->delete(route('manajemen.jurusan.destroy', $jurusan))
        ->assertRedirect(route('manajemen.jurusan.index'));

    expect(Jurusan::find($jurusan->id))->toBeNull();
});

test('jurusan nama must be unique', function () {
    Jurusan::factory()->create(['nama' => 'Informatika']);

    $this->actingAs(manajemenAdmin())
        ->post(route('manajemen.jurusan.store'), [
            'nama' => 'Informatika',
        ])
        ->assertSessionHasErrors('nama');
});

test('jurusan without ketua/sekretaris is allowed', function () {
    $this->actingAs(manajemenAdmin())
        ->post(route('manajemen.jurusan.store'), [
            'nama' => 'Sistem Informasi',
        ])
        ->assertRedirect(route('manajemen.jurusan.index'));

    expect(Jurusan::query()->where('nama', 'Sistem Informasi')->exists())->toBeTrue();
});

test('index jurusan dapat dicari berdasarkan nama', function () {
    Jurusan::factory()->create(['nama' => 'Matematika']);
    Jurusan::factory()->create(['nama' => 'Fisika']);
    Jurusan::factory()->create(['nama' => 'Biologi']);

    $this->actingAs(manajemenAdmin())
        ->get(route('manajemen.jurusan.index', ['search' => 'mat']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('manajemen/jurusan/index')
            ->has('jurusans.data', 1)
            ->where('jurusans.data.0.nama', 'Matematika'));
});

test('non-admin tidak bisa memanipulasi jurusan', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    $jurusan = Jurusan::factory()->create();

    $this->actingAs($user)
        ->post(route('manajemen.jurusan.store'), ['nama' => 'X'])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('manajemen.jurusan.destroy', $jurusan))
        ->assertForbidden();

    expect(Jurusan::find($jurusan->id))->not->toBeNull();
});
