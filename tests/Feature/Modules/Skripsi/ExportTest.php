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

function exportAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function exportMahasiswa(): Mahasiswa
{
    $user = User::factory()->create();
    $user->assignRole('mahasiswa');

    return Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'dosen_pa_id' => Dosen::factory(),
    ]);
}

function exportAjukan(Mahasiswa $mahasiswa): PengajuanJudul
{
    $user = $mahasiswa->user;

    test()->actingAs($user)->post(route('skripsi.pengajuan.store'), [
        'juduls' => [
            [
                'judul' => 'Judul Si A',
                'deskripsi' => 'Deskripsi Si A.',
                'topik' => 'Sistem Informasi',
            ],
            [
                'judul' => 'Judul Si B',
                'deskripsi' => 'Deskripsi Si B.',
                'topik' => 'Machine Learning',
            ],
            [
                'judul' => 'Judul Si C',
                'deskripsi' => 'Deskripsi Si C.',
                'topik' => 'Mobile Computing',
            ],
        ],
        'berkas' => UploadedFile::fake()->create('surat-a.pdf', 500, 'application/pdf'),
    ]);

    return PengajuanJudul::query()->where('user_id', $user->id)->firstOrFail();
}

function exportVerifikasi(PengajuanJudul $pengajuan, Dosen $dosen): void
{
    test()->actingAs(exportAdmin())
        ->post(route('skripsi.verifikasi.store', $pengajuan), [
            'disetujui' => true,
            'validator_id' => $dosen->id,
        ]);
}

test('hanya admin yang bisa membuka halaman Export', function () {
    $this->actingAs(exportAdmin())
        ->get(route('skripsi.export.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/export/index'));

    $this->actingAs(exportMahasiswa()->user)
        ->get(route('skripsi.export.index'))
        ->assertForbidden();
});

test('hanya admin yang bisa mengunduh file export', function () {
    $this->actingAs(exportMahasiswa()->user)
        ->get(route('skripsi.export.csv'))
        ->assertForbidden();
});

test('export menghadirkan semua pengajuan dengan header kolom', function () {
    $mahasiswaA = exportMahasiswa();
    exportAjukan($mahasiswaA);

    $mahasiswaB = exportMahasiswa();
    $pengajuanB = exportAjukan($mahasiswaB);
    exportVerifikasi($pengajuanB, Dosen::factory()->create());

    $response = $this->actingAs(exportAdmin())
        ->get(route('skripsi.export.csv'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))
        ->toContain('.csv');

    $csv = $response->streamedContent();

    $baris = array_map(fn (string $b): array => str_getcsv($b), preg_split('/\r\n|\r|\n/', rtrim($csv)) ?? []);

    expect($baris[0])->toBe([
        'NIM',
        'Nama Mahasiswa',
        'Judul',
        'Topik',
        'Status',
        'Validator',
        'Berkas',
        'Diajukan',
        'Diputuskan',
    ]);

    $mahasiswaA = $mahasiswaA->fresh();
    $mahasiswaB = $mahasiswaB->fresh();

    $dataBaris = array_map(
        fn (array $r): array => array_slice($r, 0, 5),
        array_slice($baris, 1),
    );

    expect($dataBaris)
        ->toContain([$mahasiswaA->nim, $mahasiswaA->nama, 'Judul Si A', 'Sistem Informasi', 'Diajukan'])
        ->toContain([$mahasiswaB->nim, $mahasiswaB->nama, 'Judul Si A', 'Sistem Informasi', 'Diverifikasi Admin']);

    $barisB = collect(array_slice($baris, 1))->first(fn (array $r): bool => $r[0] === $mahasiswaB->nim);

    expect($barisB[5])->not->toBe('-')
        ->not->toBe('');
});

test('export memfilter pengajuan berdasarkan status', function () {
    $mahasiswaA = exportMahasiswa();
    exportAjukan($mahasiswaA);

    $mahasiswaB = exportMahasiswa();
    $pengajuanB = exportAjukan($mahasiswaB);
    exportVerifikasi($pengajuanB, Dosen::factory()->create());

    $csv = $this->actingAs(exportAdmin())
        ->get(route('skripsi.export.csv', ['status' => 'diverifikasi_admin']))
        ->assertOk()
        ->streamedContent();

    expect($csv)
        ->toContain('Diverifikasi Admin')
        ->toContain($mahasiswaB->nim)
        ->not->toContain($mahasiswaA->nim);
});
