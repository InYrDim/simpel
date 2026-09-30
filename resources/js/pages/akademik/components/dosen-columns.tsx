import { Pencil, Trash2 } from 'lucide-react';
import type { Column } from '@/components/data-table';
import { Button } from '@/components/ui/button';

export type DosenRow = {
    id: number;
    nama: string;
    nip: string;
    bidang: string;
    jumlah_mahasiswa_pa: number;
    created_at: string;
};

type ColumnHandlers = {
    onEdit: (dosen: DosenRow) => void;
    onDelete: (dosen: DosenRow) => void;
};

/**
 * Kolom tabel dosen. Dialog ubah/hapus dimiliki halaman (satu instans
 * bersama), jadi kolom hanya memanggil `onEdit` / `onDelete` dengan barisnya.
 */
export const dosenColumns = ({
    onEdit,
    onDelete,
}: ColumnHandlers): Column<DosenRow>[] => [
    {
        key: 'nama',
        label: 'Dosen',
        sortKey: 'nama',
        render: (d) => (
            <div className="flex flex-col">
                <span className="font-medium">{d.nama}</span>
                <span className="text-muted-foreground text-xs">{d.nip}</span>
            </div>
        ),
    },
    {
        key: 'bidang',
        label: 'Bidang',
        sortKey: 'bidang',
    },
    {
        key: 'jumlah_mahasiswa_pa',
        label: 'Mahasiswa PA',
        render: (d) => d.jumlah_mahasiswa_pa,
    },
    {
        key: 'created_at',
        label: 'Dibuat',
        render: (d) => new Date(d.created_at).toLocaleDateString('id-ID'),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (d) => (
            <div className="flex justify-end gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    title="Ubah"
                    aria-label={`Ubah ${d.nama}`}
                    onClick={() => onEdit(d)}
                >
                    <Pencil className="size-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    title="Hapus"
                    aria-label={`Hapus ${d.nama}`}
                    onClick={() => onDelete(d)}
                >
                    <Trash2 className="text-destructive size-4" />
                </Button>
            </div>
        ),
    },
];
