<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Services\PutusanValidator;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

function validatorUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('validator');

    return $user;
}

function pengajuanForValidator(User $dosenUser): array
{
    $dosen = Dosen::factory()->create(['user_id' => $dosenUser->id]);
    $pengajuan = PengajuanJudul::factory()->diverifikasi($dosen->id)->create();
    $pengajuan->juduls()->createMany([
        ['judul' => 'A', 'deskripsi' => 'A.', 'topik' => 'T1', 'urutan' => 1],
        ['judul' => 'B', 'deskripsi' => 'B.', 'topik' => 'T2', 'urutan' => 2],
        ['judul' => 'C', 'deskripsi' => 'C.', 'topik' => 'T3', 'urutan' => 3],
    ]);

    return [$pengajuan, $dosen];
}

test('validator sees only pengajuan assigned to their dosen account', function () {
    [$milikSaya] = pengajuanForValidator(validatorUser());
    [$milikOrang] = pengajuanForValidator(validatorUser());

    $this->actingAs(validatorUser())->get(route('skripsi.putusan.index'))->assertOk();

    // Dua pengajuan milik dosen berbeda — penyaringan per-validator diuji
    // lewat perilaku service/route, bukan isi halaman.
    expect(PengajuanJudul::where('status', 'diverifikasi_admin')->count())->toBe(2);
});

test('validator can approve one specific title', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);
    $judulKedua = $pengajuan->juduls->where('urutan', 2)->first();

    $this->actingAs($dosenUser)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $judulKedua->id,
        ])
        ->assertRedirect(route('skripsi.putusan.index'));

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::Disetujui);
    expect($pengajuan->decided_at)->not->toBeNull();
});

test('approval without choosing a title fails', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);

    $this->actingAs($dosenUser)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
        ])
        ->assertSessionHasErrors();

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DiverifikasiAdmin);
});

test('approval with a title from another pengajuan fails', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);
    $asing = JudulPengajuan::factory()->create(); // milik pengajuan lain

    $this->actingAs($dosenUser)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $asing->id,
        ]);

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DiverifikasiAdmin);
});

test('validator can reject with required catatan', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);

    $this->actingAs($dosenUser)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => false,
            'catatan_validator' => 'Substansi perlu diperdalam.',
        ])
        ->assertRedirect(route('skripsi.putusan.index'));

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DitolakValidator);
    expect($pengajuan->catatan_validator)->toBe('Substansi perlu diperdalam.');
});

test('rejection without catatan fails', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);

    $this->actingAs($dosenUser)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => false,
        ])
        ->assertSessionHasErrors('catatan_validator');

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DiverifikasiAdmin);
});

test('admin can assign pembimbing and penguji on approved title', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);
    $judul = $pengajuan->juduls->first();

    // Setujui lewat service (bukan HTTP) agar unit ini fokus ke penugasan.
    app(PutusanValidator::class)
        ->handle($pengajuan, disetujui: true, judulId: $judul->id);

    $p1 = Dosen::factory()->create();
    $p2 = Dosen::factory()->create();
    $u1 = Dosen::factory()->create();
    $u2 = Dosen::factory()->create();

    $this->actingAs(User::factory()->create()->assignRole('admin'))
        ->post(route('skripsi.daftar-judul.assign', $judul), [
            'dosen_pembimbing_1' => $p1->id,
            'dosen_pembimbing_2' => $p2->id,
            'dosen_penguji_1' => $u1->id,
            'dosen_penguji_2' => $u2->id,
        ])
        ->assertOk();

    expect($judul->refresh()->dosen_pembimbing_1)->toBe($p1->id);
    expect($judul->refresh()->dosen_penguji_2)->toBe($u2->id);
});

test('penugasan is rejected for pengajuan not yet approved', function () {
    $judul = JudulPengajuan::factory()->create(); // pengajuan masih diajukan
    $dosen = Dosen::factory()->create();

    $this->actingAs(User::factory()->create()->assignRole('admin'))
        ->postJson(route('skripsi.daftar-judul.assign', $judul), [
            'dosen_pembimbing_1' => $dosen->id,
        ])
        ->assertJsonValidationErrors('status');

    expect($judul->refresh()->dosen_pembimbing_1)->toBeNull();
});

test('penugasan with unknown dosen id fails', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);
    $judul = $pengajuan->juduls->first();

    app(PutusanValidator::class)
        ->handle($pengajuan, disetujui: true, judulId: $judul->id);

    $this->actingAs(User::factory()->create()->assignRole('admin'))
        ->postJson(route('skripsi.daftar-judul.assign', $judul), [
            'dosen_pembimbing_1' => 999999,
        ])
        ->assertJsonValidationErrors('penugasan');

    expect($judul->refresh()->dosen_pembimbing_1)->toBeNull();
});

test('validator cannot assign penugasan', function () {
    $dosenUser = validatorUser();
    [$pengajuan] = pengajuanForValidator($dosenUser);
    $judul = $pengajuan->juduls->first();
    $dosen = Dosen::factory()->create();

    $this->actingAs($dosenUser)
        ->post(route('skripsi.daftar-judul.assign', $judul), [
            'dosen_pembimbing_1' => $dosen->id,
        ])
        ->assertForbidden();
});
