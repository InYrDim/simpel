---
target: Mahasiswa list page
total_score: 27
max_score: 40
na_heuristics:
p0_count: 0
p1_count: 2
target_identity: "file:C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
target_fingerprint: 'sha256:4fa691125d797bc69e0719e87148c531620abfd9628495009cffe95f0e93a6e5'
target_path: "C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
timestamp: 2026-09-30T16-30-33Z
slug: resources-js-pages-akademik-mahasiswa-index-tsx
---

Method: dual-agent (A: design review, B: detector + grep evidence, isolated)

# Critique: halaman Mahasiswa (run 3)

Target: resources/js/pages/akademik/mahasiswa/index.tsx + components (termasuk mahasiswa-filter-popover). Source-only review (no browser tool available).

## Design Health Score

| #         | Heuristik              | Skor      | Masalah utama                                                    |
| --------- | ---------------------- | --------- | ---------------------------------------------------------------- |
| 1         | Status sistem          | 3         | Loading hanya opacity-60 + aria-busy                             |
| 2         | Kesesuaian dunia nyata | 3         | Dua frasa untuk satu keadaan PA kosong                           |
| 3         | Kontrol pengguna       | 3         | Filter popover memicu request tiap perubahan; tanpa undo hapus   |
| 4         | Konsistensi            | 3         | Status di toolbar tanpa chip, filter lain di popover dengan chip |
| 5         | Pencegahan galat       | 3         | Dialog hapus tidak mengulang nama                                |
| 6         | Recognition vs recall  | 3         | NIM diketik dari ingatan                                         |
| 7         | Fleksibilitas          | 2         | Tanpa aksi massal, sort PA/Status, ekspor                        |
| 8         | Minimalis              | 3         | Tiga target aksi per baris                                       |
| 9         | Pemulihan galat        | 2         | Tanpa undo, tanpa toast sukses terlihat, tanpa onError daftar    |
| 10        | Bantuan                | 2         | Arti status dan alasan PA wajib tak dijelaskan                   |
| **Total** |                        | **27/40** | Good (batas bawah)                                               |

## Design Specificity Verdict

Kategori-interchangeable dengan momen khas (tautan PA kosong, "(wajib)" PA per status, konfirmasi NIM, simpan dan tambah lagi). Detector: 0 temuan pada 8 file. Grep: 0 warna mentah, 0 teks <12px, semua icon-only berlabel.

## Priority Issues

- [P1] Penjelasan blokir hapus tak terjangkau keyboard (item menu disabled) dan dialog tidak mengulang identitas (mahasiswa-columns.tsx:143-153, mahasiswa-delete-dialog.tsx:44-49). Command: harden.
- [P1] Cakupan PA tanpa alur efisien: tanpa aksi massal, sort PA/Status, peringatan kecil. Command: shape, layout.
- [P2] Arsitektur filter tidak konsisten (status di toolbar tanpa chip). Command: distill, polish.
- [P2] Aksi baris redundan (nama-tautan + Ubah + menu). Command: distill.
- [P3] Umpan balik/error/semantik status (loading, onError, badge lulus vs aktif). Command: clarify, harden.

## Persona Red Flags

- Alex: tiga request untuk tiga filter; tanpa ekspor/massal.
- Sam: penjelasan hapus tersembunyi; tiga tab stop per baris; popover bersarang perlu uji fokus; Select/Input 36px tanpa pointer-coarse; Close dialog berlabel Inggris; aria-invalid tanpa describedby.
- Bu Rina: PA kosong hanya tautan kecil; edit satu per satu; tanpa impor CSV.

## Minor Observations

- Reset filter beda varian (ghost vs outline); badge aria-label pada span; cabang terblokir dialog jadi cadangan; angkatan type=number; mode Ubah tanpa akun tertaut; status string literal ganda; h1 kelas lokal.

## Questions to Consider

1. Mengapa default daftar alfabetis, bukan antrean pengecualian?
2. Apakah baris itu sendiri bisa jadi tombol edit?
3. Bagaimana jika dirancang untuk momen 40 mahasiswa baru (impor, massal)?
