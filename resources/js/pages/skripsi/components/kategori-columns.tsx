import { useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { Column } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
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
import { destroy, update } from '@/routes/skripsi/kategori';

export type KategoriRow = {
    id: number;
    nama: string;
    deskripsi: string | null;
    aktif: boolean;
    jumlah_judul: number;
    created_at: string;
};

export const kategoriColumns = (): Column<KategoriRow>[] => [
    {
        key: 'nama',
        label: 'Kategori',
    },
    {
        key: 'deskripsi',
        label: 'Deskripsi',
        render: (k) => k.deskripsi ?? '-',
    },
    {
        key: 'aktif',
        label: 'Status',
        render: (k) => (
            <Badge variant={k.aktif ? 'default' : 'secondary'}>
                {k.aktif ? 'Aktif' : 'Nonaktif'}
            </Badge>
        ),
    },
    {
        key: 'jumlah_judul',
        label: 'Dipakai',
        render: (k) => `${k.jumlah_judul} judul`,
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (k) => <ActionCell kategori={k} />,
    },
];

function ActionCell({ kategori }: { kategori: KategoriRow }) {
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const {
        data,
        setData,
        put,
        delete: deleteForm,
        processing,
        errors,
    } = useForm({
        nama: kategori.nama,
        deskripsi: kategori.deskripsi ?? '',
        aktif: kategori.aktif,
    });

    const handleEdit = (e: React.FormEvent) => {
        e.preventDefault();
        put(update.url({ kategori: kategori.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ kategori: kategori.id }), {
            onSuccess: () => setDeleteOpen(false),
        });
    };

    return (
        <>
            <div className="flex justify-end gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    title="Edit"
                    onClick={() => setEditOpen(true)}
                >
                    <Pencil className="size-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    title="Hapus"
                    onClick={() => setDeleteOpen(true)}
                >
                    <Trash2 className="text-destructive size-4" />
                </Button>
            </div>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit kategori</DialogTitle>
                        <DialogDescription>
                            Ubah kategori <strong>{kategori.nama}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label
                                htmlFor={`edit-kategori-nama-${kategori.id}`}
                            >
                                Nama
                            </Label>
                            <Input
                                id={`edit-kategori-nama-${kategori.id}`}
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
                            <Label htmlFor={`edit-kategori-ket-${kategori.id}`}>
                                Deskripsi
                            </Label>
                            <Textarea
                                id={`edit-kategori-ket-${kategori.id}`}
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
                                id={`edit-kategori-aktif-${kategori.id}`}
                                checked={data.aktif}
                                onCheckedChange={(v) =>
                                    setData('aktif', v === true)
                                }
                            />
                            <Label
                                htmlFor={`edit-kategori-aktif-${kategori.id}`}
                            >
                                Aktif (tampil di form pengajuan)
                            </Label>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setEditOpen(false)}
                                disabled={processing}
                            >
                                Batal
                            </Button>
                            <Button type="submit" disabled={processing}>
                                Simpan
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus kategori</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus{' '}
                            <strong>{kategori.nama}</strong>? Kategori yang
                            sudah dipakai judul tidak bisa dihapus — nonaktifkan
                            saja.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteOpen(false)}
                            disabled={processing}
                        >
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={processing}
                        >
                            Hapus
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
