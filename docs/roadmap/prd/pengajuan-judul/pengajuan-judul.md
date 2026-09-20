# PRD — Modul Pengajuan Judul Skripsi

> Status: **Final** · Cabang: `feat/pengajuan` · Sumber: `docs/roadmap/notes/prompt.md` + wawancara `docs/research/raw/notes/wawancara/latar-belakang-awal.md`
>
> PRD ini hanya mencakup **flow pengajuan judul (Mahasiswa → Admin → Validator)**. Fitur lain hasil wawancara (similaritas judul, beban dosen, rekomendasi validator) ada di luar scope dan dicatat sebagai _future work_ (§9).

## 1. Ringkasan

Mahasiswa mengajukan **tepat 3 judul** skripsi beserta berkas surat pengajuan. Admin memverifikasi kelengkapan berkas, lalu meneruskan ke Validator (dosen) yang memeriksa dan memutuskan persetujuan judul. Hasil akhir: satu judul **disetujui** atau pengajuan **ditolak** (mahasiswa bisa membuat pengajuan baru).

## 2. Role & Aktor

Role sudah dikelola Spatie Permission (`database/seeders/RolePermissionSeeder.php`): `mahasiswa`, `admin`, `validator`.

| Aktor     | Peran dalam flow                                                                       |
| --------- | -------------------------------------------------------------------------------------- |
| Mahasiswa | Membuat pengajuan (3 judul + berkas), memantau status, melihat daftar judul terdata    |
| Admin     | Memverifikasi kelengkapan administratif, menugaskan validator, melihat semua pengajuan |
| Validator | Mereview substansi judul, menyetujui/menolak dengan catatan                            |

## 3. Data Model

Karena modul **Akademik** terpisah dari **Skripsi** (§7, §8), entitas lintas modul dirujuk lewat **ID + Contract, bukan FK constraint** (boundary rule #2: dilarang menambah FK terhadap tabel modul lain). Integritas dijaga di layer validasi/Action.

### 3.1 Modul Akademik — Mahasiswa (profil akademik)

Relasi ke tabel `users` (milik core, bukan milik modul lain).

| Field         | Tipe       | Aturan                              |
| ------------- | ---------- | ----------------------------------- |
| `user_id`     | FK → users | unique, required                    |
| `nama`        | string     | required                            |
| `nim`         | string     | required, unique                    |
| `dosen_pa_id` | ID → dosen | required (Dosen Penasehat Akademik) |
| `prodi`       | string     | opsional (disarankan)               |
| `angkatan`    | year       | opsional (disarankan)               |

### 3.2 Modul Akademik — Dosen

Bukan role login; entitas referensi untuk Dosen PA, validator, pembimbing, dan penguji.

| Field    | Tipe   | Aturan                                             |
| -------- | ------ | -------------------------------------------------- |
| `nama`   | string | required                                           |
| `nip`    | string | required, unique                                   |
| `bidang` | string | required (dipakai admin saat menugaskan validator) |

### 3.3 Modul Skripsi — PengajuanJudul

| Field                                       | Tipe                       | Aturan                                                                                                        |
| ------------------------------------------- | -------------------------- | ------------------------------------------------------------------------------------------------------------- |
| `user_id`                                   | FK → users                 | required, user penyusun pengajuan (dipakai policy & notifikasi)                                               |
| `mahasiswa_id`                              | ID (tanpa FK lintas modul) | required, merujuk Mahasiswa milik Akademik                                                                    |
| `berkas_path`                               | string                     | path PDF hasil upload, required                                                                               |
| `berkas_original_name`                      | string                     | nama asli berkas, untuk ditampilkan                                                                           |
| `status`                                    | enum                       | `diajukan`, `diverifikasi_admin`, `ditolak_admin`, `diverifikasi_validator`, `disetujui`, `ditolak_validator` |
| `catatan_admin`                             | text                       | nullable, wajib saat ditolak admin                                                                            |
| `catatan_validator`                         | text                       | nullable, wajib saat ditolak validator                                                                        |
| `validator_id`                              | ID (tanpa FK lintas modul) | nullable, diisi admin saat verifikasi                                                                         |
| `submitted_at`, `verified_at`, `decided_at` | timestamp                  | audit ringan                                                                                                  |

### 3.4 Modul Skripsi — JudulPengajuan (detail 3 judul)

| Field          | Tipe                 | Aturan   |
| -------------- | -------------------- | -------- |
| `pengajuan_id` | FK → pengajuan_judul | required |
| `judul`        | string               | required |
| `deskripsi`    | text                 | required |
| `topik`        | string               | required |
| `urutan`       | int                  | 1–3      |

### 3.5 Penugasan (hasil akhir)

Disimpan pada `JudulPengajuan` yang terpilih (atau tabel `penugasan` terpisah):

- `dosen_pembimbing_1`, `dosen_pembimbing_2`, `dosen_penguji_1`, `dosen_penguji_2` → ID dosen (tanpa FK lintas modul), nullable, diisi saat judul disetujui.

## 4. Status & Transisi

```
[tidak ada pengajuan aktif] --submit mahasiswa--> diajukan
diajukan        --admin setujui-->    diverifikasi_admin  (admin pilih validator)
diajukan        --admin tolak-->      ditolak_admin       (catatan wajib)
diverifikasi_admin --validator setujui--> disetujui
diverifikasi_admin --validator tolak-->  ditolak_validator  (catatan wajib)
```

- **Submit ulang setelah ditolak = pengajuan baru.** Riwayat pengajuan lama tetap terarsip sebagai audit trail; mahasiswa tidak bisa mengedit pengajuan yang sudah diputuskan.
- Status awal yang dilihat mahasiswa sebelum pernah submit: **"Belum mengajukan"** (derived dari tidak adanya pengajuan, bukan nilai enum tersimpan).
- Tiap transisi memicu **notifikasi in-app** (§5.4).

## 5. Halaman & Navigasi

Menu sidebar **Skripsi** dengan submenu (mengikuti pola `pages/<modul>/navigation.ts` + `@/lib/module-navigation`, difilter role):

### 5.1 Submenu Pengajuan (role: mahasiswa)

**Melihat status** — kartu/panel berisi status pengajuan terkini (`belum mengajukan`, `diverifikasi admin`, `diverifikasi validator`, `disetujui`, `ditolak admin`, `ditolak validator`), 3 judul yang diajukan, berkas, dan catatan penolakan bila ada. Riwayat pengajuan sebelumnya (yang ditolak) tampil ringkas di bawah status terkini.

**Mengajukan** — tombol submit:

- Disabled kecuali status _belum mengajukan_ atau _ditolak_ (admin/validator); dicek ulang di server (§6.4).
- Dialog **step-by-step** (satu dialog berkelanjutan, 3 langkah):
    - **Step 1 — Detail Pengajuan:** untuk masing-masing dari 3 judul: input `Judul`, `Deskripsi`, `Topik`.
    - **Step 2 — Berkas:** link download **template pengajuan (.docx)**, input upload berkas hasil pengisian template — **maks 5 MB, hanya PDF**.
    - **Step 3 — Verifikasi:** ringkasan 3 judul + berkas terpilih (nama, ukuran) untuk dicek sebelum submit; tombol submit final.

### 5.2 Submenu Daftar Judul (role: admin & validator)

- Tabel daftar judul (semua pengajuan yang sudah diverifikasi admin / status aktif), dengan pagination + pencarian sederhana.
- Klik baris → **modal detail**: Judul, Deskripsi, Topik, Dosen Pembimbing 1 & 2, Dosen Penguji 1 & 2, NIM. Field penugasan kosong bila belum diisi.

### 5.3 Aksi Admin & Validator (pendukung, tanpa submenu baru)

- Admin: daftar pengajuan masuk (status `diajukan`) → aksi **Verifikasi** (setujui + pilih validator dari daftar dosen, atau tolak + catatan wajib). Daftar dosen diambil via `AkademikContract`.
- Validator: melihat penugasan untuk dirinya dan aksi **Setujui/Tolak** (+ catatan wajib saat menolak).

### 5.4 Notifikasi In-App

Menggunakan channel **database notification** Laravel (bel notifikasi di UI, tanpa email):

| Kejadian                            | Penerima                     |
| ----------------------------------- | ---------------------------- |
| Pengajuan dibuat                    | Admin                        |
| Diverifikasi + ditugaskan validator | Mahasiswa, Validator terkait |
| Ditolak admin (dengan catatan)      | Mahasiswa                    |
| Disetujui / ditolak validator       | Mahasiswa                    |

Penerima diresolusi tanpa menyentuh modul lain: user mahasiswa dari kolom `user_id` di `PengajuanJudul` (§3.3), penerima role admin via `User::role('admin')` (`App\Models\User` adalah shared kernel yang boleh dipakai modul).

## 6. Validasi & Aturan Bisnis

1. Tepat **3 judul** per pengajuan — tidak kurang, tidak lebih (array validation `min:3`, `max:3`, tiap item punya judul/deskripsi/topik).
2. Berkas: `file` `mimes:pdf` `max:5120` (KB), disimpan di disk privat (`storage/app/private/pengajuan/`), nama asli tidak dipakai langsung sebagai path. Template yang didownload mahasiswa berformat **DOCX**.
3. Satu mahasiswa hanya punya **satu pengajuan aktif** (status `diajukan` atau `diverifikasi_admin` atau `diverifikasi_validator`). Pengajuan `ditolak_*`/`disetujui` tidak menghalangi pengajuan baru.
4. Submit hanya diizinkan saat tidak ada pengajuan aktif — dicek ulang di Action di server, bukan hanya UI disabled.
5. Transisi status diverifikasi di Action class (bukan di controller), dengan guard status asal.
6. `catatan_*` wajib saat aksi menolak.
7. Akses halaman & endpoint dijaga middleware `role:mahasiswa` / `role:admin` / `role:validator`; mahasiswa hanya bisa melihat/mengubah pengajuannya sendiri (policy).
8. Data Mahasiswa/Dosen diakses modul Skripsi hanya melalui `App\Modules\Contracts\AkademikContract` — tidak boleh mengimpor Eloquent model Akademik langsung; ModuleBoundaryTest akan menggagalkan impor `App\Modules\Akademik\*` dari modul lain.

## 7. Arsitektur (Modular Monolith)

Dua modul baru. **Catatan penting:** struktur modul mengikuti konvensi yang benar-benar dipakai repo ini (`app/Modules/Manajemen` + kerangka `App\Modules\Support\ModuleServiceProvider` + penjaga `tests/Feature/Architecture/ModuleBoundaryTest.php`) — bukan template folder yang lebih dalam (Domain/Infrastructure/Http) di `.ai/rules/modular-monolith-module-structure.md`, yang menyimpang dari implementasi. Yang ditegakkan test adalah:

- Provider modul: `app/Modules/<Nama>/<Nama>ServiceProvider.php` di **akar modul**, extends `ModuleServiceProvider`, didaftarkan di `bootstrap/providers.php`.
- Route modul: satu file `app/Modules/<Nama>/routes.php`, dimuat provider (otomatis dibungkus middleware `web`).
- Migrasi modul: `app/Modules/<Nama>/Database/Migrations/`, dimuat otomatis.
- Struktur internal rata (flat): `Controllers/`, `Models/`, `Enums/`, `Policies/`, `Services/`, `Requests/`.
- Kontrak antar-modul: interface di namespace bersama **`App\Modules\Contracts\`** (direktori `app/Modules/Contracts`) — satu-satunya namespace modul yang boleh diimpor modul lain, di luar shared kernel `App\Http\Controllers\Controller` dan `App\Models\User`.
- Setiap modul tetap menulis `CONTRACT.md` di akar modulnya (dokumentasi, per rule file).

### 7.1 Modul `App\Modules\Akademik`

```
app/Modules/Akademik/
├── AkademikServiceProvider.php    # extends ModuleServiceProvider → bootstrap/providers.php
├── CONTRACT.md
├── routes.php                     # CRUD dosen & mahasiswa — role:admin
├── Database/Migrations/           # akademik_dosens, akademik_mahasiswas
├── Controllers/
├── Models/                        # Dosen, Mahasiswa (Mahasiswa.user_id → users, core)
├── Enums/
├── Policies/
└── Services/                      # implementasi AkademikContract
```

- Tabel diberi prefix modul (`akademik_*`) agar tidak rancu. FK ke `users` diperbolehkan karena `users` tabel core, bukan tabel modul lain.
- Memublikasikan `App\Modules\Contracts\AkademikContract`: resolusi mahasiswa (by user/nim), daftar dosen, detail dosen by ID — mengembalikan DTO/array sederhana yang juga didefinisikan di `App\Modules\Contracts\`.

### 7.2 Modul `App\Modules\Skripsi`

```
app/Modules/Skripsi/
├── SkripsiServiceProvider.php     # extends ModuleServiceProvider → bootstrap/providers.php
├── CONTRACT.md
├── routes.php                     # guard role:mahasiswa / role:admin / role:validator
├── Database/Migrations/           # skripsi_pengajuan_juduls, skripsi_judul_pengajuans
├── Controllers/
├── Requests/                      # validasi (tepat 3 judul, PDF maks 5MB)
├── Models/                        # PengajuanJudul, JudulPengajuan
├── Enums/                         # StatusPengajuan
├── Policies/                      # mahasiswa hanya melihat pengajuannya sendiri
├── Services/                      # operasi bisnis: SubmitPengajuan, VerifikasiAdmin, PutusanValidator
└── Listeners/                     # kirim database notification dari domain event
```

- Controller hanya: validate → panggil Service/Action → return. Transisi status dengan guard status asal ada di Service/Action, bukan controller.
- Domain event `PengajuanDiajukan`, `PengajuanDiverifikasi`, `PengajuanDiputus` — listener mengirim database notification (§5.4).
- Mengonsumsi `App\Modules\Contracts\AkademikContract` untuk data mahasiswa/dosen — dilarang mengimpor model Akademik langsung (ModuleBoundaryTest menggagalkan).
- Jika kelak Skripsi perlu diekspos ke modul lain, interface-nya juga ditempatkan di `App\Modules\Contracts\`.

### 7.3 Frontend & navigasi

- Halaman Inertia di **`resources/js/pages/skripsi/`** (root `resources/`, bukan di dalam direktori modul — mengikuti pola `pages/manajemen/`), beserta komponen modul-specific.
- Nav item modul: `resources/js/pages/skripsi/navigation.ts`, lalu satu baris di `resources/js/lib/module-navigation.ts`; item difilter per role di sisi klien, server tetap sumber kebenaran.
- Route function Wayfinder (`@/routes/skripsi/...`) dihasilkan dari controller Skripsi — dipakai navigation & halaman, bukan URL hardcoded.
- Template DOCX ditempatkan sebagai aset statis yang disajikan via route download terproteksi (login + role mahasiswa).

### 7.4 Checks yang wajib hijau

- `php artisan test` — termasuk `tests/Feature/Architecture/ModuleBoundaryTest.php`, penjaga batas modul aktual repo ini (provider terdaftar, route dimuat provider, modul tidak mengimpor internal modul lain, core tidak menyentuh modul).
- `composer run lint:check` (Pint) dan `composer run types:check` (PHPStan/Larastan).
- `npm run check` (lint frontend) dan `npm run types:check` (tsc).

> `deptrac.yaml` dan `eslint-plugin-boundaries` dari kit README **belum terpasang** di repo ini (tidak ada di composer/package deps). Aturan "Required checks" di `.ai/rules/modular-monolith-boundaries.md` yang menyebut keduanya baru berlaku setelah tool-nya benar-benar diinstal.

### 7.5 Urutan implementasi

1. **Akademik dulu** — migrasi, model, CRUD admin, `AkademikContract` (+ CONTRACT.md).
2. **Skripsi kemudian** — konsumsi contract. Ikuti playbook: satu modul per sesi kerja, jangan membangun keduanya dalam satu langkah besar.
3. Registrasi permission `skripsi.pengajuan.submit`, `skripsi.pengajuan.verify`, `skripsi.pengajuan.decide` di `RolePermissionSeeder` (core `database/seeders` — diperbolehkan, sesuai docblock seeder).

## 8. Keputusan (diputuskan 20 Sep 2026)

| #   | Pertanyaan                             | Keputusan                                                                                                                      |
| --- | -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| 1   | Mekanisme submit ulang setelah ditolak | **Pengajuan baru** — riwayat lama terarsip sebagai audit trail, pengajuan lama tidak bisa diedit                               |
| 2   | Penempatan model Mahasiswa & Dosen     | **Modul Akademik terpisah** — Skripsi mengonsumsi via `AkademikContract`; siap untuk fitur beban dosen & rekomendasi validator |
| 3   | Notifikasi perubahan status            | **In-app saja** — database notification Laravel, tanpa email                                                                   |
| 4   | Format template berkas                 | **DOCX** — mahasiswa mengisi template Word lalu upload sebagai PDF                                                             |

## 9. Out of Scope (future work)

- Deteksi similaritas judul (embedding + LLM reranker) — topik thesis, lihat `docs/research/`.
- Sebaran beban & statistik dosen pembimbing/penguji.
- Rekomendasi validator berdasarkan bidang/topik.
- Revisi judul setelah disetujui, bimbingan, dan ujian.
