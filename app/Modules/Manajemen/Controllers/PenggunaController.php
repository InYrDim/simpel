<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        /** @var Builder<User> $query */
        $query = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->with('roles:id,name')
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        /** @var LengthAwarePaginator<int, User> $users */
        $users = $query->paginate(10)->withQueryString();

        $users->through(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'role' => $user->getRoleNames()->first(),
        ]);

        return Inertia::render('manajemen/pengguna/index', [
            'users' => $users,
            'roleOptions' => Role::query()->orderBy('name')->pluck('name')->all(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Akun yang dibuat admin langsung dianggap terverifikasi — email-nya
     * dijamin oleh admin, bukan oleh pemilik akun. Satu akun tepat satu
     * peran.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'role' => $this->roleRules(required: true),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles([$validated['role']]);

        return redirect()->route('manajemen.pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna): Response
    {
        return Inertia::render('manajemen/pengguna/edit', [
            'user' => $pengguna->only(['id', 'name', 'email', 'email_verified_at']),
        ]);
    }

    /**
     * `role` opsional: bila tidak dikirim, peran pengguna tidak disentuh;
     * bila dikirim, menggantikan seluruh peran lama.
     */
    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($pengguna->id)],
            'role' => $this->roleRules(required: false),
        ]);

        if (isset($validated['role']) && $pengguna->id === $request->user()?->id && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Anda tidak dapat mencabut peran admin dari akun sendiri.']);
        }

        $pengguna->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (isset($validated['role'])) {
            $pengguna->syncRoles([$validated['role']]);
        }

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

    /**
     * @return list<mixed>
     */
    private function roleRules(bool $required): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            Rule::exists(config('permission.table_names.roles', 'roles'), 'name'),
        ];
    }
}
