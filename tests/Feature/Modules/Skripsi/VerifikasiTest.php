<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Notifications\PengajuanDiverifikasi as PengajuanDiverifikasiNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

function verifAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('admin sees pending submissions list', function () {
    PengajuanJudul::factory()->count(2)->create();

    $this->actingAs(verifAdminUser())
        ->get(route('skripsi.verifikasi.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/verifikasi/index')->has('pengajuans', 2)->has('dosenOptions'));
});

test('admin can approve with a validator assignment', function () {
    Notification::fake();

    $dosen = Dosen::factory()->create(['user_id' => User::factory()->create()->id]);
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ])
        ->assertRedirect(route('skripsi.verifikasi.index'));

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DiverifikasiAdmin);
    expect($pengajuan->validator_id)->toBe($dosen->id);
    expect($pengajuan->verified_at)->not->toBeNull();
});

test('approval without a known dosen validator is rejected', function () {
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => 999999,
        ])
        ->assertSessionHasErrors();

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::Diajukan);
});

test('approval without validator id fails validation', function () {
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
        ])
        ->assertSessionHasErrors('validator_id');

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::Diajukan);
});

test('admin can reject with required catatan', function () {
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => false,
            'catatan_admin' => 'Berkas tidak sesuai template.',
        ])
        ->assertRedirect(route('skripsi.verifikasi.index'));

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DitolakAdmin);
    expect($pengajuan->catatan_admin)->toBe('Berkas tidak sesuai template.');
});

test('rejection without catatan fails', function () {
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => false,
            'catatan_admin' => '',
        ])
        ->assertSessionHasErrors('catatan_admin');

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::Diajukan);
});

test('already-verified pengajuan cannot be verified again', function () {
    $dosen = Dosen::factory()->create();
    $pengajuan = PengajuanJudul::factory()->diverifikasi($dosen->id)->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ])
        ->assertSessionHasErrors('status');

    expect($pengajuan->refresh()->status)->toBe(StatusPengajuan::DiverifikasiAdmin);
});

test('verification notifies mahasiswa and assigned validator account', function () {
    $dosenUser = User::factory()->create();
    $dosen = Dosen::factory()->create(['user_id' => $dosenUser->id]);
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    Notification::assertSentTo($pengajuan->user, PengajuanDiverifikasiNotification::class);
    Notification::assertSentTo($dosenUser, PengajuanDiverifikasiNotification::class);
});

test('notification to validator is skipped when dosen has no linked account', function () {
    $dosen = Dosen::factory()->create(); // tanpa user_id
    $pengajuan = PengajuanJudul::factory()->create();

    $this->actingAs(verifAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    // Hanya 1 notifikasi terkirim — ke mahasiswa. Tidak ada akun validator
    // yang bisa diresolusi dari dosen tanpa tautan akun.
    Notification::assertSentTo($pengajuan->user, PengajuanDiverifikasiNotification::class);
    expect(Notification::sent($pengajuan->user, PengajuanDiverifikasiNotification::class))->toHaveCount(1);
});
