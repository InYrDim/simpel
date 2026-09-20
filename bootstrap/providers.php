<?php

use App\Modules\Akademik\AkademikServiceProvider;
use App\Modules\Manajemen\ManajemenServiceProvider;
use App\Modules\Skripsi\SkripsiServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    // Provider modul — didaftarkan eksplisit, satu baris per modul.
    AkademikServiceProvider::class,
    ManajemenServiceProvider::class,
    SkripsiServiceProvider::class,
];
