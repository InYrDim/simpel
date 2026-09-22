# PRD — Ketahanan Teknis Alur Pengajuan Judul

> Status: **Final** — terimplementasi (21 Sep 2026), ditambah **item E**
> (UX respons 429) setelah A–D selesai. Di luar A–D, PRD ini hanya menambah
> SATU perilaku UI (item E); selebihnya memperkuat infrastruktur alur pengajuan
> judul yang sudah jalan
> (lihat [../pengajuan-judul/pengajuan-judul.md](../pengajuan-judul/pengajuan-judul.md)).
> Pelacakan progres: [progress.md](progress.md).

## 1. Ringkasan

Lima perbaikan ketahanan pada alur pengajuan judul, masing-masing kecil dan
terukur (A–D sejak awal; E ditambahkan 21 Sep 2026 setelah A–D selesai):

| #   | Perbaikan                                         | Dampak yang diharapkan                                                                        |
| --- | ------------------------------------------------- | --------------------------------------------------------------------------------------------- |
| A   | Listener notifikasi dikirim via queue             | Request aksi (submit/verifikasi/putusan/revisi) tidak memikul kerja notifikasi; siap produksi |
| B   | Resolusi identitas mahasiswa di-batch via kontrak | Halaman Riwayat Pengajuan tidak lagi N+1 panggilan kontrak                                    |
| C   | Validasi berkas diperketat + rate limit submit    | Berkas non-PDF tertolak di layer MIME; submit tidak bisa di-flood                             |
| D   | Trigger CI untuk push ke `staging`                | Branch basis pool & target PR kembali ter-cover CI                                            |
| E   | Respons 429 dipetakan jelas di UI                 | Rate limit tidak lagi berupa dialog error generik; dipakai juga 429 route lain (§6, §9)       |

## 2. Latar Belakang

Alur pengajuan judul selesai sampai PR 4 (monitoring & riwayat, 110+ test
hijau) dan berjalan baik di lingkungan dev (SQLite lokal, semua listener
sinkron). Sebelum fitur besar berikutnya (bimbingan), infrastruktur perlu
diperkuat agar tidak menumpuk utang saat data dan pengguna bertambah:

1. **Listener sinkron.** Kesepuluh listener modul Skripsi
   (`CatatRiwayat*`, `KirimNotifikasi*`) belum mengimplementasikan
   `ShouldQueue`. Di produksi dengan disk lambat / SMTP nyata, kerja
   notifikasi menambah latensi request aksi.
2. **N+1 lewat kontrak.** `RiwayatPengajuanService::barisPengajuan()`
   memanggil `AkademikContract::mahasiswaByUserId()` **satu kali per baris**
   (halaman = 10 baris). Kontrak belum punya metode batch.
3. **Validasi berkas minim.** `PengajuanJudulController` memvalidasi
   `'berkas' => ['required', 'file', 'mimes:pdf', 'max:5120']` — tanpa
   `mimetypes`, tanpa rate limit pada `store`/`resubmit`.
4. **CI tidak jalan untuk `staging`.** `.github/workflows/tests.yml` hanya
   trigger push ke `main` dan `test`, padahal `staging` kini branch basis
   worktree pool dan target semua PR.

## 3. Lingkup & Item Kerja

### 3.1 A — Listener notifikasi via queue

- 5 listener `KirimNotifikasiPengajuan*` mengimplementasikan `ShouldQueue`
  dengan `$afterCommit = true` (notifikasi tidak terkirim bila transaksi
  pembungkus event di-rollback).
- 5 listener `CatatRiwayat*` **tetap sinkron** — jejak audit harus atomik
  dengan perubahan status (ditulis dalam transaksi yang sama); mengantri
  audit justru membuka celah riwayat bolong.
- Driver queue: `database` (sudah tersedia — migrasi `jobs` ada,
  `QUEUE_CONNECTION=database` di `.env`).
- **Pengurasan queue (dampak operasional A, keputusan §8 #8):** queue
  `database` hanya berguna bila ada yang memprosesnya. Di dev `php artisan dev`
  (dipakai `composer run dev`) sudah otomatis menjalankan `queue:listen`.
  Untuk produksi, target deploy ditetapkan **shared hosting atau VPS (Docker)**
  — dan shared hosting tidak bisa menjamin proses worker permanen. Karena itu
  queue dikuras terjadwal di `routes/console.php`:
  `queue:work --stop-when-empty --max-time=55 --tries=3` tiap menit dengan
  `withoutOverlapping(10)`. Hasilnya cukup SATU cron
  (`php artisan schedule:run` per menit) di kedua target; dijaga oleh
  `tests/Feature/QueueDrainScheduleTest`. Tanpa itu notifikasi menumpuk
  diam-diam di tabel `jobs` tanpa error — prasyarat ini juga dicatat di
  `.env.example` dan `Modules/Skripsi/CONTRACT.md`.
- Verifikasi test: asersi `Notification::assertSentTo` tetap valid untuk
  notifikasi queued; tambah `Queue::fake()` hanya bila diperlukan.

### 3.2 B — Resolusi identitas mahasiswa batch

- Kontrak baru di `App\Modules\Contracts\AkademikContract`:
  `mahasiswaByUserIds(array $userIds): MahasiswaDTOList`.
- DTO `MahasiswaDTOList` dibuat di namespace bersama, mengikuti pola
  `DosenDTOList` yang sudah ada (simetri kontrak).
- `AkademikService` mengimplementasikan dengan satu query `whereIn`.
- `RiwayatPengajuanService` mengumpulkan semua `user_id` hasil paginasi
  **sebelum** `through()`, memanggil metode batch sekali, lalu memetakan
  per baris dari hasil batch.
- `CONTRACT.md` modul Akademik diperbarui; `AkademikContractTest` ditambah
  kasus batch (termasuk `user_id` yang tidak dikenal).

### 3.3 C — Validasi berkas & rate limit

- Aturan `berkas` di `store` dan `resubmit` menjadi
  `['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf,application/acrobat,application/nappdf,application/x-pdf,image/pdf', 'max:5120']`.
  Lima MIME itu bukan tebakan: `vendor/symfony/mime/MimeTypes.php` memetakan
  semuanya ke ekstensi `pdf`, jadi kelimanya adalah PDF yang sah. Memakai
  `application/pdf` saja berisiko menolak PDF asli yang terdeteksi sebagai
  alias — padahal `mimes:pdf` sudah lolos untuk berkas itu.
  `application/octet-stream` **sengaja tidak** diterima karena akan meloloskan
  berkas yang MIME-nya tak terdeteksi sama sekali.
  Catatan: `mimes:pdf` di Laravel sudah membaca isi berkas, jadi `mimetypes`
  di sini berfungsi sebagai allow-list eksplisit, bukan lapisan tunggal.
- Rate limiter bernama (mis. `pengajuan-submit`, per user, **5/menit**)
  didaftarkan di `AppServiceProvider`, dipasang `throttle:` pada route
  `store` dan `resubmit`.
- Rule dikonsolidasikan agar tidak terduplikasi dua kali di controller
  (satu metode privat / FormRequest kecil di modul Skripsi).

### 3.4 D — Trigger CI untuk staging

- `.github/workflows/tests.yml`: tambahkan `staging` ke `on.push.branches`.
- Isi job tidak berubah — `composer ci:check` sudah lengkap
  (pint + phpstan + pest + npm check + types:check).

### 3.5 E — UX respons 429 (perluasan, dicatat setelah A–D)

Item C hanya menuntut "submit ke-6 ditolak 429" — UX-nya tidak diatur. Karena
`ThrottleRequests` membalas HTML non-Inertia, Inertia menampilkannya sebagai
dialog error generik berisi halaman 429 bawaan Laravel. E menutup celah itu:

- Handler **terpusat di klien**: listener event `inertia:httpException` yang
  hanya bertindak pada status **429**, memanggil `preventDefault()` supaya
  dialog generik tidak muncul, lalu menampilkan toast ramah (menyebut sisa
  waktu dari header `Retry-After` bila tersedia).
- **Halaman error `resources/views/errors/429.blade.php`** untuk kasus non-XHR
  (navigasi browser langsung, mis. membuka link verifikasi email berulang) —
  mandiri tanpa Vite/Inertia, sadar mode gelap.
- Status selain 429 sengaja TIDAK disentuh; jalur bawaan Inertia tetap berlaku.
- Tidak ada perubahan domain, route, controller, atau service.

## 4. Data Model

**Tidak ada perubahan skema.** Tabel `jobs`/`failed_jobs` sudah ada
(migrasi core). Semua perubahan bersifat kode + konfigurasi.

## 5. Kriteria Diterima

| Item | Kriteria                                                                                                                                                         |
| ---- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A    | Semua test notifikasi lama tetap hijau; notifikasi tidak terkirim saat transaksi rollback (test rollback); tidak ada listener `KirimNotifikasi*` sinkron tersisa |
| B    | Halaman riwayat memicu **tepat 1** panggilan batch kontrak per halaman (bukan 10); test kontrak batch hijau; arch test tetap hijau                               |
| C    | Upload `.pdf` palsu (konten bukan PDF) ditolak dengan pesan validasi; submit ke-6 dalam 1 menit oleh user sama ditolak 429; test hijau                           |
| D    | Push ke `staging` memicu workflow di GitHub Actions                                                                                                              |
| E    | Submit yang kena limiter menampilkan toast ramah (bukan dialog error generik); respons 429 non-XHR menampilkan halaman error kustom; test hijau                  |

Gate tiap PR: `composer ci:check` hijau (konvensi repo).

## 6. Arsitektur & Batasan

- Semua perubahan tetap **di dalam modul** masing-masing:
    - A → `Modules/Skripsi/Listeners/` saja.
    - B → permukaan publik `Modules/Contracts/`, internal `Modules/Akademik/`,
      dan konsumen `RiwayatPengajuanService`. Tidak ada impor lintas internal —
      `ModuleBoundaryTest`/`ModuleLayerTest` wajib tetap hijau.
    - C → controller + routes modul Skripsi, limiter di core `AppServiceProvider`.
    - D → file workflow CI.
    - E → frontend bersama (hook toast + `components/ui/sonner.tsx`) dan
      `resources/views/errors/429.blade.php`. Ini **pengecualian sadar** dari
      "semua perubahan di dalam modul": UX error memang milik frontend bersama,
      bukan milik modul Skripsi. Tidak ada kode modul yang disentuh.
- Tidak ada perubahan pada Services lifecycle (`SubmitPengajuan` dkk.)
  untuk item A dan D; item B hanya mengganti sumber identitas di service
  baca-saja `RiwayatPengajuanService`.

## 7. Urutan Implementasi

Tiga PR kecil ke `staging`, checks hijau di tiap PR (konvensi sesi 3):

| PR  | Isi                    | Item    |
| --- | ---------------------- | ------- |
| 1   | Queue notifikasi       | A       |
| 2   | Batch kontrak          | B       |
| 3   | Validasi + CI + UX 429 | C, D, E |

## 8. Keputusan

| #   | Pertanyaan                  | Keputusan (21 Sep 2026)                                                                                                                                                                                                                                                                                  |
| --- | --------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | Listener mana yang di-queue | **Hanya `KirimNotifikasi*`**; `CatatRiwayat*` tetap sinkron demi atomisitas audit trail                                                                                                                                                                                                                  |
| 2   | Driver queue                | **`database`** — sudah tersedia, tanpa infrastruktur tambahan di tahap ini                                                                                                                                                                                                                               |
| 3   | Bentuk metode batch kontrak | **`mahasiswaByUserIds(array): MahasiswaDTOList`** — DTO list baru, simetris dengan `DosenDTOList`                                                                                                                                                                                                        |
| 4   | Batas rate limit submit     | **5/menit per user** pada `store` + `resubmit`                                                                                                                                                                                                                                                           |
| 5   | Urutan PR                   | **3 PR kecil** — queue, batch kontrak, validasi+CI                                                                                                                                                                                                                                                       |
| 6   | Penanganan 429 di UI        | **Hanya 429**, terpusat di klien (toast) + halaman error kustom; status lain tetap jalur bawaan Inertia. Ditambahkan 21 Sep 2026 setelah A–D selesai — C sendiri tidak mengatur UX-nya                                                                                                                   |
| 7   | Cakupan allow-list MIME     | **Lima MIME yang dipetakan Symfony ke ekstensi `pdf`**: `application/pdf`, `application/acrobat`, `application/nappdf`, `application/x-pdf`, `image/pdf`; `application/octet-stream` ditolak (melemahkan rule). Ditambahkan 21 Sep 2026 setelah A–D selesai — C sendiri hanya menyebut `application/pdf` |     | 8   | Pengurasan queue produksi | Target deploy: **shared hosting atau VPS (Docker)**. Karena shared hosting tidak bisa menjamin worker permanen, queue dikuras lewat **scheduler** (`queue:work --stop-when-empty --max-time=55 --tries=3` tiap menit, `withoutOverlapping(10)`, di `routes/console.php`) — sah untuk kedua target dengan SATU cron. Worker permanen (supervisor/systemd) & Horizon tetap out of scope (§9) |

## 9. Out of Scope (future work)

- Redis / Horizon untuk queue (database driver cukup untuk skala kampus).
- Antivirus / deep-inspection berkas PDF (cukup validasi MIME + ukuran).
- Email notification pada keputusan final (keputusan §8 PRD induk: in-app
  saja — dibuka lagi sebagai PRD tersendiri bila dibutuhkan).
- Optimasi N+1 di halaman lain (Verifikasi, Putusan, Monitoring) — dilakukan
  hanya bila terukur lambat, mengikuti pola item B.
- Worker queue PERMANEN (supervisor / systemd / service Docker / fitur worker
  platform): PRD ini memilih pengurasan terjadwal (§8 #8) karena harus jalan di
  shared hosting juga. Repo ini belum punya `Dockerfile`/compose sama sekali,
  jadi definisi service worker untuk VPS/Docker dikerjakan terpisah saat stack
  Docker-nya ditetapkan — bukan ditebak di sini.
- UX ramah untuk 429 pada route di luar alur pengajuan judul (login, 2FA,
  passkeys, verifikasi email, `settings/password`). Handler terpusat dan
  halaman error 429 memang ikut berlaku di sana karena sifatnya global, tetapi
  **tidak diuji/dijamin** oleh PRD ini.
