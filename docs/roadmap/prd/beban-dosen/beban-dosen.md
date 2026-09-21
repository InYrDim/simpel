# PRD — Beban Dosen (Sebaran Penugasan)

> Status: **Final — terimplementasi** (21 Sep 2026). PRD ini membuka item "Sebaran beban &
> statistik dosen pembimbing/penguji" dari §9 Out of Scope PRD induk
> ([../pengajuan-judul/pengajuan-judul.md](../pengajuan-judul/pengajuan-judul.md)) —
> fondasinya sudah disiapkan eksplisit (keputusan §8 #2 PRD induk).
> Pelacakan progres: [progress.md](progress.md).

## 1. Ringkasan

Admin melihat **sebaran beban penugasan per dosen** dalam satu tabel: berapa
penugasan tiap dosen menurut peran — validator (aktif), pembimbing 1,
pembimbing 2, penguji 1, penguji 2 — beserta totalnya. Tujuan: pengisian
penugasan (modal Daftar Judul) dan penugasan validator (halaman Verifikasi)
tidak lagi dilakukan "buta" terhadap beban dosen.

## 2. Latar Belakang

Saat ini admin mengisi 4 penugasan per judul yang disetujui **tanpa melihat**
beban yang sudah ada. Monitoring yang berjalan hanya menampilkan beban
**validator aktif** (`per_validator` pada `SkripsiMonitoringService`,
status `diverifikasi_admin`) — pembimbing dan penguji tidak terlihat sama
sekali. Risiko: penugasan menumpuk pada dosen tertentu (khususnya dosen
prolifik) dan admin tidak punya data untuk menyeimbangkan.

## 3. Aktor

| Aktor | Peran                                               |
| ----- | --------------------------------------------------- |
| Admin | Melihat sebaran beban (satu-satunya konsumen fitur) |

Dosen dan mahasiswa tidak melihat fitur ini di tahap ini
(lihat §9 — akses dosen melihat beban sendiri diusulkan out of scope).

## 4. Definisi & Sumber Data

- **Data penugasan milik modul Skripsi:**
    - `skripsi_pengajuan_juduls.validator_id` — validator aktif review.
    - `skripsi_judul_pengajuans.dosen_pembimbing_1/2`, `dosen_penguji_1/2`
      — diisi admin hanya pada judul yang disetujui.
- **Identitas dosen** di-resolusi lewat `AkademikContract` (`daftarDosen()`
  untuk sebaran lengkap, satu panggilan) — dilarang query lintas modul
  (boundary rule #2, dijaga arch test).
- **Definisi beban:** jumlah penugasan **tersimpan** per peran. Belum ada
  konsep "selesai bimbingan/ujian" (fitur bimbingan out of scope), jadi
  beban bersifat **kumulatif** atas semua penugasan yang pernah diisi.
- **Cakupan dosen:** SEMUA dosen Akademik tampil, termasuk yang berban 0 —
  supaya sebarannya lengkap dan admin bisa melihat dosen yang belum
  pernah ditugaskan. Dosen tanpa akun login tetap tampil (namanya cukup).

## 5. Spesifikasi UI

Bagian baru di halaman Monitoring admin (`skripsi/monitoring`) — kartu
statistik dan daftar beban validator yang ada tetap dipertahankan:

**Tabel Sebaran Beban Dosen** (urut Total menurun, lalu nama):

| Dosen | Validator aktif | Pembimbing 1 | Pembimbing 2 | Penguji 1 | Penguji 2 | Total |
| ----- | --------------- | ------------ | ------------ | --------- | --------- | ----- |

## 6. Arsitektur

- Agregasi baru di modul Skripsi — usulan: metode `bebanDosen()` pada
  `SkripsiMonitoringService` (satu domain agregasi yang sama). Tidak ada
  service/controller/route baru.
- Agregasi per kolom peran via query milik modul sendiri (`groupBy` pada
  `skripsi_judul_pengajuans` + filter status `disetujui`); nama dosen dari
  SATU panggilan `daftarDosen()` — tanpa panggilan kontrak per baris.
- Route `skripsi/monitoring` sudah terguard `role:admin` — tidak berubah.

## 7. Kriteria Diterima

- [ ] Angka agregasi benar: penugasan dihitung per peran, hanya dari judul yang disetujui
- [ ] Dosen dengan beban 0 tetap tampil; dosen tanpa nama tampil sebagai `-`
- [ ] Akses: admin lolos, mahasiswa & validator ditolak (test guard)
- [ ] Test agregasi: pembagian peran benar, urutan Total menurun stabil
- [ ] Arch test + `composer ci:check` hijau

## 8. Keputusan (dikonfirmasi via "proceed", 21 Sep 2026)

| #   | Pertanyaan                  | Keputusan                                                                                |
| --- | --------------------------- | ---------------------------------------------------------------------------------------- |
| 1   | Penempatan UI               | **Bagian baru di halaman Monitoring** (bukan halaman terpisah)                           |
| 2   | Definisi beban              | **Kumulatif** — jumlah penugasan tersimpan per peran; tanpa konsep "selesai"             |
| 3   | Cakupan dosen               | **Semua dosen Akademik** tampil, termasuk beban 0                                        |
| 4   | Struktur kode               | **Metode `bebanDosen()` di `SkripsiMonitoringService`** — tanpa service baru             |
| 5   | Daftar beban validator lama | **Dipertahankan** — kartu ringkasan tetap; tabel baru menambah detail pembimbing/penguji |
| 6   | Akses                       | **Admin saja** (konsisten Monitoring)                                                    |

## 9. Out of Scope (future work)

- Rekomendasi validator otomatis berdasarkan bidang/topik (§9 PRD induk).
- Penyeimbangan beban otomatis / saran dosen saat mengisi penugasan.
- Statistik historis (per bulan/semester) dan tren.
- Akses dosen melihat beban sendiri.
- Konsep "selesai bimbingan" dan pencabutan penugasan — menunggu fitur bimbingan.
