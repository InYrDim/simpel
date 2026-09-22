# Module: Akademik

## Owns

- Database tables: `akademik_dosens`, `akademik_mahasiswas`, `akademik_prodis`
- Core domain concepts: data referensi akademik — dosen (Dosen PA, validator, pembimbing, penguji), profil akademik mahasiswa (NIM, angkatan, dosen PA), dan program studi (Prodi, kaprodi)

## Public interface (Contracts/)

- `AkademikContract` — resolusi profil mahasiswa (by user / by NIM), daftar dosen, detail dosen by ID / by user, untuk konsumsi modul lain (mis. Skripsi)
- `MahasiswaDTO`, `DosenDTO`, `DosenDTOList` — bentuk data yang melintasi batas modul
- `DosenDTO->userId` — akun login dosen (biasanya role `validator`); modul lain memakai ini untuk resolusi penerima notifikasi tanpa menyentuh Akademik
- `MahasiswaDTO->prodi` tetap string (nama prodi, di-resolve dari relasi `prodi_id`) agar konsumen Skripsi tidak ikut berubah saat kolom beralih ke FK

## Allowed dependencies

- `App\Modules\Support\*` (kerangka modul)
- Shared kernel: `App\Http\Controllers\Controller`, `App\Models\User`
- Tidak bergantung pada modul lain

## Events published

- Tidak ada (belum ada kebutuhan; PRD §7.1 adalah CRUD referensi)

## Events consumed

- Tidak ada## Explicitly NOT exposed
- Model Eloquent `Dosen` dan `Mahasiswa` — internal modul. Modul lain wajib lewat `AkademikContract`.
- Tabel `akademik_*` — jangan di-query langsung dari modul lain (boundary rule #2).
- Penugasan role validator pada akun dosen — dikelola di luar modul (core seeder/admin), Akademik hanya menyimpan tautan `user_id`.

## Notes for maintainers

- `users` adalah tabel core, jadi FK `akademik_mahasiswas.user_id → users` diperbolehkan (PRD §3.1). FK ke tabel modul lain tetap dilarang.
- Saat modul Skripsi dibangun, ia mengonsumsi `AkademikContract` ini untuk daftar dosen (penugasan validator) dan data mahasiswa — bukan mengimpor model di sini.
- CRUD admin dosen & mahasiswa dipagari `role:admin` di `routes.php`; Skripsi nanti menambah guard role-nya sendiri.
