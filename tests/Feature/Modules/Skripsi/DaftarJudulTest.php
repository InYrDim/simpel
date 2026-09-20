<?php

use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function daftarAdminUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function pengajuanDisetujui(string $judulText, string $topik): PengajuanJudul
{
    $pengajuan = PengajuanJudul::factory()->create(['status' => 'disetujui', 'decided_at' => now()]);
    $pengajuan->juduls()->createMany([
        ['judul' => 'Cadangan A', 'deskripsi' => 'A.', 'topik' => 'T', 'urutan' => 1],
        ['judul' => $judulText, 'deskripsi' => 'D.', 'topik' => $topik, 'urutan' => 2],
        ['judul' => 'Cadangan B', 'deskripsi' => 'B.', 'topik' => 'T', 'urutan' => 3],
    ]);

    return $pengajuan;
}

test('daftar judul lists only approved pengajuan titles', function () {
    pengajuanDisetujui('Sistem Rekomendasi', 'Machine Learning');
    PengajuanJudul::factory()->create(); // masih diajukan — juduls-nya tidak boleh tampil

    $this->actingAs(daftarAdminUser())
        ->get(route('skripsi.daftar-judul.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('skripsi/daftar-judul/index')
            ->has('juduls.data', 3) // 3 judul milik pengajuan yang disetujui
            ->where('juduls.data.0.judul', 'Cadangan A') // urut abjad
        );
});

test('daftar judul supports search', function () {
    pengajuanDisetujui('Deteksi Similaritas', 'NLP');
    pengajuanDisetujui('Sistem Informasi Akademik', 'Sistem Informasi');

    $this->actingAs(daftarAdminUser())
        ->get(route('skripsi.daftar-judul.index', ['search' => 'similaritas']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('juduls.data', 1)
            ->where('juduls.data.0.judul', 'Deteksi Similaritas')
        );
});

test('detail modal fields are present with empty penugasan', function () {
    pengajuanDisetujui('Judul Detail', 'Topik Detail');

    $this->actingAs(daftarAdminUser())
        ->get(route('skripsi.daftar-judul.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('juduls.data', 3)
            ->where('juduls.data.0.penugasan.dosen_pembimbing_1', null)
            ->where('juduls.data.0.nama_mahasiswa', fn ($nama) => is_string($nama))
        );
});
