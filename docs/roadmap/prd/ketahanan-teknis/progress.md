# Progress — Ketahanan Teknis Alur Pengajuan Judul

> Cabang: `staging` · Terakhir diperbarui: **21 Sep 2026**
>
> Acuan: [ketahanan-teknis.md](ketahanan-teknis.md). Urutan implementasi
> mengikuti §7 — tiga PR kecil, `composer ci:check` hijau di tiap PR.

## Ringkasan

| PR  | Isi                       | Item (§3)       | Status | Commit          |
| --- | ------------------------- | --------------- | ------ | --------------- |
| 1   | Queue listener notifikasi | A (§3.1)        | ✅     | belum di-commit |
| 2   | Batch resolusi identitas  | B (§3.2)        | ✅     | belum di-commit |
| 3   | Validasi berkas + CI      | C, D (§3.3–3.4) | ✅     | belum di-commit |
| 3   | UX respons 429 (E)        | E (§3.5)        | ✅     | belum di-commit |

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

## ✅ PR 3a — Validasi berkas + trigger CI staging

- [x] Allow-list `mimetypes` pada `store` + `resubmit` — lima MIME yang dipetakan Symfony ke ekstensi `pdf`; `octet-stream` ditolak (§8 #7)
- [x] Rule `berkas` dikonsolidasikan ke metode privat `aturanPengajuan()` (tanpa duplikasi)
- [x] Rate limiter `pengajuan-submit` (5/menit per user) + `throttle` di route
- [x] Test: PDF palsu (berkas nyata berisi non-PDF) ditolak, submit ke-6 ditolak 429
- [x] `tests.yml`: tambah `staging` ke `on.push.branches`
- [x] `composer ci:check` hijau

## ✅ PR 3b — UX respons 429 (E, §3.5)

- [x] Hook toast terpusat: listener `inertia:httpException`, hanya status 429, `preventDefault()`
- [x] Toast ramah menyebut sisa waktu dari header `Retry-After` bila ada
- [x] `resources/views/errors/429.blade.php` untuk kasus non-XHR (mandiri, sadar mode gelap)
- [x] Status selain 429 tetap jalur bawaan Inertia
- [x] Test: respons 429 menampilkan halaman error kustom + header `Retry-After`
- [x] `composer ci:check` hijau

## ✅ Penyelesaian dampak (sebelum commit)

Tiga dampak yang muncul dari tinjauan hasil A–E — diselesaikan lebih dulu supaya
tidak mewarisi masalahnya ke `staging`:

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
- **Implementasi ada di working tree, belum di-commit** — pemecahan menjadi 3 PR
  (§7) menunggu keputusan pemilik repo; kolom Commit diisi setelah commit.
