import { Users } from 'lucide-react';
import manajemen from '@/routes/manajemen';
import jurusan from '@/routes/manajemen/jurusan';
import pengguna from '@/routes/manajemen/pengguna';
import peran from '@/routes/manajemen/peran';
import type { NavItem } from '@/types';

/**
 * Item navigasi milik modul Manajemen.
 *
 * Tiap modul mendefinisikan nav-nya sendiri di sini; penggabungan dan
 * penyaringan dikerjakan `@/lib/module-navigation`. Route `manajemen/*` sudah
 * dijaga middleware `role:admin` di server — `roles` di sini hanya untuk
 * menyembunyikan UI, bukan pengaman.
 */
export const navigation: NavItem[] = [
    {
        title: 'Manajemen',
        href: manajemen.index.url(),
        icon: Users,
        roles: ['admin'],
        children: [
            {
                title: 'Akun',
                href: pengguna.index.url(),
            },
            {
                title: 'Jurusan',
                href: jurusan.index.url(),
            },
            {
                title: 'Peran',
                href: peran.index.url(),
            },
        ],
    },
];
