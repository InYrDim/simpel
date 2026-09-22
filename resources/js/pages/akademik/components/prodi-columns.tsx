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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, update } from '@/routes/akademik/prodi';

export type KaprodiOption = {
    id: number;
    nama: string;
};

export type ProdiRow = {
    id: number;
    nama: string;
    kaprodi_id: number | null;
    kaprodi_nama: string | null;
    jumlah_mahasiswa: number;
    created_at: string;
};

/**
 * Kolom tabel prodi — dibuat via factory supaya dialog edit bisa menerima
 * daftar opsi kaprodi dari halaman yang memakainya.
 */
export const prodiColumns = (
    kaprodiOptions: KaprodiOption[],
): Column<ProdiRow>[] => [
    {
        key: 'nama',
        label: 'Program Studi',
    },
    {
        key: 'kaprodi_nama',
        label: 'Kaprodi',
        render: (p) => p.kaprodi_nama ?? '-',
    },
    {
        key: 'jumlah_mahasiswa',
        label: 'Mahasiswa',
        render: (p) => p.jumlah_mahasiswa,
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (p) => <ActionCell prodi={p} kaprodiOptions={kaprodiOptions} />,
    },
];

function ActionCell({
    prodi,
    kaprodiOptions,
}: {
    prodi: ProdiRow;
    kaprodiOptions: KaprodiOption[];
}) {
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
        nama: prodi.nama,
        kaprodi_id: prodi.kaprodi_id ? String(prodi.kaprodi_id) : '',
    });

    const handleEdit = (e: React.FormEvent) => {
        e.preventDefault();
        put(update.url({ prodi: prodi.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ prodi: prodi.id }), {
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
                        <DialogTitle>Edit prodi</DialogTitle>
                        <DialogDescription>
                            Ubah program studi <strong>{prodi.nama}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor={`edit-prodi-nama-${prodi.id}`}>
                                Nama
                            </Label>
                            <Input
                                id={`edit-prodi-nama-${prodi.id}`}
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
                            <Label htmlFor={`edit-prodi-kaprodi-${prodi.id}`}>
                                Kaprodi
                            </Label>
                            <Select
                                value={data.kaprodi_id}
                                onValueChange={(v) => setData('kaprodi_id', v)}
                            >
                                <SelectTrigger
                                    id={`edit-prodi-kaprodi-${prodi.id}`}
                                >
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
                        <DialogTitle>Hapus prodi</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus{' '}
                            <strong>{prodi.nama}</strong>? Prodi yang masih
                            memiliki mahasiswa tidak bisa dihapus.
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
