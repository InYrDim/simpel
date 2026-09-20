<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Models\PengajuanJudul;
use App\Modules\Skripsi\Services\VerifikasiAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Aksi admin (role:admin via routes, §5.3): daftar pengajuan masuk
 * (status `diajukan`) → verifikasi (setujui + pilih validator dari daftar
 * dosen via AkademikContract, atau tolak + catatan wajib).
 */
class VerifikasiAdminController extends Controller
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    /**
     * Daftar pengajuan masuk yang menunggu verifikasi admin.
     */
    public function index(): InertiaResponse
    {
        $pengajuans = PengajuanJudul::query()
            ->where('status', 'diajukan')
            ->with('juduls')
            ->orderBy('submitted_at')
            ->get()
            ->map(fn (PengajuanJudul $p): array => [
                'id' => $p->id,
                'nama_mahasiswa' => $this->akademik->mahasiswaByUserId($p->user_id)->nama ?? '-',
                'nim' => $this->akademik->mahasiswaByUserId($p->user_id)->nim ?? '-',
                'submitted_at' => $p->submitted_at?->toISOString(),
                'berkas_original_name' => $p->berkas_original_name,
                'juduls' => $p->juduls->map(fn ($j): array => $j->only(['id', 'urutan', 'judul', 'topik']))->all(),
            ]);

        return Inertia::render('skripsi/verifikasi/index', [
            'pengajuans' => $pengajuans,
            'dosenOptions' => $this->akademik->daftarDosen()->toOptionList(),
        ]);
    }

    public function store(Request $request, PengajuanJudul $pengajuan, VerifikasiAdmin $action): RedirectResponse
    {
        $validated = $request->validate([
            'disetujui' => ['required', 'boolean'],
            'validator_id' => ['required_if:disetujui,true', 'nullable', 'integer'],
            'catatan_admin' => ['required_if:disetujui,false', 'nullable', 'string'],
        ]);

        $action->handle(
            $pengajuan,
            disetujui: (bool) $validated['disetujui'],
            dosenValidatorId: $validated['validator_id'] ?? null,
            catatan: $validated['catatan_admin'] ?? null,
        );

        return redirect()->route('skripsi.verifikasi.index')
            ->with('success', 'Keputusan verifikasi tersimpan.');
    }
}
