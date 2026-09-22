# Progress — Ketahanan Teknis Alur Pengajuan Judul

> Cabang: `test` · Branch kerja: `feat/ketahanan-teknis` ·
> PR: [#4](https://github.com/InYrDim/simpel/pull/4) · Diperbarui: **22 Sep 2026**
>
> Acuan: [ketahanan-teknis.md](ketahanan-teknis.md). Urutan implementasi
> mengikuti §7 — tiga PR kecil, `composer ci:check` hijau di tiap PR.

## Ringkasan

| PR  | Isi                        | Item (§3)          | Status | Commit    |
| --- | -------------------------- | ------------------ | ------ | --------- |
| 1   | Queue listener notifikasi  | A (§3.1)           | ✅     | `0190cd9` |
| 2   | Batch resolusi identitas   | B (§3.2)           | ✅     | `767b5ee` |
| 3   | Validasi berkas + CI + 429 | C, D, E (§3.3–3.5) | ✅     | `3bc5de9` |
| —   | PRD + pelacakan progres    | —                  | ✅     | `9de95b3` |

Verifikasi: tiap commit diuji di worktree bersih (perubahan berikutnya
di-`stash`), sehingga tiap commit berdiri sendiri — bukan hanya tip yang diuji.

| Commit    | `php artisan test` | `composer ci:check` |
| --------- | ------------------ | ------------------- |
| `0190cd9` | 148 passed         | hijau               |
| `767b5ee` | 150 passed         | hijau               |
| `3bc5de9` | 159 passed         | hijau               |
| `9de95b3` | 159 passed         | hijau               |

CI GitHub Actions pada PR #4: job `ci` **pass** (51s). Push ke `test` juga
memicu run dan **success** — trigger yang dulu menunjuk branch tak ada.

## ✅ PR 1 — Queue notifikasi

- [x] 5 listener `KirimNotifikasiPengajuan*` → `ShouldQueue` + `$afterCommit = true`
- [x] Listener `CatatRiwayat*` tetap sinkron (keputusan §8 #1)
- [x] Test rollback: notifikasi tidak terkirim bila transaksi gagal
      (`SubmitTest` — submit di dalam transaksi yang di-rollback, `assertNothingSent()`)
- [x] Semua test notifikasi lama tetap hijau
- [x] Penjaga struktur: `ListenerQueueTest` mengunci notifikasi antri + jejak audit sinkron
- [x] `composer ci:check` hijau

## ✅ PR 2 — Batch resolusi identitas

- [x] `MahasiswaDTOList` di `App\Modules\Contracts\` (+ `byUserId()` untuk pemetaan per baris)
- [x] `AkademikContract::mahasiswaByUserIds(array): MahasiswaDTOList`
- [x] Implementasi `AkademikService` (satu query `whereIn`; daftar kosong → tanpa query)
- [x] `RiwayatPengajuanService` memanggil batch sekali per halaman
- [x] `CONTRACT.md` Akademik diperbarui
- [x] `AkademikContractTest` — kasus batch + id tak dikenal
- [x] `RiwayatPengajuanTest` — tepat 1 query `akademik_mahasiswas` per halaman (bukan 10)
- [x] Arch test tetap hijau; `composer ci:check` hijau

## ✅ PR 3a — Validasi berkas + trigger CI

- [x] Allow-list `mimetypes` pada `store` + `resubmit` — lima MIME yang dipetakan Symfony ke ekstensi `pdf`; `octet-stream` ditolak (§8 #7)
- [x] Rule `berkas` dikonsolidasikan ke metode privat `aturanPengajuan()` (tanpa duplikasi)
- [x] Rate limiter `pengajuan-submit` (5/menit per user) + `throttle` di route
- [x] Test: PDF palsu (berkas nyata berisi non-PDF) ditolak, submit ke-6 ditolak 429
- [x] `tests.yml`: `staging` dikembalikan ke `on.push.branches` → kini memuat
      `main` + `test` + `staging` (lihat **Koreksi item D** di bawah)
- [x] `composer ci:check` hijau

## ✅ PR 3b — UX respons 429 (E, §3.5)

- [x] Hook toast terpusat: listener `inertia:httpException`, hanya status 429, `preventDefault()`
- [x] Toast ramah menyebut sisa waktu dari header `Retry-After` bila ada
- [x] `resources/views/errors/429.blade.php` untuk kasus non-XHR (mandiri, sadar mode gelap)
- [x] Status selain 429 tetap jalur bawaan Inertia
- [x] Test: respons 429 menampilkan halaman error kustom + header `Retry-After`
- [x] `composer ci:check` hijau

## ✅ Penyelesaian dampak (dibereskan sebelum commit pertama)

Tiga dampak yang muncul dari tinjauan hasil A–E — diselesaikan lebih dulu supaya
tidak mewarisi masalahnya ke branch integrasi:

- [x] **Queue dikuras terjadwal** (dampak A, keputusan §8 #8): target deploy
      ditetapkan shared hosting atau VPS (Docker) → `routes/console.php`
      menjadwalkan `queue:work --stop-when-empty --max-time=55 --tries=3` tiap
      menit dengan `withoutOverlapping(10)`, sehingga cukup SATU cron
      `php artisan schedule:run` di kedua target. Dijaga
      `tests/Feature/QueueDrainScheduleTest` — regresinya adalah notifikasi
      menumpuk diam-diam, yang tidak terlihat dari test fungsional mana pun.
      Prasyarat didokumentasikan di `.env.example`, §3.1 PRD, dan
      `Modules/Skripsi/CONTRACT.md`. Definisi service worker Docker tetap di
      luar PRD ini (§9) karena repo belum punya stack Docker.
- [x] **Risiko false positive MIME dihilangkan** (dampak C): allow-list diperluas
      ke lima MIME yang dipetakan `vendor/symfony/mime/MimeTypes.php` ke ekstensi
      `pdf`; `application/octet-stream` tetap ditolak.
- [x] **Guard status non-429** (dampak E): test memastikan halaman 429 tidak
      melebar ke 404. Batas jujurnya: percabangan "hanya 429" di hook klien
      **tidak** punya test otomatis — repo ini tanpa test runner JS, jadi yang
      terjaga otomatis hanya sisi server.

## 🔎 Koreksi item D — regresi CI yang sempat tercatat "selesai"

Catatan sebelumnya menandai D ✅ ("`tests.yml`: tambah `staging`"). Setelah
diverifikasi, keadaan sebenarnya:

- `3c21f33` (21 Sep 06:39) memang menambahkan `staging`.
- `42e272a` (**21 Sep 09:51, sesudahnya**) **menukar** `staging` dengan `test`,
  padahal branch `test` tidak ada di lokal maupun `origin` (`git ls-remote`
  hanya menemukan `main` dan `staging`). Jadi yang terjadi bukan penambahan,
  melainkan `staging` kehilangan trigger-nya.
- `42e272a` tampaknya rename yang belum tuntas: `treehouse.toml`
  (`base_branch = "staging"`) dan skill `worktree-feature` tetap merujuk
  `staging`.

Perbaikan ada di commit `3bc5de9`: `staging` dikembalikan sehingga trigger
memuat `main` + `test` + `staging`, dan branch `test` benar-benar dibuat (dari
tip `staging`, `3c834d5`) sebagai base PR ini.

**Batas yang lebih tepat** (koreksi atas §2 PRD): karena `on: pull_request`
tidak punya filter branch, PR ke `staging` sebenarnya tetap ter-cover CI. Yang
tidak ter-cover hanya **push langsung** ke `staging` — termasuk push hasil
merge PR, sehingga state hasil merge tidak pernah diverifikasi. Jadi §2 butir 4
("CI tidak jalan untuk `staging`") benar untuk push, tapi tidak untuk PR.

## Catatan / Keputusan pelaksanaan

- **A — `afterCommit` aman untuk test suite.** Test memakai `RefreshDatabase`,
  dan Laravel membinding `Illuminate\Foundation\Testing\DatabaseTransactionsManager`
  yang menjalankan callback after-commit pada level transaksi pembungkus (bukan
  menunggu commit yang tak pernah terjadi). Jadi listener queued tetap tereksekusi
  di test (`Notification::assertSentTo` tetap valid) **dan** rollback di dalam test
  tetap membatalkan pengiriman — tidak perlu `Queue::fake()`.
- **C — `mimetypes` dievaluasi dari isi berkas.** `UploadedFile::fake()` sengaja
  memalsukan MIME dari nama, jadi test "PDF palsu" memakai `UploadedFile` nyata
  (berkas temporer berisi teks) agar deteksi MIME benar-benar membaca konten.
- **C — limiter didaftarkan di core** `AppServiceProvider` dengan nama generik
  (`pengajuan-submit`) supaya route modul cukup menunjuk nama; core tidak
  menyentuh namespace modul (aturan `ModuleBoundaryTest`).
- **E dicatat sebagai perluasan PRD (§3.5, §5, §8 #6) sebelum dikerjakan.** A–D
  sendiri tidak mengatur UX 429: item C berhenti pada "ditolak 429". Dua efek
  samping yang disadari dan diterima: (1) handler toast bersifat global,
  sehingga 429 di route lain (login, 2FA, passkeys, verifikasi email,
  `settings/password`) ikut memakai toast yang sama; (2) `errors/429.blade.php`
  dipilih Laravel berdasarkan status, jadi ikut terpakai di seluruh aplikasi.
  Keduanya tetap "tidak diuji/dijamin" oleh PRD ini (§9).
- **C — allow-list MIME dinaikkan dari 1 ke 5 nilai (§8 #7).** Awalnya PRD
  menyebut `application/pdf` saja. Saat memverifikasi terlihat
  `vendor/symfony/mime/MimeTypes.php` memetakan lima MIME ke ekstensi `pdf`
  (`application/pdf`, `application/acrobat`, `application/nappdf`,
  `application/x-pdf`, `image/pdf`). Karena `mimes:pdf` sudah lolos untuk berkas
  tersebut, membatasi `mimetypes` ke satu nilai hanya menambah risiko PDF asli
  tertolak. `application/octet-stream` tetap ditolak karena meloloskan berkas
  yang MIME-nya tak terdeteksi.
- **Dipecah 4 commit di satu branch, bukan 3 PR berantai.** §7 merencanakan tiga
  PR kecil; keputusan pemilik repo adalah satu branch kerja
  (`feat/ketahanan-teknis`) berisi empat commit — A, B, C+D+E, lalu dokumentasi —
  sehingga tiap item tetap bisa ditinjau dan di-revert sendiri. Dua file yang
  menampung lebih dari satu item dipecah supaya tiap commit berdiri sendiri:
  `SubmitTest` (test rollback di commit A, test 429 di commit C/E) dan
  `Modules/Skripsi/CONTRACT.md` (bullet A, B, C); hasil akhirnya dicocokkan
  identik dengan versi aslinya sebelum dipecah.
- **Target branch pindah ke `test`.** `staging` dipertahankan sebagai trigger CI
  selama transisi, tetapi `treehouse.toml` (`base_branch`) dan skill
  `worktree-feature` masih merujuk `staging` — peralihan sisanya dikerjakan
  terpisah.
