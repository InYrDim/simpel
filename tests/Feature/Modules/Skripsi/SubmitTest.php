<?php

use App\Models\User;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Notifications\PengajuanDiajukan as PengajuanDiajukanNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

function mahasiswaWithProfile(): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    // Profil Akademik nyata dibuat langsung dari test (namespace Tests bebas
    // aturan antar-modul) — SubmitPengajuan me-resolusinya via AkademikContract.
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    return $user;
}

function validPayload(): array
{
    return [
        'juduls' => [
            ['judul' => 'Judul Satu', 'deskripsi' => 'Deskripsi satu.', 'topik' => 'Sistem Informasi'],
            ['judul' => 'Judul Dua', 'deskripsi' => 'Deskripsi dua.', 'topik' => 'Machine Learning'],
            ['judul' => 'Judul Tiga', 'deskripsi' => 'Deskripsi tiga.', 'topik' => 'Mobile Computing'],
        ],
        'berkas' => UploadedFile::fake()->create('surat-pengajuan.pdf', 500, 'application/pdf'),
    ];
}

test('mahasiswa with profile can submit a valid pengajuan', function () {
    $user = mahasiswaWithProfile();

    $response = $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), validPayload());

    $response->assertRedirect(route('skripsi.pengajuan.status'));

    $pengajuan = PengajuanJudul::query()->where('user_id', $user->id)->firstOrFail();
    expect($pengajuan->status)->toBe(StatusPengajuan::Diajukan);
    expect($pengajuan->juduls()->count())->toBe(3);
    expect($pengajuan->juduls()->pluck('urutan')->all())->toBe([1, 2, 3]);
    expect($pengajuan->mahasiswa_id)->toBe(Mahasiswa::where('user_id', $user->id)->value('id'));
    expect(Storage::disk('local')->exists($pengajuan->berkas_path))->toBeTrue();
});

test('submission is rejected when not exactly 3 titles', function () {
    $user = mahasiswaWithProfile();

    $payload = validPayload();
    array_pop($payload['juduls']);

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), $payload)
        ->assertSessionHasErrors('juduls');

    expect(PengajuanJudul::count())->toBe(0);
});

test('submission is rejected when file is not pdf or too large', function () {
    $user = mahasiswaWithProfile();

    $payload = validPayload();
    $payload['berkas'] = UploadedFile::fake()->create('surat.docx', 100);

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), $payload)
        ->assertSessionHasErrors('berkas');

    $payload = validPayload();
    $payload['berkas'] = UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf');

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), $payload)
        ->assertSessionHasErrors('berkas');

    expect(PengajuanJudul::count())->toBe(0);
});

test('mahasiswa without akademik profile cannot submit', function () {
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), validPayload())
        ->assertSessionHasErrors();

    expect(PengajuanJudul::count())->toBe(0);
});

test('mahasiswa cannot have two active submissions', function () {
    $user = mahasiswaWithProfile();

    $this->actingAs($user)->post(route('skripsi.pengajuan.store'), validPayload());
    expect(PengajuanJudul::count())->toBe(1);

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), validPayload())
        ->assertSessionHasErrors('status');

    expect(PengajuanJudul::count())->toBe(1);
});

test('new submission allowed after previous was rejected by admin', function () {
    $user = mahasiswaWithProfile();

    PengajuanJudul::factory()->ditolakAdmin()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('skripsi.pengajuan.store'), validPayload())
        ->assertRedirect(route('skripsi.pengajuan.status'));

    expect(PengajuanJudul::count())->toBe(2);
});

test('submitting notifies admins via database notification', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = mahasiswaWithProfile();

    $this->actingAs($user)->post(route('skripsi.pengajuan.store'), validPayload());

    Notification::assertSentTo($admin, PengajuanDiajukanNotification::class);
});

test('status page shows belum mengajukan when none exists', function () {
    $user = mahasiswaWithProfile();

    $response = $this->actingAs($user)->get(route('skripsi.pengajuan.status'));

    $response->assertOk()->assertInertia(fn ($page) => $page->component('skripsi/pengajuan/index')->where('pengajuan', null));
});
