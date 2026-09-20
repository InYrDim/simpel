import { Head, router, useForm } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    mahasiswaColumns,
    type MahasiswaRow,
} from '@/pages/akademik/components/mahasiswa-columns';
import akademik from '@/routes/akademik';
import { store } from '@/routes/akademik/mahasiswa';

type UserOption = {
    id: number;
    name: string;
    email: string;
};

type DosenOption = {
    id: number;
    nama: string;
};

type PaginatedMahasiswas = {
    data: MahasiswaRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type MahasiswaIndexPageProps = {
    mahasiswas: PaginatedMahasiswas;
    filters: { search: string };
    userOptions: UserOption[];
    dosenOptions: DosenOption[];
};

export default function MahasiswaIndex({
    mahasiswas,
    filters,
    userOptions,
    dosenOptions,
}: MahasiswaIndexPageProps) {
    return (
        <>
            <Head title="Akademik - Mahasiswa" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Mahasiswa</h1>
                    <CreateMahasiswaDialog
                        userOptions={userOptions}
                        dosenOptions={dosenOptions}
                    />
                </div>

                <Card className="p-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(akademik.mahasiswa.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari nama, NIM, atau email..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={mahasiswaColumns(dosenOptions)}
                        data={mahasiswas.data}
                        getRowKey={(m) => m.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {mahasiswas.data.length} dari{' '}
                        {mahasiswas.total} mahasiswa
                    </div>
                </Card>
            </div>
        </>
    );
}

function CreateMahasiswaDialog({
    userOptions,
    dosenOptions,
}: {
    userOptions: UserOption[];
    dosenOptions: DosenOption[];
}) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '',
        nama: '',
        nim: '',
        dosen_pa_id: '',
        prodi: '',
        angkatan: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store.url(), {
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };

    return (
        <>
            <Button size="sm" onClick={() => setOpen(true)}>
                Tambah Mahasiswa
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah mahasiswa</DialogTitle>
                        <DialogDescription>
                            Hubungkan akun user dengan profil akademiknya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="mhs-user">Akun User</Label>
                            <Select
                                value={data.user_id}
                                onValueChange={(v) => setData('user_id', v)}
                            >
                                <SelectTrigger id="mhs-user">
                                    <SelectValue placeholder="Pilih akun user" />
                                </SelectTrigger>
                                <SelectContent>
                                    {userOptions.map((u) => (
                                        <SelectItem
                                            key={u.id}
                                            value={String(u.id)}
                                        >
                                            {u.name} ({u.email})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.user_id && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.user_id}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="mhs-nama">Nama</Label>
                            <Input
                                id="mhs-nama"
                                value={data.nama}
                                onChange={(e) =>
                                    setData('nama', e.target.value)
                                }
                                disabled={processing}
                            />
                            {errors.nama && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.nama}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="mhs-nim">NIM</Label>
                            <Input
                                id="mhs-nim"
                                value={data.nim}
                                onChange={(e) => setData('nim', e.target.value)}
                                disabled={processing}
                            />
                            {errors.nim && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.nim}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="mhs-dosen-pa">Dosen PA</Label>
                            <Select
                                value={data.dosen_pa_id}
                                onValueChange={(v) => setData('dosen_pa_id', v)}
                            >
                                <SelectTrigger id="mhs-dosen-pa">
                                    <SelectValue placeholder="Pilih dosen PA" />
                                </SelectTrigger>
                                <SelectContent>
                                    {dosenOptions.map((d) => (
                                        <SelectItem
                                            key={d.id}
                                            value={String(d.id)}
                                        >
                                            {d.nama}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.dosen_pa_id && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.dosen_pa_id}
                                </p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="mhs-prodi">Prodi</Label>
                                <Input
                                    id="mhs-prodi"
                                    value={data.prodi}
                                    onChange={(e) =>
                                        setData('prodi', e.target.value)
                                    }
                                    disabled={processing}
                                />
                                {errors.prodi && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.prodi}
                                    </p>
                                )}
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="mhs-angkatan">Angkatan</Label>
                                <Input
                                    id="mhs-angkatan"
                                    type="number"
                                    value={data.angkatan}
                                    onChange={(e) =>
                                        setData('angkatan', e.target.value)
                                    }
                                    disabled={processing}
                                />
                                {errors.angkatan && (
                                    <p className="text-sm text-red-600 dark:text-red-400">
                                        {errors.angkatan}
                                    </p>
                                )}
                            </div>
                        </div>

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

MahasiswaIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
            href: akademik.mahasiswa.index.url(),
        },
        {
            title: 'Mahasiswa',
            href: akademik.mahasiswa.index.url(),
        },
    ],
});
