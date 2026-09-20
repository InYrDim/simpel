<?php

namespace App\Modules\Akademik\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin untuk profil akademik mahasiswa — PRD §7.1 (role:admin).
 *
 * Controller hanya: validate → operasi model → return.
 */
class MahasiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<Mahasiswa> $query */
        $query = Mahasiswa::query()
            ->with(['user:id,name,email', 'dosenPa:id,nama'])
            ->select(['id', 'user_id', 'nama', 'nim', 'dosen_pa_id', 'prodi', 'angkatan', 'created_at'])
            ->orderBy('nama');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $uq) => $uq->where('email', 'like', "%{$search}%"));
            });
        }

        $mahasiswas = $query->paginate(10)->withQueryString()->through(
            fn (Mahasiswa $m): array => [
                'id' => $m->id,
                'nama' => $m->nama,
                'nim' => $m->nim,
                'dosen_pa_id' => $m->dosen_pa_id,
                'dosen_pa_nama' => $m->dosenPa->nama,
                'user_email' => $m->user->email,
                'prodi' => $m->prodi,
                'angkatan' => $m->angkatan,
                'created_at' => $m->created_at?->toISOString(),
            ],
        );

        // Akun yang belum punya profil mahasiswa — kandidat untuk dropdown.
        $userOptions = User::query()
            ->leftJoin('akademik_mahasiswas', 'akademik_mahasiswas.user_id', '=', 'users.id')
            ->whereNull('akademik_mahasiswas.id')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        return Inertia::render('akademik/mahasiswa/index', [
            'mahasiswas' => $mahasiswas,
            'filters' => ['search' => $search],
            'dosenOptions' => Dosen::query()->orderBy('nama')->get(['id', 'nama']),
            'userOptions' => $userOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMahasiswa($request);

        Mahasiswa::create($validated);

        return redirect()->route('akademik.mahasiswa.index')
            ->with('success', 'Profil mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        // `user_id` sengaja tidak bisa diubah lewat update — tautan akun
        // ditetapkan saat pembuatan profil, bukan bagian dari data akademik.
        $validated = $this->validateMahasiswa($request, $mahasiswa->id);

        $mahasiswa->update(collect($validated)->except('user_id')->all());

        return redirect()->route('akademik.mahasiswa.index')
            ->with('success', 'Profil mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->delete();

        return redirect()->route('akademik.mahasiswa.index')
            ->with('success', 'Profil mahasiswa berhasil dihapus.');
    }

    /**
     * @return array{user_id: int, nama: string, nim: string, dosen_pa_id: int, prodi: string|null, angkatan: int|null}
     */
    private function validateMahasiswa(Request $request, ?int $ignoreId = null): array
    {
        /** @var array{user_id: int, nama: string, nim: string, dosen_pa_id: int, prodi: string|null, angkatan: int|null} $validated */
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('akademik_mahasiswas', 'user_id')->ignore($ignoreId),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'nim' => [
                'required',
                'string',
                'max:255',
                Rule::unique('akademik_mahasiswas', 'nim')->ignore($ignoreId),
            ],
            'dosen_pa_id' => ['required', 'integer', Rule::exists('akademik_dosens', 'id')],
            'prodi' => ['nullable', 'string', 'max:255'],
            'angkatan' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        return $validated;
    }
}
