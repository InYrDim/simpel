<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Services\MintaRevisiValidator;
use App\Modules\Skripsi\Services\PutusanValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Aksi validator (role:validator via routes, §5.3): melihat penugasan
 * review untuk dirinya dan memutuskan — setujui SATU judul atau tolak
 * dengan catatan wajib.
 *
 * Dosen di-resolusi dari akun login via AkademikContract::dosenByUserId().
 */
class PutusanValidatorController extends Controller
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    public function index(): InertiaResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $dosen = $this->akademik->dosenByUserId($user->id);

        $pengajuans = collect();

        if ($dosen !== null) {
            $pengajuans = PengajuanJudul::query()
                ->where('status', 'diverifikasi_admin')
                ->where('validator_id', $dosen->id)
                ->with('juduls')
                ->orderBy('verified_at')
                ->get()
                ->map(fn (PengajuanJudul $p): array => [
                    'id' => $p->id,
                    'nama_mahasiswa' => $this->akademik->mahasiswaByUserId($p->user_id)->nama ?? '-',
                    'nim' => $this->akademik->mahasiswaByUserId($p->user_id)->nim ?? '-',
                    'verified_at' => $p->verified_at?->toISOString(),
                    'juduls' => $p->juduls->map(fn ($j): array => $j->only(['id', 'urutan', 'judul', 'deskripsi', 'topik']))->all(),
                ]);
        }

        return Inertia::render('skripsi/putusan/index', [
            'pengajuans' => $pengajuans,
        ]);
    }

    public function store(Request $request, PengajuanJudul $pengajuan, PutusanValidator $action): RedirectResponse
    {
        $validated = $request->validate([
            'disetujui' => ['required', 'boolean'],
            'judul_id' => ['required_if:disetujui,true', 'nullable', 'integer'],
            'catatan_validator' => ['required_if:disetujui,false', 'nullable', 'string'],
        ]);

        /** @var User $user */
        $user = auth()->user();

        $action->handle(
            $pengajuan,
            disetujui: (bool) $validated['disetujui'],
            judulId: $validated['judul_id'] ?? null,
            catatan: $validated['catatan_validator'] ?? null,
            aktor: $user,
        );

        return redirect()->route('skripsi.putusan.index')
            ->with('success', 'Putusan tersimpan.');
    }

    /**
     * Minta revisi (alur revisi, sesi 3): kembalikan pengajuan berstatus
     * `diverifikasi_admin` ke mahasiswa dengan catatan wajib — setelah
     * diperbaiki, mahasiswa resubmit pada pengajuan yang sama.
     */
    public function revisi(Request $request, PengajuanJudul $pengajuan, MintaRevisiValidator $action): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_validator' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = auth()->user();

        $action->handle($pengajuan, catatan: $validated['catatan_validator'], aktor: $user);

        return redirect()->route('skripsi.putusan.index')
            ->with('success', 'Pengajuan diminta revisi.');
    }
}
