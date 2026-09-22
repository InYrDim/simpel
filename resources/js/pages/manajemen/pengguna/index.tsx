import { Head, router } from '@inertiajs/react';
import { Search, Users } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { DataTable } from '@/components/data-table';
import { userColumns } from '@/pages/manajemen/components/user-columns';
import { Button } from '@/components/ui/button';
import type { PaginatedUsers } from '@/types';
import manajemen from '@/routes/manajemen';
import pengguna from '@/routes/manajemen/pengguna';

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
                    <Button variant="outline" size="sm" disabled>
                        <Users className="mr-2 size-4" />
                        Tambah Pengguna
                    </Button>
                </div>

                <section className="flex flex-col gap-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(pengguna.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari nama atau email..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={userColumns}
                        data={users.data}
                        getRowKey={(user) => user.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {users.data.length} dari {users.total}{' '}
                        pengguna
                    </div>
                </section>
            </div>
        </>
    );
}

PenggunaIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
        {
            title: 'Akun',
            href: pengguna.index.url(),
        },
    ],
});
