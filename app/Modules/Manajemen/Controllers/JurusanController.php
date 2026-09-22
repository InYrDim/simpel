<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Manajemen\Models\Jurusan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman Profil Jurusan (menu Manajemen, role:admin).
 *
 * Aplikasi melayani SATU jurusan: halaman ini menampilkan profil jurusan
 * (nama, ketua, sekretaris) dan memungkinkan pembaruannya — mirip halaman
 * profil pengguna di Settings. Tanpa daftar, tambah, atau hapus.
 */
class JurusanController extends Controller
{
    public function edit(): Response
    {
        $jurusan = Jurusan::query()->first();

        return Inertia::render('manajemen/jurusan/index', [
            'jurusan' => $jurusan ? $this->profilJurusan($jurusan) : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'ketua_nama' => ['nullable', 'string', 'max:255'],
            'ketua_nip' => ['nullable', 'string', 'max:50'],
            'sekretaris_nama' => ['nullable', 'string', 'max:255'],
            'sekretaris_nip' => ['nullable', 'string', 'max:50'],
        ], [
            'nama.required' => 'Nama jurusan wajib diisi.',
        ]);

        $jurusan = Jurusan::query()->first();

        if ($jurusan === null) {
            Jurusan::create($validated);
        } else {
            $jurusan->update($validated);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Profil jurusan berhasil diperbarui.',
        ]);

        return redirect()->route('manajemen.jurusan.edit');
    }

    /**
     * @return array{nama: string, ketua_nama: string|null, ketua_nip: string|null, sekretaris_nama: string|null, sekretaris_nip: string|null}
     */
    private function profilJurusan(Jurusan $jurusan): array
    {
        return [
            'nama' => $jurusan->nama,
            'ketua_nama' => $jurusan->ketua_nama,
            'ketua_nip' => $jurusan->ketua_nip,
            'sekretaris_nama' => $jurusan->sekretaris_nama,
            'sekretaris_nip' => $jurusan->sekretaris_nip,
        ];
    }
}
