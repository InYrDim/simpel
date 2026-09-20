<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    Notification::fake();
});

function monitoringMahasiswa(): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    return $user;
}

function monitoringAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function monitoringValidator(): User
{
    $user = User::factory()->create();
    $user->assignRole('validator');

    return $user;
}

function monitoringAjukan(User $user): PengajuanJudul
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

function monitoringVerifikasi(PengajuanJudul $pengajuan, Dosen $dosen): void
{
    test()->actingAs(monitoringAdmin())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);
}

function monitoringPutusanSetujui(User $validator, PengajuanJudul $pengajuan): void
{
    test()->actingAs($validator)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $pengajuan->juduls()->first()->id,
        ]);
}

function monitoringRevisiAdmin(PengajuanJudul $pengajuan): void
{
    test()->actingAs(monitoringAdmin())
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Perbaiki berkas.',
        ]);
}

function monitoringTolakAdmin(PengajuanJudul $pengajuan): void
{
    test()->actingAs(monitoringAdmin())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => false,
            'catatan_admin' => 'Tidak sesuai.',
        ]);
}

test('hanya admin yang bisa membuka halaman monitoring', function () {
    $this->get(route('skripsi.monitoring.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(monitoringMahasiswa())
        ->get(route('skripsi.monitoring.index'))
        ->assertForbidden();

    $this->actingAs(monitoringValidator())
        ->get(route('skripsi.monitoring.index'))
        ->assertForbidden();

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/monitoring/index'));
});

test('angka agregasi mencerminkan keadaan seluruh pengajuan', function () {
    $validator1 = monitoringValidator();
    $validator2 = monitoringValidator();
    $dosen1 = Dosen::factory()->create(['user_id' => $validator1->id]);
    $dosen2 = Dosen::factory()->create(['user_id' => $validator2->id]);

    // m1 = diajukan; m2 = diverifikasi_admin (dosen1); m3 = direvisi;
    // m4 = ditolak_admin; m5 = disetujui (via dosen2).
    monitoringAjukan(monitoringMahasiswa());

    $m2 = monitoringAjukan(monitoringMahasiswa());
    monitoringVerifikasi($m2, $dosen1);

    $m3 = monitoringAjukan(monitoringMahasiswa());
    monitoringRevisiAdmin($m3);

    $m4 = monitoringAjukan(monitoringMahasiswa());
    monitoringTolakAdmin($m4);

    $m5 = monitoringAjukan(monitoringMahasiswa());
    monitoringVerifikasi($m5, $dosen2);
    monitoringPutusanSetujui($validator2, $m5);

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('skripsi/monitoring/index')
            ->where('total', 5)
            ->where('bulan_ini', 5)
            ->where('per_status', [
                ['status' => 'diajukan', 'status_label' => 'Diajukan', 'jumlah' => 1],
                ['status' => 'direvisi', 'status_label' => 'Direvisi', 'jumlah' => 1],
                ['status' => 'diverifikasi_admin', 'status_label' => 'Diverifikasi Admin', 'jumlah' => 1],
                ['status' => 'ditolak_admin', 'status_label' => 'Ditolak Admin', 'jumlah' => 1],
                ['status' => 'diverifikasi_validator', 'status_label' => 'Diverifikasi Validator', 'jumlah' => 0],
                ['status' => 'disetujui', 'status_label' => 'Disetujui', 'jumlah' => 1],
                ['status' => 'ditolak_validator', 'status_label' => 'Ditolak Validator', 'jumlah' => 0],
            ])
            ->where('per_validator', [
                ['dosen_id' => $dosen1->id, 'dosen_nama' => $dosen1->nama, 'beban' => 1],
            ]));
});

test('beban validator diurutkan dari penugasan terbanyak', function () {
    $dosen1 = Dosen::factory()->create(['user_id' => monitoringValidator()->id]);
    $dosen2 = Dosen::factory()->create(['user_id' => monitoringValidator()->id]);

    $a = monitoringAjukan(monitoringMahasiswa());
    monitoringVerifikasi($a, $dosen1);

    $b = monitoringAjukan(monitoringMahasiswa());
    monitoringVerifikasi($b, $dosen1);

    $c = monitoringAjukan(monitoringMahasiswa());
    monitoringVerifikasi($c, $dosen2);

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('per_validator', [
                ['dosen_id' => $dosen1->id, 'dosen_nama' => $dosen1->nama, 'beban' => 2],
                ['dosen_id' => $dosen2->id, 'dosen_nama' => $dosen2->nama, 'beban' => 1],
            ]));
});
