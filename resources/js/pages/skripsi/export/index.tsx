import { Head } from '@inertiajs/react';
import { Download } from 'lucide-react';
import { useState } from 'react';
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
import exportRoutes from '@/routes/skripsi/export';

type StatusOption = {
    value: string;
    label: string;
};

type ExportPageProps = {
    statusOptions: StatusOption[];
};

export default function ExportIndex({ statusOptions }: ExportPageProps) {
    const [status, setStatus] = useState('semua');

    const csvUrl = exportRoutes.csv.url({
        query: status === 'semua' ? {} : { status },
    });

    return (
        <>
            <Head title="Laporan - Export" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <h1 className="text-2xl font-bold">Export</h1>

                <Card className="max-w-md p-4">
                    <p className="text-muted-foreground mb-4 text-sm">
                        Unduh daftar pengajuan judul sebagai file CSV. Anda
                        dapat membatasi berdasarkan status pengajuan.
                    </p>

                    <div className="grid gap-2">
                        <Label htmlFor="export-status">Status</Label>
                        <Select value={status} onValueChange={setStatus}>
                            <SelectTrigger
                                id="export-status"
                                className="w-full"
                            >
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

                        <Button asChild className="mt-2 w-fit">
                            <a href={csvUrl}>
                                <Download />
                                Unduh CSV
                            </a>
                        </Button>
                    </div>
                </Card>
            </div>
        </>
    );
}

ExportIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Laporan', href: exportRoutes.index.url() },
        { title: 'Export', href: exportRoutes.index.url() },
    ],
});
