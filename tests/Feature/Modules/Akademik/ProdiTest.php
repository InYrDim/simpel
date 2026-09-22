<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function prodiAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin can create, update, and delete a prodi', function () {
    $kaprodi = Dosen::factory()->create(['nama' => 'Dr. Budi Santoso']);

    $this->actingAs(prodiAdmin())
        ->post(route('akademik.prodi.store'), [
            'nama' => 'Teknik Informatika',
            'kaprodi_id' => $kaprodi->id,
        ])
        ->assertRedirect(route('akademik.prodi.index'));

    $prodi = Prodi::query()->where('nama', 'Teknik Informatika')->firstOrFail();
    expect($prodi->kaprodi_id)->toBe($kaprodi->id);

    $this->actingAs(prodiAdmin())
        ->put(route('akademik.prodi.update', $prodi), [
            'nama' => 'Informatika',
            'kaprodi_id' => $kaprodi->id,
        ])
        ->assertRedirect(route('akademik.prodi.index'));

    expect($prodi->refresh()->nama)->toBe('Informatika');

    $this->actingAs(prodiAdmin())
        ->delete(route('akademik.prodi.destroy', $prodi))
        ->assertRedirect(route('akademik.prodi.index'));

    expect(Prodi::find($prodi->id))->toBeNull();
});

test('prodi nama must be unique', function () {
    Prodi::factory()->create(['nama' => 'Teknik Informatika']);

    $this->actingAs(prodiAdmin())
        ->post(route('akademik.prodi.store'), [
            'nama' => 'Teknik Informatika',
        ])
        ->assertSessionHasErrors('nama');
});

test('prodi tidak bisa dihapus selama masih memiliki mahasiswa', function () {
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create(['prodi_id' => $prodi->id]);

    $this->actingAs(prodiAdmin())
        ->delete(route('akademik.prodi.destroy', $prodi))
        ->assertRedirect(route('akademik.prodi.index'))
        ->assertSessionHas('error');

    expect(Prodi::find($prodi->id))->not->toBeNull();
});

test('kaprodi wajib dosen yang terdaftar', function () {
    $this->actingAs(prodiAdmin())
        ->post(route('akademik.prodi.store'), [
            'nama' => 'Sistem Informasi',
            'kaprodi_id' => 999999,
        ])
        ->assertSessionHasErrors('kaprodi_id');
});

test('index prodi menghadirkan kaprodi dan jumlah mahasiswa', function () {
    $kaprodi = Dosen::factory()->create();
    $prodi = Prodi::factory()->create(['nama' => 'Teknik Informatika', 'kaprodi_id' => $kaprodi->id]);
    Mahasiswa::factory()->create(['prodi_id' => $prodi->id]);

    $this->actingAs(prodiAdmin())
        ->get(route('akademik.prodi.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('akademik/prodi/index')
            ->has('prodis.data', 1)
            ->where('prodis.data.0.nama', 'Teknik Informatika')
            ->where('prodis.data.0.kaprodi_nama', $kaprodi->nama)
            ->where('prodis.data.0.jumlah_mahasiswa', 1));
});

test('non-admin cannot mutate prodi data', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    $prodi = Prodi::factory()->create();

    $this->actingAs($user)
        ->post(route('akademik.prodi.store'), ['nama' => 'X'])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('akademik.prodi.destroy', $prodi))
        ->assertForbidden();

    expect(Prodi::find($prodi->id))->not->toBeNull();
});
