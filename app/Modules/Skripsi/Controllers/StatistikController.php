<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Services\SkripsiMonitoringService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Halaman Statistik (menu Laporan, role:admin): ringkasan angka pengajuan —
 * total, bulan berjalan, dan sebaran per status. Agregasi hanya bacaan;
 * transisi status tetap di Services (§7.2).
 */
class StatistikController extends Controller
{
    public function __construct(
        private readonly SkripsiMonitoringService $monitoring,
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('skripsi/statistik/index', [
            ...$this->monitoring->ringkasan(),
        ]);
    }
}
