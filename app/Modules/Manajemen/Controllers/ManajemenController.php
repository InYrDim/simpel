<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ManajemenController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('manajemen/index');
    }
}
