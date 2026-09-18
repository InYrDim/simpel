<?php

use App\Modules\Manajemen\ManajemenServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,

    // Provider modul — didaftarkan eksplisit, satu baris per modul.
    ManajemenServiceProvider::class,
];
