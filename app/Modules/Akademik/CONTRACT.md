# Module: Akademik

## Owns

- Database tables: `akademik_dosens`, `akademik_mahasiswas`, `akademik_prodis`
- Core domain concepts: data referensi akademik — dosen (Dosen PA, validator, pembimbing, penguji), profil akademik mahasiswa (NIM, angkatan, dosen PA), dan program studi (Prodi, kaprodi)

## Public interface (Contracts/)

- `AkademikContract` — resolusi profil mahasiswa (by user / by NIM), daftar dosen, detail dosen by ID / by user, untuk konsumsi modul lain (mis. Skripsi)
- `MahasiswaDTO`, `DosenDTO`, `DosenDTOList` — bentuk data yang melintasi batas modul
- `DosenDTO->userId` — akun login dosen (biasanya role `validator`); modul lain memakai ini untuk resolusi penerima notifikasi tanpa menyentuh Akademik
- `MahasiswaDTO->prodi` tetap string (nama prodi, di-resolve dari relasi `prodi_id`) agar konsumen Skripsi tidak ikut berubah saat kolom beralih ke FK
- `MahasiswaDTO->dosenPaId` nullable: mahasiswa hasil registrasi mandiri belum punya dosen PA sampai admin mengisinya (filter `dosen_pa_id=kosong` + dialog ubah). Validasi admin mewajibkan dosen PA kecuali status `lulus`/`nonaktif`
- `akademik_mahasiswas.status` (enum internal `Enums\StatusMahasiswa`: aktif, cuti, lulus, nonaktif; default `aktif`) **tidak** masuk `MahasiswaDTO`, jadi kontrak publik tidak berubah
- Registrasi mandiri Fortify dimiliki modul ini: `AkademikServiceProvider` memasang `Fortify::createUsersUsing(Services\RegistrasiMahasiswa)` dan `Fortify::registerView` (props `prodiOptions`). Pendaftar mendapat peran `mahasiswa` + profil akademik (NIM, prodi, tanpa PA). Core tidak boleh bergantung pada modul, jadi logika ini tidak ada di `app/Actions/Fortify`.
- Gate `akademik.mahasiswa-kita` (`?User`, `string $nim`, `int $prodiId`) — dipanggil registrasi sebelum akun dibuat. **Masih stub (selalu lolos)**; logika pengecekan NIM ke sumber data kampus menyusul di `AkademikServiceProvider::defineGates()`

## Allowed dependencies

- `App\Modules\Support\*` (kerangka modul)
- Shared kernel: `App\Http\Controllers\Controller`, `App\Models\User`
- `App\Modules\Contracts\SkripsiContract` — hanya untuk bertanya apakah mahasiswa masih punya pengajuan skripsi dan apakah dosen masih dirujuk pengajuan sebagai validator/pembimbing/penguji (guard hapus). Tidak mengimpor kelas Skripsi

## Events published

- Tidak ada (belum ada kebutuhan; PRD §7.1 adalah CRUD referensi)

## Events consumed

- Tidak ada

## Explicitly NOT exposed

- Model Eloquent `Dosen` dan `Mahasiswa` — internal modul. Modul lain wajib lewat `AkademikContract`.
- Tabel `akademik_*` — jangan di-query langsung dari modul lain (boundary rule #2).
- Penugasan role validator pada akun dosen — dikelola di luar modul (core seeder/admin), Akademik hanya menyimpan tautan `user_id`.

## Notes for maintainers

- `users` adalah tabel core, jadi FK `akademik_mahasiswas.user_id → users` diperbolehkan (PRD §3.1). FK ke tabel modul lain tetap dilarang.
- Saat modul Skripsi dibangun, ia mengonsumsi `AkademikContract` ini untuk daftar dosen (penugasan validator) dan data mahasiswa — bukan mengimpor model di sini.
- CRUD admin dosen & mahasiswa dipagari `role:admin` di `routes.php`; Skripsi nanti menambah guard role-nya sendiri.
- `mahasiswaByUserIds()` adalah jalur BATCH (satu query `whereIn`) untuk konsumen yang memetakan identitas per baris — dipakai halaman Riwayat Pengajuan agar tidak N+1 (PRD ketahanan-teknis §3.2, keputusan #3). Daftar `user_id` kosong tidak menembak query sama sekali.
- Daftar admin mahasiswa (`MahasiswaController::index`) menerima `search`, `prodi_id`, `angkatan`, `dosen_pa_id` (angka atau `kosong`), `status`, `sort` (`nama|nim|angkatan`), `direction`, `per_page` (10/25/50). Nilai tak sah jatuh ke default, bukan galat validasi.
- Daftar admin juga mengirim `ringkasan.tanpa_dosen_pa` (jumlah mahasiswa berstatus selain lulus/nonaktif yang belum punya dosen PA), dipakai halaman sebagai tautan ke filter `dosen_pa_id=kosong`.
- Hapus mahasiswa menuntut `konfirmasi_nim` yang sama dengan NIM-nya (dicek di server). Hapus permanen dan tidak menyentuh akun `users`. Modul Skripsi merujuk mahasiswa tanpa FK, jadi penghapusan **ditolak** (error `mahasiswa`) bila `SkripsiContract::mahasiswaIdsDenganPengajuan()` mengembalikan ID-nya; baris daftar membawa `punya_pengajuan` (satu query per halaman). Arahkan admin ke status Nonaktif.
- Hapus dosen (`DosenController::destroy`) ditolak (flash `error`) bila dosen masih PA mahasiswa atau `SkripsiContract::dosenIdsDenganPenugasan()` mengembalikan ID-nya; baris daftar membawa `jumlah_mahasiswa_pa` dan `punya_penugasan`. FK `akademik_mahasiswas.dosen_pa_id` kini `nullOnDelete` (bukan cascade) sebagai jaring pengaman.
- `update()` tidak memvalidasi `user_id`: tautan akun ditetapkan saat pembuatan profil dan tidak bisa diubah.
