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
import { destroy, update } from '@/routes/akademik/mahasiswa';

export type DosenOption = {
    id: number;
    nama: string;
};

export type MahasiswaRow = {
    id: number;
    nama: string;
    nim: string;
    dosen_pa_id: number;
    dosen_pa_nama: string | null;
    user_email: string | null;
    prodi: string | null;
    angkatan: number | null;
    created_at: string;
};

/**
 * Kolom tabel mahasiswa — dibuat via factory supaya dialog edit bisa
 * menerima daftar opsi dosen PA dari halaman yang memakainya.
 */
export const mahasiswaColumns = (
    dosenOptions: DosenOption[],
): Column<MahasiswaRow>[] => [
    {
        key: 'nama',
        label: 'Mahasiswa',
        render: (m) => (
            <div className="flex flex-col">
                <span className="font-medium">{m.nama}</span>
                <span className="text-muted-foreground text-xs">
                    {m.user_email ?? '-'}
                </span>
            </div>
        ),
    },
    {
        key: 'nim',
        label: 'NIM',
    },
    {
        key: 'dosen_pa_nama',
        label: 'Dosen PA',
        render: (m) => m.dosen_pa_nama ?? '-',
    },
    {
        key: 'prodi',
        label: 'Prodi',
        render: (m) => m.prodi ?? '-',
    },
    {
        key: 'angkatan',
        label: 'Angkatan',
        render: (m) => m.angkatan ?? '-',
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (m) => <ActionCell mahasiswa={m} dosenOptions={dosenOptions} />,
    },
];

function ActionCell({
    mahasiswa,
    dosenOptions,
}: {
    mahasiswa: MahasiswaRow;
    dosenOptions: DosenOption[];
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
        nama: mahasiswa.nama,
        nim: mahasiswa.nim,
        dosen_pa_id: String(mahasiswa.dosen_pa_id),
        prodi: mahasiswa.prodi ?? '',
        angkatan: mahasiswa.angkatan ? String(mahasiswa.angkatan) : '',
    });

    const handleEdit = (e: React.FormEvent) => {
        e.preventDefault();
        put(update.url({ mahasiswa: mahasiswa.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ mahasiswa: mahasiswa.id }), {
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
                        <DialogTitle>Edit mahasiswa</DialogTitle>
                        <DialogDescription>
                            Ubah profil akademik{' '}
                            <strong>{mahasiswa.nama}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={handleEdit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor={`edit-mhs-nama-${mahasiswa.id}`}>
                                Nama
                            </Label>
                            <Input
                                id={`edit-mhs-nama-${mahasiswa.id}`}
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
                            <Label htmlFor={`edit-mhs-nim-${mahasiswa.id}`}>
                                NIM
                            </Label>
                            <Input
                                id={`edit-mhs-nim-${mahasiswa.id}`}
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
                            <Label htmlFor={`edit-mhs-dosen-${mahasiswa.id}`}>
                                Dosen PA
                            </Label>
                            <Select
                                value={data.dosen_pa_id}
                                onValueChange={(v) => setData('dosen_pa_id', v)}
                            >
                                <SelectTrigger
                                    id={`edit-mhs-dosen-${mahasiswa.id}`}
                                >
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
                                <Label
                                    htmlFor={`edit-mhs-prodi-${mahasiswa.id}`}
                                >
                                    Prodi
                                </Label>
                                <Input
                                    id={`edit-mhs-prodi-${mahasiswa.id}`}
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
                                <Label
                                    htmlFor={`edit-mhs-angkatan-${mahasiswa.id}`}
                                >
                                    Angkatan
                                </Label>
                                <Input
                                    id={`edit-mhs-angkatan-${mahasiswa.id}`}
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
                        <DialogTitle>Hapus mahasiswa</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus{' '}
                            <strong>{mahasiswa.nama}</strong>? Aksi ini tidak
                            dapat dibatalkan.
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
