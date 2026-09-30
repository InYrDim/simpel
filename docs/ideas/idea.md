# Ide Fitur Baru

> Disimpan 2026-10-01 dari hasil agent `ideator`. Status: **usulan, belum diputuskan**. Belum ada yang dikerjakan.

## Kondisi aplikasi saat ini

- **Peran** (`database/seeders/RolePermissionSeeder.php`): hanya `mahasiswa`, `validator`, `admin`. Belum ada peran dosen umum, kaprodi, atau pembimbing, padahal `akademik_prodis.kaprodi_id` dan `akademik_dosens.user_id` sudah ada.
- **Alur skripsi berhenti di tengah.** Sekarang: pengajuan 3 judul → verifikasi admin → putusan validator → penugasan pembimbing/penguji (`AssignPenugasan`). Tidak ada bimbingan, seminar, sidang, atau status lulus.
- **Dashboard masih placeholder** (`resources/js/pages/dashboard.tsx`).
- **Kebutuhan wawancara yang belum dibangun** (`docs/research/raw/notes/wawancara/latar-belakang-awal.md`): (1) cek kemiripan judul, (3) rekomendasi validator berdasarkan bidang dan topik. Nomor (2) Beban Dosen sudah ada.
- **Data yang terkumpul tapi belum dipakai:** `DosenDTO->bidang` dengan `judul.topik`/`kategori_id`; `MahasiswaDTO->angkatan` dengan `skripsi_pengajuan_riwayats.created_at`; ringkasan `tanpa_dosen_pa`.
- **Temuan sampingan (bug/utang):** `VerifikasiAdminController::index()` memanggil `mahasiswaByUserId()` dua kali per baris (N+1). Pindahkan ke `mahasiswaByUserIds()` sesuai `app/Modules/Skripsi/CONTRACT.md`.

## Ringkasan

| #   | Ide                                          | Jenis     | Usaha | Untuk                       |
| --- | -------------------------------------------- | --------- | ----- | --------------------------- |
| 1   | Rekomendasi validator (bidang, topik, beban) | Alur inti | S–M   | Admin                       |
| 2   | Cek kemiripan judul (tahap 1 leksikal)       | Berani    | M     | Mahasiswa, admin, validator |
| 3   | Dashboard per peran                          | Quick win | M     | Semua                       |
| 4   | Umur antrean dan pengingat tenggat           | Quick win | S–M   | Admin, validator            |
| 5   | Deteksi mahasiswa berisiko macet             | Berani    | M     | Admin                       |
| 6   | Portal dosen: "Mahasiswa saya"               | Alur inti | M     | Dosen                       |
| 7   | Log dan kartu bimbingan                      | Alur inti | L     | Mahasiswa, pembimbing       |
| 8   | Pendaftaran dan penjadwalan sidang           | Berani    | L     | Mahasiswa, admin, penguji   |
| 9   | Peran Kaprodi, laporan per prodi             | Alur inti | M     | Kaprodi                     |
| 10  | Pusat notifikasi                             | Quick win | S     | Semua                       |
| 11  | Template surat terisi otomatis               | Quick win | S–M   | Mahasiswa                   |

## Top 3 rekomendasi

1. **Rekomendasi validator (#1):** permintaan langsung dari wawancara, data sudah ada, tanpa migrasi.
2. **Cek kemiripan judul tahap 1 (#2):** inti riset dan pembeda aplikasi; tahap 1 tanpa paket menyiapkan tempat untuk eksperimen embedding.
3. **Dashboard per peran (#3):** mengubah halaman kosong menjadi daftar kerja harian; rumah bagi #4 dan #5.

## Urutan PR usulan (setiap `feat/*` → `test`)

1. `fix/verifikasi-n-plus-one`: ganti pemanggilan per baris dengan `mahasiswaByUserIds()`.
2. `feat/rekomendasi-validator`: service pemeringkat (bidang/topik/kategori + beban aktif) + tes unit skor.
3. `feat/rekomendasi-validator-ui`: dropdown berperingkat dengan lencana "Disarankan" di `verifikasi/index.tsx`.
4. `feat/kemiripan-judul-service`: interface `PendeteksiKemiripan` + TF-IDF/cosine + tes.
5. `feat/kemiripan-judul-ui`: endpoint JSON, peringatan di `langkah-judul.tsx`, panel "Judul serupa" di verifikasi/putusan.
6. `feat/dashboard-kontrak`: method kontrak ringkasan (`SkripsiContract`, `AkademikContract`) + tes. Menunggu keputusan pemilik dashboard.
7. `feat/dashboard-peran`: `DashboardController` + `dashboard.tsx` dengan kartu per peran.

## Keputusan yang dibutuhkan dari user

- **#1:** perlu pemetaan kategori → bidang yang dikelola admin? (`bidang` dan `topik` teks bebas, pencocokan kata rapuh.)
- **#2:** korpus pembanding hanya judul disetujui atau semua? Ambang skor? Perlu impor arsip judul lama? Jangan bertabrakan dengan desain eksperimen skripsi.
- **#3:** controller core `DashboardController` (lewat method kontrak baru) atau widget per modul?
- **#6:** `validator` dilebur ke peran `dosen`, atau tetap terpisah?
- **#8:** apakah aplikasi menanggung alur sampai sidang dan lulus, atau tetap fokus ke pengajuan judul? Siapa pemilik master ruang?

---

## Detail ide

### 1. Rekomendasi validator berdasarkan bidang, topik, dan beban — [Alur inti]

- **Untuk siapa:** Admin
- **Masalah:** Saat verifikasi, admin memilih validator dari daftar dosen polos berdasarkan kira-kira. Kecocokan bidang dan beban tidak terlihat.
- **Ide:** Dropdown validator di dialog verifikasi diurutkan dengan skor: kecocokan `bidang` dosen dengan `topik`/kategori ketiga judul, dikurangi beban aktif. Tiap opsi berlencana "Cocok: Jaringan · 3 pengajuan aktif". Tiga teratas ditandai "Disarankan". Admin tetap bebas memilih dosen lain.
- **Pijakan:** `VerifikasiAdminController::index()` (`dosenOptions`), `AkademikContract::daftarDosen()` (`bidang`), `SkripsiMonitoringService::bebanDosen()`, `skripsi_judul_pengajuans.topik/kategori_id`, `pages/skripsi/verifikasi/index.tsx`.
- **Modul:** Skripsi (cukup `AkademikContract` yang ada).
- **Usaha:** S–M. Tanpa migrasi; service pemeringkat + satu dropdown. Mulai dari tabel pemetaan kategori→bidang atau pencocokan kata.
- **Risiko:** teks bebas → pencocokan kata rapuh.

### 2. Cek kemiripan judul (tahap 1: leksikal) — [Berani]

- **Untuk siapa:** Mahasiswa, admin, validator
- **Masalah:** Judul mirip judul lama bisa lolos karena pemeriksaan manual. Ini inti riset (`docs/research/justification.md`) tetapi belum ada kodenya.
- **Ide:** Di wizard muncul peringatan "3 judul serupa" (skor + status). Admin/validator melihat panel "Judul serupa" per judul. Tahap 1: TF-IDF + cosine di PHP murni terhadap `skripsi_judul_pengajuans`. Tahap 2 (embedding + reranker) dipasang di balik interface `PendeteksiKemiripan` yang sama.
- **Pijakan:** `skripsi_judul_pengajuans.judul/deskripsi`, `pages/skripsi/pengajuan/components/langkah-judul.tsx`, `pages/skripsi/verifikasi/index.tsx`, `pages/skripsi/putusan/index.tsx`, pola binding `PembuatTemplatePengajuan` → `TemplateStatis`.
- **Modul:** Skripsi.
- **Usaha:** M untuk tahap 1 (endpoint JSON, service, cache vektor; mungkin migrasi indeks). Tahap 2 L dan **butuh paket/servis baru**.
- **Risiko:** ambang skor dan korpus pembanding; arsip judul lama.

### 3. Dashboard per peran ("Yang perlu saya kerjakan") — [Quick win]

- **Untuk siapa:** Semua peran
- **Masalah:** Halaman pertama setelah login kosong; admin/validator harus membuka menu satu per satu; mahasiswa tidak tahu langkah berikutnya.
- **Ide:** Admin: menunggu verifikasi, disetujui belum ada pembimbing, mahasiswa aktif tanpa PA (tautan filter `dosen_pa_id=kosong`). Validator: antrean putusan + umur tertua. Mahasiswa: status pengajuan terakhir, catatan revisi, tombol aksi.
- **Pijakan:** `SkripsiMonitoringService::ringkasan()`, status `diverifikasi_admin` + `validator_id`, ringkasan `tanpa_dosen_pa`, `dashboard.tsx`.
- **Modul:** perlu keputusan. Opsi A: `DashboardController` core + `SkripsiContract::ringkasanUntuk(User)` dan `AkademikContract::jumlahTanpaPa()` (baru). Opsi B: widget per modul.
- **Usaha:** M. Dua method kontrak, satu halaman, tanpa migrasi.

### 4. Umur antrean dan pengingat tenggat — [Quick win]

- **Untuk siapa:** Admin, validator
- **Masalah:** Pengajuan bisa diam berhari-hari tanpa disadari.
- **Ide:** Kolom "Menunggu 6 hari" (warna naik di atas ambang) di Verifikasi, Putusan, Monitoring. Job harian mengirim notifikasi ke admin/validator yang punya item melewati N hari. Mahasiswa melihat "Rata-rata verifikasi: 3 hari".
- **Pijakan:** `submitted_at`, `verified_at`, `skripsi_pengajuan_riwayats.created_at`, `routes/console.php`, pola `Notifications/*` + listener `ShouldQueue`.
- **Modul:** Skripsi.
- **Usaha:** S–M. Ambang di config; satu command terjadwal, satu notifikasi, kolom UI.
- **Risiko:** nilai ambang (mis. 3 hari verifikasi, 7 hari putusan); hari kerja atau kalender.

### 5. Deteksi mahasiswa berisiko macet — [Berani]

- **Untuk siapa:** Admin (nanti kaprodi dan dosen PA)
- **Masalah:** Mahasiswa angkatan tua tanpa judul disetujui, atau revisi didiamkan, baru ketahuan menjelang batas studi.
- **Ide:** Laporan > "Perlu perhatian", daftar mahasiswa aktif dengan alasan eksplisit: angkatan ≥ 4 tahun tanpa judul disetujui; `direvisi` tanpa resubmit > 30 hari; ditolak ≥ 2 kali; disetujui tapi belum ada pembimbing. Tautan ke Riwayat dan Dosen PA; ekspor CSV lewat pola `ExportController`.
- **Pijakan:** `MahasiswaDTO->angkatan/dosenPaId`, `StatusMahasiswa`, `skripsi_pengajuan_riwayats`, `StatusPengajuan`, `ExportController`.
- **Modul:** Skripsi + method kontrak baru `AkademikContract::daftarMahasiswaAktif()` (status sengaja tidak masuk DTO, N-023).
- **Usaha:** M. Tanpa migrasi.
- **Risiko:** method baru perlu persetujuan karena terkait N-023; aturan dan ambang perlu dikonfirmasi.

### 6. Portal dosen: "Mahasiswa saya" — [Alur inti]

- **Untuk siapa:** Dosen (PA, pembimbing, penguji)
- **Masalah:** Dosen yang ditugaskan tidak punya halaman; hanya `validator` yang punya menu. Penugasan admin tidak sampai ke dosennya.
- **Ide:** Peran `dosen` dengan tab "Anak PA" (nama, angkatan, status skripsi) dan "Bimbingan/Uji" (judul, peran saya, status). Penugasan memicu notifikasi ke dosen.
- **Pijakan:** `akademik_dosens.user_id`, `AkademikContract::dosenByUserId()`, `akademik_mahasiswas.dosen_pa_id`, kolom `dosen_pembimbing_*`/`dosen_penguji_*`, `AssignPenugasan`.
- **Modul:** Skripsi untuk tab bimbingan; tab PA milik Akademik, butuh `SkripsiContract::statusTerakhirUntukMahasiswa(list<int>)` (baru). Peran/permission di `RolePermissionSeeder`.
- **Usaha:** M. Peran baru, dua halaman/tab, event + notifikasi `PenugasanDitetapkan`; tanpa migrasi.
- **Risiko:** `validator` dilebur ke `dosen` atau terpisah?

### 7. Log bimbingan dan kartu bimbingan — [Alur inti]

- **Untuk siapa:** Mahasiswa, dosen pembimbing
- **Masalah:** Setelah judul disetujui, tidak ada pencatatan. Kartu bimbingan (syarat seminar) masih kertas.
- **Ide:** Mahasiswa mencatat sesi (tanggal, topik, berkas opsional), pembimbing mengonfirmasi/memberi catatan. Timeline meniru komponen Riwayat. Jumlah bimbingan terkonfirmasi menjadi syarat ide 8.
- **Pijakan:** `skripsi_judul_pengajuans` (disetujui + pembimbing), berkas privat disk `local`, `pages/skripsi/riwayat/index.tsx`.
- **Modul:** Skripsi. Bergantung pada #6.
- **Usaha:** L. Migrasi `skripsi_bimbingans`, dua halaman, permission, notifikasi. PDF kartu **butuh paket baru**; CSV/print CSS tanpa paket.
- **Risiko:** minimal jumlah bimbingan; apakah pembimbing 2 wajib konfirmasi.

### 8. Pendaftaran dan penjadwalan sidang dengan deteksi bentrok — [Berani]

- **Untuk siapa:** Mahasiswa, admin, penguji
- **Masalah:** Penguji tersimpan (`dosen_penguji_1/2`) tapi tidak dipakai. Penjadwalan sidang bolak-balik lewat chat.
- **Ide:** Mahasiswa mendaftar (syarat bimbingan ≥ N). Admin memilih slot; sistem menandai bentrok dosen/ruang dan menyarankan slot kosong terdekat. Hasil sidang (lulus / lulus revisi / ulang) melanjutkan status; bila lulus, status mahasiswa di Akademik berubah lewat event.
- **Pijakan:** kolom `dosen_*`, pola enum + `bolehTransisiKe()` + riwayat audit, Beban Dosen, `StatusMahasiswa::Lulus`.
- **Modul:** Skripsi. Lintas modul lewat event `SidangDinyatakanLulus` atau `AkademikContract::tandaiLulus()`.
- **Usaha:** L. Migrasi jadwal/ruang/hasil, enum tahap baru, tiga halaman; kalender penuh **butuh paket baru**.
- **Risiko:** cakupan aplikasi; pemilik master ruang.

### 9. Peran Kaprodi dengan laporan terbatas per prodi — [Alur inti]

- **Untuk siapa:** Kaprodi
- **Masalah:** `kaprodi_id` tersimpan tapi kaprodi tidak bisa login melihat prodinya; laporan hanya admin dan tanpa filter prodi/angkatan.
- **Ide:** Peran `kaprodi` membaca Statistik, Monitoring, Beban Dosen, Export yang otomatis dibatasi ke prodinya. Admin mendapat filter prodi dan angkatan.
- **Pijakan:** `akademik_prodis.kaprodi_id`, `MahasiswaDTO->prodi/angkatan`, `SkripsiMonitoringService::queryPengajuan()`, `ExportController`.
- **Modul:** Skripsi + `AkademikContract::prodiYangDipimpin(int $userId)` dan `mahasiswaIdsDiProdi(...)` (baru).
- **Usaha:** M. Tanpa migrasi.
- **Risiko:** `MahasiswaDTO->prodi` berupa string nama (N-002); pakai ID lewat method kontrak. Kajur disimpan sebagai teks tanpa akun.

### 10. Pusat notifikasi — [Quick win]

- **Untuk siapa:** Semua peran
- **Masalah:** Lonceng hanya 10 notifikasi terakhir (`HandleInertiaRequests` `take(10)`); tidak ada "tandai semua dibaca".
- **Ide:** Halaman `/notifikasi` berpaginasi, filter belum dibaca, tautan ke objek, aksi "tandai semua dibaca".
- **Pijakan:** `NotificationController`, tabel `notifications`, `components/notification-bell.tsx`, `TablePagination`.
- **Modul:** Core.
- **Usaha:** S. Dua route, satu halaman, tanpa migrasi.

### 11. Template surat pengajuan terisi otomatis — [Quick win, butuh paket baru]

- **Untuk siapa:** Mahasiswa
- **Masalah:** Mahasiswa mengunduh template kosong lalu mengetik ulang data yang sudah ada di sistem.
- **Ide:** Tombol template di wizard menghasilkan DOCX berisi nama, NIM, prodi, PA, dan tiga judul dari draft.
- **Pijakan:** `Services/Template/PembuatTemplatePengajuan` → `TemplateStatis` (TODO di `app/Modules/Skripsi/CONTRACT.md`), route `skripsi.pengajuan.template.draft`, `AkademikContract::mahasiswaByUserId()`.
- **Modul:** Skripsi.
- **Usaha:** S–M. **Butuh paket baru** (mis. `phpoffice/phpword`); implementasi kedua dari interface yang ada.
- **Risiko:** placeholder template dan persetujuan paket masih menunggu user.

---

## Ide yang sengaja dibuang

- **Impor CSV mahasiswa:** sudah diputuskan tidak dikerjakan (N-024).
- **Aksi massal tetapkan PA / toast daftar:** sisa pekerjaan di N-044, bukan ide baru.
- **Status mahasiswa masuk `MahasiswaDTO`:** bertentangan dengan N-023; #5 memakai method kontrak khusus.
- **FK lintas modul untuk `mahasiswa_id`/`dosen_*`:** dilarang N-003; guard lewat kontrak sudah ada (N-039, N-042).
- **Chatbot/asisten AI umum, aplikasi mobile:** tidak menempel ke kode dan tidak menjawab kebutuhan wawancara.
- **Multi-tenant institusi:** masih "Explicitly Undecided" di `PRODUCT.md`.
- **Nilai/KRS/keuangan:** di luar fokus skripsi.
