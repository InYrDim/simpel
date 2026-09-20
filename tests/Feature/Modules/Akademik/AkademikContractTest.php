<?php

use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Contracts\AkademikContract;

/**
 * Test kontrak dari sudut pandang konsumen: modul lain (mis. Skripsi kelak)
 * hanya mengenal `AkademikContract` dan DTO-nya, bukan model internal.
 */
test('contract resolves mahasiswa by user id and nim', function () {
    $mahasiswa = Mahasiswa::factory()->create();

    $contract = app(AkademikContract::class);

    $byUser = $contract->mahasiswaByUserId($mahasiswa->user_id);
    expect($byUser)->not->toBeNull();
    expect($byUser?->nim)->toBe($mahasiswa->nim);
    expect($byUser?->userId)->toBe($mahasiswa->user_id);

    $byNim = $contract->mahasiswaByNim($mahasiswa->nim);
    expect($byNim?->id)->toBe($mahasiswa->id);

    expect($contract->mahasiswaByUserId(999999))->toBeNull();
    expect($contract->mahasiswaByNim('tidak-ada'))->toBeNull();
});

test('contract lists dosen sorted by nama with detail lookup', function () {
    $budi = Dosen::factory()->create(['nama' => 'Budi']);
    Dosen::factory()->create(['nama' => 'Andi']);
    Dosen::factory()->create(['nama' => 'Citra']);

    $contract = app(AkademikContract::class);

    $daftar = $contract->daftarDosen();
    expect($daftar->count())->toBe(3);
    expect(array_column($daftar->all(), 'nama'))->toBe(['Andi', 'Budi', 'Citra']);

    $detail = $contract->dosenById($budi->id);
    expect($detail?->nama)->toBe('Budi');
    expect($detail?->nip)->toBe($budi->nip);

    expect($contract->dosenById(999999))->toBeNull();
});

test('user accounts without a mahasiswa profile resolve to null', function () {
    $account = User::factory()->create();

    expect(app(AkademikContract::class)->mahasiswaByUserId($account->id))->toBeNull();
});
