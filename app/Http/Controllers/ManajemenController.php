<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ManajemenController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('manajemen/index');
    }
}
