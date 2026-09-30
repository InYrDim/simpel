---
target: Mahasiswa list page
total_score: 27
max_score: 40
na_heuristics:
p0_count: 0
p1_count: 2
target_identity: "file:C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
target_fingerprint: 'sha256:c268d9506b84d1c4d06df75cda781d24b3127a9b688e0c919066ee9966e30bfa'
target_path: "C:\\Users\\iyede\\code\\___mine____\\skibidi\\resources\\js\\pages\\akademik\\mahasiswa\\index.tsx"
timestamp: 2026-09-30T16-23-11Z
slug: resources-js-pages-akademik-mahasiswa-index-tsx
---

Method: dual-agent (A: design review, B: detector + grep evidence, isolated)

# Critique: halaman Mahasiswa

Target: resources/js/pages/akademik/mahasiswa/index.tsx + components. Source-only review (no browser tool available).

## Design Health Score

| #         | Heuristik              | Skor      | Masalah utama                                                    |
| --------- | ---------------------- | --------- | ---------------------------------------------------------------- |
| 1         | Status sistem          | 3         | Loading hanya opacity-60 + aria-busy, tanpa pengumuman SR        |
| 2         | Kesesuaian dunia nyata | 3         | "Akun user" campur bahasa; "PA" tidak diuraikan di header/filter |
| 3         | Kontrol pengguna       | 3         | Tanpa undo setelah hapus; tanpa pilih massal                     |
| 4         | Konsistensi            | 3         | Select dan SearchableSelect mirip tapi berbeda perilaku          |
| 5         | Pencegahan galat       | 3         | Hapus mustahil baru ketahuan di dalam dialog                     |
| 6         | Recognition vs recall  | 3         | Tidak ada chip/ringkasan filter aktif                            |
| 7         | Fleksibilitas          | 2         | Tanpa aksi massal/ekspor/pintasan/klik baris                     |
| 8         | Minimalis              | 2         | Enam kontrol berbobot sama dalam satu baris                      |
| 9         | Pemulihan galat        | 3         | focusFirstError bagus; pesan blokir ganda                        |
| 10        | Bantuan                | 2         | Arti status dan efeknya pada kewajiban PA tak dijelaskan         |
| **Total** |                        | **27/40** | Good (batas bawah)                                               |

## Design Specificity Verdict

Category-interchangeable dengan kerajinan baik. Kekhasan hanya di alur kerja (hapus dengan mengetik NIM, "(wajib)" PA bergantung status, filter "Belum ada dosen PA", "Simpan dan tambah lagi"). Detector: 0 temuan pada 7 file, tanpa false positive. Grep: 0 warna mentah, 0 teks di bawah 12px, semua icon-only button berlabel.

## Priority Issues

- [P1] Toolbar kelebihan beban dan filter aktif tidak terlihat (index.tsx:144-298). Fix: popover Filter + chip aktif. Command: distill, layout.
- [P1] Alur baris berat dan hapus terblokir ditemukan terlambat (mahasiswa-columns.tsx:101-131, mahasiswa-delete-dialog.tsx). Fix: klik baris/Ubah tampak, nonaktifkan Hapus dengan penjelasan, tawarkan Nonaktifkan. Command: harden, clarify.
- [P2] Kolom status lemah untuk dipindai (mahasiswa-columns.tsx:94-160). Fix: Aktif tenang, pengecualian menonjol, ikon selain warna, peringatan PA kosong. Command: colorize, polish.
- [P2] Umpan balik dan loading tipis (data-table.tsx:54-58). Fix: region live, pointer-events-none saat sibuk, toast. Command: harden.
- [P3] Identitas dan konsistensi salinan. Fix: "Akun pengguna", uraikan PA, ringkasan kohort. Command: clarify.

## Persona Red Flags

- Alex: tanpa aksi massal Dosen PA per angkatan, tanpa ekspor/impor, tanpa pintasan, tanpa klik baris.
- Sam: filter dosen PA tanpa label (index.tsx:263-284); SearchableSelect tanpa aria-haspopup/aria-controls; Select/Input 36px tanpa pointer-coarse; tanpa pengumuman jumlah hasil.
- Bu Rina: harus memindai baris demi baris untuk PA kosong; jalan buntu untuk mahasiswa berpengajuan; efek status pada PA baru ketahuan setelah galat.

## Minor Observations

- overflow-x-auto tanpa petunjuk; tabel 7 kolom kemungkinan meluap di layar sempit.
- aria-current="page" pada span; id per-page-label hardcoded.
- grid-cols-2 sesak di ponsel; validasi native min/max berbahasa Inggris; mode Ubah tak menampilkan akun tertaut.
- Lebar wrapper filter dosen tidak konsisten dengan filter lain.
- Mode gelap belum diverifikasi.

## Questions to Consider

1. Mengapa tampilan bawaan direktori alfabetis dan bukan daftar kerja berbasis pengecualian?
2. Bagaimana jika ganti status dan tetapkan PA dilakukan inline di baris?
3. Apakah mengetik NIM melindungi dari risiko nyata, sementara perubahan status diam-diam tanpa konfirmasi/undo?
