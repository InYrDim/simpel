Saya ingin membuat flow sistem pengajuan judul mulai dari mahasiswa->admin->validator

Requirement:

1. Mahasiswa perlu model berikut:

- nama (Required)
- nim (Required)
- dosen penasehat akademik (Dosen PA) (Required)
- sisanya tambahkan sesuai kamu

untuk berikutnya, tentukan sendiri modelnya untuk validatornya
role yang ada yang saya inginkan:

- Mahasiswa
- Admin
- Validator

Pada halaman, terdapat menu skripsi berisi submenu.
submenu 1: Pengajuan
pada submenu ini mahasiswa bisa :

- melihat keadaan pengajuan judul skripsinya, termasuk statusnya (belum mengajukan, diverifikasi admin, diverifikasi validator, disetujui, ditolak admin, ditolak validator)
- melakukan pengajuan judul skripsi, mahasiswa musti mengajukan 3 judul (tidak kurang tidak lebih). Terdapat tombol untuk melakukan pengajuan (restrict, disabled. Hanya aktif ketika statusnya belum mengajukan atau ditolak). Flow UX yang saya inginkan adalah terdpat step by step dialog card. Step 1 Isi detail pengajuan (Judul, Deskripsi, Topik) masing masing tiap judul dari 3 judul. Kemudian Next Step 2 masih di dialog yang sama, Download template pengajuan judul skripsi dan terdapat input untuk upload file berkas pengajuan berdasarkan template tadi (Max 5MB, Only PDF Allowed). Step 3 di modal dialog yang sama, Verifikasi Pengajuan. Melihat informasi judul dan berkas apakah sudah terupload dan sesuai dengan yang di sampaikan di Step 1 dan 2.

submenu 2: Daftar Judul
tampilan ui berupa tabel

- user bisa melihat list daftar judul, beserta detailnya.
- detail judul ditampilkan dalam bentuk dialog modal. Terdapat informasi: Judul, Deskripsi, Topik, Dosen Pembimbing 1 dan 2, Dosen Penguji 1 dan 2, NIM.

Tetap gunakan arsitektur modular monolith.
