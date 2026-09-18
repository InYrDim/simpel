<?php

namespace App\Modules\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Kerangka dasar setiap modul aplikasi.
 *
 * Satu modul = satu direktori `app/Modules/<Nama>` dengan satu provider yang
 * mewarisi kelas ini. Provider tersebut didaftarkan secara eksplisit di
 * `bootstrap/providers.php` — itulah satu-satunya tempat core menyebut nama
 * modul, karena bootstrap adalah composition root aplikasi.
 *
 * Konvensi isi modul:
 *
 * ```
 * app/Modules/<Nama>/
 * ├── <Nama>ServiceProvider.php   provider modul (wajib, extends kelas ini)
 * ├── routes.php                  route HTTP modul (dimuat otomatis)
 * ├── Database/Migrations/        migrasi modul (dimuat otomatis)
 * ├── Controllers/
 * ├── Models/
 * ├── Enums/
 * ├── Policies/
 * ├── Services/
 * └── Requests/
 * ```
 *
 * Aturan dependensi (ditegakkan oleh
 * `tests/Feature/Architecture/ModuleBoundaryTest.php`):
 *
 * - Core tidak boleh menyentuh namespace modul mana pun.
 * - Modul boleh memakai namespace miliknya sendiri, `App\Modules\Support\*`,
 *   `App\Modules\Contracts\*`, dan kelas core yang terdaftar sebagai shared
 *   kernel di test tersebut.
 * - Modul tidak boleh memakai internal modul lain. Kolaborasi antar-modul
 *   dilakukan lewat kontrak publik di `app/Modules/Contracts/`.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Direktori akar modul, mis. `app/Modules/Manajemen`.
     */
    abstract protected function moduleDirectory(): string;

    /**
     * Register any application services.
     */
    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
    }

    /**
     * Daftarkan route milik modul.
     *
     * Route dibungkus grup `web` dengan sengaja: route yang dimuat provider
     * tidak mendapat grup middleware apa pun, berbeda dengan `routes/web.php`
     * yang otomatis dibungkus Laravel. Tanpa ini modul kehilangan sesi, CSRF,
     * dan route model binding (`SubstituteBindings`).
     */
    protected function loadModuleRoutes(): void
    {
        $routes = $this->moduleDirectory().'/routes.php';

        if (! is_file($routes)) {
            return;
        }

        Route::middleware('web')->group(function () use ($routes): void {
            require $routes;
        });
    }

    /**
     * Daftarkan migrasi milik modul, bukan yang ada di `database/migrations`.
     */
    protected function loadModuleMigrations(): void
    {
        $migrations = $this->moduleDirectory().'/Database/Migrations';

        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
