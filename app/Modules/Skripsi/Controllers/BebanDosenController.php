<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Services\SkripsiMonitoringService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Halaman Beban Dosen (menu Laporan, role:admin): sebaran penugasan per
 * dosen (validator aktif + pembimbing/penguji dari judul disetujui) plus
 * beban review aktif per validator. Angka hanya bacaan (§7.2).
 */
class BebanDosenController extends Controller
{
    public function __construct(
        private readonly SkripsiMonitoringService $monitoring,
    ) {}

    public function index(): InertiaResponse
    {
        $ringkasan = $this->monitoring->ringkasan();

        return Inertia::render('skripsi/beban-dosen/index', [
            'beban_dosen' => $this->monitoring->bebanDosen(),
            'per_validator' => $ringkasan['per_validator'],
        ]);
    }
}
