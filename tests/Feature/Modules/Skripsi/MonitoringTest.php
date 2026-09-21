<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Models\JudulPengajuan;
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

function monitoringMahasiswa(?int $dosenPaId = null): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    Mahasiswa::factory()->create([
        'user_id' => $user->id,
        // Tanpa dosen PA eksplisit, factory membuat dosen acak — dosen itu
        // sah muncul di sebaran beban (PRD Beban Dosen §4: semua dosen
        // Akademik tampil). Test sebaran memakai dosen yang sudah dikenal.
        'dosen_pa_id' => $dosenPaId ?? Dosen::factory(),
    ]);

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

test('sebaran beban dosen mencakup semua dosen dengan rincian per peran', function () {
    // Nama eksplisit agar urutan (total desc, lalu nama asc) deterministik.
    $dosenProlifik = Dosen::factory()->create(['nama' => 'Prolifik']);
    $dosenSekali = Dosen::factory()->create(['nama' => 'Anek']);
    $dosenKosong = Dosen::factory()->create(['nama' => 'Zulfikar']);

    // Judul disetujui: b1 & u1 = prolifik, b2 = sekali.
    $disetujui1 = PengajuanJudul::factory()->disetujui()->create();
    JudulPengajuan::factory()->create([
        'pengajuan_judul_id' => $disetujui1->id,
        'dosen_pembimbing_1' => $dosenProlifik->id,
        'dosen_pembimbing_2' => $dosenSekali->id,
        'dosen_penguji_1' => $dosenProlifik->id,
    ]);

    // Judul disetujui kedua: b1 & u2 = prolifik → total prolifik = 4.
    $disetujui2 = PengajuanJudul::factory()->disetujui()->create();
    JudulPengajuan::factory()->create([
        'pengajuan_judul_id' => $disetujui2->id,
        'dosen_pembimbing_1' => $dosenProlifik->id,
        'dosen_penguji_2' => $dosenProlifik->id,
    ]);

    // Penugasan pada pengajuan BELUM disetujui tidak dihitung.
    $belumDisetujui = PengajuanJudul::factory()->create();
    JudulPengajuan::factory()->create([
        'pengajuan_judul_id' => $belumDisetujui->id,
        'dosen_pembimbing_1' => $dosenProlifik->id,
    ]);

    // Validator aktif: pengajuan sedang direview dosenValidator. Nama
    // eksplisit: total 1 seri dengan Anek — tiebreak nama harus deterministik
    // (Anek < Validator < Zulfikar).
    $validator1 = monitoringValidator();
    $dosenValidator = Dosen::factory()->create(['user_id' => $validator1->id, 'nama' => 'Validator']);
    monitoringVerifikasi(monitoringAjukan(monitoringMahasiswa(dosenPaId: $dosenKosong->id)), $dosenValidator);

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('beban_dosen', [
            ['dosen_id' => $dosenProlifik->id, 'dosen_nama' => $dosenProlifik->nama,
                'validator_aktif' => 0, 'pembimbing_1' => 2, 'pembimbing_2' => 0,
                'penguji_1' => 1, 'penguji_2' => 1, 'total' => 4],
            ['dosen_id' => $dosenSekali->id, 'dosen_nama' => $dosenSekali->nama,
                'validator_aktif' => 0, 'pembimbing_1' => 0, 'pembimbing_2' => 1,
                'penguji_1' => 0, 'penguji_2' => 0, 'total' => 1],
            ['dosen_id' => $dosenValidator->id, 'dosen_nama' => $dosenValidator->nama,
                'validator_aktif' => 1, 'pembimbing_1' => 0, 'pembimbing_2' => 0,
                'penguji_1' => 0, 'penguji_2' => 0, 'total' => 1],
            ['dosen_id' => $dosenKosong->id, 'dosen_nama' => $dosenKosong->nama,
                'validator_aktif' => 0, 'pembimbing_1' => 0, 'pembimbing_2' => 0,
                'penguji_1' => 0, 'penguji_2' => 0, 'total' => 0],
        ]));
});

test('dosen tanpa akun tetap tampil dan dosen tak terdaftar tampil sebagai "-"', function () {
    $dosenTanpaAkun = Dosen::factory()->create(['user_id' => null]);
    $disetujui = PengajuanJudul::factory()->disetujui()->create();
    JudulPengajuan::factory()->create([
        'pengajuan_judul_id' => $disetujui->id,
        'dosen_pembimbing_1' => $dosenTanpaAkun->id,
        'dosen_penguji_1' => 999999, // id dosen yang sudah tak terdaftar
    ]);

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('beban_dosen', function ($rows) use ($dosenTanpaAkun): bool {
            $perId = $rows->keyBy('dosen_id');
            expect($rows)->toHaveCount(2);
            expect($perId->get($dosenTanpaAkun->id)['dosen_nama'])->toBe($dosenTanpaAkun->nama);
            expect($perId->get($dosenTanpaAkun->id)['total'])->toBe(1);
            expect($perId->get(999999)['dosen_nama'])->toBe('-');
            expect($perId->get(999999)['penguji_1'])->toBe(1);

            return true;
        }));
});

test('sebaran beban dosen diurutkan dari total tertinggi lalu nama', function () {
    $dosenA = Dosen::factory()->create(['nama' => 'Andi']);
    $dosenB = Dosen::factory()->create(['nama' => 'Budi']);
    Dosen::factory()->create(['nama' => 'Cici']);

    // Budi 2 penugasan, Andi 1, Cici 0 — urutan Total menurun.
    $disetujui = PengajuanJudul::factory()->disetujui()->create();
    JudulPengajuan::factory()->create([
        'pengajuan_judul_id' => $disetujui->id,
        'dosen_pembimbing_1' => $dosenB->id,
        'dosen_penguji_1' => $dosenB->id,
        'dosen_pembimbing_2' => $dosenA->id,
    ]);

    $this->actingAs(monitoringAdmin())
        ->get(route('skripsi.monitoring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('beban_dosen', fn ($rows): bool => $rows->pluck('dosen_nama')->values()->all() === ['Budi', 'Andi', 'Cici']));
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
