import { MoreHorizontal, Pencil, Trash2 } from 'lucide-react';
import type { Column } from '@/components/data-table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export type DosenOption = {
    id: number;
    nama: string;
};

export type ProdiOption = {
    id: number;
    nama: string;
};

export type StatusOption = {
    value: string;
    label: string;
};

export type MahasiswaRow = {
    id: number;
    nama: string;
    nim: string;
    dosen_pa_id: number | null;
    dosen_pa_nama: string | null;
    user_email: string | null;
    prodi_id: number | null;
    prodi: string | null;
    angkatan: number | null;
    status: string;
    punya_pengajuan: boolean;
    created_at: string;
};

type ColumnHandlers = {
    statusOptions: StatusOption[];
    onEdit: (mahasiswa: MahasiswaRow) => void;
    onDelete: (mahasiswa: MahasiswaRow) => void;
};

/**
 * Kolom tabel mahasiswa. Dialog ubah/hapus dimiliki halaman (satu instans
 * bersama), jadi kolom hanya memanggil `onEdit` / `onDelete` dengan barisnya.
 */
export const mahasiswaColumns = ({
    statusOptions,
    onEdit,
    onDelete,
}: ColumnHandlers): Column<MahasiswaRow>[] => [
    {
        key: 'nama',
        label: 'Mahasiswa',
        sortKey: 'nama',
        render: (m) => (
            <div className="flex flex-col">
                <span className="font-medium">{m.nama}</span>
                <span className="text-muted-foreground text-xs">
                    {m.user_email ?? '-'}
                </span>
            </div>
        ),
    },
    {
        key: 'nim',
        label: 'NIM',
        sortKey: 'nim',
    },
    {
        key: 'prodi',
        label: 'Prodi',
        render: (m) => m.prodi ?? '-',
    },
    {
        key: 'angkatan',
        label: 'Angkatan',
        sortKey: 'angkatan',
        render: (m) => m.angkatan ?? '-',
    },
    {
        key: 'dosen_pa_nama',
        label: 'Dosen PA',
        render: (m) =>
            m.dosen_pa_nama ?? (
                <span className="text-muted-foreground">Belum ditentukan</span>
            ),
    },
    {
        key: 'status',
        label: 'Status',
        render: (m) => (
            <StatusBadge status={m.status} options={statusOptions} />
        ),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (m) => (
            <div className="flex justify-end">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            aria-label={`Aksi untuk ${m.nama}`}
                        >
                            <MoreHorizontal />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem onSelect={() => onEdit(m)}>
                            <Pencil />
                            Ubah
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            variant="destructive"
                            onSelect={() => onDelete(m)}
                        >
                            <Trash2 />
                            Hapus mahasiswa
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        ),
    },
];

function StatusBadge({
    status,
    options,
}: {
    status: string;
    options: StatusOption[];
}) {
    const label = options.find((o) => o.value === status)?.label ?? status;

    if (status === 'lulus') {
        return <Badge>{label}</Badge>;
    }

    if (status === 'aktif') {
        return <Badge variant="secondary">{label}</Badge>;
    }

    return (
        <Badge
            variant="outline"
            className={status === 'nonaktif' ? 'text-muted-foreground' : ''}
        >
            {label}
        </Badge>
    );
}
