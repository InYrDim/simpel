<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Services\RiwayatPengajuanService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman Riwayat Pengajuan (PR 4 sesi 3): daftar pengajuan baca-saja
 * dengan timeline detail di dialog. Admin melihat semua mahasiswa,
 * mahasiswa hanya miliknya — pemilihan scope lewat role di sini, data
 * dibangun RiwayatPengajuanService.
 */
class RiwayatPengajuanController extends Controller
{
    public function __construct(
        private readonly RiwayatPengajuanService $riwayat,
    ) {}

    public function index(): InertiaResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('skripsi/riwayat/index', [
            'pengajuans' => $this->riwayat->daftar($user, $user->hasRole('admin')),
        ]);
    }

    public function berkas(PengajuanJudul $pengajuan): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->hasRole('admin') && $pengajuan->user_id !== $user->id) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($pengajuan->berkas_path)) {
            abort(404);
        }

        // Nama asli berkas berasal dari klien dan masuk ke header
        // Content-Disposition. FilesystemAdapter::response() menyusun header
        // via Symfony HeaderUtils::makeDisposition() — aman dari kutip &
        // non-ASCII — tapi justru MELEMPAR exception untuk `/`, `\`, dan CRLF.
        // Bersihkan sebelum dipakai; nama asli tetap tampil utuh di UI.
        $namaAman = str_replace(
            ['/', '\\', '"', "\r", "\n", '%'],
            '',
            Str::ascii($pengajuan->berkas_original_name),
        );

        return Storage::disk('local')->response(
            $pengajuan->berkas_path,
            $namaAman !== '' ? $namaAman : 'pengajuan.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
