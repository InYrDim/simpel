import { Head, router, useForm } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Textarea } from '@/components/ui/textarea';
import {
    kategoriColumns,
    type KategoriRow,
} from '@/pages/skripsi/components/kategori-columns';
import skripsi from '@/routes/skripsi';
import { store } from '@/routes/skripsi/kategori';

type PaginatedKategoris = {
    data: KategoriRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
};

type KategoriIndexPageProps = {
    kategoris: PaginatedKategoris;
    filters: { search: string };
};

export default function KategoriIndex({
    kategoris,
    filters,
}: KategoriIndexPageProps) {
    return (
        <>
            <Head title="Skripsi - Kategori Judul" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Kategori Judul</h1>
                    <CreateKategoriDialog />
                </div>

                <section className="flex flex-col gap-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(skripsi.kategori.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari nama kategori..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <DataTable
                        columns={kategoriColumns()}
                        data={kategoris.data}
                        getRowKey={(k) => k.id}
                    />

                    <div className="text-muted-foreground text-sm">
                        Menampilkan {kategoris.data.length} dari{' '}
                        {kategoris.total} kategori
                    </div>
                </section>
            </div>
        </>
    );
}

function CreateKategoriDialog() {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        nama: '',
        deskripsi: '',
        aktif: true,
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
                Tambah Kategori
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah kategori</DialogTitle>
                        <DialogDescription>
                            Kategori dipilih mahasiswa untuk tiap judul yang
                            diajukan.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="kategori-nama">Nama</Label>
                            <Input
                                id="kategori-nama"
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
                            <Label htmlFor="kategori-deskripsi">
                                Deskripsi
                            </Label>
                            <Textarea
                                id="kategori-deskripsi"
                                value={data.deskripsi}
                                onChange={(e) =>
                                    setData('deskripsi', e.target.value)
                                }
                                disabled={processing}
                            />
                            {errors.deskripsi && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.deskripsi}
                                </p>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="kategori-aktif"
                                checked={data.aktif}
                                onCheckedChange={(v) =>
                                    setData('aktif', v === true)
                                }
                            />
                            <Label htmlFor="kategori-aktif">
                                Aktif (tampil di form pengajuan)
                            </Label>
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

KategoriIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Skripsi',
            href: skripsi.kategori.index.url(),
        },
        {
            title: 'Kategori',
            href: skripsi.kategori.index.url(),
        },
    ],
});
