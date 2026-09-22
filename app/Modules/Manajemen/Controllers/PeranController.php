<?php

namespace App\Modules\Manajemen\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PeranController extends Controller
{
    private const PROTECTED_ROLES = ['admin'];

    public function index(): Response
    {
        /** @var Builder<Role> $query */
        $query = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->orderBy('name');

        $roles = $query->get()->map(
            fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'jumlah_pengguna' => $role->users_count,
                'permissions' => $role->permissions->pluck('name')->sort()->values()->all(),
                'protected' => in_array($role->name, self::PROTECTED_ROLES, true),
            ],
        )->all();

        $permissionOptions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->map(
                fn (string $name): array => [
                    'name' => $name,
                    'label' => $this->labelPermission($name),
                ],
            )
            ->values()
            ->all();

        return Inertia::render('manajemen/peran/index', [
            'roles' => $roles,
            'permissionOptions' => $permissionOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $permissions = $request->input('permissions', []);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(config('permission.table_names.roles', 'roles'))],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists(config('permission.table_names.permissions', 'permissions'), 'name')],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($permissions);

        return redirect()->route('manajemen.peran.index')
            ->with('success', 'Peran berhasil ditambahkan.');
    }

    public function update(Request $request, Role $peran): RedirectResponse
    {
        $permissions = $request->input('permissions', []);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(config('permission.table_names.roles', 'roles'))->ignore($peran->id)],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', Rule::exists(config('permission.table_names.permissions', 'permissions'), 'name')],
        ]);

        $peran->update(['name' => $validated['name']]);
        $peran->syncPermissions($permissions);

        return redirect()->route('manajemen.peran.index')
            ->with('success', 'Peran berhasil diperbarui.');
    }

    public function destroy(Role $peran): RedirectResponse
    {
        if (in_array($peran->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('manajemen.peran.index')
                ->with('error', "Peran <strong>{$peran->name}</strong> tidak dapat dihapus.");
        }

        if ($peran->users()->exists()) {
            return redirect()->route('manajemen.peran.index')
                ->with('error', "Peran <strong>{$peran->name}</strong> masih memiliki pengguna.");
        }

        $peran->delete();

        return redirect()->route('manajemen.peran.index')
            ->with('success', 'Peran berhasil dihapus.');
    }

    private function labelPermission(string $name): string
    {
        return match ($name) {
            'skripsi.pengajuan.submit' => 'Submit pengajuan',
            'skripsi.pengajuan.verify' => 'Verifikasi pengajuan',
            'skripsi.pengajuan.decide' => 'Menentukan pengajuan',
            'skripsi.pengajuan.revise' => 'Meminta revisi',
            default => $name,
        };
    }
}
