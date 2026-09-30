import { Head, router, useForm } from '@inertiajs/react';
import { Search } from 'lucide-react';
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
    dosenColumns,
    type DosenRow,
} from '@/pages/akademik/components/dosen-columns';
import akademik from '@/routes/akademik';
import { store } from '@/routes/akademik/dosen';

type PaginatedDosens = {
    data: DosenRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type DosenIndexPageProps = {
    dosens: PaginatedDosens;
    filters: { search: string };
};

export default function DosenIndex({ dosens, filters }: DosenIndexPageProps) {
    return (
        <>
            <Head title="Akademik - Dosen" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Dosen</h1>
                    <CreateDosenDialog />
                </div>

                <section className="flex flex-col gap-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(akademik.dosen.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari nama, NIP, atau bidang..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={dosenColumns}
                        data={dosens.data}
                        getRowKey={(dosen) => dosen.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {dosens.data.length} dari {dosens.total}{' '}
                        dosen
                    </div>
                </section>
            </div>
        </>
    );
}

function CreateDosenDialog() {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        nama: '',
        nip: '',
        bidang: '',
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
                Tambah Dosen
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah dosen</DialogTitle>
                        <DialogDescription>
                            Tambahkan dosen referensi baru untuk penugasan PA,
                            validator, pembimbing, dan penguji.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="dosen-nama">Nama</Label>
                            <Input
                                id="dosen-nama"
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
                            <Label htmlFor="dosen-nip">NIP</Label>
                            <Input
                                id="dosen-nip"
                                value={data.nip}
                                onChange={(e) => setData('nip', e.target.value)}
                                disabled={processing}
                            />
                            {errors.nip && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.nip}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="dosen-bidang">Bidang</Label>
                            <Input
                                id="dosen-bidang"
                                value={data.bidang}
                                onChange={(e) =>
                                    setData('bidang', e.target.value)
                                }
                                disabled={processing}
                            />
                            {errors.bidang && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.bidang}
                                </p>
                            )}
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

DosenIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
            href: akademik.dosen.index.url(),
        },
        {
            title: 'Dosen',
            href: akademik.dosen.index.url(),
        },
    ],
});
