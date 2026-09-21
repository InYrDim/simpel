# Progress — Beban Dosen (Sebaran Penugasan)

> Cabang: `docs/prd-beban-dosen` · Terakhir diperbarui: **21 Sep 2026**
>
> Acuan: [beban-dosen.md](beban-dosen.md). Satu sesi kerja — fitur ini
> kecil: satu metode agregasi + satu bagian UI + test.

## Ringkasan

| Tahap                         | Status | Commit |
| ----------------------------- | ------ | ------ |
| PRD tersusun, §8 dikonfirmasi | ✅     | —      |
| Implementasi backend + UI     | ✅     | —      |
| Tests                         | ✅     | —      |
| Checks (§7) hijau             | ✅     | —      |

## ✅ Implementasi

- [x] `SkripsiMonitoringService::bebanDosen()` — agregasi 5 peran via query modul + 1 panggilan `daftarDosen()` (§6)
- [x] `MonitoringController` meneruskan `beban_dosen` ke halaman
- [x] Halaman `skripsi/monitoring` — kartu **Sebaran Beban Dosen** (tabel `DataTable`, §5), kartu Beban Validator lama dipertahankan
- [x] `PengajuanJudulFactory::disetujui()` — state test untuk pengajuan berstatus disetujui
- [x] `CONTRACT.md` modul Skripsi diperbarui (catatan maintainer; permukaan publik tidak berubah)

## ✅ Tests (`MonitoringTest`)

- [x] Agregasi per peran benar — hanya judul milik pengajuan `disetujui` yang dihitung
- [x] Dosen beban 0 tetap tampil; dosen tanpa nama/tak terdaftar tampil `-`
- [x] Dosen tanpa akun login tetap tampil
- [x] Guard akses admin/mahasiswa/validator (test guard lama, tetap lulus)
- [x] Urutan Total menurun stabil, tiebreak nama

## ✅ Checks

- [x] `vendor/bin/pint --dirty --format agent`
- [x] `phpstan analyse`
- [x] `php artisan test --compact`
- [x] `npm run check` + `npm run types:check`
- [x] Arch test (boundary) hijau

## Catatan / Keputusan pelaksanaan

- §8 PRD dikonfirmasi user via "let's proceed" — semua 6 usulan disetujui
  apa adanya, tidak ada amendemen.
- Baris dosen disusun dari gabungan ID dosen Akademik + ID yang muncul di
  kolom penugasan, sehingga dosen yang sudah dihapus dari Akademik tetap
  tampil (`-`) dan totalnya tidak hilang.
- ID `dosen_*` yang `null` dibuang sebelum agregasi agar tidak muncul
  baris hantu `dosen_id = 0`.
- Komponen `DataTable` generik dipakai ulang untuk tabel sebaran — kolom
  Total dirender tebal.
