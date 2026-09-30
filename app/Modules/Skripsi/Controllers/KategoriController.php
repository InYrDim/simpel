<?php

namespace App\Modules\Skripsi\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Skripsi\Models\Kategori;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin kategori judul skripsi (role:admin, route modul). Kategori yang
 * sudah dipakai judul tidak bisa dihapus — server menolak, admin cukup
 * menonaktifkannya agar tidak muncul lagi di form pengajuan.
 */
class KategoriController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<Kategori> $query */
        $query = Kategori::query()
            ->select(['id', 'nama', 'deskripsi', 'aktif', 'created_at'])
            ->withCount('juduls')
            ->orderBy('nama');

        if ($search !== '') {
            $query->where('nama', 'like', "%{$search}%");
        }

        /** @var LengthAwarePaginator<int, Kategori> $paginator */
        $paginator = $query->paginate(10)->withQueryString();

        $kategoris = $paginator->through(
            fn (Kategori $k): array => [
                'id' => $k->id,
                'nama' => $k->nama,
                'deskripsi' => $k->deskripsi,
                'aktif' => $k->aktif,
                'jumlah_judul' => $k->juduls_count,
                'created_at' => $k->created_at?->toISOString(),
            ],
        );

        return Inertia::render('skripsi/kategori/index', [
            'kategoris' => $kategoris,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Kategori::create($this->validateKategori($request));

        return redirect()->route('skripsi.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $kategori->update($this->validateKategori($request, $kategori->id));

        return redirect()->route('skripsi.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->juduls()->exists()) {
            return redirect()->route('skripsi.kategori.index')
                ->with('error', 'Kategori tidak bisa dihapus karena sudah dipakai judul. Nonaktifkan saja.');
        }

        $kategori->delete();

        return redirect()->route('skripsi.kategori.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * @return array{nama: string, deskripsi: string|null, aktif: bool}
     */
    private function validateKategori(Request $request, ?int $ignoreId = null): array
    {
        /** @var array{nama: string, deskripsi: string|null, aktif: bool} $validated */
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skripsi_kategoris', 'nama')->ignore($ignoreId),
            ],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['required', 'boolean'],
        ]);

        return $validated;
    }
}
