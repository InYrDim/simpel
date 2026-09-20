# Progress — Modul Pengajuan Judul Skripsi

> Cabang: `feat/pengajuan` · Terakhir diperbarui: **20 Sep 2026**
>
> Acuan: [pengajuan-judul.md](pengajuan-judul.md). Urutan implementasi mengikuti §7.5 — satu modul per sesi kerja.

## Ringkasan

| Tahap (§7.5)                      | Status         | Commit    |
| --------------------------------- | -------------- | --------- |
| 1. Modul Akademik (§7.1)          | ✅ **Selesai** | `9bddf4a` |
| 2. Modul Skripsi (§7.2)           | ⬜ Belum       | —         |
| 3. Registrasi permission (§7.5.3) | ⬜ Belum       | —         |

Checks wajib hijau (§7.4) pada saat Akademik selesai: `pint` ✅ · `phpstan` ✅ · `php artisan test` **68 passed** ✅ · `npm run check` ✅ · `npm run types:check` ✅

## ✅ Selesai — Modul Akademik (commit `9bddf4a`)

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

### Tests (12 test baru)

- [x] `AkademikAccessTest` — guest redirect, role guard (mahasiswa/validator forbidden), CRUD dosen & mahasiswa, validasi unik
- [x] `AkademikContractTest` — kontrak dari sudut pandang konsumen (resolusi mahasiswa, daftar dosen terurut, null handling)

### Pendukung

- [x] (`19b1d67`) Penjaga batas via Pest Arch: `ModularMonolithArchTest`, `ModuleLayerTest`, dep `pestphp/pest-plugin-arch`, README kit — pengganti Deptrac
- [x] (`4b1623a`) Akun admin lokal `admin@simpel.com` / `admin123` di `DatabaseSeeder` (di luar scope PRD — kebutuhan dev)

## ⬜ Belum — Modul Skripsi (tahap §7.5.2, sesi kerja berikutnya)

### Backend (§7.2, §3.3–§3.4)

- [ ] Registrasi permission di `RolePermissionSeeder`: `skripsi.pengajuan.submit`, `skripsi.pengajuan.verify`, `skripsi.pengajuan.decide` (§7.5.3)
- [ ] Migrasi `skripsi_pengajuan_juduls` + `skripsi_judul_pengajuans` — ID lintas modul **tanpa FK**
- [ ] Model `PengajuanJudul`, `JudulPengajuan`; enum `StatusPengajuan`
- [ ] Services/Actions dengan guard status asal: `SubmitPengajuan`, `VerifikasiAdmin`, `PutusanValidator` (§6.4, §6.5)
- [ ] Validasi tepat 3 judul + berkas PDF maks 5 MB, disk privat (§6.1, §6.2)
- [ ] Aturan satu pengajuan aktif per mahasiswa — dicek ulang di server (§6.3)
- [ ] Policy: mahasiswa hanya melihat/mengubah pengajuannya sendiri (§6.7)
- [ ] Domain event `PengajuanDiajukan`, `PengajuanDiverifikasi`, `PengajuanDiputus` + listener database notification (§5.4)
- [ ] Konsumsi `AkademikContract` untuk daftar dosen & data mahasiswa — dilarang impor model Akademik (§6.8)
- [ ] `SkripsiServiceProvider` + routes guard role + `CONTRACT.md`

### Frontend (§5, §7.3)

- [ ] Nav "Skripsi" (`pages/skripsi/navigation.ts`) + baris di `module-navigation.ts`
- [ ] Halaman mahasiswa: panel status + riwayat, dialog submit step-by-step 3 langkah (§5.1)
- [ ] Template pengajuan **DOCX** + route download terproteksi (§6.2, §7.3)
- [ ] Halaman daftar judul admin & validator (tabel + modal detail, §5.2)
- [ ] Aksi admin (verifikasi + pilih validator / tolak + catatan) dan validator (setujui/tolak + catatan) (§5.3)
- [ ] Bel notifikasi in-app (§5.4)

## Catatan / Keputusan pelaksanaan

- Direktori kosong `app/Modules/Skripsi/` (sisa sesi sebelumnya) dihapus karena `ModuleBoundaryTest` menggagalkan modul tanpa provider — modul dibuat lengkap saat tahap §7.5.2.
- Struktur modul mengikuti konvensi repo (flat, pola Manajemen), bukan template Domain/Infrastructure di rule file — sesuai catatan §7.
- `npm run check` menuntut perbaikan format pada ±29 file lama; efek samping formatter, tanpa perubahan logika — masih uncommitted (churn di docs/`.ai/rules`/komponen lama juga uncommitted).
