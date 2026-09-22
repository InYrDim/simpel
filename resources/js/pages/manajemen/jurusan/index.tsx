import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import manajemen from '@/routes/manajemen';
import jurusanRoutes from '@/routes/manajemen/jurusan';

export type JurusanProfile = {
    nama: string;
    ketua_nama: string | null;
    ketua_nip: string | null;
    sekretaris_nama: string | null;
    sekretaris_nip: string | null;
};

type JurusanPageProps = {
    jurusan: JurusanProfile | null;
};

export default function JurusanProfilePage({ jurusan }: JurusanPageProps) {
    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({
        nama: jurusan?.nama ?? '',
        ketua_nama: jurusan?.ketua_nama ?? '',
        ketua_nip: jurusan?.ketua_nip ?? '',
        sekretaris_nama: jurusan?.sekretaris_nama ?? '',
        sekretaris_nip: jurusan?.sekretaris_nip ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(jurusanRoutes.update.url(), {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="Manajemen - Profil Jurusan" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Profil Jurusan</h1>

                <Card className="max-w-lg">
                    <CardHeader>
                        <CardTitle>Identitas jurusan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="flex flex-col gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="jurusan-nama">
                                    Nama jurusan
                                </Label>
                                <Input
                                    id="jurusan-nama"
                                    value={data.nama}
                                    onChange={(e) =>
                                        setData('nama', e.target.value)
                                    }
                                    disabled={processing}
                                    placeholder="Nama jurusan"
                                />
                                {errors.nama && (
                                    <InputError message={errors.nama} />
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="jurusan-ketua-nama">
                                        Nama ketua
                                    </Label>
                                    <Input
                                        id="jurusan-ketua-nama"
                                        value={data.ketua_nama}
                                        onChange={(e) =>
                                            setData(
                                                'ketua_nama',
                                                e.target.value,
                                            )
                                        }
                                        disabled={processing}
                                    />
                                    {errors.ketua_nama && (
                                        <InputError
                                            message={errors.ketua_nama}
                                        />
                                    )}
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="jurusan-ketua-nip">
                                        NIP ketua
                                    </Label>
                                    <Input
                                        id="jurusan-ketua-nip"
                                        value={data.ketua_nip}
                                        onChange={(e) =>
                                            setData('ketua_nip', e.target.value)
                                        }
                                        disabled={processing}
                                    />
                                    {errors.ketua_nip && (
                                        <InputError
                                            message={errors.ketua_nip}
                                        />
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="jurusan-sekretaris-nama">
                                        Nama sekretaris
                                    </Label>
                                    <Input
                                        id="jurusan-sekretaris-nama"
                                        value={data.sekretaris_nama}
                                        onChange={(e) =>
                                            setData(
                                                'sekretaris_nama',
                                                e.target.value,
                                            )
                                        }
                                        disabled={processing}
                                    />
                                    {errors.sekretaris_nama && (
                                        <InputError
                                            message={errors.sekretaris_nama}
                                        />
                                    )}
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="jurusan-sekretaris-nip">
                                        NIP sekretaris
                                    </Label>
                                    <Input
                                        id="jurusan-sekretaris-nip"
                                        value={data.sekretaris_nip}
                                        onChange={(e) =>
                                            setData(
                                                'sekretaris_nip',
                                                e.target.value,
                                            )
                                        }
                                        disabled={processing}
                                    />
                                    {errors.sekretaris_nip && (
                                        <InputError
                                            message={errors.sekretaris_nip}
                                        />
                                    )}
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Menyimpan...' : 'Simpan'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

JurusanProfilePage.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
        {
            title: 'Jurusan',
            href: jurusanRoutes.edit.url(),
        },
    ],
});
