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
import { Textarea } from '@/components/ui/textarea';
import skripsi from '@/routes/skripsi';
import { revisi, store } from '@/routes/skripsi/putusan';

type JudulItem = {
    id: number;
    urutan: number;
    judul: string;
    deskripsi: string;
    topik: string;
};

type PengajuanItem = {
    id: number;
    nama_mahasiswa: string;
    nim: string;
    verified_at: string | null;
    juduls: JudulItem[];
};

type PutusanPageProps = {
    pengajuans: PengajuanItem[];
};

export default function PutusanIndex({ pengajuans }: PutusanPageProps) {
    return (
        <>
            <Head title="Skripsi - Putusan Validator" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Penugasan Review</h1>

                {pengajuans.length === 0 ? (
                    <Card>
                        <CardContent className="text-muted-foreground py-8 text-center text-sm">
                            Tidak ada pengajuan yang ditugaskan kepada Anda.
                        </CardContent>
                    </Card>
                ) : (
                    pengajuans.map((p) => (
                        <PengajuanCard key={p.id} pengajuan={p} />
                    ))
                )}
            </div>
        </>
    );
}

function PengajuanCard({ pengajuan }: { pengajuan: PengajuanItem }) {
    const [dialog, setDialog] = useState<
        'none' | 'approve' | 'reject' | 'revisi'
    >('none');
    const { data, setData, post, processing, errors, reset, transform } =
        useForm<{
            judul_id: string;
            catatan_validator: string;
        }>({
            judul_id: '',
            catatan_validator: '',
        });
    // Form terpisah agar error validasi revisi tidak bocor ke dialog tolak
    // (yang memakai field `catatan_validator` yang sama).
    const revisiForm = useForm<{ catatan_validator: string }>({
        catatan_validator: '',
    });

    const closeDialog = () => {
        setDialog('none');
        reset();
    };

    const closeRevisiDialog = () => {
        setDialog('none');
        revisiForm.reset();
    };

    const submit = (disetujui: boolean) => {
        transform(() => ({
            disetujui,
            judul_id: data.judul_id || null,
            catatan_validator: data.catatan_validator || null,
        }));
        post(store.url({ pengajuan: pengajuan.id }), {
            onSuccess: closeDialog,
        });
    };

    const submitRevisi = () => {
        revisiForm.post(revisi.url({ pengajuan: pengajuan.id }), {
            onSuccess: closeRevisiDialog,
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
                                {pengajuan.verified_at
                                    ? new Date(
                                          pengajuan.verified_at,
                                      ).toLocaleDateString('id-ID')
                                    : '-'}
                            </p>
                        </div>
                        <div className="flex gap-2">
                            <Button
                                size="sm"
                                onClick={() => setDialog('approve')}
                            >
                                Setujui
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => setDialog('revisi')}
                            >
                                Minta Revisi
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
                                className="rounded-lg border p-3 text-sm"
                            >
                                <div className="flex items-center gap-2">
                                    <Badge variant="outline">{j.urutan}</Badge>
                                    <span className="font-medium">
                                        {j.judul}
                                    </span>
                                </div>
                                <p className="text-muted-foreground mt-1">
                                    {j.deskripsi}
                                </p>
                                <p className="text-muted-foreground mt-1 text-xs">
                                    Topik: {j.topik}
                                </p>
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
                        <DialogTitle>Setujui salah satu judul</DialogTitle>
                        <DialogDescription>
                            Pilih satu judul yang disetujui dari 3 judul
                            pengajuan.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`judul-${pengajuan.id}`}>
                            Judul disetujui
                        </Label>
                        <div className="grid gap-2">
                            {pengajuan.juduls.map((j) => (
                                <label
                                    key={j.id}
                                    className="hover:bg-accent flex cursor-pointer items-start gap-2 rounded-lg border p-3 text-sm"
                                >
                                    <input
                                        type="radio"
                                        name={`judul-${pengajuan.id}`}
                                        value={j.id}
                                        checked={data.judul_id === String(j.id)}
                                        onChange={() =>
                                            setData('judul_id', String(j.id))
                                        }
                                        className="mt-1"
                                    />
                                    <span>
                                        <span className="font-medium">
                                            {j.judul}
                                        </span>
                                        <span className="text-muted-foreground block">
                                            {j.deskripsi}
                                        </span>
                                    </span>
                                </label>
                            ))}
                        </div>
                        {errors.judul_id && (
                            <p className="text-sm text-red-600">
                                {errors.judul_id}
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
                            Setujui Judul
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
                            value={data.catatan_validator}
                            onChange={(e) =>
                                setData('catatan_validator', e.target.value)
                            }
                        />
                        {errors.catatan_validator && (
                            <p className="text-sm text-red-600">
                                {errors.catatan_validator}
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

            <Dialog
                open={dialog === 'revisi'}
                onOpenChange={(v) => !v && closeRevisiDialog()}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Minta revisi</DialogTitle>
                        <DialogDescription>
                            Pengajuan dikembalikan ke mahasiswa untuk
                            diperbaiki, lalu diajukan ulang pada pengajuan yang
                            sama. Catatan wajib diisi — akan dilihat mahasiswa.
                        </DialogDescription>
                    </DialogHeader>
                    <div className="grid gap-2">
                        <Label htmlFor={`catatan-revisi-${pengajuan.id}`}>
                            Catatan revisi
                        </Label>
                        <Textarea
                            id={`catatan-revisi-${pengajuan.id}`}
                            value={revisiForm.data.catatan_validator}
                            onChange={(e) =>
                                revisiForm.setData(
                                    'catatan_validator',
                                    e.target.value,
                                )
                            }
                        />
                        {revisiForm.errors.catatan_validator && (
                            <p className="text-sm text-red-600">
                                {revisiForm.errors.catatan_validator}
                            </p>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={closeRevisiDialog}>
                            Batal
                        </Button>
                        <Button
                            disabled={revisiForm.processing}
                            onClick={submitRevisi}
                        >
                            Kirim Permintaan Revisi
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

PutusanIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.putusan.index.url() },
        { title: 'Putusan', href: skripsi.putusan.index.url() },
    ],
});
