<?php

namespace App\Modules\Akademik\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Akademik\Models\Dosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin untuk referensi dosen — PRD §7.1 (role:admin, route modul).
 *
 * Controller hanya: validate → operasi model → return. Tidak ada logika
 * bisnis lintas modul di sini.
 */
class DosenController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<Dosen> $query */
        $query = Dosen::query()
            ->select(['id', 'nama', 'nip', 'bidang', 'created_at'])
            ->orderBy('nama');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('bidang', 'like', "%{$search}%");
            });
        }

        $dosens = $query->paginate(10)->withQueryString();

        return Inertia::render('akademik/dosen/index', [
            'dosens' => $dosens,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDosen($request);

        Dosen::create($validated);

        return redirect()->route('akademik.dosen.index')
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $validated = $this->validateDosen($request, $dosen->id);

        $dosen->update($validated);

        return redirect()->route('akademik.dosen.index')
            ->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        $dosen->delete();

        return redirect()->route('akademik.dosen.index')
            ->with('success', 'Dosen berhasil dihapus.');
    }

    /**
     * @return array{nama: string, nip: string, bidang: string}
     */
    private function validateDosen(Request $request, ?int $ignoreId = null): array
    {
        /** @var array{nama: string, nip: string, bidang: string} $validated */
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => [
                'required',
                'string',
                'max:255',
                Rule::unique('akademik_dosens', 'nip')->ignore($ignoreId),
            ],
            'bidang' => ['required', 'string', 'max:255'],
        ]);

        return $validated;
    }
}
