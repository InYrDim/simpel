<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Enums\StatusPengajuan;
use App\Modules\Skripsi\Services\SkripsiMonitoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman Export (menu Laporan, role:admin): pilih filter lalu unduh CSV
 * daftar pengajuan. Struktur siap dilebarkan ke format Excel belakangan;
 * data hanya bacaan dari tabel milik modul Skripsi.
 */
class ExportController extends Controller
{
    /**
     * Header kolom CSV — identitas mahasiswa di-resolusi via kontrak.
     */
    private const KOLOM = [
        'NIM',
        'Nama Mahasiswa',
        'Judul',
        'Topik',
        'Status',
        'Validator',
        'Berkas',
        'Diajukan',
        'Diputuskan',
    ];

    public function __construct(
        private readonly SkripsiMonitoringService $monitoring,
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('skripsi/export/index', [
            'statusOptions' => collect(StatusPengajuan::cases())
                ->map(fn (StatusPengajuan $s): array => [
                    'value' => $s->value,
                    'label' => $s->label(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function download(Request $request): StreamedResponse
    {
        $status = $request->string('status')->value();
        $status = $status !== '' && StatusPengajuan::tryFrom($status) !== null
            ? $status
            : null;

        $baris = $this->monitoring->daftarPengajuanForExport($status);

        return response()->streamDownload(function () use ($baris): void {
            $out = fopen('php://output', 'wb');

            if ($out === false) {
                throw new \RuntimeException('Gagal membuka stream output.');
            }

            fputcsv($out, self::KOLOM);

            foreach ($baris as $row) {
                fputcsv($out, [
                    $row['nim'],
                    $row['nama_mahasiswa'],
                    $row['judul'],
                    $row['topik'],
                    $row['status_label'],
                    $row['validator_nama'],
                    $row['berkas_original_name'],
                    $row['submitted_at'] ?? '',
                    $row['decided_at'] ?? '',
                ]);
            }

            fclose($out);
        }, sprintf('pengajuan-judul-%s.csv', now()->format('Ymd-His')), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
