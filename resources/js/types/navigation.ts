import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';

export type BreadcrumbItem = {
    title: string;
    href: InertiaLinkProps['href'];
};

export type NavItem = {
    title: string;
    href?: InertiaLinkProps['href'];
    icon?: LucideIcon | null;
    isActive?: boolean;
    /** Role yang boleh melihat item ini. Kosong = terlihat oleh semua user. */
    roles?: string[];
    children?: { title: string; href: InertiaLinkProps['href'] }[];
};
