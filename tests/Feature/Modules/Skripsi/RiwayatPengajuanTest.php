<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    Notification::fake();
});

function rwyPgMahasiswa(): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    return $user;
}

function rwyPgAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function rwyPgValidator(): User
{
    $user = User::factory()->create();
    $user->assignRole('validator');

    return $user;
}

function rwyPgAjukan(User $user): PengajuanJudul
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

function rwyPgRevisiVerifikasi(User $admin, PengajuanJudul $pengajuan): void
{
    test()->actingAs($admin)
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Perbaiki berkas.',
        ]);
}

function rwyPgResubmit(User $mahasiswa, PengajuanJudul $pengajuan): void
{
    test()->actingAs($mahasiswa)->post(route('skripsi.pengajuan.resubmit', $pengajuan), [
        'juduls' => [
            ['judul' => 'Judul Revisi Satu', 'deskripsi' => 'Deskripsi revisi satu.', 'topik' => 'Sistem Informasi'],
            ['judul' => 'Judul Revisi Dua', 'deskripsi' => 'Deskripsi revisi dua.', 'topik' => 'Machine Learning'],
            ['judul' => 'Judul Revisi Tiga', 'deskripsi' => 'Deskripsi revisi tiga.', 'topik' => 'Mobile Computing'],
        ],
        'berkas' => UploadedFile::fake()->create('revisi.pdf', 500, 'application/pdf'),
    ]);
}

function rwyPgVerifikasi(User $admin, PengajuanJudul $pengajuan, Dosen $dosen): void
{
    test()->actingAs($admin)
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);
}

function rwyPgPutusanSetujui(User $validator, PengajuanJudul $pengajuan): void
{
    test()->actingAs($validator)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $pengajuan->juduls()->first()->id,
        ]);
}

test('hanya admin dan mahasiswa yang bisa membuka halaman riwayat', function () {
    $this->get(route('skripsi.riwayat.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(rwyPgValidator())
        ->get(route('skripsi.riwayat.index'))
        ->assertForbidden();

    $this->actingAs(rwyPgMahasiswa())
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/riwayat/index'));

    $this->actingAs(rwyPgAdmin())
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/riwayat/index'));
});

test('mahasiswa hanya melihat pengajuan miliknya', function () {
    $mahasiswaA = rwyPgMahasiswa();
    $pengajuanA = rwyPgAjukan($mahasiswaA);
    rwyPgAjukan(rwyPgMahasiswa());

    $this->actingAs($mahasiswaA)
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('skripsi/riwayat/index')
            ->has('pengajuans.data', 1)
            ->where('pengajuans.data.0.id', $pengajuanA->id));
});

test('admin melihat semua pengajuan beserta kronologi lengkapnya', function () {
    $admin = rwyPgAdmin();
    $validator = rwyPgValidator();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);
    $mahasiswa = rwyPgMahasiswa();

    $pengajuan = rwyPgAjukan($mahasiswa);
    rwyPgRevisiVerifikasi($admin, $pengajuan);
    rwyPgResubmit($mahasiswa, $pengajuan);
    rwyPgVerifikasi($admin, $pengajuan, $dosen);
    rwyPgPutusanSetujui($validator, $pengajuan);

    $this->actingAs($admin)
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('skripsi/riwayat/index')
            ->has('pengajuans.data', 1)
            ->where('pengajuans.data.0.status', 'disetujui')
            ->where('pengajuans.data.0.status_label', 'Disetujui')
            ->where('pengajuans.data.0.nama_mahasiswa', fn ($nama) => is_string($nama) && $nama !== '-')
            ->where('pengajuans.data.0.jumlah_judul', 3)
            ->where('pengajuans.data.0.judul_list', [
                'Judul Revisi Satu', 'Judul Revisi Dua', 'Judul Revisi Tiga',
            ])
            ->has('pengajuans.data.0.riwayat', 5)
            ->where('pengajuans.data.0.riwayat.0.aksi', 'submit')
            ->where('pengajuans.data.0.riwayat.1.aksi', 'verifikasi_revisi')
            ->where('pengajuans.data.0.riwayat.2.aksi', 'resubmit')
            ->where('pengajuans.data.0.riwayat.3.aksi', 'verifikasi_setuju')
            ->where('pengajuans.data.0.riwayat.4.aksi', 'putusan_setuju')
            ->where('pengajuans.data.0.riwayat.1.aktor_nama', $admin->name)
            ->where('pengajuans.data.0.riwayat.1.catatan', 'Perbaiki berkas.')
            ->where('pengajuans.data.0.riwayat.2.aktor_nama', $mahasiswa->name)
            ->where('pengajuans.data.0.riwayat.4.aktor_nama', $validator->name));
});

test('resolusi identitas mahasiswa memakai satu query batch per halaman, bukan N+1', function () {
    // 12 pengajuan dari 12 akun berbeda → halaman pertama memuat 10 baris.
    PengajuanJudul::factory()->count(12)->create();

    DB::enableQueryLog();

    $this->actingAs(rwyPgAdmin())
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('pengajuans.data', 10));

    $queryIdentitas = collect(DB::getQueryLog())
        ->filter(fn (array $log): bool => str_contains($log['query'], 'akademik_mahasiswas'))
        ->count();

    expect($queryIdentitas)->toBe(1);
});

test('pengajuan terbaru muncul pertama', function () {
    $lama = rwyPgAjukan(rwyPgMahasiswa());
    $baru = rwyPgAjukan(rwyPgMahasiswa());

    $this->actingAs(rwyPgAdmin())
        ->get(route('skripsi.riwayat.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('pengajuans.data.0.id', $baru->id)
            ->where('pengajuans.data.1.id', $lama->id));
});
