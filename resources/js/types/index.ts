export * from './auth';
export * from './navigation';
export * from './ui';

export type PaginatedUsers = {
    data: {
        id: number;
        name: string;
        email: string;
        email_verified_at: string | null;
        created_at: string;
    }[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};
