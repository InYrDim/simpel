# Catatan Proyek (single source of truth)

Semua catatan kerja proyek ada **di file ini saja**: keputusan, aturan praktis, jebakan teknis, dan utang. `AGENTS.md` (lokal, di-ignore git) hanya berisi progres dan menunjuk ke sini. Jangan menyalin isi catatan ke tempat lain; cukup tautkan ID-nya (mis. `N-021`).

Kontrak publik modul tetap hidup di `CONTRACT.md` masing-masing modul (aturan `.ai/rules/modular-monolith-boundaries.md`). Catatan di sini boleh menunjuk ke sana, tidak menggandakannya.

## Cara memakai

**Menambah catatan.** Beri ID berikutnya (`N-0xx`, berurutan, tidak dipakai ulang), isi `Dibuat` dengan tanggal hari itu (`YYYY-MM-DD`), `Status: aktif`, dan tulis isinya singkat.

**Mengganti catatan.** Jangan hapus atau timpa catatan lama. Ubah `Status` lama menjadi `digantikan`, isi `Diganti oleh` (ID catatan baru) dan `Diganti pada` (tanggal), lalu tulis catatan baru yang memuat `Menggantikan: N-0xx`. Riwayat harus tetap terbaca.

**Status yang dipakai.**

| Status             | Arti                                                                       |
| ------------------ | -------------------------------------------------------------------------- |
| `aktif`            | Berlaku sekarang.                                                          |
| `digantikan`       | Sudah diganti catatan lain (lihat `Diganti oleh` dan `Diganti pada`).      |
| `dicabut`          | Ternyata keliru atau tidak lagi relevan, tanpa pengganti. Tulis alasannya. |
| `perlu-verifikasi` | Belum dicek ulang terhadap keadaan sekarang.                               |
| `terbuka`          | Utang atau keputusan yang belum selesai.                                   |
| `selesai`          | Utang atau keputusan yang sudah tuntas (isi `Selesai pada`).               |

**Tanggal.** Tanda `≈` berarti perkiraan: catatan itu dipindahkan dari `AGENTS.md` lama yang tidak mencatat tanggal; batasnya diambil dari tanggal migrasi dan `git log`.

**Melihat semuanya sekaligus.** `grep -n "^- \*\*Status" docs/notes.md` menampilkan status setiap catatan; tambahkan `digantikan` atau `terbuka` untuk menyaring.

---

## A. Keputusan produk dan arsitektur

### N-001 Restrukturisasi menu jadi Akademik / Manajemen / Laporan

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Laporan = data pengajuan skripsi; Monitoring lama dipecah ke Laporan. Prodi = `akademik_prodis` (+ `mahasiswa.prodi` string menjadi `prodi_id`, kaprodi lewat `kaprodi_id` ke `akademik_dosens`). Jurusan = `manajemen_jurusans` (ketua/sekretaris berupa teks, tanpa FK lintas modul). Peran = CRUD Spatie (definisi permission tetap di `RolePermissionSeeder`). Pengguna menjadi child `Akun`. Export = CSV via `StreamedResponse` + `fputcsv`. Beban Dosen dihitung dari pengajuan skripsi. Dieksekusi sebagai PR A sampai E, semuanya selesai.

### N-002 `MahasiswaDTO->prodi` tetap string

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Di-resolve dari `prodiRef->nama` supaya konsumen Skripsi tidak berubah saat kolom beralih ke FK. Kontrak Akademik tidak berubah. Rincian: `app/Modules/Akademik/CONTRACT.md`.

### N-003 Batas modul dan tabel infrastruktur core

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Dilarang mengimpor kelas antar modul atau membuat FK lintas modul; kontrak publik di `app/Modules/Contracts/`; navigasi per modul di `pages/<modul>/navigation.ts`. Tabel `roles`, `permissions`, `model_has_roles` adalah infrastruktur core Spatie (setara `users`) dan boleh dipakai modul Manajemen.

### N-004 Alur branch dan commit

- **Dibuat:** 2026-09-22 (aturan di `.ai/rules/git-workflow.md`), ringkasan ditambah 2026-09-30
- **Status:** aktif
- **Isi:** Alur `feat/<nama>` → `test` → `staging` → `main`; PR fitur menuju `test`. Jangan pernah commit otomatis; minta izin user. Branch baru dipotong dari `origin/test`, lalu jalankan `git branch --unset-upstream` supaya `git push` biasa tidak mengarah ke `test`. Pesan commit mengikuti gaya `git log` (Indonesia, awalan `feat:`, `fix:`, `docs:`).

### N-005 Rujukan lama "PR ke staging"

- **Dibuat:** ≈2026-09-21
- **Status:** digantikan
- **Diganti oleh:** N-004
- **Diganti pada:** 2026-09-22
- **Isi:** Semula PR A sampai E dijadwalkan ke `staging`. Sejak alur branch berubah, rujukan itu dibaca sebagai PR ke `test`.

## B. Jebakan teknis dan konvensi kode

### N-006 Pola PHPStan level 7

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Paginator `through()` wajib diberi `@var \Illuminate\Pagination\LengthAwarePaginator<int, Model>` (kelas konkret, bukan `Contracts\...`). Guard `fopen === false` dengan `RuntimeException`. Loop manual untuk list `array`. Nullsafe sebelum `??` tidak boleh. Docblock `@var` harus tepat di atas variabel yang menerima hasil (mis. `$validated = $request->validate($rules)`), bukan di atas variabel lain (ditambahkan 2026-09-30).

### N-007 Pint merombak FQCN di docblock

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Pint mengubah FQCN di docblock menjadi `use`. Jalankan `vendor/bin/pint --dirty --format agent` sebelum PHPStan akhir.

### N-008 Factory dan urutan `select`/`withCount`

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** `Model::newFactory()` harus `protected static` dan mengimpor factory. `->select()` harus dipanggil sebelum `->withCount()`; urutan terbalik menghapus kolom agregat.

### N-009 Helper global Pest saling bentrok

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Fungsi helper di file tes Pest bersifat global. Beri awalan per file (`manajemenAdmin`, `peranAdmin`, `mahasiswaListAdmin`).

### N-010 Tidak ada `deptrac.yaml`

- **Dibuat:** ≈2026-09-21
- **Status:** aktif
- **Isi:** Diverifikasi tidak ada, padahal `.ai/rules/modular-monolith-boundaries.md` menyebut `vendor/bin/deptrac analyse`. Pengecekan batas PHP dilakukan lewat Pest Arch (`tests/Unit/ModularMonolithArchTest.php`) dan review.

### N-011 Skrip Python panjang lewat heredoc Bash sering rusak

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Kutip dan tanda backslash di heredoc terpotong dan menghasilkan `unexpected EOF`. Tulis skrip ke file (Write tool) lalu jalankan, atau edit langsung dengan Edit/Write.

### N-012 Impor `cn` di komponen shadcn

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (impor `cn` di `scroll-area.tsx` dan `textarea.tsx` diganti `@/lib/utils`).
- **Isi:** CLI shadcn menghasilkan `import { cn } from "cn"` (paket npm `cn`); di proyek ini seharusnya `@/lib/utils`. File baru sudah diperbaiki (`command.tsx`, `popover.tsx`). `scroll-area.tsx` dan `textarea.tsx` masih memakai impor lama; belum dibereskan.

### N-037 `CLAUDE.md` digabung ke `AGENTS.md`

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Blok `<laravel-boost-guidelines>` (206 baris, isi identik dengan aslinya) dipindahkan ke akhir `AGENTS.md`. `CLAUDE.md` kini hanya berisi satu baris `@AGENTS.md`; file itu **tidak dihapus** karena Claude Code hanya membaca `CLAUDE.md` dan mengimpor `AGENTS.md` lewat baris tadi. Kedua file di-ignore git (lokal). **Risiko:** `boost.json` mendaftarkan agen `claude_code` dan `opencode`, jadi `composer update` (hook `boost:update`) bisa menulis ulang blok Boost ke `CLAUDE.md`. Bila blok muncul ganda (di `CLAUDE.md` dan `AGENTS.md`), hapus salah satunya atau ubah `agents` di `boost.json`.

## C. Baseline pengujian

### N-013 Baseline full suite 162 tes / 803 asersi

- **Dibuat:** ≈2026-09-22
- **Status:** digantikan
- **Diganti oleh:** N-014
- **Diganti pada:** 2026-09-30
- **Isi:** Setelah PR E: 162 tes / 803 asersi lulus, PHPStan 0, lint dan types 0.

### N-014 Baseline setelah PR F-2

- **Dibuat:** 2026-09-30
- **Status:** perlu-verifikasi
- **Menggantikan:** N-013
- **Isi:** `tests/Feature/Modules` + `tests/Unit`: **177 tes / 953 asersi lulus** (termasuk 11 tes baru di `MahasiswaListTest`). PHPStan 0, Pint lulus, `npm run check:fix` 0 (105 file), `types:check` exit 0, `npm run build` berhasil. **Suite penuh belum dijalankan ulang**; jalankan `php artisan test --compact` lalu perbarui catatan ini.

### N-015 Lingkungan agen tidak bisa menjalankan `npm install` dari registry

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** `npm install --package-lock-only` gagal dengan "Fetching packages of type remote have been disabled". `shadcn add` memakai pnpm dan berhasil, tetapi hanya memperbarui `pnpm-lock.yaml` yang di-ignore git. Lihat N-034.

## D. Konteks desain dan UI (sesi Impeccable)

### N-016 Berkas konteks desain

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** `PRODUCT.md` (pengguna, tujuan), `DESIGN.md` (sistem visual), `.impeccable/design.json` (sidecar), `.impeccable/live/config.json`. North star "The Academic Commons". Nilai diverifikasi dari kode: radius dasar 1.4rem; `--spacing` 0.27rem sehingga `h-9` = 38.88px dan `size-11` = 47.52px. Jangan menyalin angka Tailwind bawaan ke dokumen desain.

### N-017 Nilai `DESIGN.md` versi pertama

- **Dibuat:** 2026-09-30
- **Status:** digantikan
- **Diganti oleh:** N-016
- **Diganti pada:** 2026-09-30
- **Isi:** Versi pertama ditulis dari pembacaan sebagian dan memuat nilai keliru (radius tombol `sm`, tinggi kontrol 36px, ukuran tipografi dan breakpoint yang dikira-kira, klaim "warna hangat"). Ditemukan saat audit; dokumen dibuat ulang dari nilai kode.

### N-018 Token warna dan aturan `destructive-foreground`

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Token dinaikkan agar lolos WCAG AA: primary L0.55 (dark L0.56), destructive L0.55, accent-foreground L0.5; token baru `--success`. Di dark mode `--destructive-foreground` sengaja hampir hitam. `text-destructive-foreground` adalah warna konten di atas isian destructive, **bukan** warna teks di atas latar terang (alert dan menu dropdown sempat salah memakainya). Teks galat: `text-destructive`; sukses: `text-success`. Dilarang kelas palet mentah (`red-600`, `neutral-*`) atau hex di komponen.

### N-019 Pengecualian kontras yang diketahui

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Teks `text-primary` di atas latar dark mode 4.38:1 (di bawah 4.5). Pakai hanya untuk teks besar atau tebal.

### N-020 Aksesibilitas tombol, sentuh, dan gerak

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Tombol ikon wajib `aria-label` (`title` saja tidak cukup). Ukuran kontrol membesar otomatis di pointer kasar (`pointer-coarse:`); jangan menimpanya dengan `h-*`/`w-*` tetap. `prefers-reduced-motion` menghapus slide dan zoom tetapi mempertahankan fade (aturan di akhir `resources/css/app.css`).

### N-021 Baseline critique halaman Mahasiswa

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Skor 22/40 (1 P0, 2 P1, 2 P2), peninjauan hanya dari kode tanpa browser. Snapshot di `.impeccable/critique/`. Setelah pengecekan visual, jalankan ulang `$impeccable critique` untuk membandingkan.

### N-022 Membersihkan live mode Impeccable

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Live mode menyuntik `<script>` ke `resources/views/app.blade.php`. Setelah selesai jalankan `impeccable.cmd live-server stop` **dan** `impeccable.cmd live-inject --remove`, lalu pastikan `git diff resources/views/app.blade.php` kosong. Pernah tertinggal karena pengecekan memakai glob `**` yang tidak cocok apa pun.

## E. Halaman Mahasiswa (PR F-2)

### N-023 Status keaktifan mahasiswa

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Kolom `akademik_mahasiswas.status` (`aktif|cuti|lulus|nonaktif`, default `aktif`), enum internal `StatusMahasiswa`, **tidak** masuk `MahasiswaDTO`. Keputusan user. Rincian kontrak: `app/Modules/Akademik/CONTRACT.md`.

### N-024 Entri massal

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Cukup "Simpan dan tambah lagi" (prodi, angkatan, dosen PA, dan status dipertahankan antar entri). Impor CSV tidak dikerjakan. Keputusan user.

### N-025 `cmdk` dan komponen shadcn baru

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** User menyetujui `cmdk` (dipasang lewat `npx shadcn add popover command`) untuk `SearchableSelect`. Saat CLI bertanya menimpa `dialog.tsx`, jawab **tidak** (komponen dipakai luas). Hapus `CommandDialog` dari `command.tsx` karena bergantung pada prop yang tidak ada di `dialog.tsx`.

### N-026 Dosen PA: wajib secara bisnis, sementara boleh kosong

- **Dibuat:** 2026-09-30
- **Status:** digantikan
- **Diganti oleh:** N-038
- **Diganti pada:** 2026-09-30
- **Menggantikan:** perilaku sebelumnya, yaitu validasi `dosen_pa_id` `required` di `MahasiswaController` (bertentangan dengan migrasi 2026-09-30 yang membuat kolomnya nullable).
- **Isi:** Sesuai keputusan user, validasi kini `nullable` karena mahasiswa hasil registrasi mandiri belum punya PA. Kembalikan ke `required` setelah alur pengisian PA ada, dan sesuaikan tes `admin can update a mahasiswa without resending the linked account`.

### N-027 Perbaikan bug `update()` mahasiswa

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Dulu `update()` memakai validasi `store()` yang mewajibkan `user_id`, padahal form edit tidak mengirimnya, sehingga setiap edit gagal (galatnya tidak ditampilkan). Sekarang `user_id` hanya divalidasi saat membuat; tautan akun tidak bisa diubah. Tercakup tes.

### N-028 Hapus mahasiswa wajib `konfirmasi_nim`

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Dicek di server, bukan hanya di UI. Hapus bersifat permanen dan tidak menyentuh akun login.

### N-029 Parameter daftar mahasiswa

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** `search`, `prodi_id`, `angkatan`, `dosen_pa_id` (angka atau `kosong`), `status`, `sort` (`nama|nim|angkatan`), `direction`, `per_page` (10/25/50), `page`. Nilai tak sah jatuh ke default, bukan galat validasi. Daftar lengkap: `CONTRACT.md` Akademik.

### N-030 Komponen bersama dan pola dialog

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** `components/searchable-select.tsx` (Popover + Command), `components/table-pagination.tsx`. `DataTable` mendukung `sortKey`/`onSort`/`sort`, `caption`, `emptyState`, `isLoading`, dan `colSpan` pada state kosong (kompatibel mundur untuk Prodi dan Dosen). `ui/table.tsx`: `TableCell`/`TableHead` memakai `TdHTMLAttributes`/`ThHTMLAttributes`. Dialog tambah/ubah dan hapus dimiliki halaman (satu instans, dirender hanya saat terbuka sehingga form selalu mulai dari data terbaru); tidak ada lagi `useForm` per baris.

## F. Utang dan pekerjaan terbuka

### N-031 Data yatim di Skripsi setelah mahasiswa dihapus

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (larang hapus lewat `SkripsiContract`, lihat N-039).
- **Isi:** `skripsi_pengajuan.mahasiswa_id` sengaja tanpa FK lintas modul, jadi pengajuan tetap ada setelah profil mahasiswa dihapus. Dialog hapus hanya memperingatkan. Perlu keputusan produk: larang hapus bila ada pengajuan (lewat kontrak), atau arahkan ke status Nonaktif.

### N-032 Halaman Mahasiswa belum diverifikasi di browser

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (user mengonfirmasi tampilan sudah baik).
- **Isi:** Belum dicek: combobox di dalam dialog, menu yang membuka dialog (fokus dan `pointer-events`), tampilan mode gelap, dan mobile.

### N-033 Sisa dari audit UI

- **Dibuat:** 2026-09-30
- **Status:** digantikan
- **Diganti oleh:** N-040
- **Diganti pada:** 2026-09-30
- **Isi:** Sekitar 25 blok galat `<p>` belum disatukan ke `InputError` (sudah memakai token). `bg-zinc-900` di `auth-split-layout.tsx`. Teks 13px di `welcome.tsx`. Breadcrumb "Akademik" dan "Mahasiswa" menuju URL yang sama. Halaman Prodi dan Dosen belum memakai pola filter, pagination, dan dialog bersama.

### N-034 `package-lock.json` belum berisi `cmdk`

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (`cmdk` sudah ada di `package-lock.json`).
- **Isi:** Jalankan `npm install` di mesin lokal dan commit lockfile. CI memakai `composer setup` (yang menjalankan `npm install`), jadi tidak macet. Lihat N-015.

### N-035 Migrasi kolom status belum dijalankan di database dev

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (migrasi sudah `Ran`).
- **Isi:** Jalankan `php artisan migrate` (migrasi `2026_09_30_123415_add_status_to_akademik_mahasiswas_table`).

### N-036 Pekerjaan PR F-2 belum di-commit

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (3 commit, masuk `test` lewat #14).
- **Isi:** Branch `feat/mahasiswa-registrar`. Saran: tiga commit (backend, komponen bersama + `cmdk`, halaman Mahasiswa), PR ke `test`. Menunggu izin user (N-004).

### N-038 Dosen PA wajib kecuali lulus atau nonaktif

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Menggantikan:** N-026
- **Isi:** `MahasiswaController::validateMahasiswa()` memakai `required_unless:status,lulus,nonaktif` untuk `dosen_pa_id`. `update()` memakai status tersimpan bila request tidak mengirim status. Mahasiswa hasil registrasi mandiri tanpa PA tetap bisa dinonaktifkan tanpa memilih PA. Alur pengisian PA = filter `dosen_pa_id=kosong` + dialog ubah. Kolom DB tetap nullable.

### N-039 Hapus mahasiswa diblokir bila punya pengajuan skripsi

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Menggantikan:** N-031
- **Isi:** Kontrak baru `App\Modules\Contracts\SkripsiContract::mahasiswaIdsDenganPengajuan()` (implementasi `Skripsi\Services\SkripsiService`). `MahasiswaController::destroy()` melempar error `mahasiswa`; daftar membawa `punya_pengajuan` (satu query per halaman) sehingga dialog hapus menonaktifkan tombol dan menyarankan status Nonaktif.

### N-040 Sisa audit UI: pola bersama untuk Prodi dan Dosen

- **Dibuat:** 2026-09-30
- **Status:** selesai
- **Selesai pada:** 2026-09-30 (lihat N-041)
- **Menggantikan:** N-033
- **Isi:** Sudah beres: blok galat `<p>` memakai `InputError` (kecuali ternary hint di `langkah-judul.tsx` dan blok catatan di `riwayat`), `bg-zinc-900` menjadi `bg-primary` + `text-primary-foreground`, teks 13px menjadi `text-sm`, breadcrumb grup "Akademik" tanpa tautan (`BreadcrumbItem.href` opsional). Belum: halaman Prodi dan Dosen belum memakai pola filter, pagination, dan dialog bersama seperti Mahasiswa.

### N-041 Prodi dan Dosen memakai pola daftar bersama; hapus dosen dijaga

- **Dibuat:** 2026-09-30
- **Status:** aktif
- **Isi:** Halaman Prodi dan Dosen kini seperti Mahasiswa: pencarian debounce, urutan, `per_page` (10/25/50), `TablePagination`, dialog ubah dan hapus tunggal milik halaman. Logika query string ada di hook `resources/js/hooks/use-list-query.ts` dan trait `Akademik\Controllers\Concerns\MembacaQueryDaftar` (halaman Mahasiswa dipindah ke keduanya pada 2026-10-01, dengan `extraParams` untuk filter tambahan).
- **Jebakan:** FK `akademik_mahasiswas.dosen_pa_id` bersifat `cascadeOnDelete`, jadi dulu menghapus dosen ikut menghapus profil mahasiswa yang dibimbingnya. `DosenController::destroy()` kini menolak selama dosen masih menjadi PA (flash `error`), dan dialog hapus memakai `jumlah_mahasiswa_pa`. Lihat N-042 untuk lanjutan (FK dan penugasan Skripsi).

### N-042 FK `dosen_pa_id` nullOnDelete dan guard dosen yang dirujuk Skripsi

- **Dibuat:** 2026-10-01
- **Status:** aktif
- **Isi:** Migrasi `2026_09_30_160821_change_dosen_pa_foreign_key_to_null_on_delete_on_akademik_mahasiswas` mengganti cascade menjadi `nullOnDelete` (kolom sudah nullable). Kontrak `SkripsiContract::dosenIdsDenganPenugasan()` (validator_id + empat kolom `dosen_*`) dipakai `DosenController` untuk menolak hapus dosen yang masih dirujuk pengajuan (flash `error`) dan untuk flag `punya_penugasan` pada baris daftar. Migrasi belum dijalankan di database dev: `php artisan migrate`.
