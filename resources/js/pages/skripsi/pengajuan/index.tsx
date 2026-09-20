import { Head, router, useForm } from '@inertiajs/react';
import { FileDown, Send } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Textarea } from '@/components/ui/textarea';
import skripsi from '@/routes/skripsi';
import { store, template } from '@/routes/skripsi/pengajuan';

type JudulItem = {
    id: number;
    urutan: number;
    judul: string;
    deskripsi: string;
    topik: string;
};

type PengajuanProp = {
    id: number;
    status: string;
    catatan_admin: string | null;
    catatan_validator: string | null;
    berkas_original_name: string;
    submitted_at: string | null;
    verified_at: string | null;
    decided_at: string | null;
};

type RiwayatItem = {
    id: number;
    status_label: string;
    catatan_admin: string | null;
    catatan_validator: string | null;
    submitted_at: string | null;
    jumlah_judul: number;
};

type PengajuanPageProps = {
    pengajuan: PengajuanProp | null;
    judulTerkini: JudulItem[];
    riwayat: RiwayatItem[];
};

const STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    diajukan: 'secondary',
    diverifikasi_admin: 'default',
    diverifikasi_validator: 'default',
    disetujui: 'default',
    ditolak_admin: 'destructive',
    ditolak_validator: 'destructive',
};

const STATUS_LABEL: Record<string, string> = {
    diajukan: 'Diajukan',
    diverifikasi_admin: 'Diverifikasi Admin',
    diverifikasi_validator: 'Diverifikasi Validator',
    disetujui: 'Disetujui',
    ditolak_admin: 'Ditolak Admin',
    ditolak_validator: 'Ditolak Validator',
};

const STATUS_JUDUL_LABEL: Record<string, string> = {
    ...STATUS_LABEL,
    belum: 'Belum mengajukan',
};

export default function PengajuanIndex({
    pengajuan,
    judulTerkini,
    riwayat,
}: PengajuanPageProps) {
    const statusKey = pengajuan?.status ?? 'belum';
    const bolehMengajukan =
        pengajuan === null ||
        statusKey === 'ditolak_admin' ||
        statusKey === 'ditolak_validator';

    return (
        <>
            <Head title="Skripsi - Pengajuan Judul" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">
                        Pengajuan Judul Skripsi
                    </h1>
                    <SubmitDialog disabled={!bolehMengajukan} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-3">
                            Status Terkini
                            <Badge
                                variant={STATUS_VARIANT[statusKey] ?? 'outline'}
                            >
                                {STATUS_JUDUL_LABEL[statusKey] ?? statusKey}
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        {pengajuan === null ? (
                            <p className="text-muted-foreground text-sm">
                                Anda belum mengajukan judul skripsi. Siapkan 3
                                judul beserta berkas surat pengajuan, lalu klik
                                tombol pengajuan di atas.
                            </p>
                        ) : (
                            <>
                                <div className="grid gap-2 text-sm">
                                    <div>
                                        <span className="text-muted-foreground">
                                            Berkas:{' '}
                                        </span>
                                        {pengajuan.berkas_original_name}
                                    </div>
                                    {pengajuan.catatan_admin && (
                                        <div className="text-destructive">
                                            <span className="font-medium">
                                                Catatan admin:{' '}
                                            </span>
                                            {pengajuan.catatan_admin}
                                        </div>
                                    )}
                                    {pengajuan.catatan_validator && (
                                        <div className="text-destructive">
                                            <span className="font-medium">
                                                Catatan validator:{' '}
                                            </span>
                                            {pengajuan.catatan_validator}
                                        </div>
                                    )}
                                </div>

                                <div className="grid gap-3">
                                    {judulTerkini.map((j) => (
                                        <div
                                            key={j.id}
                                            className="rounded-lg border p-3"
                                        >
                                            <div className="flex items-center gap-2">
                                                <Badge variant="outline">
                                                    {j.urutan}
                                                </Badge>
                                                <span className="font-medium">
                                                    {j.judul}
                                                </span>
                                            </div>
                                            <p className="text-muted-foreground mt-1 text-sm">
                                                {j.deskripsi}
                                            </p>
                                            <p className="text-muted-foreground mt-1 text-xs">
                                                Topik: {j.topik}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>

                {riwayat.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Riwayat Pengajuan</CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {riwayat.map((r) => (
                                <div
                                    key={r.id}
                                    className="flex items-center gap-3 text-sm"
                                >
                                    <Badge variant="outline">
                                        {r.status_label}
                                    </Badge>
                                    <span className="text-muted-foreground">
                                        {r.submitted_at
                                            ? new Date(
                                                  r.submitted_at,
                                              ).toLocaleDateString('id-ID')
                                            : '-'}{' '}
                                        · {r.jumlah_judul} judul
                                    </span>
                                    {r.catatan_admin && (
                                        <span className="text-muted-foreground truncate">
                                            “{r.catatan_admin}”
                                        </span>
                                    )}
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

type JudulForm = { judul: string; deskripsi: string; topik: string };

function SubmitDialog({ disabled }: { disabled: boolean }) {
    const [open, setOpen] = useState(false);
    const [step, setStep] = useState(1);
    const [berkas, setBerkas] = useState<File | null>(null);

    const emptyJuduls: JudulForm[] = [
        { judul: '', deskripsi: '', topik: '' },
        { judul: '', deskripsi: '', topik: '' },
        { judul: '', deskripsi: '', topik: '' },
    ];

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm<{
            juduls: JudulForm[];
            berkas: File | null;
        }>({
            juduls: emptyJuduls,
            berkas: null,
        });

    const judulError = (index: number, field: keyof JudulForm) =>
        errors[`juduls.${index}.${field}`];

    const close = () => {
        setOpen(false);
        setStep(1);
        setBerkas(null);
        reset();
        clearErrors();
    };

    const next = () => setStep((s) => Math.min(3, s + 1));
    const back = () => setStep((s) => Math.max(1, s - 1));

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(store.url(), {
            onSuccess: () => {
                close();
                router.reload({
                    only: ['pengajuan', 'judulTerkini', 'riwayat'],
                });
            },
        });
    };

    return (
        <>
            <Button size="sm" disabled={disabled} onClick={() => setOpen(true)}>
                <Send className="mr-2 size-4" />
                Ajukan Judul
            </Button>
            {disabled && (
                <p className="text-muted-foreground self-center text-xs">
                    Pengajuan aktif tidak memungkinkan submit baru.
                </p>
            )}

            <Dialog
                open={open}
                onOpenChange={(v) => (v ? setOpen(true) : close())}
            >
                <DialogContent className="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>
                            Ajukan Judul — Langkah {step} dari 3
                        </DialogTitle>
                        <DialogDescription>
                            {step === 1 &&
                                'Isi tepat 3 judul beserta deskripsi dan topiknya.'}
                            {step === 2 &&
                                'Unduh template, isi, lalu unggah sebagai PDF (maks 5 MB).'}
                            {step === 3 &&
                                'Periksa kembali sebelum mengirim pengajuan.'}
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={submit} className="flex flex-col gap-4">
                        {step === 1 && (
                            <div className="flex flex-col gap-4">
                                {data.juduls.map((j, i) => (
                                    <div
                                        key={i}
                                        className="grid gap-2 rounded-lg border p-3"
                                    >
                                        <Label>Judul {i + 1}</Label>
                                        <Input
                                            value={j.judul}
                                            placeholder="Judul"
                                            onChange={(e) =>
                                                setData(
                                                    `juduls.${i}.judul`,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {judulError(i, 'judul') && (
                                            <p className="text-sm text-red-600">
                                                {judulError(i, 'judul')}
                                            </p>
                                        )}
                                        <Textarea
                                            value={j.deskripsi}
                                            placeholder="Deskripsi"
                                            onChange={(e) =>
                                                setData(
                                                    `juduls.${i}.deskripsi`,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {judulError(i, 'deskripsi') && (
                                            <p className="text-sm text-red-600">
                                                {judulError(i, 'deskripsi')}
                                            </p>
                                        )}
                                        <Input
                                            value={j.topik}
                                            placeholder="Topik"
                                            onChange={(e) =>
                                                setData(
                                                    `juduls.${i}.topik`,
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        {judulError(i, 'topik') && (
                                            <p className="text-sm text-red-600">
                                                {judulError(i, 'topik')}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}

                        {step === 2 && (
                            <div className="flex flex-col gap-4">
                                <a
                                    href={template.url()}
                                    className="text-primary flex items-center gap-2 text-sm underline"
                                >
                                    <FileDown className="size-4" />
                                    Unduh template pengajuan (.docx)
                                </a>
                                <div className="grid gap-2">
                                    <Label htmlFor="berkas">
                                        Berkas pengajuan (PDF, maks 5 MB)
                                    </Label>
                                    <Input
                                        id="berkas"
                                        type="file"
                                        accept="application/pdf"
                                        onChange={(e) => {
                                            const file =
                                                e.target.files?.[0] ?? null;
                                            setBerkas(file);
                                            setData('berkas', file);
                                        }}
                                    />
                                    {errors.berkas && (
                                        <p className="text-sm text-red-600">
                                            {errors.berkas}
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}

                        {step === 3 && (
                            <div className="flex flex-col gap-3 text-sm">
                                <div className="rounded-lg border p-3">
                                    <p className="mb-2 font-medium">
                                        Ringkasan judul
                                    </p>
                                    {data.juduls.map((j, i) => (
                                        <div key={i} className="mb-2">
                                            <span className="font-medium">
                                                {i + 1}. {j.judul || '(kosong)'}
                                            </span>
                                            <span className="text-muted-foreground">
                                                {' '}
                                                — {j.topik || '(topik kosong)'}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                                <div className="rounded-lg border p-3">
                                    <p className="font-medium">Berkas</p>
                                    <p className="text-muted-foreground">
                                        {berkas
                                            ? `${berkas.name} (${(berkas.size / 1024).toFixed(0)} KB)`
                                            : 'Belum ada berkas dipilih'}
                                    </p>
                                </div>
                            </div>
                        )}

                        <DialogFooter className="flex items-center justify-between">
                            <div>
                                {step > 1 && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={back}
                                    >
                                        Kembali
                                    </Button>
                                )}
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={close}
                                >
                                    Batal
                                </Button>
                                {step < 3 ? (
                                    <Button type="button" onClick={next}>
                                        Lanjut
                                    </Button>
                                ) : (
                                    <Button
                                        type="submit"
                                        disabled={processing || !berkas}
                                    >
                                        {processing
                                            ? 'Mengirim...'
                                            : 'Kirim Pengajuan'}
                                    </Button>
                                )}
                            </div>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

PengajuanIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.pengajuan.status.url() },
        { title: 'Pengajuan', href: skripsi.pengajuan.status.url() },
    ],
});
