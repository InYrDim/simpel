import { useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { Column } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
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
import { destroy, update } from '@/routes/manajemen/pengguna';

export type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    role: string | null;
};

export const userColumns = (roleOptions: string[]): Column<UserRow>[] => [
    {
        key: 'name',
        label: 'Pengguna',
        render: (u) => (
            <div className="flex items-center gap-3">
                <div className="bg-muted flex size-9 items-center justify-center rounded-full">
                    <UserIcon />
                </div>
                <div className="flex flex-col">
                    <span className="font-medium">{u.name}</span>
                    <span className="text-muted-foreground text-xs">
                        {u.email}
                    </span>
                </div>
            </div>
        ),
    },
    {
        key: 'roles',
        label: 'Peran',
        render: (u) =>
            u.role === null ? (
                <span className="text-muted-foreground text-sm">
                    Belum ada peran
                </span>
            ) : (
                <Badge variant="outline">{u.role}</Badge>
            ),
    },
    {
        key: 'email_verified_at',
        label: 'Verifikasi Email',
        render: (u) => {
            const verified = u.email_verified_at !== null;

            return (
                <Badge variant={verified ? 'default' : 'secondary'}>
                    {verified ? 'Terverifikasi' : 'Belum verifikasi'}
                </Badge>
            );
        },
    },
    {
        key: 'created_at',
        label: 'Dibuat',
        render: (u) => new Date(u.created_at).toLocaleDateString('id-ID'),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (u) => <ActionCell user={u} roleOptions={roleOptions} />,
    },
];

export function RolePicker({
    id,
    options,
    value,
    onChange,
    disabled,
    error,
}: {
    id: string;
    options: string[];
    value: string;
    onChange: (role: string) => void;
    disabled: boolean;
    error?: string;
}) {
    return (
        <div className="grid gap-2">
            <Label htmlFor={id}>Peran</Label>
            <Select value={value} onValueChange={onChange} disabled={disabled}>
                <SelectTrigger id={id}>
                    <SelectValue placeholder="Pilih peran" />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option} value={option}>
                            {option}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-destructive text-sm">{error}</p>}
        </div>
    );
}

function UserIcon() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className="size-4"
        >
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    );
}

function ActionCell({
    user,
    roleOptions,
}: {
    user: UserRow;
    roleOptions: string[];
}) {
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);
    const {
        put,
        delete: deleteForm,
        processing,
        setData,
        data,
        errors,
    } = useForm({
        name: user.name,
        email: user.email,
        role: user.role ?? '',
    });

    const handleEdit = () => {
        put(update.url({ pengguna: user.id }), {
            onSuccess: () => setEditOpen(false),
        });
    };

    const handleDelete = () => {
        deleteForm(destroy.url({ pengguna: user.id }), {
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
                    aria-label="Edit"
                    onClick={() => setEditOpen(true)}
                >
                    <Pencil className="size-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    title="Hapus"
                    aria-label="Hapus"
                    onClick={() => setDeleteOpen(true)}
                >
                    <Trash2 className="text-destructive size-4" />
                </Button>
            </div>

            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit pengguna</DialogTitle>
                        <DialogDescription>
                            Ubah nama, email, dan peran pengguna{' '}
                            <strong>{user.name}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor={`edit-name-${user.id}`}>Nama</Label>
                            <Input
                                id={`edit-name-${user.id}`}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            {errors.name && (
                                <p className="text-destructive text-sm">
                                    {errors.name}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor={`edit-email-${user.id}`}>
                                Email
                            </Label>
                            <Input
                                id={`edit-email-${user.id}`}
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
                            />
                            {errors.email && (
                                <p className="text-destructive text-sm">
                                    {errors.email}
                                </p>
                            )}
                        </div>
                        <RolePicker
                            id={`edit-role-${user.id}`}
                            options={roleOptions}
                            value={data.role}
                            onChange={(role) => setData('role', role)}
                            disabled={processing}
                            error={errors.role}
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
                        <DialogTitle>Hapus pengguna</DialogTitle>
                        <DialogDescription>
                            Apakah Anda yakin ingin menghapus pengguna{' '}
                            <strong>{user.name}</strong>? Aksi ini tidak dapat
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
