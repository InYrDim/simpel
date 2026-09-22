# Module: Manajemen

## Owns

- Database tables: `manajemen_jurusans` (data referensi jurusan beserta ketua/sekretaris)
- Core domain concepts: pengelolaan admin-level — akun pengguna (`users`), jurusan, dan role/permission (Spatie)

## Public interface (Contracts/)

- Tidak ada (belum ada konsumen; seluruh fitur Manajemen dipagari `role:admin`)

## Allowed dependencies

- `App\Modules\Support\*` (kerangka modul)
- Shared kernel: `App\Http\Controllers\Controller`, `App\Models\User`
- Package shared: `Spatie\Permission\Models\Role` & `Permission` — tabel `roles`, `permissions`, `model_has_roles` adalah infrastruktur core (milk Spatie), setara `users`
- Tidak bergantung pada modul lain

## Events published

- Tidak ada

## Events consumed

- Tidak ada

## Explicitly NOT exposed

- Model `Jurusan` — internal modul; belum ada kontrak publik (akankan mencuat saat modul lain butuh referensi jurusan)
- Manajemen role `admin` — terproteksi dari penghapusan (`PeranController::PROTECTED_ROLES`); role yang masih memiliki pengguna tidak dapat dihapus
- Penetapan permission per modul dikelola di core `Database\Seeders\RolePermissionSeeder`, bukan oleh modul ini — modul Manajemen hanya menata-ulang assignment-nya lewat UI admin

## Notes for maintainers

- Tabel `manajemen_*` hanya di-query dari modul ini (boundary rule #2); jurusan sengaja berisi teks (nama/NIP) tanpa FK agar tidak mengikat modul lain.
- `users` adalah tabel core, jadi CRUD akun di modul ini langsung memakai `App\Models\User`.
- UI dan route Manajemen sepenuhnya `role:admin`; `roles` di nav hanya untuk menyembunyikan UI, bukan pengaman.
