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
- Service agregasi `SkripsiMonitoringService` (statistik dashboard admin) — hanya dipanggil `MonitoringController` modul ini

## Notes for maintainers

- Transisi status di-guard di Services (bukan controller) — `StatusPengajuan::bolehTransisiKe()` + guard eksplisit per action (§6.5).
- `mahasiswa_id`, `validator_id`, dan kolom `dosen_*` penugasan TANPA FK lintas modul; keberadaan ID dicek via `AkademikContract` di layer Action (§6.8).
- Penugasan pembimbing/penguji diisi admin lewat modal detail Daftar Judul, hanya pada judul milik pengajuan `disetujui` (keputusan pelaksanaan §3.5).
- Validator login di-resolusi ke dosen-nya lewat `AkademikContract::dosenByUserId()` — tautan `user_id` ada di tabel `akademik_dosens` (keputusan pelaksanaan, sesi 20 Sep 2026).
- Berkas pengajuan di disk `local` (storage privat) dengan nama acak; nama asli disimpan terpisah untuk ditampilkan (§6.2).
- Jejak audit transisi status tersimpan dalam tabel `skripsi_pengajuan_riwayats` melalui listener internal modul (PR 1 sesi 3).
- Alur revisi (PR 2 sesi 3): status `direvisi` masuk himpunan pengajuan aktif (menghalangi pengajuan baru); resubmit terjadi pada pengajuan yang SAMA — berbeda dari penolakan, yang menuntut pengajuan baru (§8 keputusan #1).
- Dashboard monitoring (PR 3 sesi 3): statistik agregat baca-saja (`total`, `per_status`, `per_validator`, `bulan_ini`) lewat `SkripsiMonitoringService`; nama dosen di-resolusi via `AkademikContract::dosenById()` — semua status enum selalu hadir di `per_status` (termasuk nol) agar urutan UI stabil.
