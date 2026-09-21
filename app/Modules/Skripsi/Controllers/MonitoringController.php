<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Services\SkripsiMonitoringService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Dashboard monitoring admin (PR 3 sesi 3): ringkasan statistik pengajuan —
 * total, per status, beban validator, dan pengajuan bulan berjalan — plus
 * sebaran beban penugasan per dosen (PRD Beban Dosen). Angka agregat hanya
 * bacaan; transisi status tetap di Services (§7.2).
 */
class MonitoringController extends Controller
{
    public function __construct(
        private readonly SkripsiMonitoringService $monitoring,
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('skripsi/monitoring/index', [
            ...$this->monitoring->ringkasan(),
            'beban_dosen' => $this->monitoring->bebanDosen(),
        ]);
    }
}
