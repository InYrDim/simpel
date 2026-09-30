import { Pencil, Trash2 } from 'lucide-react';
import type { Column } from '@/components/data-table';
import { Button } from '@/components/ui/button';

export type KaprodiOption = {
    id: number;
    nama: string;
};

export type ProdiRow = {
    id: number;
    nama: string;
    kaprodi_id: number | null;
    kaprodi_nama: string | null;
    jumlah_mahasiswa: number;
    created_at: string;
};

type ColumnHandlers = {
    onEdit: (prodi: ProdiRow) => void;
    onDelete: (prodi: ProdiRow) => void;
};

/**
 * Kolom tabel prodi. Dialog ubah/hapus dimiliki halaman (satu instans
 * bersama), jadi kolom hanya memanggil `onEdit` / `onDelete` dengan barisnya.
 */
export const prodiColumns = ({
    onEdit,
    onDelete,
}: ColumnHandlers): Column<ProdiRow>[] => [
    {
        key: 'nama',
        label: 'Program Studi',
        sortKey: 'nama',
    },
    {
        key: 'kaprodi_nama',
        label: 'Kaprodi',
        render: (p) => p.kaprodi_nama ?? '-',
    },
    {
        key: 'jumlah_mahasiswa',
        label: 'Mahasiswa',
        sortKey: 'jumlah_mahasiswa',
        render: (p) => p.jumlah_mahasiswa,
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (p) => (
            <div className="flex justify-end gap-1">
                <Button
                    variant="ghost"
                    size="icon"
                    title="Ubah"
                    aria-label={`Ubah ${p.nama}`}
                    onClick={() => onEdit(p)}
                >
                    <Pencil className="size-4" />
                </Button>
                <Button
                    variant="ghost"
                    size="icon"
                    title="Hapus"
                    aria-label={`Hapus ${p.nama}`}
                    onClick={() => onDelete(p)}
                >
                    <Trash2 className="text-destructive size-4" />
                </Button>
            </div>
        ),
    },
];
