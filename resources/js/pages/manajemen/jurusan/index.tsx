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
    jurusanColumns,
    type JurusanRow,
} from '@/pages/manajemen/components/jurusan-columns';
import manajemen from '@/routes/manajemen';
import { store } from '@/routes/manajemen/jurusan';

type PaginatedJurusans = {
    data: JurusanRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type JurusanIndexPageProps = {
    jurusans: PaginatedJurusans;
    filters: { search: string };
};

export default function JurusanIndex({
    jurusans,
    filters,
}: JurusanIndexPageProps) {
    return (
        <>
            <Head title="Manajemen - Jurusan" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Jurusan</h1>
                    <CreateJurusanDialog />
                </div>

                <Card className="p-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(manajemen.jurusan.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari jurusan..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={jurusanColumns}
                        data={jurusans.data}
                        getRowKey={(j) => j.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {jurusans.data.length} dari {jurusans.total}{' '}
                        jurusan
                    </div>
                </Card>
            </div>
        </>
    );
}

function CreateJurusanDialog() {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        nama: '',
        ketua_nama: '',
        ketua_nip: '',
        sekretaris_nama: '',
        sekretaris_nip: '',
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
                Tambah Jurusan
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah jurusan</DialogTitle>
                        <DialogDescription>
                            Tambahkan jurusan baru beserta ketua dan
                            sekretarisnya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="jurusan-nama">Nama</Label>
                            <Input
                                id="jurusan-nama"
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
                            <Label htmlFor="jurusan-ketua-nama">
                                Nama ketua
                            </Label>
                            <Input
                                id="jurusan-ketua-nama"
                                value={data.ketua_nama}
                                onChange={(e) =>
                                    setData('ketua_nama', e.target.value)
                                }
                                disabled={processing}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="jurusan-ketua-nip">NIP ketua</Label>
                            <Input
                                id="jurusan-ketua-nip"
                                value={data.ketua_nip}
                                onChange={(e) =>
                                    setData('ketua_nip', e.target.value)
                                }
                                disabled={processing}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="jurusan-sekretaris-nama">
                                Nama sekretaris
                            </Label>
                            <Input
                                id="jurusan-sekretaris-nama"
                                value={data.sekretaris_nama}
                                onChange={(e) =>
                                    setData('sekretaris_nama', e.target.value)
                                }
                                disabled={processing}
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="jurusan-sekretaris-nip">
                                NIP sekretaris
                            </Label>
                            <Input
                                id="jurusan-sekretaris-nip"
                                value={data.sekretaris_nip}
                                onChange={(e) =>
                                    setData('sekretaris_nip', e.target.value)
                                }
                                disabled={processing}
                            />
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

JurusanIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
        {
            title: 'Jurusan',
            href: manajemen.jurusan.index.url(),
        },
    ],
});
