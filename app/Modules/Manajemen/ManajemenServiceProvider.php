<?php

namespace App\Modules\Manajemen;

use App\Modules\Support\ModuleServiceProvider;

class ManajemenServiceProvider extends ModuleServiceProvider
{
    /**
     * Direktori akar modul Manajemen.
     */
    protected function moduleDirectory(): string
    {
        return __DIR__;
    }
}
