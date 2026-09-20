<?php

/*
|--------------------------------------------------------------------------
| Layer Modul — Pest Arch (Modular Monolith)
|--------------------------------------------------------------------------
|
| Menegakkan aturan layer modular monolith dalam bahasa yang sama dengan
| test suite yang sudah berjalan. Melengkapi (bukan menggantikan)
| `ModuleBoundaryTest.php`, yang mengecek aspek non-namespace: provider
| terdaftar, route dimuat provider, dan namespace `routes/`.
|
| Aturan ditegakkan dari sisi penyedia (supplier-side):
| "siapa yang boleh MEMAKAI kelas modul X" — bukan dari sisi konsumen.
| Ini menghindari false positive dari helper function global
| (`redirect()`, `auth()`, dsb.) dan dependensi vendor yang memang sah.
|
| Modul baru otomatis tercakup — dipindai dari direktori `app/Modules`
| (filesystem murni, karena daftar test dievaluasi sebelum container
| Laravel siap). Tidak ada daftar modul manual di sini.
*/

/**
 * Nama semua modul, hasil pemindaian direktori `app/Modules`.
 *
 * `Support` (kerangka modul) dan `Contracts` (kontrak publik) dikecualikan —
 * keduanya punya aturan tersendiri di bawah.
 *
 * @return list<string>
 */
function moduleLayer_moduleNames(): array
{
    $modulesDirectory = dirname(__DIR__, 3).'/app/Modules';

    return collect(scandir($modulesDirectory) ?: [])
        ->filter(fn (string $name): bool => is_dir($modulesDirectory.'/'.$name))
        ->reject(fn (string $name): bool => in_array($name, ['.', '..', 'Support', 'Contracts'], true))
        ->sort()
        ->values()
        ->all();
}

/**
 * Namespace yang boleh memakai namespace bersama (`Support`, `Contracts`):
 * semua modul, namespace bersama itu sendiri, dan test.
 *
 * @return list<string>
 */
function moduleLayer_sharedConsumers(): array
{
    return [
        ...array_map(fn (string $module): string => "App\\Modules\\{$module}", moduleLayer_moduleNames()),
        'App\\Modules\\Support',
        'App\\Modules\\Contracts',
        'Tests', // test boleh menyentuh apa saja
    ];
}

foreach (moduleLayer_moduleNames() as $moduleLayerModule) {
    arch("internal modul [{$moduleLayerModule}] hanya boleh dipakai oleh modul itu sendiri", function () use ($moduleLayerModule): void {
        expect("App\\Modules\\{$moduleLayerModule}")
            ->toOnlyBeUsedIn("App\\Modules\\{$moduleLayerModule}");
    });
}

arch('namespace bersama Support hanya dipakai modul dan test, bukan core', function (): void {
    expect('App\\Modules\\Support')
        ->toOnlyBeUsedIn(moduleLayer_sharedConsumers());
});

arch('namespace bersama Contracts hanya dipakai modul dan test, bukan core', function (): void {
    expect('App\\Modules\\Contracts')
        ->toOnlyBeUsedIn(moduleLayer_sharedConsumers());
});
