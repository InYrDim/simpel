# Module: Akademik

## Owns

- Database tables: `akademik_dosens`, `akademik_mahasiswas`
- Core domain concepts: data referensi akademik — dosen (Dosen PA, validator, pembimbing, penguji) dan profil akademik mahasiswa (NIM, prodi, angkatan, dosen PA)

## Public interface (Contracts/)

- `AkademikContract` — resolusi profil mahasiswa (by user / by NIM), daftar dosen, detail dosen by ID
- `MahasiswaDTO`, `DosenDTO`, `DosenDTOList` — bentuk data yang melintasi batas modul

## Allowed dependencies

- `App\Modules\Support\*` (kerangka modul)
- Shared kernel: `App\Http\Controllers\Controller`, `App\Models\User`
- Tidak bergantung pada modul lain

## Events published

- Tidak ada (belum ada kebutuhan; PRD §7.1 adalah CRUD referensi)

## Events consumed

- Tidak ada

## Explicitly NOT exposed

- Model Eloquent `Dosen` dan `Mahasiswa` — internal modul. Modul lain wajib lewat `AkademikContract`.
- Tabel `akademik_*` — jangan di-query langsung dari modul lain (boundary rule #2).

## Notes for maintainers

- `users` adalah tabel core, jadi FK `akademik_mahasiswas.user_id → users` diperbolehkan (PRD §3.1). FK ke tabel modul lain tetap dilarang.
- Saat modul Skripsi dibangun, ia mengonsumsi `AkademikContract` ini untuk daftar dosen (penugasan validator) dan data mahasiswa — bukan mengimpor model di sini.
- CRUD admin dosen & mahasiswa dipagari `role:admin` di `routes.php`; Skripsi nanti menambah guard role-nya sendiri.
