import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
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
    peranColumns,
    type PermissionOption,
    type PeranRow,
    PermissionPicker,
} from '@/pages/manajemen/components/peran-columns';
import manajemen from '@/routes/manajemen';
import { store } from '@/routes/manajemen/peran';

type PeranIndexPageProps = {
    roles: PeranRow[];
    permissionOptions: PermissionOption[];
};

export default function PeranIndex({
    roles,
    permissionOptions,
}: PeranIndexPageProps) {
    return (
        <>
            <Head title="Manajemen - Peran" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Peran</h1>
                    <CreatePeranDialog permissionOptions={permissionOptions} />
                </div>

                <Card className="p-4">
                    <DataTable
                        columns={peranColumns(permissionOptions)}
                        data={roles}
                        getRowKey={(r) => r.id}
                    />
                </Card>
            </div>
        </>
    );
}

function CreatePeranDialog({
    permissionOptions,
}: {
    permissionOptions: PermissionOption[];
}) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        permissions: [] as string[],
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

    const togglePermission = (name: string) => {
        setData(
            'permissions',
            data.permissions.includes(name)
                ? data.permissions.filter((p) => p !== name)
                : [...data.permissions, name],
        );
    };

    return (
        <>
            <Button size="sm" onClick={() => setOpen(true)}>
                Tambah Peran
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tambah peran</DialogTitle>
                        <DialogDescription>
                            Buat peran baru dan tentukan hak aksesnya.
                        </DialogDescription>
                    </DialogHeader>
                    <form onSubmit={submit} className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="peran-name">Nama</Label>
                            <Input
                                id="peran-name"
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

PeranIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
        {
            title: 'Peran',
            href: manajemen.peran.index.url(),
        },
    ],
});
