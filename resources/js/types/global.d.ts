import type { Auth } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            notifications: {
                unread_count: number;
                items: {
                    id: string;
                    data: { judul?: string; pesan?: string; jenis?: string };
                    read_at: string | null;
                    created_at: string;
                }[];
            };
            [key: string]: unknown;
        };
    }
}
