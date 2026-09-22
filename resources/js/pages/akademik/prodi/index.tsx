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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    prodiColumns,
    type KaprodiOption,
    type ProdiRow,
} from '@/pages/akademik/components/prodi-columns';
import akademik from '@/routes/akademik';
import { store } from '@/routes/akademik/prodi';

type PaginatedProdis = {
    data: ProdiRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type ProdiIndexPageProps = {
    prodis: PaginatedProdis;
    filters: { search: string };
    kaprodiOptions: KaprodiOption[];
};

export default function ProdiIndex({
    prodis,
    filters,
    kaprodiOptions,
}: ProdiIndexPageProps) {
    return (
        <>
            <Head title="Akademik - Prodi" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Prodi</h1>
                    <CreateProdiDialog kaprodiOptions={kaprodiOptions} />
                </div>

                <section className="flex flex-col gap-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(akademik.prodi.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari nama prodi atau kaprodi..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={prodiColumns(kaprodiOptions)}
                        data={prodis.data}
                        getRowKey={(p) => p.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {prodis.data.length} dari {prodis.total}{' '}
                        prodi
                    </div>
                </section>
            </div>
        </>
    );
}

function CreateProdiDialog({
    kaprodiOptions,
}: {
    kaprodiOptions: KaprodiOption[];
}) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        nama: '',
        kaprodi_id: '',
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
                Tambah Prodi
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah prodi</DialogTitle>
                        <DialogDescription>
                            Tambahkan program studi baru beserta kaprodinya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="prodi-nama">Nama</Label>
                            <Input
                                id="prodi-nama"
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
                            <Label htmlFor="prodi-kaprodi">Kaprodi</Label>
                            <Select
                                value={data.kaprodi_id}
                                onValueChange={(v) => setData('kaprodi_id', v)}
                            >
                                <SelectTrigger id="prodi-kaprodi">
                                    <SelectValue placeholder="Pilih kaprodi" />
                                </SelectTrigger>
                                <SelectContent>
                                    {kaprodiOptions.map((d) => (
                                        <SelectItem
                                            key={d.id}
                                            value={String(d.id)}
                                        >
                                            {d.nama}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.kaprodi_id && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.kaprodi_id}
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

ProdiIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
            href: akademik.prodi.index.url(),
        },
        {
            title: 'Prodi',
            href: akademik.prodi.index.url(),
        },
    ],
});
