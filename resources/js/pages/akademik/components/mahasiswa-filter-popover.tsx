import { SlidersHorizontal } from 'lucide-react';
import { SearchableSelect } from '@/components/searchable-select';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type {
    DosenOption,
    ProdiOption,
} from '@/pages/akademik/components/mahasiswa-columns';

export const SEMUA = 'semua';
export const TANPA_DOSEN_PA = 'kosong';

export type DosenPaFilter = number | typeof TANPA_DOSEN_PA | null;

export type FilterPatch = {
    prodi_id?: number | null;
    angkatan?: number | null;
    dosen_pa_id?: DosenPaFilter;
};

type MahasiswaFilterPopoverProps = {
    prodiId: number | null;
    angkatan: number | null;
    dosenPaId: DosenPaFilter;
    prodiOptions: ProdiOption[];
    angkatanOptions: number[];
    dosenOptions: DosenOption[];
    onChange: (patch: FilterPatch) => void;
};

/**
 * Filter yang jarang dipakai (prodi, angkatan, dosen PA) dikumpulkan dalam
 * satu popover supaya toolbar hanya menampilkan pencarian. Status ada di baris ringkasan.
 * Jumlah filter aktif tampil di tombol; nilainya tampil sebagai chip di halaman.
 */
export function MahasiswaFilterPopover({
    prodiId,
    angkatan,
    dosenPaId,
    prodiOptions,
    angkatanOptions,
    dosenOptions,
    onChange,
}: MahasiswaFilterPopoverProps) {
    const activeCount = [prodiId, angkatan, dosenPaId].filter(
        (value) => value !== null,
    ).length;

    const dosenChoices = [
        { value: TANPA_DOSEN_PA, label: 'Belum ada dosen PA' },
        ...dosenOptions.map((d) => ({ value: String(d.id), label: d.nama })),
    ];

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button type="button" variant="outline">
                    <SlidersHorizontal />
                    Filter
                    {activeCount > 0 && (
                        <Badge
                            variant="secondary"
                            aria-label={`${activeCount} filter aktif`}
                        >
                            {activeCount}
                        </Badge>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent align="start" className="grid w-80 gap-4">
                <div className="grid gap-2">
                    <Label htmlFor="mhs-filter-prodi">Prodi</Label>
                    <Select
                        value={prodiId === null ? SEMUA : String(prodiId)}
                        onValueChange={(v) =>
                            onChange({
                                prodi_id: v === SEMUA ? null : Number(v),
                            })
                        }
                    >
                        <SelectTrigger id="mhs-filter-prodi" className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={SEMUA}>Semua prodi</SelectItem>
                            {prodiOptions.map((p) => (
                                <SelectItem key={p.id} value={String(p.id)}>
                                    {p.nama}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="mhs-filter-angkatan">Angkatan</Label>
                    <Select
                        value={angkatan === null ? SEMUA : String(angkatan)}
                        onValueChange={(v) =>
                            onChange({
                                angkatan: v === SEMUA ? null : Number(v),
                            })
                        }
                    >
                        <SelectTrigger
                            id="mhs-filter-angkatan"
                            className="w-full"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={SEMUA}>
                                Semua angkatan
                            </SelectItem>
                            {angkatanOptions.map((tahun) => (
                                <SelectItem key={tahun} value={String(tahun)}>
                                    {tahun}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="mhs-filter-dosen">
                        Dosen PA (penasehat akademik)
                    </Label>
                    <SearchableSelect
                        id="mhs-filter-dosen"
                        value={dosenPaId === null ? '' : String(dosenPaId)}
                        onChange={(v) =>
                            onChange({
                                dosen_pa_id:
                                    v === ''
                                        ? null
                                        : v === TANPA_DOSEN_PA
                                          ? TANPA_DOSEN_PA
                                          : Number(v),
                            })
                        }
                        options={dosenChoices}
                        placeholder="Semua dosen PA"
                        searchPlaceholder="Cari nama dosen..."
                        emptyText="Dosen tidak ditemukan."
                        clearLabel="Semua dosen PA"
                    />
                </div>
            </PopoverContent>
        </Popover>
    );
}
