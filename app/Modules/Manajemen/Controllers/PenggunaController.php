<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function edit(User $pengguna): Response
    {
        return Inertia::render('manajemen/pengguna/edit', [
            'user' => $pengguna->only(['id', 'name', 'email', 'email_verified_at']),
        ]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($pengguna->id)],
        ]);

        $pengguna->update($validated);

        return redirect()->route('manajemen.pengguna.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna): RedirectResponse
    {
        if ($pengguna->id === auth()->id()) {
            return redirect()->route('manajemen.pengguna.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $pengguna->delete();

        return redirect()->route('manajemen.pengguna.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
