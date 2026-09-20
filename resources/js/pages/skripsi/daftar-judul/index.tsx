import { Head, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { usePage } from '@inertiajs/react';
import skripsi from '@/routes/skripsi';
import { assign } from '@/routes/skripsi/daftar-judul';

type Penugasan = {
    dosen_pembimbing_1: number | null;
    dosen_pembimbing_2: number | null;
    dosen_penguji_1: number | null;
    dosen_penguji_2: number | null;
};

type JudulRow = {
    id: number;
    judul: string;
    topik: string;
    deskripsi: string;
    nama_mahasiswa: string;
    nim: string;
    penugasan: Penugasan;
};

type DaftarJudulPageProps = {
    juduls: {
        data: JudulRow[];
        total: number;
        current_page: number;
        last_page: number;
    };
    filters: { search: string };
    dosenOptions: { id: number; nama: string; nip: string; bidang: string }[];
};

export default function DaftarJudulIndex({
    juduls,
    filters,
}: DaftarJudulPageProps) {
    const { auth } = usePage().props;
    const isAdmin = auth.roles.includes('admin');
    const dosenOptions = usePage().props
        .dosenOptions as DaftarJudulPageProps['dosenOptions'];

    const [detail, setDetail] = useState<JudulRow | null>(null);

    return (
        <>
            <Head title="Skripsi - Daftar Judul" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Daftar Judul</h1>
                </div>

                <Card className="p-4">
                    <form
                        className="flex items-center gap-2"
                        onChange={(e) => {
                            e.preventDefault();
                            router.get(skripsi.daftarJudul.index.url(), {
                                search: (e.target as HTMLFormElement).search
                                    .value,
                            });
                        }}
                    >
                        <div className="relative max-w-sm flex-1">
                            <Search className="text-muted-foreground absolute top-2.5 left-2.5 size-4" />
                            <Input
                                name="search"
                                placeholder="Cari judul, topik, atau deskripsi..."
                                defaultValue={filters.search}
                                className="pl-9"
                            />
                        </div>
                    </form>

                    <div className="rounded-md border">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="p-3 text-left font-medium">
                                        Judul
                                    </th>
                                    <th className="p-3 text-left font-medium">
                                        Topik
                                    </th>
                                    <th className="p-3 text-left font-medium">
                                        Mahasiswa
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {juduls.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={3}
                                            className="text-muted-foreground h-24 text-center"
                                        >
                                            Belum ada judul disetujui.
                                        </td>
                                    </tr>
                                ) : (
                                    juduls.data.map((j) => (
                                        <tr
                                            key={j.id}
                                            className="hover:bg-accent cursor-pointer border-b last:border-b-0"
                                            onClick={() => setDetail(j)}
                                        >
                                            <td className="p-3 font-medium">
                                                {j.judul}
                                            </td>
                                            <td className="text-muted-foreground p-3">
                                                {j.topik}
                                            </td>
                                            <td className="p-3">
                                                {j.nama_mahasiswa} ({j.nim})
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="text-muted-foreground mt-2 flex items-center justify-between text-sm">
                        <span>
                            Menampilkan {juduls.data.length} dari {juduls.total}{' '}
                            judul
                        </span>
                        <div className="flex gap-2">
                            {juduls.current_page > 1 && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.get(
                                            skripsi.daftarJudul.index.url(),
                                            {
                                                page: juduls.current_page - 1,
                                                search: filters.search,
                                            },
                                        )
                                    }
                                >
                                    Sebelumnya
                                </Button>
                            )}
                            {juduls.current_page < juduls.last_page && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        router.get(
                                            skripsi.daftarJudul.index.url(),
                                            {
                                                page: juduls.current_page + 1,
                                                search: filters.search,
                                            },
                                        )
                                    }
                                >
                                    Berikutnya
                                </Button>
                            )}
                        </div>
                    </div>
                </Card>

                <DetailModal
                    judul={detail}
                    onClose={() => setDetail(null)}
                    isAdmin={isAdmin}
                    dosenOptions={dosenOptions}
                />
            </div>
        </>
    );
}

function DetailModal({
    judul,
    onClose,
    isAdmin,
    dosenOptions,
}: {
    judul: JudulRow | null;
    onClose: () => void;
    isAdmin: boolean;
    dosenOptions: DaftarJudulPageProps['dosenOptions'];
}) {
    const [penugasan, setPenugasan] = useState<Penugasan>({
        dosen_pembimbing_1: null,
        dosen_pembimbing_2: null,
        dosen_penguji_1: null,
        dosen_penguji_2: null,
    });
    const [saving, setSaving] = useState(false);

    // Sinkronkan state modal dengan baris yang diklik.
    useEffect(() => {
        if (judul) {
            setPenugasan(judul.penugasan);
        }
    }, [judul]);

    const fields: { key: keyof Penugasan; label: string }[] = [
        { key: 'dosen_pembimbing_1', label: 'Dosen Pembimbing 1' },
        { key: 'dosen_pembimbing_2', label: 'Dosen Pembimbing 2' },
        { key: 'dosen_penguji_1', label: 'Dosen Penguji 1' },
        { key: 'dosen_penguji_2', label: 'Dosen Penguji 2' },
    ];

    const save = () => {
        if (!judul) return;
        setSaving(true);
        router.post(assign.url({ judul: judul.id }), penugasan, {
            preserveScroll: true,
            onFinish: () => setSaving(false),
            onSuccess: onClose,
        });
    };

    return (
        <Dialog open={judul !== null} onOpenChange={(v) => !v && onClose()}>
            <DialogContent className="max-w-xl">
                <DialogHeader>
                    <DialogTitle>Detail Judul</DialogTitle>
                    <DialogDescription>
                        {judul
                            ? `${judul.nama_mahasiswa} · NIM ${judul.nim}`
                            : ''}
                    </DialogDescription>
                </DialogHeader>

                {judul && (
                    <div className="grid gap-3 text-sm">
                        <div>
                            <p className="font-medium">{judul.judul}</p>
                            <p className="text-muted-foreground">
                                {judul.deskripsi}
                            </p>
                            <p className="text-muted-foreground mt-1 text-xs">
                                Topik: {judul.topik}
                            </p>
                        </div>

                        <div className="grid gap-2">
                            {fields.map(({ key, label }) => (
                                <div key={key} className="grid gap-1">
                                    <Label>{label}</Label>
                                    {isAdmin ? (
                                        <Select
                                            value={
                                                penugasan[key]
                                                    ? String(penugasan[key])
                                                    : ''
                                            }
                                            onValueChange={(v) =>
                                                setPenugasan((prev) => ({
                                                    ...prev,
                                                    [key]: Number(v),
                                                }))
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Belum ditugaskan" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {dosenOptions.map((d) => (
                                                    <SelectItem
                                                        key={d.id}
                                                        value={String(d.id)}
                                                    >
                                                        {d.nama} ({d.bidang})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <p className="text-muted-foreground">
                                            {dosenOptions.find(
                                                (d) =>
                                                    d.id ===
                                                    judul.penugasan[key],
                                            )?.nama ?? 'Belum ditugaskan'}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Tutup
                    </Button>
                    {isAdmin && (
                        <Button onClick={save} disabled={saving}>
                            {saving ? 'Menyimpan...' : 'Simpan Penugasan'}
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

DaftarJudulIndex.layout = () => ({
    breadcrumbs: [
        { title: 'Skripsi', href: skripsi.daftarJudul.index.url() },
        { title: 'Daftar Judul', href: skripsi.daftarJudul.index.url() },
    ],
});
