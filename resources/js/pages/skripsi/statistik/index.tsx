import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import statistik from '@/routes/skripsi/statistik';

type StatusStat = {
    status: string;
    status_label: string;
    jumlah: number;
};

type StatistikPageProps = {
    total: number;
    bulan_ini: number;
    per_status: StatusStat[];
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

export default function StatistikIndex({
    total,
    bulan_ini,
    per_status,
}: StatistikPageProps) {
    const jumlahStatus = (status: string) =>
        per_status.find((s) => s.status === status)?.jumlah ?? 0;
    const maxJumlah = Math.max(...per_status.map((s) => s.jumlah), 1);

    return (
        <>
            <Head title="Laporan - Statistik" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Statistik Pengajuan</h1>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard title="Total Pengajuan" value={total} />
                    <StatCard title="Bulan Ini" value={bulan_ini} />
                    <StatCard
                        title="Menunggu Verifikasi"
                        value={jumlahStatus('diajukan')}
                    />
                    <StatCard
                        title="Menunggu Putusan"
                        value={jumlahStatus('diverifikasi_admin')}
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Pengajuan per Status</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-3">
                        {per_status.map((s) => (
                            <div
                                key={s.status}
                                className="flex items-center gap-3 text-sm"
                            >
                                <Badge
                                    variant={
                                        STATUS_VARIANT[s.status] ?? 'outline'
                                    }
                                    className="w-44 justify-center"
                                >
                                    {s.status_label}
                                </Badge>
                                <div className="bg-muted h-4 flex-1 overflow-hidden rounded-full">
                                    <div
                                        className="bg-primary h-full rounded-full"
                                        style={{
                                            width: `${(s.jumlah / maxJumlah) * 100}%`,
                                        }}
                                    />
                                </div>
                                <span className="w-8 text-right tabular-nums">
                                    {s.jumlah}
                                </span>
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function StatCard({ title, value }: { title: string; value: number }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-muted-foreground text-sm font-medium">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p className="text-3xl font-bold tabular-nums">{value}</p>
            </CardContent>
        </Card>
    );
}

StatistikIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Laporan', href: statistik.index.url() },
        { title: 'Statistik', href: statistik.index.url() },
    ],
});
