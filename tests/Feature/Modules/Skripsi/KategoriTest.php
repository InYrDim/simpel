<?php

use App\Models\User;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\Kategori;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function kategoriUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('admin dapat menambah, mengubah, dan menghapus kategori', function () {
    $admin = kategoriUser('admin');

    $this->actingAs($admin)
        ->post(route('skripsi.kategori.store'), ['nama' => 'Studi Kasus', 'deskripsi' => null, 'aktif' => true])
        ->assertRedirect(route('skripsi.kategori.index'));

    $kategori = Kategori::query()->where('nama', 'Studi Kasus')->firstOrFail();

    $this->actingAs($admin)
        ->put(route('skripsi.kategori.update', $kategori), ['nama' => 'Penelitian', 'deskripsi' => 'Ket.', 'aktif' => false])
        ->assertRedirect(route('skripsi.kategori.index'));

    expect($kategori->refresh()->nama)->toBe('Penelitian')
        ->and($kategori->aktif)->toBeFalse();

    $this->actingAs($admin)
        ->delete(route('skripsi.kategori.destroy', $kategori))
        ->assertRedirect(route('skripsi.kategori.index'));

    expect(Kategori::find($kategori->id))->toBeNull();
});

test('nama kategori harus unik', function () {
    Kategori::factory()->create(['nama' => 'Penelitian']);

    $this->actingAs(kategoriUser('admin'))
        ->post(route('skripsi.kategori.store'), ['nama' => 'Penelitian', 'aktif' => true])
        ->assertSessionHasErrors('nama');
});

test('kategori yang dipakai judul tidak bisa dihapus', function () {
    $kategori = Kategori::factory()->create();
    JudulPengajuan::factory()->create(['kategori_id' => $kategori->id]);

    $this->actingAs(kategoriUser('admin'))
        ->delete(route('skripsi.kategori.destroy', $kategori))
        ->assertSessionHas('error');

    expect(Kategori::find($kategori->id))->not->toBeNull();
});

test('selain admin tidak dapat mengelola kategori', function (string $role) {
    $this->actingAs(kategoriUser($role))
        ->get(route('skripsi.kategori.index'))
        ->assertForbidden();

    $this->actingAs(kategoriUser($role))
        ->post(route('skripsi.kategori.store'), ['nama' => 'X', 'aktif' => true])
        ->assertForbidden();
})->with(['mahasiswa', 'validator']);
