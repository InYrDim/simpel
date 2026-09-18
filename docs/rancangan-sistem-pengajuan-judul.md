# Rancangan: Sistem Pengajuan Judul Skripsi

> Status: **Menunggu review** — belum diimplementasikan.

## 1. Keputusan Arsitektur

| Keputusan | Pilihan | Alasan |
|-----------|---------|--------|
| Bentuk aplikasi | Monolith modular (tetap Laravel + Inertia) | Skala 3 role tidak perlu microservice; deploy & test tetap satu codebase |
| Role & permission | `spatie/laravel-permission` | Role bisa bertambah (dosen pembimbing, kaprodi) tanpa ubah skema; permission per modul |
| Organisasi kode | Folder `app/Modules/<Modul>/` | Modul terkapsulasi; namespace otomatis jalan via PSR-4 `App\` → `app/` tanpa ubah `composer.json`. Tiap modul punya provider sendiri yang memuat route & migrasinya |
| Batas modul | Ditegakkan `tests/Feature/Architecture/ModuleBoundaryTest.php` | Aturan dependensi bisa gagal, bukan sekadar konvensi di dokumen |
| Workflow status | Enum + transisi terpusat (service class) | Modul berikutnya tinggal tiru pola; tidak ada if-else status tersebar |

**Yang sengaja TIDAK dilakukan:** package `nwidart/laravel-modules` (konvensi baru yang bentrok dengan starter kit), pemisahan service/API.

## 2. Struktur Modul

```
app/
├── Modules/
│   ├── Support/
│   │   └── ModuleServiceProvider.php        (kerangka modul: memuat route + migrasi)
│   └── Skripsi/
│       ├── SkripsiServiceProvider.php       (didaftarkan di bootstrap/providers.php)
│       ├── routes.php
│       ├── Database/Migrations/
│       ├── Controllers/
│       │   ├── PengajuanController.php      (mahasiswa)
│       │   ├── ValidasiController.php       (validator)
│       │   └── MonitoringController.php     (admin)
│       ├── Models/
│       │   ├── PengajuanJudul.php
│       │   └── PengajuanJudulRiwayat.php
│       ├── Enums/
│       │   ├── StatusPengajuan.php
│       │   └── AksiValidasi.php
│       ├── Requests/                        (FormRequest per aksi)
│       ├── Policies/
│       │   └── PengajuanJudulPolicy.php     (ownership mahasiswa)
│       └── Services/
│           └── PengajuanJudulService.php    (satu-satunya yang boleh mengubah status)
resources/js/pages/
└── skripsi/
    ├── pengajuan/{index,create,edit,show}.tsx
    ├── validasi/{index,show}.tsx
    └── monitoring/index.tsx
```

**Modul memuat dirinya sendiri** — tiap modul punya satu provider yang mewarisi `App\Modules\Support\ModuleServiceProvider`, dan provider itu didaftarkan eksplisit di `bootstrap/providers.php`. Provider memuat `routes.php` serta `Database/Migrations/` milik modul, sehingga `routes/` dan `database/migrations/` tidak pernah tahu soal modul:

```php
// app/Modules/Skripsi/SkripsiServiceProvider.php
class SkripsiServiceProvider extends ModuleServiceProvider
{
    protected function moduleDirectory(): string
    {
        return __DIR__;
    }
}
```

```php
// bootstrap/providers.php — composition root, satu-satunya tempat core menyebut modul
return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    ManajemenServiceProvider::class,
    SkripsiServiceProvider::class,
];
```

### Aturan dependensi

Ditegakkan `tests/Feature/Architecture/ModuleBoundaryTest.php` — bukan sekadar konvensi di dokumen ini:

| Aturan | Maksud |
|--------|--------|
| Core tidak menyentuh namespace modul | `app/` (di luar `app/Modules/`) dan `routes/` bebas dari referensi `App\Modules\*` |
| Modul hanya memakai namespace sendiri + namespace bersama | `App\Modules\Support\*` (kerangka) dan `App\Modules\Contracts\*` (kontrak publik) boleh dipakai semua modul |
| Modul hanya boleh memakai shared kernel dari core | daftar kelas core yang boleh dipakai modul dideklarasikan eksplisit di test; saat ini `App\Models\User` dan `App\Http\Controllers\Controller` |
| Antar-modul lewat kontrak | Modul tidak menyentuh internal modul lain; kalau perlu, publikasikan di `app/Modules/Contracts/` |

`User` sengaja tetap di core sebagai **shared kernel**: identitas/auth dipakai Fortify, Settings, seeder, dan factory, jadi bukan milik modul Manajemen. Modul baru otomatis ikut diperiksa — tidak ada daftar modul manual di test.

## 3. Role & Permission

### Role

| Role | Cakupan |
|------|---------|
| `mahasiswa` | Mengelola & mengajukan judul miliknya sendiri |
| `validator` | Meninjau & memutuskan semua pengajuan |
| `admin` | Akses penuh (via `Gate::before`) + manajemen pengguna/role (modul Manajemen yang sudah ada) |

### Permission per modul (penamaan: `<modul>.<entitas>.<aksi>`)

| Permission | mahasiswa | validator | admin |
|------------|:---:|:---:|:---:|
| `skripsi.pengajuan.view-own` | ✓ | | ✓* |
| `skripsi.pengajuan.create` | ✓ | | ✓* |
| `skripsi.pengajuan.update-own` | ✓ | | ✓* |
| `skripsi.pengajuan.submit-own` | ✓ | | ✓* |
| `skripsi.pengajuan.view-all` | | ✓ | ✓* |
| `skripsi.pengajuan.validate` | | ✓ | ✓* |
| `skripsi.pengajuan.monitor` | | | ✓* |

\* admin otomatis punya semua permission lewat `Gate::before`, tidak perlu di-assign satu-satu.

**Pembagian tanggung jawab:** permission = akses kasar per endpoint (middleware `can:` / `role:`), **Policy** = aturan ownership (`update-own` dicek di `PengajuanJudulPolicy`). Keduanya dipakai bersamaan.

### Integrasi

- Role di-assign oleh admin lewat halaman **Manajemen → Pengguna → Edit** (yang sudah ada) — ditambah field multi-role.
- `HandleInertiaRequests` share `auth.user.roles` + `auth.user.permissions` → sidebar & tombol di React difilter berdasarkan itu (sumber kebenaran tetap server).

## 4. Model Data

```
users ──1:1── profil_mahasiswa        (nim, program_studi, angkatan) — opsional, lihat §8
users ──1:N── pengajuan_judul         (sebagai pemilik)
users ──1:N── pengajuan_judul         (sebagai validator pemroses)
pengajuan_judul ──1:N── pengajuan_judul_riwayat
users ◇── roles/permissions           (tabel bawaan spatie)
```

### `pengajuan_judul`

| Kolom | Tipe | Catatan |
|-------|------|---------|
| `user_id` | FK users | mahasiswa pemilik |
| `judul` | string | |
| `ringkasan` | text nullable | |
| `kata_kunci` | string nullable | dipisah koma |
| `status` | string | backed enum `StatusPengajuan` |
| `validator_id` | FK users nullable | validator terakhir yang memproses |
| `catatan_validator` | text nullable | catatan terakhir (denormalisasi untuk list; riwayat tetap lengkap) |
| `submitted_at`, `decided_at` | timestamp nullable | |
| timestamps + soft deletes | | |

### `pengajuan_judul_riwayat` (audit trail — pola reusable untuk modul lain)

| Kolom | Tipe |
|-------|------|
| `pengajuan_judul_id` | FK cascade |
| `dari_status` / `ke_status` | string nullable |
| `aksi` | string (`diajukan`, `revisi_diminta`, `disetujui`, `ditolak`, ...) |
| `aktor_id` | FK users |
| `catatan` | text nullable |
| `created_at` | timestamp |

## 5. Alur Status (state machine)

```
                   ── ajukan (mahasiswa) ──────────────────┐
                   │                                       ▼
[draft] ───────────┘                              ┌───────────────┐
                                                   │   diajukan    │
[draft] (mahasiswa bisa edit isi)                  └───────────────┘
                                                    │      │       │
                    ┌───────────────────────────────┘      │       └──────────────────────┐
                    ▼                                      ▼                              ▼
            ┌──────────────┐   ajukan ulang   ┌──────────────┐              ┌──────────────┐
            │    revisi    │ ───────────────► │   diterima   │              │    ditolak   │
            │ (minta revisi│  (mahasiswa)     │   (final)    │              │   (final)    │
            │  validator)  │                  └──────────────┘              └──────────────┘
            └──────────────┘
```

Aturan transisi (dipusatkan di `PengajuanJudulService::transisi()`, dipetakan via enum):

| Dari | Aksi | Oleh | Ke |
|------|------|------|-----|
| `draft` | `ajukan` | pemilik | `diajukan` |
| `diajukan` | `setujui` | validator | `diterima` |
| `diajukan` | `tolak` | validator | `ditolak` |
| `diajukan` | `minta_revisi` | validator | `revisi` |
| `revisi` | `ajukan` | pemilik | `diajukan` |

Aturan pendamping:
- Transisi ilegal → `LogicException` (bukan validasi di controller).
- Mahasiswa hanya boleh edit isi saat `draft` / `revisi` (dicek Policy).
- `ditolak` final → mahasiswa membuat **pengajuan baru**, bukan mengedit yang lama.
- Setiap transisi menulis 1 baris riwayat di dalam DB transaction.

## 6. Routing & Middleware

```php
// app/Modules/Skripsi/routes.php (dimuat SkripsiServiceProvider)
Route::middleware(['auth', 'verified'])->prefix('skripsi')->name('skripsi.')->group(function (): void {
    Route::middleware('permission:skripsi.pengajuan.view-own')->group(function (): void {
        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/baru', [PengajuanController::class, 'create'])->name('pengajuan.create');
        // ... store, edit, update, submit (submit + policy ownership)
    });

    Route::middleware('permission:skripsi.pengajuan.validate')->prefix('validasi')->name('validasi.')->group(function (): void {
        Route::get('/', [ValidasiController::class, 'index'])->name('index');
        Route::get('/{pengajuan}', [ValidasiController::class, 'show'])->name('show');
        Route::post('/{pengajuan}/setujui', ...);
        Route::post('/{pengajuan}/revisi', ...);
        Route::post('/{pengajuan}/tolak', ...);
    });

    Route::middleware('permission:skripsi.pengajuan.monitor')
        ->get('/monitoring', MonitoringController::class)->name('monitoring.index');
});
```

Frontend memakai **Wayfinder** (`@/actions/...`, `@/routes/...`) untuk memanggil route — konsisten dengan pola yang sudah ada.

## 7. Navigasi

Item nav per modul didefinisikan sebagai config (mendekati struktur `types/navigation.ts` yang ada) dengan properti `roles`/`permissions`; sidebar memfilter berdasar props yang di-share Inertia:

```
mahasiswa : [Dashboard, Pengajuan Judul]
validator : [Dashboard, Validasi Pengajuan]
admin     : [Dashboard, Manajemen, Pengajuan (monitoring), Validasi]
```

## 8. Pertanyaan Terbuka (butuh keputusan sebelum implementasi)

1. **Profil mahasiswa** — perlukah tabel `profil_mahasiswa` (NIM, prodi, angkatan) di tahap ini, atau judul dulu?
2. **Alur revisi** — cocok dengan proses di kampus, atau ada tahap lain (mis. pengajuan langsung ke dosen pembimbing sebelum validator)?
3. **Satu pengajuan aktif** — mahasiswa hanya boleh punya 1 pengajuan berstatus `draft/revisi/diajukan`?
4. **Notifikasi** — perlu notifikasi (email/database) saat status berubah, atau UI saja dulu?

## 9. Rencana Implementasi (commit kecil, berurutan)

| # | Tahap | Isi |
|---|-------|-----|
| 1 | Fondasi role | Install spatie, publish migrasi, seeder role + permission, `Gate::before` admin, registrasi middleware alias |
| 2 | UI manajemen role | Assign role di halaman Edit Pengguna yang ada |
| 3 | Share auth ke frontend | `HandleInertiaRequests` share roles/permissions + filter navigasi |
| 4 | Skeleton modul Skripsi | `SkripsiServiceProvider` terdaftar di `bootstrap/providers.php`, `routes.php`, migrasi 2 tabel di `Database/Migrations/`, model, enum |
| 5 | Flow mahasiswa | Service state machine, Policy, Controller + routes pengajuan (draft → submit), pages React |
| 6 | Flow validator | ValidasiController (setujui/revisi/tolak + catatan), pages React |
| 7 | Monitoring admin | Halaman statistik/rekap pengajuan |
| 8 | Test | Pest feature: akses per role, transisi status, ownership |
| 9 | Polish | `wayfinder:generate`, pint, phpstan, build |

## 10. Checklist Menambah Modul Baru (mis. "Ujian Proposal")

1. `app/Modules/<Modul>/<Modul>ServiceProvider.php` → daftarkan di `bootstrap/providers.php`
2. `app/Modules/<Modul>/routes.php` → dimuat provider, tanpa menyentuh `routes/web.php`
3. `app/Modules/<Modul>/{Controllers,Models,Enums,Policies,Services}` + `Database/Migrations/` untuk migrasinya
4. Tambah permission di seeder → assign ke role
5. `resources/js/pages/<modul>/...`
6. Tambah entri navigasi dengan `roles`
7. Feature test per role di `tests/Feature/Modules/<Modul>/`
8. Jalankan `vendor/bin/pest tests/Feature/Architecture/ModuleBoundaryTest.php` → pastikan batas modul masih utuh
