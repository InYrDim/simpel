<?php

namespace App\Modules\Akademik;

use App\Models\User;
use App\Modules\Akademik\Services\AkademikService;
use App\Modules\Akademik\Services\RegistrasiMahasiswa;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class AkademikServiceProvider extends ModuleServiceProvider
{
    /**
     * Bind kontrak antar-modul ke implementasinya di modul ini.
     *
     * Modul lain (mis. Skripsi) hanya mengenal `AkademikContract`; binding ke
     * implementasi internal modul terjadi di sini, di composition root modul.
     */
    public function register(): void
    {
        $this->app->bind(AkademikContract::class, AkademikService::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->defineGates();
        $this->registerRegistrasiMahasiswa();
    }

    /**
     * Registrasi mandiri Fortify milik modul ini: halaman register butuh
     * daftar prodi dan pendaftar langsung menjadi mahasiswa.
     */
    private function registerRegistrasiMahasiswa(): void
    {
        Fortify::createUsersUsing(RegistrasiMahasiswa::class);
        Fortify::registerView(fn () => Inertia::render('auth/register', RegistrasiMahasiswa::registerViewProps()));
    }

    /**
     * `akademik.mahasiswa-kita` — apakah NIM pada prodi tersebut benar
     * mahasiswa kampus ini. Dipanggil `RegistrasiMahasiswa` sebelum akun dibuat,
     * sehingga user masih null (guest).
     *
     * STUB: selalu lolos. Ganti dengan pengecekan ke sumber data NIM kampus.
     */
    private function defineGates(): void
    {
        Gate::define('akademik.mahasiswa-kita', fn (?User $user, string $nim, int $prodiId): bool => true);
    }

    /**
     * Direktori akar modul Akademik.
     */
    protected function moduleDirectory(): string
    {
        return __DIR__;
    }
}
