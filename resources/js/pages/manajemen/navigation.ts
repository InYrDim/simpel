import { Users } from 'lucide-react';
import manajemen from '@/routes/manajemen';
import pengguna from '@/routes/manajemen/pengguna';
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
                title: 'Pengguna',
                href: pengguna.index.url(),
            },
        ],
    },
];
