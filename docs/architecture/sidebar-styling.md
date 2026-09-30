# Referensi Styling Sidebar

Panduan untuk siapa pun (manusia maupun agent) yang ingin mengubah tampilan sidebar aplikasi. Dokumen ini menjelaskan arsitektur styling, token yang dipakai, batasan yang harus dijaga, dan bagian lain yang terpengaruh oleh perubahan sidebar.

## Peta File

| File | Peran | Boleh diubah? |
|---|---|---|
| `resources/css/app.css` | Definisi token `--sidebar-*` (`:root` dan `.dark`) + treatment state di `@layer components` | Ya, ini titik masuk utama restyle |
| `resources/js/components/app-sidebar.tsx` | Komposisi sidebar: pilihan `variant`, urutan header/content/footer, divider | Ya |
| `resources/js/components/nav-main.tsx` | Item nav tingkat atas + submenu (collapsible, rekursif) | Ya, tapi lihat restriction hook |
| `resources/js/components/nav-footer.tsx` | Tautan eksternal di footer (Repository, Documentation) | Ya |
| `resources/js/components/nav-user.tsx` | Kartu user + dropdown menu di footer | Ya |
| `resources/js/components/app-logo.tsx` | Logo + nama aplikasi di header sidebar | Ya |
| `resources/js/components/user-info.tsx` | Avatar + nama user (dipakai sidebar **dan** dropdown menu) | Hati-hati, lihat relasi |
| `resources/js/components/ui/sidebar.tsx` | Primitives shadcn/ui (Sidebar, SidebarMenuButton, dsb.) | Hindari; lihat limitasi |
| `resources/js/components/app-sidebar-header.tsx` | Topbar (bukan bagian sidebar, tapi bersebelahan visual) | Di luar lingkup dokumen ini |
| `resources/js/components/app-header.tsx` | Header varian `header` + drawer nav mobile | Terpengaruh via token, jangan diubah untuk restyle sidebar |

## Arsitektur Styling (Cara Kerja Saat Ini)

Styling sidebar dibagi dua lapis:

1. **Token global di `app.css` (`:root` / `.dark`).** Nilai netral yang ikut tema. Dipakai konsumen token sidebar DI LUAR panel (card dashboard, topbar, drawer nav mobile).
2. **Override ter-scope di `app.css` (`@layer components`).** Gaya desain aktif adalah **"ink flat"**: panel sidebar SELALU gelap (light & dark mode) dengan warna solid — tanpa gradient, glow, atau shadow dekoratif. Satu ide: kontras panel gelap vs konten terang; identitas datang dari pill aktif solid biru brand dan tipografi. Panel memakai `variant="sidebar"` (attached, flat border, tanpa rounded). Ini dicapai dengan me-override `--sidebar`, `--sidebar-foreground`, `--sidebar-accent`, `--sidebar-accent-foreground`, `--sidebar-border` **hanya di dalam** `[data-slot='sidebar']`, plus treatment state di blok yang sama.

Konsekuensi desain ini: di light mode aplikasi terlihat seperti "konten terang + panel navigasi gelap" — itu disengaja. Card dashboard tidak ikut gelap karena override-nya ter-scope.

Pola restyle yang disarankan: **ubah blok komponen ter-scope dulu** (itu sumber kebenaran visual panel), dan jaga token global tetap netral agar konsumen luar tidak terdampak. Hindari hardcode warna di TSX. Prinsip anti-slop yang dipakai: satu ide jelas, nol gradient dekoratif, satu warna aksen.

## Token Referensi

Didefinisikan di `:root` dan `.dark` dalam `app.css` (format `oklch()`):

| Token | Peran | Dipakai oleh |
|---|---|---|
| `--sidebar` | Latar panel (di-override navy di dalam `[data-slot='sidebar']`) | `bg-sidebar` di `ui/sidebar.tsx` |
| `--sidebar-foreground` | Teks normal (di-override putih di dalam panel) | `text-sidebar-foreground`, ikon nav |
| `--sidebar-primary` / `--sidebar-primary-foreground` | Tidak dipakai komponen sidebar saat ini | kotak logo memakai `bg-primary` (token umum) di `app-logo.tsx` |
| `--sidebar-accent` | Latar hover & state terbuka (di-override; di panel dipakai via override) | `ui/sidebar.tsx` (menu action, trigger dropdown) |
| `--sidebar-accent-foreground` | Teks saat hover | idem |
| `--sidebar-border` | Border panel floating, garis submenu, divider, **card dashboard & topbar** | lihat relasi; nilai global dijaga netral |
| `--sidebar-ring` | Focus ring | `ui/sidebar.tsx` |

Perilaku aktif/hover saat ini (didefinisikan di blok komponen ter-scope, jangan diubah lewat TSX):

- Panel: solid `oklch(0.19 0.014 264)` (ink) + teks `0.93 L`, di kedua mode.
- Hover: putih transparan `oklch(1 0 0 / 0.06)` (bukan accent brand).
- Aktif: **pill solid biru diperdalam** `oklch(0.42 0.13 258)` + teks putih + `font-weight: 500`, tanpa gradient/shadow/glow (kontras AA terjaga).
- Aktif + hover: `oklch(0.47 0.13 258)`.
- Tekan (`:active`): `scale(0.98)`.

## Limitasi

1. **`ui/sidebar.tsx` adalah primitives shadcn/ui.** Semua class Tailwind di dalamnya tersebar di string cva yang panjang. Untuk restyle, jangan edit file ini — itu akan mengubah semua pemakai primitives (termasuk drawer nav di `app-header.tsx`). Restyle lewat token + blok komponen ter-scope. Sesuatu yang mustahil lewat token? Baru pertimbangkan edit primitives, dengan alasan tercatat.
2. **Lebar & lebar ikon hardcoded sebagai konstanta TS** (`SIDEBAR_WIDTH = "16rem"`, `SIDEBAR_WIDTH_ICON = "3rem"`) di `ui/sidebar.tsx`, bukan token CSS. Mengubah lebar panel = edit konstanta itu, bukan `app.css`.
3. **Collapse ke mode ikon (`collapsible="icon"`)** menuntut ikon berukuran tetap dan tooltip. Kalau menambah item nav baru, pastikan punya `icon` dan judul pendek; label tidak ikut terpotong rapi saat collapse.
4. **Nested submenu didukung rekursif** (`NavSubItem` memanggil dirinya untuk `NavChild.children`). Treatment CSS aktif harus tetap berlaku untuk `menu-sub-button`, bukan hanya `menu-button` — lihat selektor di blok komponen.
5. **Aksen tunggal.** Warna aksen hanya untuk status aktif; jangan menambah warna kedua untuk hover/badge/divider. Hover memakai accent netral, bukan aksen brand.
6. **Kontras AA wajib** di semua kombinasi (teks putih atas pill biru `0.42 L`, teks `0.93 L` atas panel `0.19 L`, hover putih transparan). Kalau mengganti nilai, cek ulang.
7. **Mobile drawer** di-render sebagai Sheet dengan `[data-slot='sidebar']` + `data-mobile="true"`; karena override token ter-scope ke atribut itu, drawer mobile **ikut tema ink gelap**. Uji restyle di mobile juga.
8. **Panel selalu gelap adalah keputusan desain, bukan bug light mode.** Jangan "memperbaiki" dengan menghapus override ter-scope tanpa keputusan desain baru; card dashboard & topbar mengandalkan token global yang netral.
9. **Tidak ada animasi masuk/keluar panel**; satu-satunya transksi adalah lebar saat collapse dan `:active` scale. Tambah animasi hanya kalau bermotif (feedback/hierarki) dan hormati `prefers-reduced-motion`.

## Restriction (Jangan Diubah)

- **State collapse disimpan di cookie `sidebar_state`** (`ui/sidebar.tsx`), dikecualikan dari enkripsi di `bootstrap/app.php`, dan di-share server ke `props.sidebarOpen` (`app/Http/Middleware/HandleInertiaRequests.php`) sebagai initial state `AppShell`. Jangan ganti nama cookie atau prop Inertia; itu mengubah kontrak server-client.
- **Nama cookie/prop → jangan sentuh.** `data-test="sidebar-menu-button"` di `nav-user.tsx` juga kontrak UI test; pertahankan.
- **`sidebar.tsx` bukan tempat memindahkan warna ke komponen.** Semua warna tetap lewat token; warna hardcoded (hex/oklch di className TSX) akan rusak saat token berubah dan dianggap regressi review.
- **`user-info.tsx` dipakai dua konteks** (sidebar + dropdown menu user yang muncul di atas konten terang): jangan menyesuaikan warnanya untuk panel sidebar; sesuaikan lewat token.
- **`app-header.tsx` (drawer mobile varian header) memakai `bg-sidebar`** tapi bukan bagian dari sidebar utama; restyle sidebar jangan dilakukan di file itu.
- **Struktur nav (item, urutan, role filter)** di `app-sidebar.tsx` + `lib/module-navigation.ts` bukan urusan styling; perubahan di sana mengubah hak akses UI, bukan tampilan.

## Bagian Lain yang Terpengaruh (Relasi)

Mengubah token `--sidebar-*` tidak hanya mengubah sidebar. Konsumen lain dari token/utility yang sama:

| Bagian | Kepentingan | Efek jika token berubah |
|---|---|---|
| `pages/dashboard.tsx` + `pages/manajemen/index.tsx` | Card memakai `border-sidebar-border/70 dark:border-sidebar-border` (bawaan starter kit) | Border card ikut bergeser mengikuti `--sidebar-border`; jalur aman: nilai `--sidebar-border` dibiarkan sama dengan `--border` |
| `app-sidebar-header.tsx` (topbar) | Border bawah memakai `border-sidebar-border/50` | Border topbar ikut bergeser |
| `app-header.tsx` | Drawer mobile varian `header`: `bg-sidebar` + border `sidebar-border` | Panel drawer ikut bergeser warna |
| `ui/sidebar.tsx` | Primitives: semua warna panel/hover/border di dalam drawer ini juga dari token | Ikon toggle, trigger, dsb. ikut |
| `nav-user.tsx` | `text-sidebar-accent-foreground` + `data-[state=open]:bg-sidebar-accent` pada trigger dropdown user | Dropdown trigger ikut bergeser |
| `user-info.tsx` | Avatar fallback `bg-muted text-foreground` (netral, token umum) | Tidak terpengaruh token sidebar |
| `app-logo.tsx` | Kotak logo memakai `bg-primary`/`text-primary-foreground` (token umum) | Tidak terpengaruh token sidebar; berubah kalau `--primary` berubah |
| Blok `@layer components` di `app.css` | Selektor ter-scope `[data-slot='sidebar']` | Tidak memengaruhi area di luar sidebar |

## Checklist Sebelum Merge

1. `npm run types:check` dan `npm run build` lolos.
2. Sidebar dicek di **kedua mode** (light/dark; panel memang selalu navy) dan di **mobile drawer** (juga navy).
3. Status collapse (ikon-only) masih rapi; tooltip muncul.
4. Item aktif masih satu aksen (pill solid biru brand); tidak ada warna hardcode baru di TSX.
5. Card dashboard & topbar tidak berubah warna (kalau `--sidebar-border` berubah, ini di luar batas, lihat tabel relasi).
6. Cookie `sidebar_state` dan prop `sidebarOpen` tidak berubah.
7. Tanpa gradient/glow/shadow dekoratif — panel solid, pill aktif solid. Treatment baru harus punya alasan (feedback/hierarki), satu ide desain yang jelas.
