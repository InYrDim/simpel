import { Head, router, useForm } from '@inertiajs/react';
import { Search, Users } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    RolePicker,
    userColumns,
} from '@/pages/manajemen/components/user-columns';
import type { PaginatedUsers } from '@/types';
import manajemen from '@/routes/manajemen';
import pengguna from '@/routes/manajemen/pengguna';
import InputError from '@/components/input-error';

type UsersPageProps = {
    users: PaginatedUsers;
    roleOptions: string[];
    filters: { search: string };
};

export default function PenggunaIndex({
    users,
    roleOptions,
    filters,
}: UsersPageProps) {
    return (
        <>
            <Head title="Manajemen - Pengguna" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Pengguna</h1>
                    <CreatePenggunaDialog roleOptions={roleOptions} />
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
                        columns={userColumns(roleOptions)}
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

function CreatePenggunaDialog({ roleOptions }: { roleOptions: string[] }) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(pengguna.store.url(), {
            onSuccess: () => {
                reset();
                setOpen(false);
            },
            onError: () => reset('password', 'password_confirmation'),
        });
    };

    const fields = [
        { key: 'name', label: 'Nama', type: 'text' },
        { key: 'email', label: 'Email', type: 'email' },
        { key: 'password', label: 'Password', type: 'password' },
        {
            key: 'password_confirmation',
            label: 'Konfirmasi password',
            type: 'password',
        },
    ] as const;

    return (
        <>
            <Button size="sm" onClick={() => setOpen(true)}>
                <Users className="mr-2 size-4" />
                Tambah Pengguna
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah pengguna</DialogTitle>
                        <DialogDescription>
                            Akun yang dibuat admin langsung terverifikasi dan
                            dapat dipakai login.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        {fields.map((field) => (
                            <div key={field.key} className="grid gap-2">
                                <Label htmlFor={`pengguna-${field.key}`}>
                                    {field.label}
                                </Label>
                                <Input
                                    id={`pengguna-${field.key}`}
                                    type={field.type}
                                    value={data[field.key]}
                                    onChange={(e) =>
                                        setData(field.key, e.target.value)
                                    }
                                    disabled={processing}
                                />
                                <InputError message={errors[field.key]} />
                            </div>
                        ))}

                        <RolePicker
                            id="pengguna-role"
                            options={roleOptions}
                            value={data.role}
                            onChange={(role) => setData('role', role)}
                            disabled={processing}
                            error={errors.role}
                        />

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setOpen(false)}
                                disabled={processing}
                            >
                                Batal
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? 'Menyimpan...' : 'Simpan'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
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
