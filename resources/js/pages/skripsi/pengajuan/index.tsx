import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import skripsi from '@/routes/skripsi';
import { PengajuanWizard } from './components/pengajuan-wizard';
import type { JudulItem, KategoriOption } from './components/types';

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

type RiwayatStatusItem = {
    aksi: string;
    dari_status_label: string | null;
    ke_status: string;
    ke_status_label: string;
    aktor_nama: string;
    catatan: string | null;
    created_at: string | null;
};

type PengajuanPageProps = {
    pengajuan: PengajuanProp | null;
    judulTerkini: JudulItem[];
    kategoriOptions: KategoriOption[];
    riwayat: RiwayatItem[];
    riwayatStatus: RiwayatStatusItem[];
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

const STATUS_LABEL: Record<string, string> = {
    diajukan: 'Diajukan',
    diverifikasi_admin: 'Diverifikasi Admin',
    diverifikasi_validator: 'Diverifikasi Validator',
    direvisi: 'Direvisi',
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
    kategoriOptions,
    riwayat,
    riwayatStatus,
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
                    <PengajuanWizard
                        mode={statusKey === 'direvisi' ? 'revisi' : 'baru'}
                        disabled={!bolehMengajukan}
                        pengajuanId={pengajuan?.id ?? null}
                        judulTerkini={judulTerkini}
                        kategoriOptions={kategoriOptions}
                    />
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
                                                Topik: {j.topik} · Kategori:{' '}
                                                {j.kategori_nama ?? '-'}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </>
                        )}
                    </CardContent>
                </Card>

                {riwayatStatus.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Kronologi Pengajuan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ol className="border-l-border ml-3 space-y-4 border-l-2 pl-5">
                                {riwayatStatus.map((r, i) => (
                                    <li key={i} className="relative text-sm">
                                        <span className="bg-primary absolute top-1.5 -left-[27px] size-2.5 rounded-full" />
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="font-medium">
                                                {AKSI_RIWAYAT_LABEL[r.aksi] ??
                                                    r.aksi}
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
                        </CardContent>
                    </Card>
                )}

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

PengajuanIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.pengajuan.status.url() },
        { title: 'Pengajuan', href: skripsi.pengajuan.status.url() },
    ],
});
