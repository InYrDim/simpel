import { usePage } from '@inertiajs/react';
import { navigation as akademik } from '@/pages/akademik/navigation';
import { navigation as manajemen } from '@/pages/manajemen/navigation';
import { navigation as skripsi } from '@/pages/skripsi/navigation';
import type { NavItem } from '@/types';

/**
 * Composition root navigasi frontend: satu baris per modul.
 *
 * Modul memiliki item nav-nya sendiri (`pages/<modul>/navigation.ts`); file ini
 * satu-satunya tempat core menyebut modul, lalu menyaring berdasarkan role yang
 * di-share Inertia. Server tetap sumber kebenaran — penyaringan di sini hanya
 * menyembunyikan UI yang memang tidak bisa diakses.
 */
const moduleNavigation: NavItem[] = [...akademik, ...manajemen, ...skripsi];

function isVisibleFor(item: { roles?: string[] }, roles: string[]): boolean {
    return (
        !item.roles?.length || item.roles.some((role) => roles.includes(role))
    );
}

export function useModuleNavigation(): NavItem[] {
    const { auth } = usePage().props;

    return moduleNavigation
        .filter((item) => isVisibleFor(item, auth.roles))
        .map((item) => ({
            ...item,
            children: item.children?.filter((child) =>
                isVisibleFor(child, auth.roles),
            ),
        }));
}
