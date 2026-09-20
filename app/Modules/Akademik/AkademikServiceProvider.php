<?php

namespace App\Modules\Akademik;

use App\Modules\Akademik\Services\AkademikService;
use App\Modules\Contracts\AkademikContract;
use App\Modules\Support\ModuleServiceProvider;

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

    /**
     * Direktori akar modul Akademik.
     */
    protected function moduleDirectory(): string
    {
        return __DIR__;
    }
}
