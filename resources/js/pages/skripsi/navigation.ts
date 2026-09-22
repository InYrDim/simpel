import { BarChart3, BookOpen } from 'lucide-react';
import bebanDosen from '@/routes/skripsi/beban-dosen';
import daftarJudul from '@/routes/skripsi/daftar-judul';
import exportRoutes from '@/routes/skripsi/export';
import monitoring from '@/routes/skripsi/monitoring';
import pengajuan from '@/routes/skripsi/pengajuan';
import putusan from '@/routes/skripsi/putusan';
import riwayat from '@/routes/skripsi/riwayat';
import statistik from '@/routes/skripsi/statistik';
import verifikasi from '@/routes/skripsi/verifikasi';
import type { NavItem } from '@/types';

/**
 * Item navigasi milik modul Skripsi (PRD §5).
 *
 * Submenu difilter per role di sisi klien oleh `@/lib/module-navigation`;
 * server tetap sumber kebenaran lewat middleware `role:` di routes modul.
 * Satu item teratas per modul — tautan `href` menunjuk submenu pertama yang
 * tersedia untuk semua role pemilik modul.
 */
export const navigation: NavItem[] = [
    {
        title: 'Skripsi',
        href: daftarJudul.index.url(),
        icon: BookOpen,
        roles: ['mahasiswa', 'admin', 'validator'],
        children: [
            {
                title: 'Pengajuan',
                href: pengajuan.status.url(),
                roles: ['mahasiswa'],
            },
            {
                title: 'Riwayat Pengajuan',
                href: riwayat.index.url(),
                roles: ['mahasiswa', 'admin'],
            },
            {
                title: 'Verifikasi',
                href: verifikasi.index.url(),
                roles: ['admin'],
            },
            {
                title: 'Putusan',
                href: putusan.index.url(),
                roles: ['validator'],
            },
            {
                title: 'Daftar Judul',
                href: daftarJudul.index.url(),
                roles: ['admin', 'validator'],
            },
        ],
    },
    {
        title: 'Laporan',
        href: statistik.index.url(),
        icon: BarChart3,
        roles: ['admin'],
        children: [
            {
                title: 'Statistik',
                href: statistik.index.url(),
            },
            {
                title: 'Monitoring',
                href: monitoring.index.url(),
            },
            {
                title: 'Beban Dosen',
                href: bebanDosen.index.url(),
            },
            {
                title: 'Export',
                href: exportRoutes.index.url(),
            },
        ],
    },
];
