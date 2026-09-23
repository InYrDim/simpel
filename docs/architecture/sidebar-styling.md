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

1. **Token di `app.css`.** Warna sidebar diekspresikan lewat sembilan token semantic di `:root` (mode terang) dan `.dark` (mode gelap). Tailwind memetakan token ini ke utility (`bg-sidebar`, `text-sidebar-foreground`, `bg-sidebar-accent`, dst.) lewat blok `@theme inline`. Sidebar **mengikuti tema halaman** (bukan panel yang selalu gelap).
2. **Treatment state di `app.css` (`@layer components`).** Hal yang tidak bisa diekspresikan token saja, di-scope ke `[data-slot='sidebar']`: hover lembut, status aktif berupa pill dengan tint aksen brand (`--sidebar-active-bg` + `--sidebar-active-fg`), dan tactile feedback `:active`.

Pola restyle yang disarankan: **ubah token dulu, buka blok komponen hanya kalau perlu treatment baru**. Hindari hardcode warna di TSX; semua warna lewat token.

## Token Referensi

Didefinisikan di `:root` dan `.dark` dalam `app.css` (format `oklch()`):

| Token | Peran | Dipakai oleh |
|---|---|---|
| `--sidebar` | Latar panel | `bg-sidebar` di `ui/sidebar.tsx` |
| `--sidebar-foreground` | Teks normal | `text-sidebar-foreground`, ikon nav |
| `--sidebar-primary` / `--sidebar-primary-foreground` | Aksen brand (kotak logo) | `app-logo.tsx` memakai `bg-primary`/`text-primary-foreground`, bukan token ini |
| `--sidebar-accent` | Latar hover & state terbuka | `ui/sidebar.tsx` (menu button, sub button, menu action, trigger dropdown) |
| `--sidebar-accent-foreground` | Teks saat hover | idem |
| `--sidebar-border` | Border panel floating, garis submenu, divider, **card dashboard & topbar** | lihat relasi |
| `--sidebar-ring` | Focus ring | `ui/sidebar.tsx` |
| `--sidebar-active-bg` / `--sidebar-active-fg` | Pill item aktif (custom, bukan bawaan shadcn) | blok `@layer components` di `app.css` |

Perilaku aktif/hover saat ini (jangan diubah lewat TSX):

- Hover: `--sidebar-accent` / `--sidebar-accent-foreground`.
- Aktif: `--sidebar-active-bg` (tint biru brand) + `--sidebar-active-fg` + `font-weight: 500`.
- Aktif + hover: campuran via `color-mix()`.
- Tekan (`:active`): `scale(0.98)`.

## Limitasi

1. **`ui/sidebar.tsx` adalah primitives shadcn/ui.** Semua class Tailwind di dalamnya tersebar di string cva yang panjang. Untuk restyle, jangan edit file ini — itu akan mengubah semua pemakai primitives (termasuk drawer nav di `app-header.tsx`). Restyle lewat token + blok komponen ter-scope. Sesuatu yang mustahil lewat token? Baru pertimbangkan edit primitives, dengan alasan tercatat.
2. **Lebar & lebar ikon hardcoded sebagai konstanta TS** (`SIDEBAR_WIDTH = "16rem"`, `SIDEBAR_WIDTH_ICON = "3rem"`) di `ui/sidebar.tsx`, bukan token CSS. Mengubah lebar panel = edit konstanta itu, bukan `app.css`.
3. **Collapse ke mode ikon (`collapsible="icon"`)** menuntut ikon berukuran tetap dan tooltip. Kalau menambah item nav baru, pastikan punya `icon` dan judul pendek; label tidak ikut terpotong rapi saat collapse.
4. **Nested submenu didukung rekursif** (`NavSubItem` memanggil dirinya untuk `NavChild.children`). Treatment CSS aktif harus tetap berlaku untuk `menu-sub-button`, bukan hanya `menu-button` — lihat selektor di blok komponen.
5. **Aksen tunggal.** Warna aksen hanya untuk status aktif; jangan menambah warna kedua untuk hover/badge/divider. Hover memakai accent netral, bukan aksen brand.
6. **Kontras AA wajib** di semua kombinasi token (`--sidebar-foreground` atas `--sidebar`, `--sidebar-active-fg` atas `--sidebar-active-bg` atas `--sidebar`), di kedua mode. Kalau mengganti nilai token, cek ulang.
7. **Mobile drawer** di-render sebagai Sheet (bukan elemen sidebar yang sama) dengan `data-mobile="true"`; ia mewarisi token yang sama, jadi uji restyle di mobile juga.
8. **Tidak ada animasi masuk/keluar panel**; satu-satunya transksi adalah lebar saat collapse dan `:active` scale. Tambah animasi hanya kalau bermotif (feedback/hierarki) dan hormati `prefers-reduced-motion`.

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
| `app-logo.tsx` | Memakai `bg-primary`/`text-primary-foreground` (token umum, bukan token sidebar) | Mengubah `--sidebar-primary` saja tidak mengubah logo; logo berubah kalau `--primary` berubah |
| Blok `@layer components` di `app.css` | Selektor ter-scope `[data-slot='sidebar']` | Tidak memengaruhi area di luar sidebar |

## Checklist Sebelum Merge

1. `npm run types:check` dan `npm run build` lolos.
2. Sidebar dicek di **kedua mode** (light/dark) dan di **mobile drawer**.
3. Status collapse (ikon-only) masih rapi; tooltip muncul.
4. Item aktif masih satu aksen; tidak ada warna hardcode baru di TSX.
5. Card dashboard & topbar tidak berubah warna (kalau `--sidebar-border` berubah, ini di luar batas, lihat tabel relasi).
6. Cookie `sidebar_state` dan prop `sidebarOpen` tidak berubah.
7. Tanpa gradient/glow/eyebrow dekoratif (anti-slop); treatment baru harus punya alasan (feedback/hierarki).
