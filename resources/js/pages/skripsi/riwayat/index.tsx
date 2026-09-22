import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
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
import { Label } from '@/components/ui/label';
import riwayat from '@/routes/skripsi/riwayat';

type RiwayatStatusItem = {
    aksi: string;
    dari_status_label: string | null;
    ke_status: string;
    ke_status_label: string;
    aktor_nama: string;
    catatan: string | null;
    created_at: string | null;
};

type PengajuanRow = {
    id: number;
    status: string;
    status_label: string;
    submitted_at: string | null;
    nama_mahasiswa: string;
    nim: string;
    jumlah_judul: number;
    judul_list: string[];
    catatan_admin: string | null;
    catatan_validator: string | null;
    riwayat: RiwayatStatusItem[];
};

type RiwayatPageProps = {
    pengajuans: {
        data: PengajuanRow[];
        total: number;
        current_page: number;
        last_page: number;
    };
};

const AKSI_RIWAYAT_LABEL: Record<string, string> = {
    submit: 'Pengajuan dikirim',
    verifikasi_setuju: 'Verifikasi admin — disetujui',
    verifikasi_tolak: 'Verifikasi admin — ditolak',
    putusan_setuju: 'Putusan validator — disetujui',
    putusan_tolak: 'Putusan validator — ditolak',
    verifikasi_revisi: 'Verifikasi admin — minta revisi',
    putusan_revisi: 'Putusan validator — minta revisi',
    resubmit: 'Revisi dikirim ulang',
};

const STATUS_VARIANT: Record<
    string,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    diajukan: 'secondary',
    diverifikasi_admin: 'default',
    diverifikasi_validator: 'default',
    direvisi: 'secondary',
    disetujui: 'default',
    ditolak_admin: 'destructive',
    ditolak_validator: 'destructive',
};

export default function RiwayatIndex({ pengajuans }: RiwayatPageProps) {
    const [detail, setDetail] = useState<PengajuanRow | null>(null);

    return (
        <>
            <Head title="Skripsi - Riwayat Pengajuan" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Riwayat Pengajuan</h1>

                <section className="flex flex-col gap-4">
                    <div className="rounded-md border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="p-3 text-left font-medium">
                                        Status
                                    </th>
                                    <th className="p-3 text-left font-medium">
                                        Mahasiswa
                                    </th>
                                    <th className="p-3 text-left font-medium">
                                        Diajukan
                                    </th>
                                    <th className="p-3 text-left font-medium">
                                        Judul
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {pengajuans.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={4}
                                            className="text-muted-foreground h-24 text-center"
                                        >
                                            Belum ada pengajuan.
                                        </td>
                                    </tr>
                                ) : (
                                    pengajuans.data.map((p) => (
                                        <tr
                                            key={p.id}
                                            className="hover:bg-accent cursor-pointer border-b last:border-b-0"
                                            onClick={() => setDetail(p)}
                                        >
                                            <td className="p-3">
                                                <Badge
                                                    variant={
                                                        STATUS_VARIANT[
                                                            p.status
                                                        ] ?? 'outline'
                                                    }
                                                >
                                                    {p.status_label}
                                                </Badge>
                                            </td>
                                            <td className="p-3">
                                                {p.nama_mahasiswa} ({p.nim})
                                            </td>
                                            <td className="text-muted-foreground p-3">
                                                {p.submitted_at
                                                    ? new Date(
                                                          p.submitted_at,
                                                      ).toLocaleString('id-ID')
                                                    : '-'}
                                            </td>
                                            <td className="p-3">
                                                {p.jumlah_judul} judul
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="text-muted-foreground mt-2 flex items-center justify-between text-sm">
                        <span>
                            Menampilkan {pengajuans.data.length} dari{' '}
                            {pengajuans.total} pengajuan
                        </span>
                        <div className="flex gap-2">
                            {pengajuans.current_page > 1 && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.get(riwayat.index.url(), {
                                            page: pengajuans.current_page - 1,
                                        })
                                    }
                                >
                                    Sebelumnya
                                </Button>
                            )}
                            {pengajuans.current_page < pengajuans.last_page && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.get(riwayat.index.url(), {
                                            page: pengajuans.current_page + 1,
                                        })
                                    }
                                >
                                    Berikutnya
                                </Button>
                            )}
                        </div>
                    </div>
                </section>

                <DetailDialog
                    pengajuan={detail}
                    onClose={() => setDetail(null)}
                />
            </div>
        </>
    );
}

function DetailDialog({
    pengajuan,
    onClose,
}: {
    pengajuan: PengajuanRow | null;
    onClose: () => void;
}) {
    return (
        <Dialog open={pengajuan !== null} onOpenChange={(v) => !v && onClose()}>
            <DialogContent className="max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Detail Pengajuan</DialogTitle>
                    <DialogDescription>
                        {pengajuan
                            ? `${pengajuan.nama_mahasiswa} · NIM ${pengajuan.nim}`
                            : ''}
                    </DialogDescription>
                </DialogHeader>

                {pengajuan && (
                    <div className="grid gap-4 text-sm">
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge
                                variant={
                                    STATUS_VARIANT[pengajuan.status] ??
                                    'outline'
                                }
                            >
                                {pengajuan.status_label}
                            </Badge>
                            <span className="text-muted-foreground text-xs">
                                Diajukan{' '}
                                {pengajuan.submitted_at
                                    ? new Date(
                                          pengajuan.submitted_at,
                                      ).toLocaleString('id-ID')
                                    : '-'}
                            </span>
                        </div>

                        {(pengajuan.catatan_admin ||
                            pengajuan.catatan_validator) && (
                            <div className="grid gap-1">
                                {pengajuan.catatan_admin && (
                                    <p className="text-destructive">
                                        <span className="font-medium">
                                            Catatan admin:{' '}
                                        </span>
                                        {pengajuan.catatan_admin}
                                    </p>
                                )}
                                {pengajuan.catatan_validator && (
                                    <p className="text-destructive">
                                        <span className="font-medium">
                                            Catatan validator:{' '}
                                        </span>
                                        {pengajuan.catatan_validator}
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="grid gap-2">
                            <Label>
                                Judul diajukan ({pengajuan.jumlah_judul})
                            </Label>
                            {pengajuan.judul_list.map((judul, i) => (
                                <div key={i} className="flex items-start gap-2">
                                    <Badge variant="outline">{i + 1}</Badge>
                                    <span>{judul}</span>
                                </div>
                            ))}
                        </div>

                        {pengajuan.riwayat.length > 0 && (
                            <div>
                                <Label className="mb-2">
                                    Kronologi pengajuan
                                </Label>
                                <ol className="border-l-border ml-3 space-y-4 border-l-2 pl-5">
                                    {pengajuan.riwayat.map((r, i) => (
                                        <li
                                            key={i}
                                            className="relative text-sm"
                                        >
                                            <span className="bg-primary absolute top-1.5 -left-[27px] size-2.5 rounded-full" />
                                            <div className="flex flex-wrap items-center gap-2">
                                                <span className="font-medium">
                                                    {AKSI_RIWAYAT_LABEL[
                                                        r.aksi
                                                    ] ?? r.aksi}
                                                </span>
                                                <Badge
                                                    variant={
                                                        STATUS_VARIANT[
                                                            r.ke_status
                                                        ] ?? 'outline'
                                                    }
                                                >
                                                    {r.ke_status_label}
                                                </Badge>
                                            </div>
                                            <p className="text-muted-foreground mt-0.5 text-xs">
                                                {r.aktor_nama} ·{' '}
                                                {r.created_at
                                                    ? new Date(
                                                          r.created_at,
                                                      ).toLocaleString('id-ID')
                                                    : '-'}
                                            </p>
                                            {r.catatan && (
                                                <p className="text-muted-foreground mt-1">
                                                    “{r.catatan}”
                                                </p>
                                            )}
                                        </li>
                                    ))}
                                </ol>
                            </div>
                        )}
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Tutup
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

RiwayatIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: riwayat.index.url() },
        { title: 'Riwayat Pengajuan', href: riwayat.index.url() },
    ],
});
