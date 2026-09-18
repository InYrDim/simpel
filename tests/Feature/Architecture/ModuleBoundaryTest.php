<?php

/*
|--------------------------------------------------------------------------
| Batas Modul (Modular Monolith)
|--------------------------------------------------------------------------
|
| Aplikasi ini monolith modular: satu codebase, satu deploy, tapi dibagi
| menjadi modul di `app/Modules/<Nama>`. Modularitas hanya nyata kalau
| batasnya bisa gagal — file ini adalah penjaganya.
|
| Aturan yang ditegakkan:
|   1. Setiap modul punya provider yang terdaftar di bootstrap/providers.php.
|   2. Route modul dimuat oleh provider modul, bukan dari `routes/`.
|   3. Modul hanya boleh memakai namespace miliknya, namespace bersama, dan
|      shared kernel — tidak boleh menyentuh internal modul lain.
|   4. Core tidak boleh menyentuh namespace modul.
|
| Modul baru otomatis ikut diperiksa: tidak ada daftar modul manual di sini.
*/

use App\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Direktori di `app/Modules` yang bukan modul, tapi boleh dipakai semua modul:
 * `Support` (kerangka modul) dan `Contracts` (kontrak publik antar-modul).
 */
const SHARED_MODULE_DIRECTORIES = ['Support', 'Contracts'];

/**
 * Shared kernel: kelas core yang boleh dipakai modul.
 *
 * Setiap entri di sini adalah keputusan sadar bahwa kelas tersebut dipakai
 * lintas modul. Kalau sebuah modul butuh kelas core lain, tambahkan di sini
 * atau — lebih baik — pindahkan kelas itu ke modul yang bersangkutan.
 */
const MODULE_SHARED_KERNEL = [
    'App\Http\Controllers\Controller',
    'App\Models\User',
];

/**
 * Nama semua modul, hasil pemindaian direktori agar modul baru ikut terjaga.
 *
 * @return list<string>
 */
function architecture_moduleDirectories(): array
{
    return collect(File::directories(base_path('app/Modules')))
        ->map(fn (string $directory): string => basename($directory))
        ->reject(fn (string $name): bool => in_array($name, SHARED_MODULE_DIRECTORIES, true))
        ->sort()
        ->values()
        ->all();
}

/**
 * Semua file PHP di bawah satu direktori, dipetakan sebagai path relatif => isi.
 *
 * @return array<string, string>
 */
function architecture_phpSources(string $directory): array
{
    return collect(File::allFiles($directory))
        ->filter(fn ($file): bool => $file->getExtension() === 'php')
        ->mapWithKeys(fn ($file): array => [
            str_replace('\\', '/', $file->getRelativePathname()) => $file->getContents(),
        ])
        ->all();
}

/**
 * Semua referensi namespace `App\...` yang muncul di dalam satu sumber PHP.
 *
 * Deklarasi `namespace` milik file itu sendiri dibuang lebih dulu: itu bukan
 * ketergantungan, hanya pernyataan tempat file berada.
 *
 * @return list<string>
 */
function architecture_appReferences(string $source): array
{
    $source = preg_replace('/^\s*namespace\s+[^;{]+[;{]/m', '', $source) ?? $source;

    preg_match_all('/App(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+/', $source, $matches);

    return array_values(array_unique($matches[0]));
}

/**
 * Apakah referensi menunjuk ke salah satu kelas shared kernel.
 */
function architecture_isSharedKernel(string $reference): bool
{
    foreach (MODULE_SHARED_KERNEL as $kernelClass) {
        if ($reference === $kernelClass || Str::startsWith($reference, $kernelClass.'\\')) {
            return true;
        }
    }

    return false;
}

test('setiap modul punya provider yang terdaftar di bootstrap/providers.php', function () {
    /** @var list<class-string> $registered */
    $registered = require base_path('bootstrap/providers.php');

    $problems = [];

    foreach (architecture_moduleDirectories() as $module) {
        $provider = "App\\Modules\\{$module}\\{$module}ServiceProvider";

        if (! class_exists($provider)) {
            $problems[] = "Modul {$module}: provider {$provider} tidak ditemukan.";

            continue;
        }

        if (! is_subclass_of($provider, ModuleServiceProvider::class)) {
            $problems[] = "Modul {$module}: {$provider} harus extends ModuleServiceProvider.";
        }

        if (! in_array($provider, $registered, true)) {
            $problems[] = "Modul {$module}: {$provider} belum terdaftar di bootstrap/providers.php.";
        }
    }

    expect($problems)->toBe([], implode(PHP_EOL, $problems));
});

test('route modul dimuat oleh provider modul, bukan dari routes/', function () {
    $actions = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route): string => (string) $route->getActionName());

    $problems = [];

    foreach (architecture_moduleDirectories() as $module) {
        if (! is_file(base_path("app/Modules/{$module}/routes.php"))) {
            continue;
        }

        $prefix = "App\\Modules\\{$module}\\";

        if (! $actions->contains(fn (string $action): bool => str_contains($action, $prefix))) {
            $problems[] = "Modul {$module}: routes.php tidak menghasilkan route apa pun — apakah provider-nya memuat route?";
        }
    }

    expect($problems)->toBe([], implode(PHP_EOL, $problems));
});

test('modul hanya memakai namespace sendiri, namespace bersama, dan shared kernel', function () {
    expect(architecture_moduleDirectories())->not->toBeEmpty();

    $sharedPrefixes = array_map(
        fn (string $directory): string => "App\\Modules\\{$directory}\\",
        SHARED_MODULE_DIRECTORIES,
    );

    $problems = [];

    foreach (architecture_moduleDirectories() as $module) {
        $ownPrefix = "App\\Modules\\{$module}\\";

        foreach (architecture_phpSources(base_path("app/Modules/{$module}")) as $path => $source) {
            foreach (architecture_appReferences($source) as $reference) {
                if (Str::startsWith($reference, $ownPrefix)) {
                    continue;
                }

                if (Str::startsWith($reference, $sharedPrefixes)) {
                    continue;
                }

                if (architecture_isSharedKernel($reference)) {
                    continue;
                }

                $problems[] = "app/Modules/{$module}/{$path} → {$reference}";
            }
        }
    }

    expect($problems)->toBe([], implode(PHP_EOL, $problems));
});

test('core tidak mereferensikan namespace modul', function () {
    $sources = array_filter(
        architecture_phpSources(base_path('app')),
        fn (string $path): bool => ! str_starts_with($path, 'Modules/'),
        ARRAY_FILTER_USE_KEY,
    );

    $problems = [];

    foreach ($sources as $path => $source) {
        foreach (architecture_appReferences($source) as $reference) {
            if (str_starts_with($reference, 'App\\Modules\\')) {
                $problems[] = "app/{$path} → {$reference}";
            }
        }
    }

    expect($problems)->toBe([], implode(PHP_EOL, $problems));
});

test('routes inti tidak mereferensikan namespace modul', function () {
    $problems = [];

    foreach (architecture_phpSources(base_path('routes')) as $path => $source) {
        foreach (architecture_appReferences($source) as $reference) {
            if (str_starts_with($reference, 'App\\Modules\\')) {
                $problems[] = "routes/{$path} → {$reference}";
            }
        }
    }

    expect($problems)->toBe([], implode(PHP_EOL, $problems));
});
