<?php

namespace App\Http\Controllers\Manajemen;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PenggunaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<User> $query */
        $query = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('manajemen/pengguna/index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
