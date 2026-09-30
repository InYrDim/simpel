<?php

namespace App\Modules\Akademik\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Akademik\Enums\StatusMahasiswa;
use App\Modules\Akademik\Models\Dosen;
use App\Modules\Akademik\Models\Mahasiswa;
use App\Modules\Akademik\Models\Prodi;
use App\Modules\Contracts\SkripsiContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CRUD admin untuk profil akademik mahasiswa — PRD §7.1 (role:admin).
 *
 * Controller hanya: validate → operasi model → return.
 */
class MahasiswaController extends Controller
{
    private const TANPA_DOSEN_PA = 'kosong';

    /** @var list<int> */
    private const PER_PAGE_OPTIONS = [10, 25, 50];

    /** @var list<string> */
    private const SORTABLE = ['nama', 'nim', 'angkatan'];

    public function __construct(private SkripsiContract $skripsi) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $prodiId = $this->intOrNull($request->input('prodi_id'));
        $angkatan = $this->intOrNull($request->input('angkatan'));
        $status = StatusMahasiswa::tryFrom((string) $request->input('status', ''));

        $dosenPaInput = $request->input('dosen_pa_id');
        $tanpaDosenPa = $dosenPaInput === self::TANPA_DOSEN_PA;
        $dosenPaId = $tanpaDosenPa ? null : $this->intOrNull($dosenPaInput);

        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? (string) $request->input('sort') : 'nama';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int) $request->input('per_page'), self::PER_PAGE_OPTIONS, true)
            ? (int) $request->input('per_page')
            : self::PER_PAGE_OPTIONS[0];

        /** @var Builder<Mahasiswa> $query */
        $query = Mahasiswa::query()
            ->with(['user:id,name,email', 'dosenPa:id,nama', 'prodiRef:id,nama'])
            ->select(['id', 'user_id', 'nama', 'nim', 'dosen_pa_id', 'prodi_id', 'angkatan', 'status', 'created_at'])
            ->orderBy($sort, $direction)
            ->orderBy('id');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn (Builder $uq) => $uq->where('email', 'like', "%{$search}%"));
            });
        }

        $query
            ->when($prodiId !== null, fn (Builder $q) => $q->where('prodi_id', $prodiId))
            ->when($angkatan !== null, fn (Builder $q) => $q->where('angkatan', $angkatan))
            ->when($status !== null, fn (Builder $q) => $q->where('status', $status))
            ->when($tanpaDosenPa, fn (Builder $q) => $q->whereNull('dosen_pa_id'))
            ->when($dosenPaId !== null, fn (Builder $q) => $q->where('dosen_pa_id', $dosenPaId));

        /** @var LengthAwarePaginator<int, Mahasiswa> $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        $idsDenganPengajuan = array_flip(
            $this->skripsi->mahasiswaIdsDenganPengajuan(
                array_values($paginator->getCollection()->map(fn (Mahasiswa $m): int => $m->id)->all()),
            ),
        );

        $mahasiswas = $paginator->through(
            fn (Mahasiswa $m): array => [
                'id' => $m->id,
                'nama' => $m->nama,
                'nim' => $m->nim,
                'dosen_pa_id' => $m->dosen_pa_id,
                'dosen_pa_nama' => $m->dosenPa?->nama,
                'user_email' => $m->user->email,
                'prodi_id' => $m->prodi_id,
                'prodi' => $m->prodiRef?->nama,
                'angkatan' => $m->angkatan,
                'status' => $m->status->value,
                'punya_pengajuan' => isset($idsDenganPengajuan[$m->id]),
                'created_at' => $m->created_at?->toISOString(),
            ],
        );

        // Akun yang belum punya profil mahasiswa — kandidat untuk dropdown.
        $userOptions = User::query()
            ->leftJoin('akademik_mahasiswas', 'akademik_mahasiswas.user_id', '=', 'users.id')
            ->whereNull('akademik_mahasiswas.id')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        $angkatanOptions = [];
        foreach (Mahasiswa::query()->whereNotNull('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan') as $tahun) {
            $angkatanOptions[] = (int) $tahun;
        }

        return Inertia::render('akademik/mahasiswa/index', [
            'mahasiswas' => $mahasiswas,
            'filters' => [
                'search' => $search,
                'prodi_id' => $prodiId,
                'angkatan' => $angkatan,
                'dosen_pa_id' => $tanpaDosenPa ? self::TANPA_DOSEN_PA : $dosenPaId,
                'status' => $status?->value,
                'sort' => $sort,
                'direction' => $direction,
                'per_page' => $perPage,
            ],
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'statusOptions' => StatusMahasiswa::options(),
            'angkatanOptions' => $angkatanOptions,
            'dosenOptions' => Dosen::query()->orderBy('nama')->get(['id', 'nama']),
            'prodiOptions' => Prodi::query()->orderBy('nama')->get(['id', 'nama']),
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
        // Aturan dosen PA bergantung pada status; tanpa status di request,
        // pakai status yang tersimpan.
        $request->mergeIfMissing(['status' => $mahasiswa->status->value]);

        $mahasiswa->update($this->validateMahasiswa($request, $mahasiswa->id));

        return redirect()->route('akademik.mahasiswa.index')
            ->with('success', 'Profil mahasiswa berhasil diperbarui.');
    }

    public function destroy(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        // Hapus permanen menghilangkan profil akademik; konfirmasi NIM dicek
        // di server supaya tidak bisa dilewati dari klien.
        $request->validate([
            'konfirmasi_nim' => ['required', 'string', Rule::in([$mahasiswa->nim])],
        ], [
            'konfirmasi_nim.required' => 'Ketik NIM mahasiswa untuk mengonfirmasi penghapusan.',
            'konfirmasi_nim.in' => 'NIM yang diketik tidak sesuai.',
        ]);

        // Pengajuan skripsi merujuk mahasiswa tanpa FK, jadi penghapusan
        // diblokir di sini agar tidak menyisakan pengajuan yatim.
        if ($this->skripsi->mahasiswaIdsDenganPengajuan([$mahasiswa->id]) !== []) {
            throw ValidationException::withMessages([
                'mahasiswa' => 'Mahasiswa ini masih memiliki pengajuan skripsi sehingga tidak bisa dihapus. Ubah statusnya menjadi Nonaktif.',
            ]);
        }

        $mahasiswa->delete();

        return redirect()->route('akademik.mahasiswa.index')
            ->with('success', 'Profil mahasiswa berhasil dihapus.');
    }

    /**
     * @return array{user_id?: int, nama: string, nim: string, dosen_pa_id: int|null, prodi_id: int|null, angkatan: int|null, status?: string}
     */
    private function validateMahasiswa(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'nama' => ['required', 'string', 'max:255'],
            'nim' => [
                'required',
                'string',
                'max:255',
                Rule::unique('akademik_mahasiswas', 'nim')->ignore($ignoreId),
            ],
            // Dosen PA wajib bagi mahasiswa yang masih berkuliah. Mahasiswa hasil
            // registrasi mandiri memang belum punya PA, jadi profil yang sudah
            // lulus/nonaktif tetap boleh disimpan tanpa PA.
            'dosen_pa_id' => [
                'nullable',
                'required_unless:status,'.StatusMahasiswa::Lulus->value.','.StatusMahasiswa::Nonaktif->value,
                'integer',
                Rule::exists('akademik_dosens', 'id'),
            ],
            'prodi_id' => ['nullable', 'integer', Rule::exists('akademik_prodis', 'id')],
            'angkatan' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'status' => ['sometimes', Rule::enum(StatusMahasiswa::class)],
        ];

        // Tautan akun hanya ditetapkan saat pembuatan profil.
        if ($ignoreId === null) {
            $rules['user_id'] = [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('akademik_mahasiswas', 'user_id'),
            ];
        }

        /** @var array{user_id?: int, nama: string, nim: string, dosen_pa_id: int|null, prodi_id: int|null, angkatan: int|null, status?: string} $validated */
        $validated = $request->validate($rules);

        return $validated;
    }

    private function intOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return null;
    }
}
