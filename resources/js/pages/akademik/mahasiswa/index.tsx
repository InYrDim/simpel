import { Head } from '@inertiajs/react';
import { Plus, Search, X } from 'lucide-react';
import { useState } from 'react';
import { DataTable, type SortState } from '@/components/data-table';
import { SearchableSelect } from '@/components/searchable-select';
import {
    TablePagination,
    type PaginationMeta,
} from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useListQuery, type ListFilters } from '@/hooks/use-list-query';
import {
    mahasiswaColumns,
    type DosenOption,
    type MahasiswaRow,
    type ProdiOption,
    type StatusOption,
} from '@/pages/akademik/components/mahasiswa-columns';
import { MahasiswaDeleteDialog } from '@/pages/akademik/components/mahasiswa-delete-dialog';
import {
    MahasiswaFormDialog,
    type UserOption,
} from '@/pages/akademik/components/mahasiswa-form-dialog';
import akademik from '@/routes/akademik';

const SEMUA = 'semua';
const TANPA_DOSEN_PA = 'kosong';
const DEFAULT_SORT: SortState = { key: 'nama', direction: 'asc' };

type Filters = ListFilters & {
    prodi_id: number | null;
    angkatan: number | null;
    dosen_pa_id: number | typeof TANPA_DOSEN_PA | null;
    status: string | null;
};

type PaginatedMahasiswas = PaginationMeta & {
    data: MahasiswaRow[];
};

type MahasiswaIndexPageProps = {
    mahasiswas: PaginatedMahasiswas;
    filters: Filters;
    perPageOptions: number[];
    statusOptions: StatusOption[];
    angkatanOptions: number[];
    userOptions: UserOption[];
    dosenOptions: DosenOption[];
    prodiOptions: ProdiOption[];
};

type DialogState =
    | { type: 'none' }
    | { type: 'create' }
    | { type: 'edit'; mahasiswa: MahasiswaRow }
    | { type: 'delete'; mahasiswa: MahasiswaRow };

export default function MahasiswaIndex({
    mahasiswas,
    filters,
    perPageOptions,
    statusOptions,
    angkatanOptions,
    userOptions,
    dosenOptions,
    prodiOptions,
}: MahasiswaIndexPageProps) {
    const [dialog, setDialog] = useState<DialogState>({ type: 'none' });
    const list = useListQuery<Filters>({
        url: akademik.mahasiswa.index.url(),
        filters,
        defaultSort: DEFAULT_SORT,
        extraParams: (f) => ({
            prodi_id: f.prodi_id,
            angkatan: f.angkatan,
            dosen_pa_id: f.dosen_pa_id,
            status: f.status,
        }),
    });
    const { visit } = list;

    const hasActiveFilters =
        filters.search !== '' ||
        filters.prodi_id !== null ||
        filters.angkatan !== null ||
        filters.dosen_pa_id !== null ||
        filters.status !== null;

    const resetFilters = () => {
        list.clearSearchInput();
        visit({
            search: '',
            prodi_id: null,
            angkatan: null,
            dosen_pa_id: null,
            status: null,
        });
    };

    const columns = mahasiswaColumns({
        statusOptions,
        onEdit: (mahasiswa) => setDialog({ type: 'edit', mahasiswa }),
        onDelete: (mahasiswa) => setDialog({ type: 'delete', mahasiswa }),
    });

    const closeDialog = () => setDialog({ type: 'none' });

    const dosenFilterOptions = [
        { value: TANPA_DOSEN_PA, label: 'Belum ada dosen PA' },
        ...dosenOptions.map((d) => ({
            value: String(d.id),
            label: d.nama,
        })),
    ];

    return (
        <>
            <Head title="Akademik - Mahasiswa" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="text-2xl font-bold">Mahasiswa</h1>
                        <p className="text-muted-foreground text-sm">
                            Profil akademik mahasiswa: prodi, angkatan, dosen
                            PA, dan status.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ type: 'create' })}>
                        <Plus />
                        Tambah mahasiswa
                    </Button>
                </div>

                <section className="flex flex-col gap-4">
                    <div
                        role="search"
                        className="flex flex-wrap items-center gap-2"
                    >
                        <form
                            className="relative w-full sm:max-w-xs sm:flex-1"
                            onSubmit={(e) => {
                                e.preventDefault();
                                list.submitSearch();
                            }}
                        >
                            <Label htmlFor="mhs-search" className="sr-only">
                                Cari mahasiswa berdasarkan nama, NIM, atau email
                            </Label>
                            <Search
                                aria-hidden="true"
                                className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                            />
                            <Input
                                id="mhs-search"
                                type="search"
                                name="search"
                                placeholder="Cari nama, NIM, atau email..."
                                value={list.search}
                                onChange={(e) =>
                                    list.onSearchChange(e.target.value)
                                }
                                className="pl-9"
                            />
                        </form>

                        <Select
                            value={
                                filters.prodi_id === null
                                    ? SEMUA
                                    : String(filters.prodi_id)
                            }
                            onValueChange={(v) =>
                                visit({
                                    prodi_id: v === SEMUA ? null : Number(v),
                                })
                            }
                        >
                            <SelectTrigger
                                aria-label="Filter prodi"
                                className="w-full sm:w-44"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={SEMUA}>
                                    Semua prodi
                                </SelectItem>
                                {prodiOptions.map((p) => (
                                    <SelectItem key={p.id} value={String(p.id)}>
                                        {p.nama}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={
                                filters.angkatan === null
                                    ? SEMUA
                                    : String(filters.angkatan)
                            }
                            onValueChange={(v) =>
                                visit({
                                    angkatan: v === SEMUA ? null : Number(v),
                                })
                            }
                        >
                            <SelectTrigger
                                aria-label="Filter angkatan"
                                className="w-full sm:w-36"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={SEMUA}>
                                    Semua angkatan
                                </SelectItem>
                                {angkatanOptions.map((tahun) => (
                                    <SelectItem
                                        key={tahun}
                                        value={String(tahun)}
                                    >
                                        {tahun}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.status ?? SEMUA}
                            onValueChange={(v) =>
                                visit({ status: v === SEMUA ? null : v })
                            }
                        >
                            <SelectTrigger
                                aria-label="Filter status"
                                className="w-full sm:w-36"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={SEMUA}>
                                    Semua status
                                </SelectItem>
                                {statusOptions.map((s) => (
                                    <SelectItem key={s.value} value={s.value}>
                                        {s.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <div className="w-full sm:w-52">
                            <SearchableSelect
                                id="mhs-filter-dosen"
                                value={
                                    filters.dosen_pa_id === null
                                        ? ''
                                        : String(filters.dosen_pa_id)
                                }
                                onChange={(v) =>
                                    visit({
                                        dosen_pa_id:
                                            v === ''
                                                ? null
                                                : v === TANPA_DOSEN_PA
                                                  ? TANPA_DOSEN_PA
                                                  : Number(v),
                                    })
                                }
                                options={dosenFilterOptions}
                                placeholder="Semua dosen PA"
                                searchPlaceholder="Cari nama dosen..."
                                emptyText="Dosen tidak ditemukan."
                                clearLabel="Semua dosen PA"
                            />
                        </div>

                        {hasActiveFilters && (
                            <Button
                                type="button"
                                variant="ghost"
                                onClick={resetFilters}
                            >
                                <X />
                                Reset filter
                            </Button>
                        )}
                    </div>

                    <DataTable
                        caption="Daftar mahasiswa"
                        columns={columns}
                        data={mahasiswas.data}
                        getRowKey={(m) => m.id}
                        sort={{
                            key: filters.sort,
                            direction: filters.direction,
                        }}
                        onSort={list.onSort}
                        isLoading={list.loading}
                        emptyState={
                            hasActiveFilters ? (
                                <div className="flex flex-col items-center gap-3">
                                    <p>
                                        Tidak ada mahasiswa yang cocok
                                        {filters.search
                                            ? ` dengan “${filters.search}”`
                                            : ' dengan filter ini'}
                                        .
                                    </p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={resetFilters}
                                    >
                                        Reset filter
                                    </Button>
                                </div>
                            ) : (
                                <div className="flex flex-col items-center gap-1">
                                    <p>Belum ada mahasiswa.</p>
                                    <p className="text-muted-foreground text-sm">
                                        Pilih “Tambah mahasiswa” untuk membuat
                                        profil pertama.
                                    </p>
                                </div>
                            )
                        }
                    />

                    <TablePagination
                        meta={mahasiswas}
                        perPageOptions={perPageOptions}
                        unit="mahasiswa"
                        onPageChange={(page) => visit({ page })}
                        onPerPageChange={(per_page) => visit({ per_page })}
                    />
                </section>
            </div>

            {dialog.type === 'create' && (
                <MahasiswaFormDialog
                    key="create"
                    mahasiswa={null}
                    userOptions={userOptions}
                    dosenOptions={dosenOptions}
                    prodiOptions={prodiOptions}
                    statusOptions={statusOptions}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'edit' && (
                <MahasiswaFormDialog
                    key={dialog.mahasiswa.id}
                    mahasiswa={dialog.mahasiswa}
                    userOptions={userOptions}
                    dosenOptions={dosenOptions}
                    prodiOptions={prodiOptions}
                    statusOptions={statusOptions}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'delete' && (
                <MahasiswaDeleteDialog
                    key={dialog.mahasiswa.id}
                    mahasiswa={dialog.mahasiswa}
                    onClose={closeDialog}
                />
            )}
        </>
    );
}

MahasiswaIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
        },
        {
            title: 'Mahasiswa',
            href: akademik.mahasiswa.index.url(),
        },
    ],
});
