<?php

use App\Models\User;
use App\Modules\Akademik\Enums\StatusMahasiswa;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function mahasiswaListAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin can reach every page of a list longer than one page', function () {
    Mahasiswa::factory()->count(12)->create();

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('mahasiswas.data', 2)
            ->where('mahasiswas.total', 12)
            ->where('mahasiswas.current_page', 2)
            ->where('mahasiswas.last_page', 2));
});

test('per_page accepts only the offered sizes and falls back to the first', function () {
    Mahasiswa::factory()->count(30)->create();

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['per_page' => 25]))
        ->assertInertia(fn ($page) => $page
            ->has('mahasiswas.data', 25)
            ->where('filters.per_page', 25)
            ->where('perPageOptions', [10, 25, 50]));

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['per_page' => 9999]))
        ->assertInertia(fn ($page) => $page
            ->has('mahasiswas.data', 10)
            ->where('filters.per_page', 10));
});

test('list can be filtered by prodi, angkatan, dosen pa, and status', function () {
    $prodi = Prodi::factory()->create();
    $dosen = Dosen::factory()->create();

    $cocok = Mahasiswa::factory()->create([
        'prodi_id' => $prodi->id,
        'angkatan' => 2022,
        'dosen_pa_id' => $dosen->id,
        'status' => StatusMahasiswa::Cuti,
    ]);
    Mahasiswa::factory()->create(['angkatan' => 2022, 'status' => StatusMahasiswa::Cuti]);
    Mahasiswa::factory()->create(['prodi_id' => $prodi->id, 'angkatan' => 2023]);

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', [
            'prodi_id' => $prodi->id,
            'angkatan' => 2022,
            'dosen_pa_id' => $dosen->id,
            'status' => 'cuti',
        ]))
        ->assertInertia(fn ($page) => $page
            ->has('mahasiswas.data', 1)
            ->where('mahasiswas.data.0.id', $cocok->id)
            ->where('mahasiswas.data.0.status', 'cuti'));
});

test('list can be narrowed to mahasiswa without a dosen pa', function () {
    $tanpaPa = Mahasiswa::factory()->create(['dosen_pa_id' => null]);
    Mahasiswa::factory()->create();

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['dosen_pa_id' => 'kosong']))
        ->assertInertia(fn ($page) => $page
            ->has('mahasiswas.data', 1)
            ->where('mahasiswas.data.0.id', $tanpaPa->id)
            ->where('filters.dosen_pa_id', 'kosong'));
});

test('list sorts by an allowed column and ignores unknown ones', function () {
    Mahasiswa::factory()->create(['nama' => 'Adi', 'nim' => '2100000002']);
    Mahasiswa::factory()->create(['nama' => 'Budi', 'nim' => '2100000001']);

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['sort' => 'nim', 'direction' => 'asc']))
        ->assertInertia(fn ($page) => $page
            ->where('mahasiswas.data.0.nama', 'Budi')
            ->where('filters.sort', 'nim'));

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index', ['sort' => 'password', 'direction' => 'desc']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.sort', 'nama')
            ->where('mahasiswas.data.0.nama', 'Budi'));
});

test('angkatan options list each year once, newest first', function () {
    Mahasiswa::factory()->create(['angkatan' => 2021]);
    Mahasiswa::factory()->create(['angkatan' => 2021]);
    Mahasiswa::factory()->create(['angkatan' => 2024]);
    Mahasiswa::factory()->create(['angkatan' => null]);

    $this->actingAs(mahasiswaListAdmin())
        ->get(route('akademik.mahasiswa.index'))
        ->assertInertia(fn ($page) => $page->where('angkatanOptions', [2024, 2021]));
});

test('new mahasiswa is aktif by default and status can be chosen', function () {
    $userDefault = User::factory()->create();
    $userCuti = User::factory()->create();

    $this->actingAs(mahasiswaListAdmin())
        ->post(route('akademik.mahasiswa.store'), [
            'user_id' => $userDefault->id,
            'nama' => 'Siti',
            'nim' => '2100000010',
        ])
        ->assertRedirect(route('akademik.mahasiswa.index'));

    $this->actingAs(mahasiswaListAdmin())
        ->post(route('akademik.mahasiswa.store'), [
            'user_id' => $userCuti->id,
            'nama' => 'Rani',
            'nim' => '2100000011',
            'status' => 'cuti',
        ])
        ->assertRedirect(route('akademik.mahasiswa.index'));

    expect(Mahasiswa::where('nim', '2100000010')->firstOrFail()->status)->toBe(StatusMahasiswa::Aktif)
        ->and(Mahasiswa::where('nim', '2100000010')->firstOrFail()->dosen_pa_id)->toBeNull()
        ->and(Mahasiswa::where('nim', '2100000011')->firstOrFail()->status)->toBe(StatusMahasiswa::Cuti);
});

test('an unknown status is rejected', function () {
    $this->actingAs(mahasiswaListAdmin())
        ->post(route('akademik.mahasiswa.store'), [
            'user_id' => User::factory()->create()->id,
            'nama' => 'Siti',
            'nim' => '2100000012',
            'status' => 'terbang',
        ])
        ->assertSessionHasErrors('status');
});

test('admin can update a mahasiswa without resending the linked account', function () {
    $mahasiswa = Mahasiswa::factory()->create(['nama' => 'Lama']);
    $userId = $mahasiswa->user_id;

    $this->actingAs(mahasiswaListAdmin())
        ->put(route('akademik.mahasiswa.update', $mahasiswa), [
            'nama' => 'Baru',
            'nim' => $mahasiswa->nim,
            'dosen_pa_id' => null,
            'status' => 'lulus',
        ])
        ->assertRedirect(route('akademik.mahasiswa.index'))
        ->assertSessionHasNoErrors();

    $mahasiswa->refresh();

    expect($mahasiswa->nama)->toBe('Baru')
        ->and($mahasiswa->status)->toBe(StatusMahasiswa::Lulus)
        ->and($mahasiswa->dosen_pa_id)->toBeNull()
        ->and($mahasiswa->user_id)->toBe($userId);
});

test('updating cannot take a nim that belongs to another mahasiswa', function () {
    $lain = Mahasiswa::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create();

    $this->actingAs(mahasiswaListAdmin())
        ->put(route('akademik.mahasiswa.update', $mahasiswa), [
            'nama' => $mahasiswa->nama,
            'nim' => $lain->nim,
        ])
        ->assertSessionHasErrors('nim');
});

test('deleting a mahasiswa requires typing their nim', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    $this->actingAs(mahasiswaListAdmin())
        ->delete(route('akademik.mahasiswa.destroy', $mahasiswa))
        ->assertSessionHasErrors('konfirmasi_nim');

    $this->actingAs(mahasiswaListAdmin())
        ->delete(route('akademik.mahasiswa.destroy', $mahasiswa), ['konfirmasi_nim' => 'salah'])
        ->assertSessionHasErrors('konfirmasi_nim');

    expect(Mahasiswa::find($mahasiswa->id))->not->toBeNull();

    $this->actingAs(mahasiswaListAdmin())
        ->delete(route('akademik.mahasiswa.destroy', $mahasiswa), ['konfirmasi_nim' => $mahasiswa->nim])
        ->assertRedirect(route('akademik.mahasiswa.index'))
        ->assertSessionHasNoErrors();

    expect(Mahasiswa::find($mahasiswa->id))->toBeNull();
});
