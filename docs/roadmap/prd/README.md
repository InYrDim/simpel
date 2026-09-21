# PRD — Product Requirements

Dokumen kebutuhan produk (PRD) untuk setiap fitur besar, beserta pelacakan progres implementasinya.

## Konvensi: satu folder per PRD

Setiap PRD hidup di **foldernya sendiri** (nama folder = nama fitur, `kebab-case`). Isi wajib dua file:

```
docs/roadmap/prd/
├── README.md                  ← file ini (index + konvensi)
└── <nama-fitur>/
    ├── <nama-fitur>.md        ← dokumen PRD-nya (sumber kebutuhan)
    └── progress.md            ← checklist progres implementasi, dipetakan ke §PRD
```

Aturan singkat:

1. **Nama folder = nama file PRD** — mis. folder `pengajuan-judul` berisi `pengajuan-judul.md`. Satu fitur, satu folder, tidak dicampur.
2. **PRD adalah sumber kebutuhan** — status, keputusan, dan out-of-scope dicatat di sini; jangan mengubah kebutuhan lewat kode.
3. **`progress.md` wajib ada sejak awal** (boleh berupa kerangka kosong) dan diperbarui setiap kali ada tahap yang selesai — bukan di akhir proyek. Tautkan ke section/§ PRD dan nomor commit agar bisa ditelusuri.
4. **Penamaan `kebab-case`** untuk folder dan file, mengikuti pola yang sudah ada.

### Cara menambah PRD baru

1. Buat folder `docs/roadmap/prd/<nama-fitur>/`.
2. Tulis PRD di `<nama-fitur>.md` — minimal memuat: ringkasan, aktor/role, data model, aturan bisnis, arsitektur, dan keputusan yang sudah diputuskan. Contoh lengkap: [`pengajuan-judul/pengajuan-judul.md`](pengajuan-judul/pengajuan-judul.md).
3. Buat `progress.md` dengan struktur: ringkasan tahapan (tabel status + commit), checklist ✅ selesai, checklist ⬜ belum, dan catatan pelaksanaan.
4. **Daftarkan di tabel index di bawah** — satu baris per PRD, jangan sampai PRD baru tidak ketahuan ada.

## Daftar PRD

| PRD                                                           | Status                                  | Progres                                                    |
| ------------------------------------------------------------- | --------------------------------------- | ---------------------------------------------------------- |
| [Pengajuan Judul Skripsi](pengajuan-judul/pengajuan-judul.md) | Final — Akademik selesai, Skripsi belum | [pengajuan-judul/progress.md](pengajuan-judul/progress.md) |
| [Beban Dosen](beban-dosen/beban-dosen.md)                     | Final — terimplementasi (21 Sep 2026)   | [beban-dosen/progress.md](beban-dosen/progress.md)         |
