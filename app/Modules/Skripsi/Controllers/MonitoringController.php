<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Services\SkripsiMonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Halaman Monitoring (menu Laporan, role:admin): daftar pengajuan per status,
 * difilter per status via query string. Hanya bacaan — transisi status tetap
 * di Services (§7.2).
 */
class MonitoringController extends Controller
{
    public function __construct(
        private readonly SkripsiMonitoringService $monitoring,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $status = $request->string('status')->value();
        $status = $status !== '' && StatusPengajuan::tryFrom($status) !== null
            ? $status
            : null;

        return Inertia::render('skripsi/monitoring/index', [
            'pengajuans' => $this->monitoring->daftarPengajuan($status),
            'filters' => ['status' => $status],
            'statusOptions' => collect(StatusPengajuan::cases())
                ->map(fn (StatusPengajuan $s): array => [
                    'value' => $s->value,
                    'label' => $s->label(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
