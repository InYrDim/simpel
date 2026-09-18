import { Head, router } from '@inertiajs/react';
import { Search, Trash2 } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { DataTable } from '@/components/data-table';
import { userColumns } from '@/components/users/user-columns';
import type { PaginatedUsers } from '@/types';
import { manajemen, pengguna } from '@/routes';

type UsersPageProps = {
    users: PaginatedUsers;
    filters: { search: string };
};

export default function PenggunaIndex({ users, filters }: UsersPageProps) {
    return (
        <>
            <Head title="Manajemen - Pengguna" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Pengguna</h1>
                </div>

                <form
                    className="flex items-center gap-2"
                    onChange={(e) => {
                        e.preventDefault();
                        router.get(pengguna(), { search: (e.target as HTMLFormElement).search.value });
                    }}
                >
                    <div className="relative flex-1 max-w-sm">
                        <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                        <Input
                            name="search"
                            placeholder="Cari nama atau email..."
                            defaultValue={filters.search}
                            className="pl-9"
                        />
                    </div>
                </form>

                <DataTable columns={userColumns} data={users.data} />

                <div className="text-muted-foreground text-sm">
                    Menampilkan {users.data.length} dari {users.total} pengguna
                </div>
            </div>
        </>
    );
}

PenggunaIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen(),
        },
        {
            title: 'Pengguna',
            href: pengguna(),
        },
    ],
});
