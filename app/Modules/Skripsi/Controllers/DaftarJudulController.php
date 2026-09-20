<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Skripsi\Models\JudulPengajuan;
use App\Modules\Skripsi\Services\AssignPenugasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * Submenu Daftar Judul (role:admin & role:validator, §5.2): tabel judul
 * dengan pagination + pencarian sederhana; klik baris → modal detail
 * (judul, deskripsi, topik, penugasan, NIM).
 *
 * Admin dapat mengisi penugasan pembimbing/penguji pada judul milik
 * pengajuan `disetujui` langsung dari modal detail (keputusan pelaksanaan
 * atas §3.5).
 */
class DaftarJudulController extends Controller
{
    public function __construct(
        private readonly AkademikContract $akademik,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<JudulPengajuan> $query */
        $query = JudulPengajuan::query()
            ->disetujui()
            ->orderBy('judul');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('topik', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $juduls = $query->with('pengajuan:id,user_id')->paginate(10)->withQueryString()->through(fn (JudulPengajuan $j): array => [
            'id' => $j->id,
            'judul' => $j->judul,
            'topik' => $j->topik,
            'deskripsi' => $j->deskripsi,
            // Identitas mahasiswa di-resolusi via kontrak, bukan FK/relasi
            // langsung ke model Akademik (boundary rule).
            'nama_mahasiswa' => $this->akademik->mahasiswaByUserId($j->pengajuan->user_id)->nama ?? '-',
            'nim' => $this->akademik->mahasiswaByUserId($j->pengajuan->user_id)->nim ?? '-',
            'penugasan' => [
                'dosen_pembimbing_1' => $j->dosen_pembimbing_1,
                'dosen_pembimbing_2' => $j->dosen_pembimbing_2,
                'dosen_penguji_1' => $j->dosen_penguji_1,
                'dosen_penguji_2' => $j->dosen_penguji_2,
            ],
        ]);

        return Inertia::render('skripsi/daftar-judul/index', [
            'juduls' => $juduls,
            'filters' => ['search' => $search],
            'dosenOptions' => $this->akademik->daftarDosen()->toOptionList(),
        ]);
    }

    /**
     * Simpan penugasan dari modal detail. Admin saja — route ini berada di
     * grup middleware `role:admin` (§6.7), pola yang sama dengan Manajemen.
     */
    public function assign(Request $request, JudulPengajuan $judul, AssignPenugasan $action): JsonResponse
    {
        $validated = $request->validate([
            'dosen_pembimbing_1' => ['nullable', 'integer'],
            'dosen_pembimbing_2' => ['nullable', 'integer'],
            'dosen_penguji_1' => ['nullable', 'integer'],
            'dosen_penguji_2' => ['nullable', 'integer'],
        ]);

        $action->handle($judul, $validated);

        return response()->json(['status' => 'ok']);
    }
}
