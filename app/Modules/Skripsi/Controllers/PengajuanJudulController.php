<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Services\SubmitPengajuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller mahasiswa (role:mahasiswa via routes) — controller hanya
 * validate → Action → return; transisi status ada di Services (§7.2).
 */
class PengajuanJudulController extends Controller
{
    /**
     * Panel status pengajuan mahasiswa (§5.1): status terkini, 3 judul,
     * berkas, catatan, dan riwayat pengajuan lama yang ditolak.
     */
    public function status(): InertiaResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $pengajuans = PengajuanJudul::query()
            ->where('user_id', $user->id)
            ->with('juduls')
            ->orderByDesc('submitted_at')
            ->get();

        // Status terkini = pengajuan aktif, atau pengajuan terakhir bila
        // semuanya sudah diputus. "Belum mengajukan" bila kosong (§4).
        $terkini = $pengajuans->first(fn (PengajuanJudul $p): bool => $p->isActive())
            ?? $pengajuans->first();

        return Inertia::render('skripsi/pengajuan/index', [
            'pengajuan' => $terkini?->only([
                'id', 'status', 'catatan_admin', 'catatan_validator', 'berkas_original_name', 'submitted_at', 'verified_at', 'decided_at',
            ]),
            'judulTerkini' => $terkini?->juduls->map(fn ($j): array => $j->only(['id', 'urutan', 'judul', 'deskripsi', 'topik']))->all(),
            'riwayat' => $pengajuans
                ->reject(fn (PengajuanJudul $p): bool => $terkini !== null && $p->id === $terkini->id)
                ->map(fn (PengajuanJudul $p): array => [
                    'id' => $p->id,
                    'status' => $p->status->value,
                    'status_label' => $p->status->label(),
                    'catatan_admin' => $p->catatan_admin,
                    'catatan_validator' => $p->catatan_validator,
                    'submitted_at' => $p->submitted_at?->toISOString(),
                    'jumlah_judul' => $p->juduls->count(),
                ])->all(),
        ]);
    }

    /**
     * Submit pengajuan baru — tepat 3 judul + PDF maks 5 MB (§6.1, §6.2).
     * Aturan "satu pengajuan aktif" dicek ulang di SubmitPengajuan (§6.4).
     */
    public function store(
        Request $request,
        SubmitPengajuan $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = auth()->user();
        $validated = $request->validate([
            'juduls' => ['required', 'array', 'min:3', 'max:3'],
            'juduls.*.judul' => ['required', 'string', 'max:255'],
            'juduls.*.deskripsi' => ['required', 'string'],
            'juduls.*.topik' => ['required', 'string', 'max:255'],
            'berkas' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $action->handle($user, $validated['juduls'], $request->file('berkas'));

        return redirect()->route('skripsi.pengajuan.status')
            ->with('success', 'Pengajuan berhasil dikirim. Menunggu verifikasi admin.');
    }

    /**
     * Download template pengajuan DOCX — aset statis disajikan lewat route
     * terproteksi login + role (§6.2, §7.3).
     */
    public function template(): StreamedResponse
    {
        return Storage::disk('local')->download('template/template-pengajuan.docx', 'template-pengajuan.docx');
    }
}
