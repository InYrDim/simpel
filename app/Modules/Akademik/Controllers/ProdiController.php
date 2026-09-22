<?php

namespace App\Modules\Akademik\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin program studi — PRD §4.2 (role:admin, route modul).
 *
 * Kaprodi dikaitkan ke dosen modul yang sama. Prodi yang masih terpakai
 * mahasiswa tidak bisa dihapus (server menolak, bukan sekadar UI).
 */
class ProdiController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<Prodi> $query */
        $query = Prodi::query()
            ->select(['id', 'nama', 'kaprodi_id', 'created_at'])
            ->with('kaprodi:id,nama')
            ->withCount('mahasiswas')
            ->orderBy('nama');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhereHas('kaprodi', fn (Builder $kq) => $kq->where('nama', 'like', "%{$search}%"));
            });
        }

        $prodis = $query->paginate(10)->withQueryString()->through(
            fn (Prodi $p): array => [
                'id' => $p->id,
                'nama' => $p->nama,
                'kaprodi_id' => $p->kaprodi_id,
                'kaprodi_nama' => $p->kaprodi?->nama,
                'jumlah_mahasiswa' => $p->mahasiswas_count,
                'created_at' => $p->created_at?->toISOString(),
            ],
        );

        return Inertia::render('akademik/prodi/index', [
            'prodis' => $prodis,
            'filters' => ['search' => $search],
            'kaprodiOptions' => Dosen::query()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProdi($request);

        Prodi::create($validated);

        return redirect()->route('akademik.prodi.index')
            ->with('success', 'Prodi berhasil ditambahkan.');
    }

    public function update(Request $request, Prodi $prodi): RedirectResponse
    {
        $validated = $this->validateProdi($request, $prodi->id);

        $prodi->update($validated);

        return redirect()->route('akademik.prodi.index')
            ->with('success', 'Prodi berhasil diperbarui.');
    }

    public function destroy(Prodi $prodi): RedirectResponse
    {
        if (Mahasiswa::query()->where('prodi_id', $prodi->id)->exists()) {
            return redirect()->route('akademik.prodi.index')
                ->with('error', 'Prodi tidak bisa dihapus karena masih memiliki mahasiswa.');
        }

        $prodi->delete();

        return redirect()->route('akademik.prodi.index')
            ->with('success', 'Prodi berhasil dihapus.');
    }

    /**
     * @return array{nama: string, kaprodi_id: int|null}
     */
    private function validateProdi(Request $request, ?int $ignoreId = null): array
    {
        /** @var array{nama: string, kaprodi_id: int|null} $validated */
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('akademik_prodis', 'nama')->ignore($ignoreId),
            ],
            'kaprodi_id' => ['nullable', 'integer', Rule::exists('akademik_dosens', 'id')],
        ]);

        return $validated;
    }
}
