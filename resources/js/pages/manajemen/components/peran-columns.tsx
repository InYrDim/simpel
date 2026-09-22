import { useForm } from '@inertiajs/react';
import { Pencil, Shield, Trash2 } from 'lucide-react';
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
import { destroy, update } from '@/routes/manajemen/peran';

export type PermissionOption = {
    name: string;
    label: string;
};

export type PeranRow = {
    id: number;
    name: string;
    jumlah_pengguna: number;
    permissions: string[];
    protected: boolean;
};

export const peranColumns = (
    permissionOptions: PermissionOption[],
): Column<PeranRow>[] => [
    {
        key: 'name',
        label: 'Peran',
        render: (r) => (
            <div className="flex items-center gap-2">
                <Shield className="text-muted-foreground size-4" />
                <span className="font-medium">{r.name}</span>
                {r.protected && <Badge variant="secondary">Terproteksi</Badge>}
            </div>
        ),
    },
    {
        key: 'jumlah_pengguna',
        label: 'Pengguna',
    },
    {
        key: 'permissions',
        label: 'Hak Akses',
        render: (r) =>
            r.permissions.length === 0 ? (
                <span className="text-muted-foreground">Tidak ada</span>
            ) : (
                <div className="flex flex-wrap gap-1">
                    {r.permissions.map((p) => (
                        <Badge key={p} variant="outline">
                            {p}
                        </Badge>
                    ))}
                </div>
            ),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (r) => (
            <ActionCell peran={r} permissionOptions={permissionOptions} />
        ),
    },
];

export function PermissionPicker({
    options,
    selected,
    onToggle,
    disabled,
}: {
    options: PermissionOption[];
    selected: string[];
    onToggle: (name: string) => void;
    disabled: boolean;
}) {
    return (
        <div className="grid gap-2">
            <Label>Hak akses</Label>
            <div className="grid max-h-56 gap-1 overflow-y-auto rounded-md border p-3">
                {options.map((option) => {
                    const checked = selected.includes(option.name);

                    return (
                        <label
                            key={option.name}
                            className="hover:bg-muted/50 flex cursor-pointer items-center gap-2 rounded px-1 py-1 text-sm"
                        >
                            <Checkbox
                                checked={checked}
                                onCheckedChange={() =>
                                    !disabled && onToggle(option.name)
                                }
                                disabled={disabled}
                            />
                            {option.label}
                            <span className="text-muted-foreground ml-auto font-mono text-xs">
                                {option.name}
                            </span>
                        </label>
                    );
                })}
            </div>
        </div>
    );
}

function ActionCell({
    peran,
    permissionOptions,
}: {
    peran: PeranRow;
    permissionOptions: PermissionOption[];
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
        name: peran.name,
        permissions: peran.permissions,
    });

    const handleEdit = () => {
        put(update.url({ peran: peran.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ peran: peran.id }), {
            onSuccess: () => setDeleteOpen(false),
        });
    };

    const togglePermission = (name: string) => {
        setData(
            'permissions',
            data.permissions.includes(name)
                ? data.permissions.filter((p) => p !== name)
                : [...data.permissions, name],
        );
    };

    const cannotDelete = peran.protected || peran.jumlah_pengguna > 0;

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
                    title={cannotDelete ? 'Peran tidak dapat dihapus' : 'Hapus'}
                    onClick={() => setDeleteOpen(true)}
                    disabled={cannotDelete}
                >
                    <Trash2 className="text-destructive size-4" />
                </Button>
            </div>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit peran</DialogTitle>
                        <DialogDescription>
                            Ubah nama dan hak akses peran{' '}
                            <strong>{peran.name}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="edit-peran-name">Nama</Label>
                            <Input
                                id="edit-peran-name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                disabled={processing}
                            />
                            {errors.name && (
                                <p className="text-sm text-red-600 dark:text-red-400">
                                    {errors.name}
                                </p>
                            )}
                        </div>
                        <PermissionPicker
                            options={permissionOptions}
                            selected={data.permissions}
                            onToggle={togglePermission}
                            disabled={processing}
                        />
                        {errors.permissions?.[0] && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {errors.permissions[0]}
                            </p>
                        )}
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
                        <DialogTitle>Hapus peran</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus peran{' '}
                            <strong>{peran.name}</strong>? Aksi ini tidak dapat
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
