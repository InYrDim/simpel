import { Head } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Column, DataTable } from '@/components/data-table';
import bebanDosen from '@/routes/skripsi/beban-dosen';

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

type ValidatorStat = {
    dosen_id: number;
    dosen_nama: string;
    beban: number;
};

type BebanDosenPageProps = {
    beban_dosen: BebanDosenRow[];
    per_validator: ValidatorStat[];
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

export default function BebanDosenIndex({
    beban_dosen,
    per_validator,
}: BebanDosenPageProps) {
    return (
        <>
            <Head title="Laporan - Beban Dosen" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Beban Dosen</h1>

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

BebanDosenIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Laporan', href: bebanDosen.index.url() },
        { title: 'Beban Dosen', href: bebanDosen.index.url() },
    ],
});
