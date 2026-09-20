import { useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { Column } from '@/components/data-table';
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
import { destroy, update } from '@/routes/akademik/dosen';

export type DosenRow = {
    id: number;
    nama: string;
    nip: string;
    bidang: string;
    created_at: string;
};

export const dosenColumns: Column<DosenRow>[] = [
    {
        key: 'nama',
        label: 'Dosen',
        render: (d) => (
            <div className="flex flex-col">
                <span className="font-medium">{d.nama}</span>
                <span className="text-muted-foreground text-xs">{d.nip}</span>
            </div>
        ),
    },
    {
        key: 'bidang',
        label: 'Bidang',
    },
    {
        key: 'created_at',
        label: 'Dibuat',
        render: (d) => new Date(d.created_at).toLocaleDateString('id-ID'),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (d) => <ActionCell dosen={d} />,
    },
];

function ActionCell({ dosen }: { dosen: DosenRow }) {
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
        nama: dosen.nama,
        nip: dosen.nip,
        bidang: dosen.bidang,
    });

    const handleEdit = (e: React.FormEvent) => {
        e.preventDefault();
        put(update.url({ dosen: dosen.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ dosen: dosen.id }), {
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
                        <DialogTitle>Edit dosen</DialogTitle>
                        <DialogDescription>
                            Ubah data dosen <strong>{dosen.nama}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor={`edit-nama-${dosen.id}`}>
                                Nama
                            </Label>
                            <Input
                                id={`edit-nama-${dosen.id}`}
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
                            <Label htmlFor={`edit-nip-${dosen.id}`}>NIP</Label>
                            <Input
                                id={`edit-nip-${dosen.id}`}
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
                            <Label htmlFor={`edit-bidang-${dosen.id}`}>
                                Bidang
                            </Label>
                            <Input
                                id={`edit-bidang-${dosen.id}`}
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
                        <DialogTitle>Hapus dosen</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus{' '}
                            <strong>{dosen.nama}</strong>? Aksi ini tidak dapat
                            dibatalkan.
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
