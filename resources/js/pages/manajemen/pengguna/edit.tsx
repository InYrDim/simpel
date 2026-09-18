import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import InputError from '@/components/input-error';
import manajemen from '@/routes/manajemen';
import pengguna from '@/routes/manajemen/pengguna';

type User = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
};

type EditPageProps = {
    user: User;
    errors?: { name?: string; email?: string };
};

export default function PenggunaEdit({ user, errors }: EditPageProps) {
    const { data, setData, put, processing, errors: formErrors } = useForm({
        name: user.name,
        email: user.email,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put(pengguna.update.url({ pengguna: user.id }), {
            preserveScroll: true,
        });
    };

    const nameError = formErrors.name || errors?.name;
    const emailError = formErrors.email || errors?.email;

    return (
        <>
            <Head title={`Edit Pengguna - ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Edit Pengguna</h1>
                </div>

                <form onSubmit={submit} className="flex max-w-lg flex-col gap-4">
                    <div>
                        <label htmlFor="name" className="mb-1 block text-sm font-medium">Nama</label>
                        <Input
                            id="name"
                            name="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                        />
                        {nameError && <InputError message={nameError} />}
                    </div>

                    <div>
                        <label htmlFor="email" className="mb-1 block text-sm font-medium">Email</label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                        />
                        {emailError && <InputError message={emailError} />}
                    </div>

                    <div className="flex items-center gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                        <Button type="button" variant="outline" asChild>
                            <a href={pengguna.index.url()}>Batal</a>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

PenggunaEdit.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
        {
            title: 'Pengguna',
            href: pengguna.index.url(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
});
