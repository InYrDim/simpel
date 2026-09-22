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
import { destroy, update } from '@/routes/manajemen/jurusan';

export type JurusanRow = {
    id: number;
    nama: string;
    ketua_nama: string | null;
    ketua_nip: string | null;
    sekretaris_nama: string | null;
    sekretaris_nip: string | null;
    created_at: string;
};

type StafJurusan = {
    nama: string | null;
    nip: string | null;
};

function renderStaf(staf: StafJurusan) {
    if (!staf.nama) {
        return <span className="text-muted-foreground">Belum ditentukan</span>;
    }

    return (
        <div className="flex flex-col">
            <span>{staf.nama}</span>
            {staf.nip && (
                <span className="text-muted-foreground text-xs">
                    NIP {staf.nip}
                </span>
            )}
        </div>
    );
}

export const jurusanColumns: Column<JurusanRow>[] = [
    {
        key: 'nama',
        label: 'Jurusan',
    },
    {
        key: 'ketua_nama',
        label: 'Ketua',
        render: (j) => renderStaf({ nama: j.ketua_nama, nip: j.ketua_nip }),
    },
    {
        key: 'sekretaris_nama',
        label: 'Sekretaris',
        render: (j) =>
            renderStaf({ nama: j.sekretaris_nama, nip: j.sekretaris_nip }),
    },
    {
        key: 'created_at',
        label: 'Dibuat',
        render: (j) => new Date(j.created_at).toLocaleDateString('id-ID'),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (j) => <ActionCell jurusan={j} />,
    },
];

type JurusanForm = {
    nama: string;
    ketua_nama: string;
    ketua_nip: string;
    sekretaris_nama: string;
    sekretaris_nip: string;
};

function StafFields({
    prefix,
    labelNama,
    labelNip,
    data,
    setData,
    errors,
    disabled,
}: {
    prefix: 'ketua' | 'sekretaris';
    labelNama: string;
    labelNip: string;
    data: JurusanForm;
    setData: <K extends keyof JurusanForm>(
        key: K,
        value: JurusanForm[K],
    ) => void;
    errors: Partial<Record<keyof JurusanForm, string>>;
    disabled: boolean;
}) {
    return (
        <div className="grid grid-cols-2 gap-4">
            <div className="grid gap-2">
                <Label htmlFor={`${prefix}-nama`}>{labelNama}</Label>
                <Input
                    id={`${prefix}-nama`}
                    value={data[`${prefix}_nama`]}
                    onChange={(e) => setData(`${prefix}_nama`, e.target.value)}
                    disabled={disabled}
                />
                {errors[`${prefix}_nama`] && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {errors[`${prefix}_nama`]}
                    </p>
                )}
            </div>
            <div className="grid gap-2">
                <Label htmlFor={`${prefix}-nip`}>{labelNip}</Label>
                <Input
                    id={`${prefix}-nip`}
                    value={data[`${prefix}_nip`]}
                    onChange={(e) => setData(`${prefix}_nip`, e.target.value)}
                    disabled={disabled}
                />
                {errors[`${prefix}_nip`] && (
                    <p className="text-sm text-red-600 dark:text-red-400">
                        {errors[`${prefix}_nip`]}
                    </p>
                )}
            </div>
        </div>
    );
}

function ActionCell({ jurusan }: { jurusan: JurusanRow }) {
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
        nama: jurusan.nama,
        ketua_nama: jurusan.ketua_nama ?? '',
        ketua_nip: jurusan.ketua_nip ?? '',
        sekretaris_nama: jurusan.sekretaris_nama ?? '',
        sekretaris_nip: jurusan.sekretaris_nip ?? '',
    });

    const handleEdit = () => {
        put(update.url({ jurusan: jurusan.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ jurusan: jurusan.id }), {
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
                        <DialogTitle>Edit jurusan</DialogTitle>
                        <DialogDescription>
                            Ubah data jurusan <strong>{jurusan.nama}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-jurusan-nama">Nama</Label>
                            <Input
                                id="edit-jurusan-nama"
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
                        <StafFields
                            prefix="ketua"
                            labelNama="Nama ketua"
                            labelNip="NIP ketua"
                            data={data}
                            setData={setData}
                            errors={errors}
                            disabled={processing}
                        />
                        <StafFields
                            prefix="sekretaris"
                            labelNama="Nama sekretaris"
                            labelNip="NIP sekretaris"
                            data={data}
                            setData={setData}
                            errors={errors}
                            disabled={processing}
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setEditOpen(false)}
                            disabled={processing}
                        >
                            Batal
                        </Button>
                        <Button onClick={handleEdit} disabled={processing}>
                            Simpan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Hapus jurusan</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus jurusan{' '}
                            <strong>{jurusan.nama}</strong>?
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
