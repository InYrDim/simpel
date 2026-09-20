<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Models\PengajuanRiwayat;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    Notification::fake();
});

function riwayatMahasiswa(): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    return $user;
}

function riwayatAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function riwayatValidatorUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('validator');

    return $user;
}

function submitRiwayan(User $user): PengajuanJudul
{
    test()->actingAs($user)->post(route('skripsi.pengajuan.store'), [
        'juduls' => [
            ['judul' => 'Judul Satu', 'deskripsi' => 'Deskripsi satu.', 'topik' => 'Sistem Informasi'],
            ['judul' => 'Judul Dua', 'deskripsi' => 'Deskripsi dua.', 'topik' => 'Machine Learning'],
            ['judul' => 'Judul Tiga', 'deskripsi' => 'Deskripsi tiga.', 'topik' => 'Mobile Computing'],
        ],
        'berkas' => UploadedFile::fake()->create('surat-pengajuan.pdf', 500, 'application/pdf'),
    ]);

    return PengajuanJudul::query()->where('user_id', $user->id)->firstOrFail();
}

test('submit records an audit entry with mahasiswa as aktor', function () {
    $user = riwayatMahasiswa();
    $pengajuan = submitRiwayan($user);

    $riwayat = PengajuanRiwayat::query()->where('pengajuan_judul_id', $pengajuan->id)->get();
    expect($riwayat)->toHaveCount(1);
    expect($riwayat[0]->dari_status)->toBeNull();
    expect($riwayat[0]->ke_status)->toBe(StatusPengajuan::Diajukan->value);
    expect($riwayat[0]->aksi)->toBe('submit');
    expect($riwayat[0]->aktor_id)->toBe($user->id);
    expect($riwayat[0]->created_at)->not->toBeNull();
});

test('admin approval records verifikasi_setuju entry with admin as aktor', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $dosen = Dosen::factory()->create(['user_id' => riwayatValidatorUser()->id]);
    $admin = riwayatAdminUser();

    $this->actingAs($admin)
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(2);
    expect($riwayat[1]->aksi)->toBe('verifikasi_setuju');
    expect($riwayat[1]->dari_status)->toBe(StatusPengajuan::Diajukan->value);
    expect($riwayat[1]->ke_status)->toBe(StatusPengajuan::DiverifikasiAdmin->value);
    expect($riwayat[1]->aktor_id)->toBe($admin->id);
});

test('admin rejection records verifikasi_tolak entry with catatan', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $admin = riwayatAdminUser();

    $this->actingAs($admin)
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => false,
            'catatan_admin' => 'Berkas tidak sesuai template.',
        ]);

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(2);
    expect($riwayat[1]->aksi)->toBe('verifikasi_tolak');
    expect($riwayat[1]->ke_status)->toBe(StatusPengajuan::DitolakAdmin->value);
    expect($riwayat[1]->catatan)->toBe('Berkas tidak sesuai template.');
});

test('validator approval records putusan_setuju entry with validator as aktor', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $validator = riwayatValidatorUser();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);

    $this->actingAs(riwayatAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    $judul = $pengajuan->juduls()->first();

    $this->actingAs($validator)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $judul->id,
        ]);

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(3);
    expect($riwayat[2]->aksi)->toBe('putusan_setuju');
    expect($riwayat[2]->dari_status)->toBe(StatusPengajuan::DiverifikasiAdmin->value);
    expect($riwayat[2]->ke_status)->toBe(StatusPengajuan::Disetujui->value);
    expect($riwayat[2]->aktor_id)->toBe($validator->id);
});

test('validator rejection records putusan_tolak entry with catatan', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $validator = riwayatValidatorUser();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);

    $this->actingAs(riwayatAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    $this->actingAs($validator)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => false,
            'catatan_validator' => 'Topik kurang spesifik.',
        ]);

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(3);
    expect($riwayat[2]->aksi)->toBe('putusan_tolak');
    expect($riwayat[2]->ke_status)->toBe(StatusPengajuan::DitolakValidator->value);
    expect($riwayat[2]->catatan)->toBe('Topik kurang spesifik.');
});

test('riwayat entries are ordered chronologically', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $dosen = Dosen::factory()->create(['user_id' => riwayatValidatorUser()->id]);

    $this->actingAs(riwayatAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    $aksis = $pengajuan->riwayat()->orderBy('created_at')->orderBy('id')->pluck('aksi')->all();
    expect($aksis)->toBe(['submit', 'verifikasi_setuju']);
});

test('mahasiswa status page exposes riwayatStatus timeline prop', function () {
    $user = riwayatMahasiswa();
    $pengajuan = submitRiwayan($user);

    $this->actingAs($user)
        ->get(route('skripsi.pengajuan.status'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/pengajuan/index')->has('riwayatStatus', 1));
});

test('daftar-judul page exposes riwayat entries in judul rows', function () {
    $pengajuan = submitRiwayan(riwayatMahasiswa());
    $dosen = Dosen::factory()->create(['user_id' => riwayatValidatorUser()->id]);

    $this->actingAs(riwayatAdminUser())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);

    // Setujui satu judul agar judul muncul di daftar-judul.
    $this->actingAs(riwayatValidatorUser())
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $pengajuan->juduls()->first()->id,
        ]);

    $this->actingAs(riwayatAdminUser())
        ->get(route('skripsi.daftar-judul.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/daftar-judul/index')->has(
            'juduls.data.0.riwayat',
            3,
        ));
});
