# Roadmap — Yang Belum Dikerjakan

Snapshot: 19 September 2026, branch `refactor/arsitektur-modular`.

Catatan ini hanya memuat pekerjaan yang **belum** selesai, beserta alasan dan
titik masuknya. Kondisi yang sudah berjalan tidak didaftarkan di sini.

| #   | Area                                        | Ringkas                                                                      |
| --- | ------------------------------------------- | ---------------------------------------------------------------------------- |
| 1   | [RBAC](#1-rbac)                             | Baru `role:admin`; permission per aksi, policy, dan UI assign role belum ada |
| 2   | [Arsitektur modular](#2-arsitektur-modular) | Batas modul berlaku untuk PHP saja; frontend belum dijaga                    |
| 3   | [Modul Skripsi](#3-modul-skripsi)           | Belum ada — perlu jawaban §8 rancangan lebih dulu                            |
| 4   | [Test & environment](#4-test--environment)  | Test butuh env var khusus; format repo masih gagal                           |
| 5   | [Kebersihan](#5-kebersihan)                 | Sisa tipe/kode milik modul yang masih di core                                |

---

## 1. RBAC

**Kondisi sekarang:** route modul Manajemen dijaga `role:admin` saja
(`app/Modules/Manajemen/routes.php`). Ini pilihan sadar untuk tahap ini.

- [ ] **Permission per aksi** — konvensi `<modul>.<entitas>.<aksi>` di
      `docs/rancangan-sistem-pengajuan-judul.md` §3 belum diimplementasikan.
      `auth.permissions` sudah di-share (`HandleInertiaRequests`) dan bertipe di
      `resources/js/types/auth.ts`, tapi nilainya **selalu `[]`** karena tidak ada
      satu pun permission yang di-seed. Belum ada consumer-nya; baru terpakai saat
      modul Skripsi butuh membedakan aksi dalam satu halaman.
- [ ] **Cara assign role ke user** — belum ada UI-nya. Satu-satunya jalan adalah
      `DatabaseSeeder` (user dev diberi `admin`), tinker, atau factory di test.
      Rancangan §9 tahap 2 menaruhnya di Manajemen → Pengguna → Edit; halaman
      `resources/js/pages/manajemen/pengguna/edit.tsx` sudah ada tapi belum punya
      field role.
- [ ] **Policy** — belum ada satu pun. Aturan ownership baru relevan saat
      pengajuan judul ada; sekarang `PenggunaController` hanya mengandalkan peran
      admin plus satu penjagaan manual (tidak bisa menghapus akun sendiri).
- [ ] **Middleware untuk modul lain** — belum ada modul lain, jadi belum ada
      keputusan apakah modul berikutnya memakai `role:` atau `permission:`.

## 2. Arsitektur modular

**Kondisi sekarang:** `tests/Feature/Architecture/ModuleBoundaryTest.php`
menegakkan 5 aturan batas untuk PHP: registrasi provider, route dimuat provider,
modul tidak menyentuh internal modul lain, core tidak menyentuh modul, dan
`routes/` tidak menyentuh modul.

- [ ] **Batas frontend belum ditegakkan.** Penjaga hanya memindai `app/` (di luar
      `app/Modules/`) dan `routes/`. Di frontend,
      `resources/js/lib/module-navigation.ts` adalah satu-satunya composition root
      yang boleh menyebut modul — setara `bootstrap/providers.php` — tapi tidak
      ada apa pun yang memaksanya. Komponen core bebas `import` dari
      `@/pages/<modul>/...` (persis yang dulu terjadi: `components/data-table.tsx`
      mengimpor tipe dari `components/users/user-columns.tsx`). Perluas penjaga ke
      `resources/js/`.
- [ ] **`app/Modules/Contracts/` masih kosong.** Aturan batas mengizinkan modul
      memakai `App\Modules\Contracts\*`, tapi belum ada kontrak yang
      dipublikasikan — wajar karena baru ada satu modul. Kontrak pertama
      kemungkinan dibutuhkan saat modul lain perlu membaca data pengajuan.
- [ ] **Modul Manajemen belum punya domain sendiri.** Isinya masih `Controllers/`
      saja: tidak ada `Models/`, `Services/`, atau `Requests/`. Logika
      daftar/edit/hapus menumpuk di controller. `App\Models\User` tetap di core
      secara sadar (shared kernel), jadi yang perlu pindah ke modul adalah
      _perilaku_-nya, bukan modelnya.
- [ ] **Belum ada `Database/Migrations/` di modul.** `ModuleServiceProvider` sudah
      memuatnya bila ada, tapi belum pernah diuji karena modul Manajemen tidak
      punya migrasi. Migrasi Skripsi (2 tabel) akan jadi pengujian pertama.
- [ ] **Modul Skripsi belum ada** — lihat bagian 3. Selama baru ada satu modul,
      pola modular ini belum terbukti menskalakan.

## 3. Modul Skripsi

Belum diimplementasikan sama sekali. Rancangan lengkap ada di
[`docs/rancangan-sistem-pengajuan-judul.md`](../docs/rancangan-sistem-pengajuan-judul.md),
berstatus **menunggu review**.

- [ ] **Jawab 4 pertanyaan terbuka di §8** sebelum mulai: perlukah tabel
      `profil_mahasiswa` (NIM, prodi, angkatan); apakah alur revisi cocok dengan
      proses kampus; apakah mahasiswa hanya boleh punya 1 pengajuan aktif; perlu
      notifikasi atau UI saja dulu.
- [ ] **Modul ini yang akan menguji dua klaim yang sengaja belum dibangun:**
      permission per aksi, dan migrasi yang tinggal di dalam modul.

## 4. Test & environment

### 4a. Test suite butuh env var khusus

Shell ini mengekspor isi `.env`, dan dua nilai merusak test:

- `APP_ENV=local` masuk ke `$_SERVER`. `Env` Laravel membaca `$_SERVER` lebih
  dulu, dan PHPUnit tidak menimpanya — `force="true"` di `phpunit.xml` hanya
  memperbaiki `getenv`/`$_ENV`, bukan `$_SERVER` (sudah diuji, lalu perubahan itu
  dikembalikan agar tidak ada konfigurasi menyesatkan). Akibatnya
  `runningUnitTests()` = false, bypass CSRF bawaan Laravel mati, dan **semua
  request non-GET gagal 419**.
- `SESSION_PATH=/` di-mangle Git Bash (MSYS) menjadi `C:/Program Files/Git/`
  karena namanya berakhiran `_PATH`; Symfony menolak path berisi spasi sehingga
  aplikasi 500.

Perintah yang membuat suite hijau:

```bash
MSYS_NO_PATHCONV=1 APP_ENV=testing php artisan test
```

- [ ] **Perbaikan akarnya ada di sisi shell, bukan repo** — dan belum dilakukan.
      Berhenti mengekspor `.env` (mis. `set -a; source .env; set +a`) atau jangan
      ekspor `APP_ENV` dan `SESSION_PATH`. Kalau ingin tahan lingkungan, opsi
      repo-nya adalah menegakkan `$_SERVER['APP_ENV'] = 'testing'` di
      `tests/Pest.php`.

### 4b. Formatter markdown tidak idempoten

Oxfmt menambah indentasi 4 spasi pada konten lanjutan di dalam item daftar
**setiap kali dijalankan**, tanpa henti. Akibatnya `vp fmt --write` tidak pernah
konvergen dan `npm run check` tidak akan pernah lulus untuk file tersebut.

Dua pemicu yang sudah diisolasi, keduanya membuat konten jadi "blok kedua" di
dalam satu item daftar:

1. paragraf kedua di dalam satu item, dipisah baris kosong;
2. blok kode berpagar di dalam satu item.

Bukti: tiga kali `--write` menghasilkan tiga hash berbeda, dan diff antar-pass
menunjukkan indentasi bergerak 34 → 38 → 42 spasi. File catatan ini sempat
terkena; sekarang setiap butir hanya satu paragraf dan semua blok kode ditaruh di
luar daftar.

- [ ] **Laporkan ke Vite+ / Oxfmt** atau tambahkan pengecualian di
      `vite.config.ts`. Sementara ini, jaga aturan "satu butir satu paragraf"
      untuk file `.md` di repo ini.

### 4c. Format repo

- [ ] **`npm run check` masih gagal untuk 19 file** yang sudah bermasalah sebelum
      pekerjaan RBAC. Berbeda dengan 4b, file-file ini konvergen (sudah diuji pada
      dua contoh), artinya sekali `npm run check:fix` membereskan semuanya. Belum
      dijalankan agar diff RBAC tetap fokus.

Daftarnya:

- `.ai/rules/index.md`, `.ai/rules/ui-ux.md`,
  `docs/rancangan-sistem-pengajuan-judul.md`
- `components/appearance-tabs.tsx`, `components/delete-user.tsx`,
  `components/manage-two-factor.tsx`, `components/passkey-item.tsx`,
  `components/two-factor-recovery-codes.tsx`,
  `components/two-factor-setup-modal.tsx`
- `pages/auth/confirm-password.tsx`, `pages/auth/forgot-password.tsx`,
  `pages/auth/login.tsx`, `pages/auth/register.tsx`,
  `pages/auth/reset-password.tsx`, `pages/auth/two-factor-challenge.tsx`
- `pages/settings/profile.tsx`, `pages/settings/security.tsx`
- `pages/manajemen/index.tsx`, `pages/manajemen/pengguna/edit.tsx`

**Sisa pekerjaan test lain:**

- [ ] **Filter navigasi frontend belum ada test-nya** — repo ini belum punya
      runner test frontend, jadi `resources/js/lib/module-navigation.ts` hanya
      terverifikasi lewat `tsc` dan `vp check`. Kalau filter ini bertambah rumit
      (permission, anak menu), pertimbangkan menambah runner.
- [ ] **Seeder belum punya test langsung.** `RolePermissionSeeder` hanya diuji
      tidak langsung lewat `beforeEach` di `ManajemenAccessTest`.

## 5. Kebersihan

- [ ] **`PaginatedUsers` masih di `resources/js/types/index.ts`** — tipe milik
      modul Manajemen yang tertinggal di core. Pindahkan ke
      `resources/js/pages/manajemen/`.
- [ ] **Filter tombol berbasis role belum ada.** Baru navigasi yang disaring. Saat
      ini tidak ada bedanya karena halaman Manajemen hanya bisa dibuka admin; baru
      terasa saat ada halaman yang diakses lebih dari satu role.
- [ ] **`.env.example` belum menandai** mana env var yang aman diekspor ke shell —
      berkaitan dengan masalah di 4a.
