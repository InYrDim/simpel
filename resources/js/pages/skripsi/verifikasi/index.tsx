import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import skripsi from '@/routes/skripsi';
import { store } from '@/routes/skripsi/verifikasi';

type JudulItem = { id: number; urutan: number; judul: string; topik: string };

type PengajuanItem = {
    id: number;
    nama_mahasiswa: string;
    nim: string;
    submitted_at: string | null;
    berkas_original_name: string;
    juduls: JudulItem[];
};

type DosenOption = { id: number; nama: string; nip: string; bidang: string };

type VerifikasiPageProps = {
    pengajuans: PengajuanItem[];
    dosenOptions: DosenOption[];
};

export default function VerifikasiIndex({
    pengajuans,
    dosenOptions,
}: VerifikasiPageProps) {
    return (
        <>
            <Head title="Skripsi - Verifikasi Pengajuan" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Verifikasi Pengajuan</h1>

                {pengajuans.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-8 text-center text-sm">
                            Tidak ada pengajuan menunggu verifikasi.
                        </CardContent>
                    </Card>
                ) : (
                    pengajuans.map((p) => (
                        <PengajuanCard
                            key={p.id}
                            pengajuan={p}
                            dosenOptions={dosenOptions}
                        />
                    ))
                )}
            </div>
        </>
    );
}

function PengajuanCard({
    pengajuan,
    dosenOptions,
}: {
    pengajuan: PengajuanItem;
    dosenOptions: DosenOption[];
}) {
    const [dialog, setDialog] = useState<'none' | 'approve' | 'reject'>('none');
    const { data, setData, post, processing, errors, reset, transform } =
        useForm<{
            validator_id: string;
            catatan_admin: string;
        }>({
            validator_id: '',
            catatan_admin: '',
        });

    const closeDialog = () => {
        setDialog('none');
        reset();
    };

    const submit = (disetujui: boolean) => {
        transform(() => ({
            disetujui,
            validator_id: data.validator_id || null,
            catatan_admin: data.catatan_admin || null,
        }));
        post(store.url({ pengajuan: pengajuan.id }), {
            onSuccess: closeDialog,
        });
    };

    return (
        <>
            <Card>
                <CardContent className="flex flex-col gap-3">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p className="font-medium">
                                {pengajuan.nama_mahasiswa}
                            </p>
                            <p className="text-muted-foreground text-sm">
                                NIM {pengajuan.nim} ·{' '}
                                {pengajuan.submitted_at
                                    ? new Date(
                                          pengajuan.submitted_at,
                                      ).toLocaleDateString('id-ID')
                                    : '-'}{' '}
                                · {pengajuan.berkas_original_name}
                            </p>
                        </div>
                        <div className="flex gap-2">
                            <Button
                                size="sm"
                                onClick={() => setDialog('approve')}
                            >
                                Verifikasi
                            </Button>
                            <Button
                                size="sm"
                                variant="destructive"
                                onClick={() => setDialog('reject')}
                            >
                                Tolak
                            </Button>
                        </div>
                    </div>

                    <div className="grid gap-2">
                        {pengajuan.juduls.map((j) => (
                            <div
                                key={j.id}
                                className="flex items-center gap-2 text-sm"
                            >
                                <Badge variant="outline">{j.urutan}</Badge>
                                <span>{j.judul}</span>
                                <span className="text-muted-foreground">
                                    ({j.topik})
                                </span>
                            </div>
                        ))}
                    </div>
                </CardContent>
            </Card>

            <Dialog
                open={dialog === 'approve'}
                onOpenChange={(v) => !v && closeDialog()}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Verifikasi pengajuan</DialogTitle>
                        <DialogDescription>
                            Pilih dosen validator untuk pengajuan{' '}
                            <strong>{pengajuan.nama_mahasiswa}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`validator-${pengajuan.id}`}>
                            Dosen Validator
                        </Label>
                        <Select
                            value={data.validator_id}
                            onValueChange={(v) => setData('validator_id', v)}
                        >
                            <SelectTrigger id={`validator-${pengajuan.id}`}>
                                <SelectValue placeholder="Pilih dosen validator" />
                            </SelectTrigger>
                            <SelectContent>
                                {dosenOptions.map((d) => (
                                    <SelectItem key={d.id} value={String(d.id)}>
                                        {d.nama} ({d.bidang})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.validator_id && (
                            <p className="text-sm text-red-600">
                                {errors.validator_id}
                            </p>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={closeDialog}>
                            Batal
                        </Button>
                        <Button
                            disabled={processing}
                            onClick={() => submit(true)}
                        >
                            Setujui
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog
                open={dialog === 'reject'}
                onOpenChange={(v) => !v && closeDialog()}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tolak pengajuan</DialogTitle>
                        <DialogDescription>
                            Catatan wajib diisi — akan dilihat mahasiswa.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`catatan-${pengajuan.id}`}>
                            Catatan
                        </Label>
                        <Textarea
                            id={`catatan-${pengajuan.id}`}
                            value={data.catatan_admin}
                            onChange={(e) =>
                                setData('catatan_admin', e.target.value)
                            }
                        />
                        {errors.catatan_admin && (
                            <p className="text-sm text-red-600">
                                {errors.catatan_admin}
                            </p>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={closeDialog}>
                            Batal
                        </Button>
                        <Button
                            variant="destructive"
                            disabled={processing}
                            onClick={() => submit(false)}
                        >
                            Tolak Pengajuan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

VerifikasiIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.verifikasi.index.url() },
        { title: 'Verifikasi', href: skripsi.verifikasi.index.url() },
    ],
});
