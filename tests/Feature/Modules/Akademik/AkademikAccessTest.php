<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guests are redirected to the login page', function () {
    $this->get(route('akademik.dosen.index'))->assertRedirect(route('login'));
    $this->get(route('akademik.mahasiswa.index'))->assertRedirect(route('login'));
    $this->get(route('akademik.prodi.index'))->assertRedirect(route('login'));
});

test('admin can visit the akademik pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('akademik.dosen.index'))->assertOk();
    $this->actingAs($admin)->get(route('akademik.mahasiswa.index'))->assertOk();
    $this->actingAs($admin)->get(route('akademik.prodi.index'))->assertOk();
});

test('users without the admin role are forbidden', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get(route('akademik.dosen.index'))->assertForbidden();
    $this->actingAs($user)->get(route('akademik.mahasiswa.index'))->assertForbidden();
    $this->actingAs($user)->get(route('akademik.prodi.index'))->assertForbidden();
})->with(['mahasiswa', 'validator']);

test('admin can create, update, and delete a dosen', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->post(route('akademik.dosen.store'), [
            'nama' => 'Dr. Budi Santoso',
            'nip' => '197001012000121001',
            'bidang' => 'Rekayasa Perangkat Lunak',
        ])
        ->assertRedirect(route('akademik.dosen.index'));

    $dosen = Dosen::query()->where('nip', '197001012000121001')->firstOrFail();
    expect($dosen->nama)->toBe('Dr. Budi Santoso');
    expect($dosen->bidang)->toBe('Rekayasa Perangkat Lunak');

    $this->actingAs($admin)
        ->put(route('akademik.dosen.update', $dosen), [
            'nama' => 'Dr. Budi Santoso, M.Kom',
            'nip' => '197001012000121001',
            'bidang' => 'Sistem Cerdas',
        ])
        ->assertRedirect(route('akademik.dosen.index'));

    expect($dosen->refresh()->bidang)->toBe('Sistem Cerdas');

    $this->actingAs($admin)
        ->delete(route('akademik.dosen.destroy', $dosen))
        ->assertRedirect(route('akademik.dosen.index'));

    expect(Dosen::find($dosen->id))->toBeNull();
});

test('dosen nip must be unique', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Dosen::factory()->create(['nip' => '197001012000121001']);

    $this->actingAs($admin)
        ->post(route('akademik.dosen.store'), [
            'nama' => 'Duplikat',
            'nip' => '197001012000121001',
            'bidang' => 'Sistem Cerdas',
        ])
        ->assertSessionHasErrors('nip');
});

test('admin can create a mahasiswa profile linked to a user and dosen pa', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $account = User::factory()->create();
    $dosenPa = Dosen::factory()->create();
    $prodi = Prodi::factory()->create(['nama' => 'Teknik Informatika']);

    $this->actingAs($admin)
        ->post(route('akademik.mahasiswa.store'), [
            'user_id' => $account->id,
            'nama' => 'Andi Wijaya',
            'nim' => '2110512001',
            'dosen_pa_id' => $dosenPa->id,
            'prodi_id' => $prodi->id,
            'angkatan' => 2021,
        ])
        ->assertRedirect(route('akademik.mahasiswa.index'));

    $mahasiswa = Mahasiswa::query()->where('nim', '2110512001')->firstOrFail();
    expect($mahasiswa->user_id)->toBe($account->id);
    expect($mahasiswa->dosen_pa_id)->toBe($dosenPa->id);
    expect($mahasiswa->prodi_id)->toBe($prodi->id);
});

test('mahasiswa user and nim must be unique', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $existing = Mahasiswa::factory()->create();

    $this->actingAs($admin)
        ->post(route('akademik.mahasiswa.store'), [
            'user_id' => $existing->user_id,
            'nama' => 'Duplikat',
            'nim' => $existing->nim,
            'dosen_pa_id' => Dosen::factory()->create()->id,
        ])
        ->assertSessionHasErrors(['user_id', 'nim']);
});

test('non-admin cannot mutate dosen or mahasiswa data', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    $dosen = Dosen::factory()->create();

    $this->actingAs($user)
        ->post(route('akademik.dosen.store'), ['nama' => 'X', 'nip' => '1', 'bidang' => 'Y'])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('akademik.dosen.destroy', $dosen))
        ->assertForbidden();

    expect(Dosen::find($dosen->id))->not->toBeNull();
});
