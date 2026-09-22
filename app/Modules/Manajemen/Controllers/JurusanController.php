<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manajemen\Models\Jurusan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JurusanController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<Jurusan> $query */
        $query = Jurusan::query()
            ->select(['id', 'nama', 'ketua_nama', 'ketua_nip', 'sekretaris_nama', 'sekretaris_nip', 'created_at'])
            ->orderBy('nama');

        if ($search !== '') {
            $query->where('nama', 'like', "%{$search}%");
        }

        /** @var LengthAwarePaginator<int, Jurusan> $paginator */
        $paginator = $query->paginate(10)->withQueryString();

        $jurusans = $paginator->through(
            fn (Jurusan $j): array => [
                'id' => $j->id,
                'nama' => $j->nama,
                'ketua_nama' => $j->ketua_nama,
                'ketua_nip' => $j->ketua_nip,
                'sekretaris_nama' => $j->sekretaris_nama,
                'sekretaris_nip' => $j->sekretaris_nip,
                'created_at' => (string) $j->created_at,
            ],
        );

        return Inertia::render('manajemen/jurusan/index', [
            'jurusans' => $jurusans,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('manajemen_jurusans')],
            'ketua_nama' => ['nullable', 'string', 'max:255'],
            'ketua_nip' => ['nullable', 'string', 'max:50'],
            'sekretaris_nama' => ['nullable', 'string', 'max:255'],
            'sekretaris_nip' => ['nullable', 'string', 'max:50'],
        ], [
            'nama.required' => 'Nama jurusan wajib diisi.',
        ]);

        Jurusan::create($validated);

        return redirect()->route('manajemen.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('manajemen_jurusans')->ignore($jurusan->id)],
            'ketua_nama' => ['nullable', 'string', 'max:255'],
            'ketua_nip' => ['nullable', 'string', 'max:50'],
            'sekretaris_nama' => ['nullable', 'string', 'max:255'],
            'sekretaris_nip' => ['nullable', 'string', 'max:50'],
        ]);

        $jurusan->update($validated);

        return redirect()->route('manajemen.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        $jurusan->delete();

        return redirect()->route('manajemen.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
