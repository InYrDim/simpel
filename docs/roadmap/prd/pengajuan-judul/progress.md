# Progress — Modul Pengajuan Judul Skripsi

> Cabang: `feat/pengajuan` · Terakhir diperbarui: **20 Sep 2026 (sesi 2)**
>
> Acuan: [pengajuan-judul.md](pengajuan-judul.md). Urutan implementasi mengikuti §7.5 — satu modul per sesi kerja.

## Ringkasan

| Tahap (§7.5)                      | Status         | Commit                                       |
| --------------------------------- | -------------- | -------------------------------------------- |
| 1. Modul Akademik (§7.1)          | ✅ **Selesai** | `9bddf4a` + ekstensi validator-link (sesi 2) |
| 2. Modul Skripsi (§7.2)           | ✅ **Selesai** | sesi 2 — belum di-commit                     |
| 3. Registrasi permission (§7.5.3) | ✅ **Selesai** | sesi 2 — `RolePermissionSeeder`              |

Checks wajib hijau (§7.4) pada akhir sesi 2: `pint` ✅ · `phpstan` ✅ · `php artisan test` **110 passed** ✅ · `npm run check` ✅ · `npm run types:check` ✅

## ✅ Selesai — Modul Akademik (sesi 1, commit `9bddf4a`)

### Backend (§7.1)

- [x] `app/Modules/Akademik/AkademikServiceProvider.php` — extends `ModuleServiceProvider`, terdaftar di `bootstrap/providers.php`; binding `AkademikContract` → `AkademikService`
- [x] Migrasi `akademik_dosens` + `akademik_mahasiswas` — FK ke `users` (core) diperbolehkan; **tidak ada FK lintas modul** (boundary rule #2)
- [x] Model `Dosen`, `Mahasiswa` + factory dalam modul (`Database/Factories/`, `$table` & `$model` eksplisit karena prefix modul)
- [x] Kontrak publik di namespace bersama `App\Modules\Contracts\`: `AkademikContract`, `MahasiswaDTO`, `DosenDTO`, `DosenDTOList`
- [x] `AkademikService` — implementasi kontrak: `mahasiswaByUserId`, `mahasiswaByNim`, `daftarDosen`, `dosenById`
- [x] CRUD admin: `DosenController`, `MahasiswaController` (validasi unik `nip`, `nim`, `user_id`; `user_id` tidak bisa diubah lewat update)
- [x] `routes.php` — guard `auth + verified + role:admin`, prefix `akademik`
- [x] `CONTRACT.md` modul Akademik

### Frontend (§7.3)

- [x] Halaman `resources/js/pages/akademik/dosen/index.tsx` + `mahasiswa/index.tsx` — DataTable + dialog create/edit/delete (pola Manajemen, komponen shadcn)
- [x] Nav item `resources/js/pages/akademik/navigation.ts` + satu baris di `resources/js/lib/module-navigation.ts` (filter role `admin`)
- [x] Wayfinder routes/actions di-generate (`@/routes/akademik/*`)

### Tests (12 test)

- [x] `AkademikAccessTest` — guest redirect, role guard, CRUD dosen & mahasiswa, validasi unik
- [x] `AkademikContractTest` — kontrak dari sudut pandang konsumen (resolusi mahasiswa, daftar dosen terurut, null handling)

### Pendukung (sesi 1)

- [x] (`19b1d67`) Penjaga batas via Pest Arch: `ModularMonolithArchTest`, `ModuleLayerTest`, dep `pestphp/pest-plugin-arch` — pengganti Deptrac
- [x] (`4b1623a`) Akun admin lokal `admin@simpel.com` / `admin123` di `DatabaseSeeder` (di luar scope PRD — kebutuhan dev)

## ✅ Selesai — Ekstensi Akademik (sesi 2)

- [x] Migrasi `user_id` nullable + unique di `akademik_dosens` (tautan dosen ↔ akun login validator)
- [x] `DosenDTO->userId` + `AkademikContract::dosenByUserId()`; relasi `Dosen::user()`; factory default `user_id => null`
- [x] `CONTRACT.md` Akademik diperbarui

## ✅ Selesai — Modul Skripsi (sesi 2, belum di-commit)

### Backend (§7.2, §3.3–§3.5)

- [x] Registrasi permission di `RolePermissionSeeder`: `skripsi.pengajuan.submit` (mahasiswa), `.verify` (admin), `.decide` (validator) (§7.5.3)
- [x] Migrasi `skripsi_pengajuan_juduls` + `skripsi_judul_pengajuans` — `mahasiswa_id`, `validator_id`, `dosen_*` **tanpa FK lintas modul**
- [x] Model `PengajuanJudul`, `JudulPengajuan` + enum `StatusPengajuan` (guard transisi `bolehTransisiKe()`)
- [x] Services dengan guard status asal: `SubmitPengajuan`, `VerifikasiAdmin`, `PutusanValidator`, `AssignPenugasan` (§6.4, §6.5)
- [x] Validasi tepat 3 judul + berkas PDF maks 5 MB, disk privat `local` (§6.1, §6.2)
- [x] Aturan satu pengajuan aktif per mahasiswa — dicek ulang di `SubmitPengajuan` (§6.3)
- [x] Policy `PengajuanJudulPolicy` + routes guard `role:` per submenu (§6.7)
- [x] Domain event `PengajuanDiajukan`, `PengajuanDiverifikasi`, `PengajuanDiputus` + 3 listener database notification (§5.4)
- [x] Konsumsi `AkademikContract` — identitas mahasiswa, daftar dosen, resolusi dosen validator dari akun login; **nol impor model Akademik** (diverifikasi Pest Arch) (§6.8)
- [x] `SkripsiServiceProvider` (registrasi event-listener) + `routes.php` + `CONTRACT.md`

### Frontend (§5, §7.3)

- [x] Nav "Skripsi" dengan submenu per-role + baris di `module-navigation.ts` (children kini difilter role juga)
- [x] Halaman mahasiswa `skripsi/pengajuan`: panel status + riwayat + dialog submit step-by-step 3 langkah (§5.1)
- [x] Template pengajuan **DOCX** di `storage/app/private/template/` + route download terproteksi role:mahasiswa (§6.2, §7.3)
- [x] Halaman admin `skripsi/verifikasi`: setujui + pilih validator / tolak + catatan (§5.3)
- [x] Halaman validator `skripsi/putusan`: setujui SATU judul / tolak + catatan (§5.3)
- [x] Halaman `skripsi/daftar-judul` (admin & validator): tabel + pencarian + pagination + modal detail + isi penugasan (admin) (§5.2)
- [x] Bel notifikasi in-app (`NotificationBell` di header; data di-share `HandleInertiaRequests`; tandai-dibaca via `NotificationController` core) (§5.4)

### Tests (28 test baru)

- [x] `AccessTest` — guard per-role untuk semua route Skripsi + template download
- [x] `SubmitTest` — happy path, validasi 3 judul/PDF/ukuran, tanpa profil Akademik, satu pengajuan aktif, submit ulang setelah ditolak, notifikasi admin, status "belum mengajukan"
- [x] `VerifikasiTest` — daftar pending, setujui + validator, validasi validator & catatan, guard status, notifikasi mahasiswa + validator (skip bila dosen tanpa akun)
- [x] `PutusanDanPenugasanTest` — setujui satu judul, catatan wajib, guard status, penugasan hanya admin & hanya pada status disetujui, dosen harus valid
- [x] `DaftarJudulTest` — hanya judul ter-disetujui, pencarian, field modal detail

## Catatan / Keputusan pelaksanaan

Sesi 2 (20 Sep 2026):

- **Link validator (§5.3/§5.4):** kolom `user_id` di `akademik_dosens` + `dosenByUserId()` di kontrak — admin memilih validator dari daftar dosen, penerima notifikasi di-resolusi via contract.
- **Penugasan (§3.5):** admin mengisi 4 dosen via modal detail Daftar Judul, hanya saat status `disetujui`.
- **Putusan validator:** menyetujui berarti memilih SATU judul dari 3; judul itulah yang diberi penugasan (konsisten §1).
- **Tabel `notifications`:** migrasi core dibuat (`php artisan make:notifications-table`) — prasyarat database notification.
- **Test harness:** factory Skripsi tidak membuat profil Akademik (melanggar arch rule supplier-side); test yang butuh profil nyata membuatnya dari namespace `Tests` (dikecualikan dari scan arch).
- **Sintaks multi-role Spatie:** `role:admin|validator` (pipe) — koma dibaca sebagai argumen guard kedua.

Sesi 1:

- Direktori kosong `app/Modules/Skripsi/` (sisa sesi sebelumnya) dihapus karena `ModuleBoundaryTest` menggagalkan modul tanpa provider — modul dibuat lengkap saat tahap §7.5.2.
- Struktur modul mengikuti konvensi repo (flat, pola Manajemen), bukan template Domain/Infrastructure di rule file — sesuai catatan §7.
- `npm run check` menuntut perbaikan format pada ±29 file lama; efek samping formatter, tanpa perubahan logika — churn di docs/`.ai/rules`/komponen lama tetap uncommitted.

## 📋 Terencana — Revisi · Riwayat · Monitoring (sesi 3)

> Sumber ide: tag `archive/feat-pengajuan-lifecycle` (branch lifecycle lama diarsip; pola tabel riwayat, model, dan agregasi statistik diadaptasi ke arsitektur staging).
>
> Basis: branch `feat/skripsi-riwayat` dari `staging` — PR ke `staging`. Struktur: **3 PR kecil**, checks §7.4 hijau di tiap PR.
>
> Keputusan desain (konfirmasi user): **revisi oleh admin + validator**; struktur **3 PR**; kerja di **branch baru**.

### PR 1 — Audit trail riwayat (fondasi) ✅ **Selesai**

- [x] Migrasi `skripsi_pengajuan_riwayats`: `pengajuan_judul_id` (FK **internal modul**), `dari_status` nullable, `ke_status`, `aksi`, `aktor_id` (FK `users`), `catatan` nullable, `created_at` (tanpa `updated_at`)
- [x] Model `PengajuanRiwayat` (`$timestamps = false`) + relasi `riwayat()` di `PengajuanJudul`
- [x] Pencatatan via **listener event existing** (`PengajuanDiajukan/Diverifikasi/Diputus`) — lifecycle service tidak disentuh
- [x] UI: panel/timeline riwayat di modal detail Daftar Judul + halaman pengajuan mahasiswa
- [x] Tests: baris riwayat tercatat per transisi, aktor & catatan benar

### PR 2 — Alur revisi (admin + validator) ✅ **Selesai**

- [x] `StatusPengajuan::Direvisi = 'direvisi'` + transisi sah: `diajukan → direvisi → diajukan` (dari dua titik asal); `direvisi` masuk himpunan aktif
- [x] Aksi `mintaRevisi` (admin saat verifikasi, validator saat putusan) — catatan wajib, event `PengajuanDirevisi` + notifikasi ke mahasiswa
- [x] Resubmit mahasiswa: unggah ulang berkas/judul pada pengajuan yang sama (bukan pengajuan baru — berbeda dari §8 #1 yang berlaku untuk penolakan); event `PengajuanDiajukanUlang` + notifikasi ke admin
- [x] Guard status (guard eksplisit di Services), permission `skripsi.pengajuan.revise`; routes `revisi` (verifikasi & putusan) + `resubmit`
- [x] Tests: `RevisiTest` — 8 test (revisi admin & validator, catatan wajib, guard asal status, resubmit same-record + berkas lama terhapus, guard kepemilikan, direvisi menghalangi pengajuan baru, kronologi alur lengkap)
- [x] UI: tombol "Minta Revisi" di verifikasi & putusan (dialog catatan, form terpisah agar error tidak bocor antar-dialog) + tombol "Kirim Revisi" di pengajuan mahasiswa (judul lama di-prefill, posting ke `resubmit`); status/aksi `direvisi` ditambahkan ke label & kronologi

### PR 3 — Dashboard monitoring (admin)

- [ ] `SkripsiMonitoringService` (atau query service): `total`, `per_status`, `per_validator` (beban), `bulan_ini` — diadaptasi dari arsip tanpa impor model lintas modul
- [ ] Controller + halaman `skripsi/monitoring` (kartu statistik + grafik ringan)
- [ ] Route + nav submenu admin; permission `view` bila perlu
- [ ] Tests: angka agregasi benar, guard akses admin
