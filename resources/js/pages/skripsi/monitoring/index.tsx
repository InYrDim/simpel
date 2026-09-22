import { Head, router } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Column, DataTable } from '@/components/data-table';
import monitoring from '@/routes/skripsi/monitoring';

type PengajuanRow = {
    id: number;
    nama_mahasiswa: string;
    nim: string;
    judul: string;
    topik: string;
    status: string;
    status_label: string;
    validator_nama: string;
    berkas_original_name: string;
    submitted_at: string | null;
    decided_at: string | null;
};

type PaginatedPengajuan = {
    data: PengajuanRow[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
};

type StatusOption = {
    value: string;
    label: string;
};

type MonitoringPageProps = {
    pengajuans: PaginatedPengajuan;
    filters: { status: string | null };
    statusOptions: StatusOption[];
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

const pengajuanColumns: Column<PengajuanRow>[] = [
    {
        key: 'nama_mahasiswa',
        label: 'Mahasiswa',
        render: (row) => (
            <div className="flex flex-col">
                <span>{row.nama_mahasiswa}</span>
                <span className="text-muted-foreground text-xs">{row.nim}</span>
            </div>
        ),
    },
    {
        key: 'judul',
        label: 'Judul',
        render: (row) => (
            <div className="flex flex-col">
                <span>{row.judul}</span>
                <span className="text-muted-foreground text-xs">
                    {row.topik}
                </span>
            </div>
        ),
    },
    {
        key: 'status',
        label: 'Status',
        render: (row) => (
            <Badge variant={STATUS_VARIANT[row.status] ?? 'outline'}>
                {row.status_label}
            </Badge>
        ),
    },
    { key: 'validator_nama', label: 'Validator' },
    {
        key: 'submitted_at',
        label: 'Diajukan',
        render: (row) =>
            row.submitted_at
                ? new Date(row.submitted_at).toLocaleDateString('id-ID')
                : '-',
    },
];

export default function MonitoringIndex({
    pengajuans,
    filters,
    statusOptions,
}: MonitoringPageProps) {
    const filter = (status: string) =>
        router.get(monitoring.index.url(), { status });

    const goto = (page: number) =>
        router.get(monitoring.index.url(), { status: filters.status, page });

    return (
        <>
            <Head title="Laporan - Monitoring" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Monitoring Pengajuan</h1>

                <Card className="p-4">
                    <div className="mb-4 grid max-w-xs gap-2">
                        <Label htmlFor="mntr-status">Status</Label>
                        <Select
                            value={filters.status ?? 'semua'}
                            onValueChange={filter}
                        >
                            <SelectTrigger id="mntr-status" className="w-full">
                                <SelectValue placeholder="Semua status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="semua">
                                    Semua status
                                </SelectItem>
                                {statusOptions.map((o) => (
                                    <SelectItem key={o.value} value={o.value}>
                                        {o.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <DataTable
                        columns={pengajuanColumns}
                        data={pengajuans.data}
                        getRowKey={(row) => row.id}
                    />

                    <div className="mt-4 flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">
                            Menampilkan {pengajuans.data.length} dari{' '}
                            {pengajuans.total} pengajuan
                        </span>
                        <div className="flex items-center gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={!pengajuans.prev_page_url}
                                onClick={() =>
                                    goto(pengajuans.current_page - 1)
                                }
                            >
                                Sebelumnya
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={!pengajuans.next_page_url}
                                onClick={() =>
                                    goto(pengajuans.current_page + 1)
                                }
                            >
                                Berikutnya
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
        </>
    );
}

MonitoringIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Laporan', href: monitoring.index.url() },
        { title: 'Monitoring', href: monitoring.index.url() },
    ],
});
