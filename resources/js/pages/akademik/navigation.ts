import { GraduationCap } from 'lucide-react';
import akademik from '@/routes/akademik';
import dosen from '@/routes/akademik/dosen';
import mahasiswa from '@/routes/akademik/mahasiswa';
import type { NavItem } from '@/types';

/**
 * Item navigasi milik modul Akademik.
 *
 * Tiap modul mendefinisikan nav-nya sendiri di sini; penggabungan dan
 * penyaringan dikerjakan `@/lib/module-navigation`. Route `akademik/*` sudah
 * dijaga middleware `role:admin` di server — `roles` di sini hanya untuk
 * menyembunyikan UI, bukan pengaman.
 */
export const navigation: NavItem[] = [
    {
        title: 'Akademik',
        href: akademik.dosen.index.url(),
        icon: GraduationCap,
        roles: ['admin'],
        children: [
            {
                title: 'Dosen',
                href: dosen.index.url(),
            },
            {
                title: 'Mahasiswa',
                href: mahasiswa.index.url(),
            },
        ],
    },
];
