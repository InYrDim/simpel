<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Skripsi\Services\RiwayatPengajuanService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
}
