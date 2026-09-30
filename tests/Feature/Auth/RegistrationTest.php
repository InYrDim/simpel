<?php

use App\Models\User;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use App\Modules\Contracts\AkademikContract;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
    $this->seed(RolePermissionSeeder::class);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrasiPayload(Prodi $prodi, array $overrides = []): array
{
    return [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'nim' => '2201001',
        'prodi_id' => (string) $prodi->id,
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$overrides,
    ];
}

test('registration screen can be rendered with prodi options', function () {
    $prodi = Prodi::factory()->create(['nama' => 'Informatika']);

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/register')
            ->where('prodiOptions', [['id' => $prodi->id, 'nama' => 'Informatika']]));
});

test('new users register as mahasiswa with a linked akademik profile', function () {
    $prodi = Prodi::factory()->create();

    $response = $this->post(route('register.store'), registrasiPayload($prodi));

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    expect($user->hasExactRoles(['mahasiswa']))->toBeTrue();

    $mahasiswa = app(AkademikContract::class)->mahasiswaByNim('2201001');
    expect($mahasiswa?->userId)->toBe($user->id);
    expect($mahasiswa?->nama)->toBe('Test User');
    expect($mahasiswa?->prodi)->toBe($prodi->nama);
    expect($mahasiswa?->dosenPaId)->toBeNull();
});

test('registration requires nim and prodi', function () {
    $prodi = Prodi::factory()->create();

    $this->post(route('register.store'), registrasiPayload($prodi, ['nim' => '', 'prodi_id' => '']))
        ->assertSessionHasErrors(['nim', 'prodi_id']);

    $this->assertGuest();
});

test('registration rejects an unknown prodi', function () {
    $prodi = Prodi::factory()->create();

    $this->post(route('register.store'), registrasiPayload($prodi, ['prodi_id' => (string) ($prodi->id + 1)]))
        ->assertSessionHasErrors(['prodi_id' => 'Prodi tidak dikenal.']);

    $this->assertGuest();
});

test('registration rejects a nim that is already registered', function () {
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create(['nim' => '2201001']);

    $this->post(route('register.store'), registrasiPayload($prodi))
        ->assertSessionHasErrors(['nim' => 'NIM sudah terdaftar.']);

    $this->assertGuest();
    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});

test('registration is rejected when the mahasiswa gate denies the nim', function () {
    $prodi = Prodi::factory()->create();
    Gate::define('akademik.mahasiswa-kita', fn (?User $user, string $nim, int $prodiId): bool => false);

    $this->post(route('register.store'), registrasiPayload($prodi))
        ->assertSessionHasErrors(['nim' => 'NIM tidak terdaftar sebagai mahasiswa.']);

    $this->assertGuest();
    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
    expect(app(AkademikContract::class)->mahasiswaByNim('2201001'))->toBeNull();
});

test('the mahasiswa gate receives the submitted nim and prodi', function () {
    $prodi = Prodi::factory()->create();
    $received = null;
    Gate::define('akademik.mahasiswa-kita', function (?User $user, string $nim, int $prodiId) use (&$received): bool {
        $received = [$user, $nim, $prodiId];

        return true;
    });

    $this->post(route('register.store'), registrasiPayload($prodi));

    expect($received)->toBe([null, '2201001', $prodi->id]);
});
