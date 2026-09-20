<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Notifications\PengajuanDiajukanUlang as PengajuanDiajukanUlangNotification;
use App\Modules\Skripsi\Notifications\PengajuanDirevisi as PengajuanDirevisiNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    Notification::fake();
});

function revisiMahasiswa(): User
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    return $user;
}

function revisiAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function revisiValidatorUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('validator');

    return $user;
}

function ajukanRevisi(User $user): PengajuanJudul
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

function verifikasiKeValidator(PengajuanJudul $pengajuan, Dosen $dosen): void
{
    test()->actingAs(revisiAdmin())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);
}

function resubmitRevisi(User $mahasiswa, PengajuanJudul $pengajuan, ?string $namaBerkas = 'revisi.pdf'): void
{
    test()->actingAs($mahasiswa)->post(route('skripsi.pengajuan.resubmit', $pengajuan), [
        'juduls' => [
            ['judul' => 'Judul Revisi Satu', 'deskripsi' => 'Deskripsi revisi satu.', 'topik' => 'Sistem Informasi'],
            ['judul' => 'Judul Revisi Dua', 'deskripsi' => 'Deskripsi revisi dua.', 'topik' => 'Machine Learning'],
            ['judul' => 'Judul Revisi Tiga', 'deskripsi' => 'Deskripsi revisi tiga.', 'topik' => 'Mobile Computing'],
        ],
        'berkas' => UploadedFile::fake()->create($namaBerkas, 500, 'application/pdf'),
    ]);
}

test('admin minta revisi mengembalikan pengajuan ke direvisi dengan catatan dan notifikasi', function () {
    $mahasiswa = revisiMahasiswa();
    $admin = revisiAdmin();
    $pengajuan = ajukanRevisi($mahasiswa);

    $this->actingAs($admin)
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Berkas kurang lengkap, perbaiki dulu.',
        ]);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(StatusPengajuan::Direvisi);
    expect($pengajuan->catatan_admin)->toBe('Berkas kurang lengkap, perbaiki dulu.');

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(2);
    expect($riwayat[1]->aksi)->toBe('verifikasi_revisi');
    expect($riwayat[1]->dari_status)->toBe(StatusPengajuan::Diajukan->value);
    expect($riwayat[1]->ke_status)->toBe(StatusPengajuan::Direvisi->value);
    expect($riwayat[1]->aktor_id)->toBe($admin->id);
    expect($riwayat[1]->catatan)->toBe('Berkas kurang lengkap, perbaiki dulu.');

    Notification::assertSentTo($mahasiswa, PengajuanDirevisiNotification::class);
});

test('validator minta revisi mengembalikan pengajuan ke direvisi dengan catatan dan notifikasi', function () {
    $mahasiswa = revisiMahasiswa();
    $validator = revisiValidatorUser();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);
    $pengajuan = ajukanRevisi($mahasiswa);
    verifikasiKeValidator($pengajuan, $dosen);

    $this->actingAs($validator)
        ->post(route('skripsi.putusan.revisi', $pengajuan), [
            'catatan_validator' => 'Topik perlu dipersempit.',
        ]);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(StatusPengajuan::Direvisi);
    expect($pengajuan->catatan_validator)->toBe('Topik perlu dipersempit.');

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(3);
    expect($riwayat[2]->aksi)->toBe('putusan_revisi');
    expect($riwayat[2]->dari_status)->toBe(StatusPengajuan::DiverifikasiAdmin->value);
    expect($riwayat[2]->aktor_id)->toBe($validator->id);

    Notification::assertSentTo($mahasiswa, PengajuanDirevisiNotification::class);
});

test('minta revisi wajib mencantumkan catatan', function () {
    $mahasiswa = revisiMahasiswa();
    $pengajuan = ajukanRevisi($mahasiswa);

    $this->actingAs(revisiAdmin())
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [])
        ->assertSessionHasErrors('catatan_admin');

    $this->actingAs(revisiValidatorUser())
        ->post(route('skripsi.putusan.revisi', $pengajuan), [])
        ->assertSessionHasErrors('catatan_validator');

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(StatusPengajuan::Diajukan);
});

test('minta revisi ditolak saat status asal tidak sah', function () {
    $mahasiswa = revisiMahasiswa();
    $validator = revisiValidatorUser();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);
    $pengajuan = ajukanRevisi($mahasiswa);

    // Admin hanya boleh minta revisi dari `diajukan` — bukan setelah
    // pengajuan diteruskan ke validator.
    verifikasiKeValidator($pengajuan, $dosen);

    $this->actingAs(revisiAdmin())
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Coba revisi.',
        ])
        ->assertSessionHasErrors('status');

    // Validator hanya boleh minta revisi dari `diverifikasi_admin` — bukan
    // pengajuan yang belum diverifikasi (mahasiswa lain karena satu
    // mahasiswa hanya boleh punya satu pengajuan aktif).
    $baru = ajukanRevisi(revisiMahasiswa());

    $this->actingAs($validator)
        ->post(route('skripsi.putusan.revisi', $baru), [
            'catatan_validator' => 'Coba revisi.',
        ])
        ->assertSessionHasErrors('status');
});

test('resubmit memperbarui pengajuan yang sama dan kembali ke diajukan', function () {
    $mahasiswa = revisiMahasiswa();
    $admin = revisiAdmin();
    $pengajuan = ajukanRevisi($mahasiswa);
    $pengajuanId = $pengajuan->id;
    $berkasLama = $pengajuan->berkas_path;

    $this->actingAs($admin)
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Berkas kurang lengkap, perbaiki dulu.',
        ]);

    resubmitRevisi($mahasiswa, $pengajuan);

    $pengajuan->refresh();
    expect($pengajuan->id)->toBe($pengajuanId);
    expect($pengajuan->status)->toBe(StatusPengajuan::Diajukan);
    expect($pengajuan->catatan_admin)->toBeNull();
    expect($pengajuan->validator_id)->toBeNull();
    expect($pengajuan->verified_at)->toBeNull();
    expect($pengajuan->berkas_original_name)->toBe('revisi.pdf');

    $judulTexts = $pengajuan->juduls()->pluck('judul')->all();
    expect($judulTexts)->toHaveCount(3);
    expect($judulTexts)->toContain('Judul Revisi Satu');
    expect($judulTexts)->not->toContain('Judul Satu');

    expect(Storage::disk('local')->exists($berkasLama))->toBeFalse();
    expect(Storage::disk('local')->exists($pengajuan->berkas_path))->toBeTrue();

    $riwayat = $pengajuan->riwayat()->orderBy('id')->get();
    expect($riwayat)->toHaveCount(3);
    expect($riwayat[2]->aksi)->toBe('resubmit');
    expect($riwayat[2]->dari_status)->toBe(StatusPengajuan::Direvisi->value);
    expect($riwayat[2]->ke_status)->toBe(StatusPengajuan::Diajukan->value);
    expect($riwayat[2]->aktor_id)->toBe($mahasiswa->id);

    Notification::assertSentTo($admin, PengajuanDiajukanUlangNotification::class);
});

test('resubmit hanya untuk pemilik pengajuan berstatus direvisi', function () {
    $mahasiswa = revisiMahasiswa();
    $mahasiswaLain = User::factory()->create();
    $mahasiswaLain->assignRole('mahasiswa');
    $pengajuan = ajukanRevisi($mahasiswa);

    // Guard status: resubmit saat masih `diajukan` ditolak.
    $this->actingAs($mahasiswa)
        ->post(route('skripsi.pengajuan.resubmit', $pengajuan), resubmitPayload())
        ->assertSessionHasErrors('status');

    // Guard kepemilikan: mahasiswa lain tidak boleh resubmit pengajuan orang.
    $this->actingAs($mahasiswaLain)
        ->post(route('skripsi.pengajuan.resubmit', $pengajuan), resubmitPayload())
        ->assertSessionHasErrors('pengajuan');

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(StatusPengajuan::Diajukan);
    expect($pengajuan->juduls()->count())->toBe(3);
});

function resubmitPayload(): array
{
    return [
        'juduls' => [
            ['judul' => 'Judul Revisi Satu', 'deskripsi' => 'Deskripsi.', 'topik' => 'Sistem Informasi'],
            ['judul' => 'Judul Revisi Dua', 'deskripsi' => 'Deskripsi.', 'topik' => 'Machine Learning'],
            ['judul' => 'Judul Revisi Tiga', 'deskripsi' => 'Deskripsi.', 'topik' => 'Mobile Computing'],
        ],
        'berkas' => UploadedFile::fake()->create('revisi.pdf', 500, 'application/pdf'),
    ];
}

test('pengajuan berstatus direvisi tetap menghalangi pengajuan baru', function () {
    $mahasiswa = revisiMahasiswa();
    $pengajuan = ajukanRevisi($mahasiswa);

    $this->actingAs(revisiAdmin())
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Perbaiki dulu.',
        ]);

    $this->actingAs($mahasiswa)
        ->post(route('skripsi.pengajuan.store'), [
            'juduls' => [
                ['judul' => 'Judul Baru Satu', 'deskripsi' => 'Deskripsi.', 'topik' => 'Sistem Informasi'],
                ['judul' => 'Judul Baru Dua', 'deskripsi' => 'Deskripsi.', 'topik' => 'Machine Learning'],
                ['judul' => 'Judul Baru Tiga', 'deskripsi' => 'Deskripsi.', 'topik' => 'Mobile Computing'],
            ],
            'berkas' => UploadedFile::fake()->create('surat-baru.pdf', 500, 'application/pdf'),
        ])
        ->assertSessionHasErrors('status');

    expect(PengajuanJudul::query()->where('user_id', $mahasiswa->id)->count())->toBe(1);
});

test('alur revisi lengkap berakhir disetujui dengan kronologi tercatat', function () {
    $mahasiswa = revisiMahasiswa();
    $validator = revisiValidatorUser();
    $dosen = Dosen::factory()->create(['user_id' => $validator->id]);
    $pengajuan = ajukanRevisi($mahasiswa);

    // 1. Admin minta revisi.
    $this->actingAs(revisiAdmin())
        ->post(route('skripsi.verifikasi.revisi', $pengajuan), [
            'catatan_admin' => 'Perbaiki berkas.',
        ]);

    // 2. Mahasiswa resubmit pada pengajuan yang sama.
    resubmitRevisi($mahasiswa, $pengajuan);
    $pengajuan->refresh();

    // 3. Admin verifikasi ulang + pilih validator.
    verifikasiKeValidator($pengajuan, $dosen);

    // 4. Validator menyetujui satu judul.
    $this->actingAs($validator)
        ->post(route('skripsi.putusan.store', $pengajuan), [
            'disetujui' => true,
            'judul_id' => $pengajuan->juduls()->first()->id,
        ]);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(StatusPengajuan::Disetujui);

    $aksis = $pengajuan->riwayat()->orderBy('id')->pluck('aksi')->all();
    expect($aksis)->toBe(['submit', 'verifikasi_revisi', 'resubmit', 'verifikasi_setuju', 'putusan_setuju']);
});
