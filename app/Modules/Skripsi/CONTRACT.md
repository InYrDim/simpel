# Module: Skripsi

## Owns

- Database tables: `skripsi_pengajuan_juduls`, `skripsi_judul_pengajuans`, `skripsi_pengajuan_riwayats`
- Core domain concepts: alur pengajuan judul skripsi — mahasiswa mengajukan tepat 3 judul + berkas, admin memverifikasi kelengkapan & menugaskan validator, validator memutuskan persetujuan satu judul; jejak audit transisi status

## Public interface (Contracts/)

- Belum ada — modul ini adalah ujung (consumer) yang mengonsumsi `AkademikContract` dari modul Akademik. Bila kelak modul lain perlu membaca data pengajuan, interface-nya ditempatkan di `App\Modules\Contracts\` (PRD §7.2).

## Allowed dependencies

- `App\Modules\Support\*` (kerangka modul)
- `App\Modules\Contracts\*` (`AkademikContract` + DTO — satu-satunya akses ke data Akademik)
- Shared kernel: `App\Http\Controllers\Controller`, `App\Models\User`
- DILARANG mengimpor `App\Modules\Akademik\*` — digagalkan ModuleBoundaryTest/Pest Arch

## Events published

- `PengajuanDiajukan` — mahasiswa submit pengajuan baru; payload: `pengajuan`
- `PengajuanDiverifikasi` — admin memverifikasi (menugaskan validator) atau menolak; payload: `pengajuan`, `aktor` (nullable User)
- `PengajuanDiputus` — validator memutuskan; payload: `pengajuan`, `judulDisetujui` (nullable), `aktor` (nullable User)
- `PengajuanDirevisi` — admin/validator meminta revisi (status → `direvisi`); payload: `pengajuan`, `dariStatus` (status asal, karena model sudah `direvisi`), `aktor` (nullable User)
- `PengajuanDiajukanUlang` — mahasiswa resubmit pengajuan yang direvisi (status → `diajukan`); payload: `pengajuan`

## Events consumed

- Tidak ada — listener internal modul mengirim database notification dari event milik sendiri (§5.4)

## Explicitly NOT exposed

- Model Eloquent `PengajuanJudul`, `JudulPengajuan`, `PengajuanRiwayat` — internal modul
- Tabel `skripsi_*` — jangan di-query langsung dari modul lain (boundary rule #2)
- Services transisi status (`SubmitPengajuan`, `VerifikasiAdmin`, `PutusanValidator`, `AssignPenugasan`, `MintaRevisiAdmin`, `MintaRevisiValidator`, `ResubmitPengajuan`) — hanya dipanggil controller modul ini
- Service agregasi `SkripsiMonitoringService` (statistik dashboard admin + sebaran beban dosen) — hanya dipanggil `MonitoringController` modul ini
- Service baca-saja `RiwayatPengajuanService` (daftar pengajuan + timeline jejak audit) — hanya dipanggil `RiwayatPengajuanController` modul ini

## Notes for maintainers

- Transisi status di-guard di Services (bukan controller) — `StatusPengajuan::bolehTransisiKe()` + guard eksplisit per action (§6.5).
- `mahasiswa_id`, `validator_id`, dan kolom `dosen_*` penugasan TANPA FK lintas modul; keberadaan ID dicek via `AkademikContract` di layer Action (§6.8).
- Penugasan pembimbing/penguji diisi admin lewat modal detail Daftar Judul, hanya pada judul milik pengajuan `disetujui` (keputusan pelaksanaan §3.5).
- Validator login di-resolusi ke dosen-nya lewat `AkademikContract::dosenByUserId()` — tautan `user_id` ada di tabel `akademik_dosens` (keputusan pelaksanaan, sesi 20 Sep 2026).
- Berkas pengajuan di disk `local` (storage privat) dengan nama acak; nama asli disimpan terpisah untuk ditampilkan (§6.2).
- Jejak audit transisi status tersimpan dalam tabel `skripsi_pengajuan_riwayats` melalui listener internal modul (PR 1 sesi 3).
- Alur revisi (PR 2 sesi 3): status `direvisi` masuk himpunan pengajuan aktif (menghalangi pengajuan baru); resubmit terjadi pada pengajuan yang SAMA — berbeda dari penolakan, yang menuntut pengajuan baru (§8 keputusan #1).
- Dashboard monitoring (PR 3 sesi 3): statistik agregat baca-saja (`total`, `per_status`, `per_validator`, `bulan_ini`) lewat `SkripsiMonitoringService`; nama dosen di-resolusi via `AkademikContract::dosenById()` — semua status enum selalu hadir di `per_status` (termasuk nol) agar urutan UI stabil.
- Sebaran Beban Dosen (PRD Beban Dosen, 21 Sep 2026): `SkripsiMonitoringService::bebanDosen()` mengagregasi 5 peran (validator aktif + 4 kolom `dosen_*` judul disetujui) per dosen; cakupan SEMUA dosen Akademik via SATU panggilan `daftarDosen()` (beban 0 tetap tampil, dosen tak terdaftar tampil `-`); beban kumulatif tanpa konsep "selesai"; urutan Total menurun lalu nama. Tetap baca-saja, tanpa route baru (guard `role:admin` grup monitoring).
- Halaman Riwayat Pengajuan (PR 4 sesi 3): daftar baca-saja; admin melihat semua pengajuan, mahasiswa hanya miliknya; timeline kronologi berasal dari relasi `riwayat()` yang sama, dituang per baris agar dialog detail tidak butuh endpoint terpisah (pola modal Daftar Judul).
- Ketahanan teknis (PRD ketahanan-teknis, 21 Sep 2026):
    - Listener notifikasi (`KirimNotifikasiPengajuan*`) kini `ShouldQueue` + `$afterCommit = true`; `CatatRiwayat*` sengaja TETAP sinkron agar jejak audit atomik dengan transisi status. Penjaga: `tests/Feature/Modules/Skripsi/ListenerQueueTest`. Driver queue produksi = `database`.
    - **PRASYARAT DEPLOY:** karena notifikasi kini antri, harus ada yang memproses queue. Dev: `composer run dev` (`php artisan dev`) sudah menjalankan `queue:listen`. Produksi (shared hosting ATAU VPS/Docker): cukup SATU cron `* * * * * cd /path/app && php artisan schedule:run >> /dev/null 2>&1` — `routes/console.php` menjadwalkan `queue:work --stop-when-empty --max-time=55 --tries=3` tiap menit (`withoutOverlapping(10)`), dijaga `tests/Feature/QueueDrainScheduleTest`. Alternatif VPS/Docker: jalankan `php artisan queue:work` sebagai service permanen. Tanpa salah satunya, notifikasi menumpuk diam-diam di tabel `jobs` tanpa error — periksa dengan `php artisan queue:failed`.
    - Identitas mahasiswa di halaman Riwayat di-resolusi lewat SATU panggilan `AkademikContract::mahasiswaByUserIds()` per halaman (sebelumnya satu panggilan per baris) — jangan kembalikan ke per-baris.
    - Berkas pengajuan divalidasi `mimes:pdf` + allow-list `mimetypes` (maks 5 MB), dikonsolidasikan di `aturanPengajuan()`. Allow-list memuat LIMA MIME yang dipetakan Symfony ke ekstensi `pdf` (`application/pdf`, `application/acrobat`, `application/nappdf`, `application/x-pdf`, `image/pdf`) supaya PDF asli tidak tertolak; `application/octet-stream` sengaja TIDAK diizinkan (PRD §8 #7). `store` & `resubmit` dipagari rate limiter core `pengajuan-submit` (5/menit per akun, didaftarkan di `AppServiceProvider`).
