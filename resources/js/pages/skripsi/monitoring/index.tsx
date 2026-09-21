import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Column, DataTable } from '@/components/data-table';
import monitoring from '@/routes/skripsi/monitoring';
import skripsi from '@/routes/skripsi';

type StatusStat = {
    status: string;
    status_label: string;
    jumlah: number;
};

type ValidatorStat = {
    dosen_id: number;
    dosen_nama: string;
    beban: number;
};

type BebanDosenRow = {
    dosen_id: number;
    dosen_nama: string;
    validator_aktif: number;
    pembimbing_1: number;
    pembimbing_2: number;
    penguji_1: number;
    penguji_2: number;
    total: number;
};

type MonitoringPageProps = {
    total: number;
    per_status: StatusStat[];
    per_validator: ValidatorStat[];
    bulan_ini: number;
    beban_dosen: BebanDosenRow[];
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

const bebanDosenColumns: Column<BebanDosenRow>[] = [
    { key: 'dosen_nama', label: 'Dosen' },
    { key: 'validator_aktif', label: 'Validator aktif' },
    { key: 'pembimbing_1', label: 'Pembimbing 1' },
    { key: 'pembimbing_2', label: 'Pembimbing 2' },
    { key: 'penguji_1', label: 'Penguji 1' },
    { key: 'penguji_2', label: 'Penguji 2' },
    {
        key: 'total',
        label: 'Total',
        render: (row) => (
            <span className="font-semibold tabular-nums">{row.total}</span>
        ),
    },
];

export default function MonitoringIndex({
    total,
    per_status,
    per_validator,
    bulan_ini,
    beban_dosen,
}: MonitoringPageProps) {
    const jumlahStatus = (status: string) =>
        per_status.find((s) => s.status === status)?.jumlah ?? 0;
    const maxJumlah = Math.max(...per_status.map((s) => s.jumlah), 1);

    return (
        <>
            <Head title="Skripsi - Monitoring" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Monitoring Pengajuan</h1>

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

                <Card>
                    <CardHeader>
                        <CardTitle>Sebaran Beban Dosen</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <DataTable
                            columns={bebanDosenColumns}
                            data={beban_dosen}
                            getRowKey={(row) => row.dosen_id}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Beban Validator</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-3">
                        {per_validator.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                Tidak ada penugasan review aktif.
                            </p>
                        ) : (
                            per_validator.map((v) => (
                                <div
                                    key={v.dosen_id}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <span>{v.dosen_nama}</span>
                                    <Badge variant="secondary">
                                        {v.beban} pengajuan
                                    </Badge>
                                </div>
                            ))
                        )}
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

MonitoringIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.daftarJudul.index.url() },
        { title: 'Monitoring', href: monitoring.index.url() },
    ],
});
