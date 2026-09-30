import { Head } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table';
import {
    TablePagination,
    type PaginationMeta,
} from '@/components/table-pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useListQuery, type ListFilters } from '@/hooks/use-list-query';
import {
    prodiColumns,
    type KaprodiOption,
    type ProdiRow,
} from '@/pages/akademik/components/prodi-columns';
import { ProdiDeleteDialog } from '@/pages/akademik/components/prodi-delete-dialog';
import { ProdiFormDialog } from '@/pages/akademik/components/prodi-form-dialog';
import akademik from '@/routes/akademik';

type ProdiIndexPageProps = {
    prodis: PaginationMeta & { data: ProdiRow[] };
    filters: ListFilters;
    perPageOptions: number[];
    kaprodiOptions: KaprodiOption[];
};

type DialogState =
    | { type: 'none' }
    | { type: 'create' }
    | { type: 'edit'; prodi: ProdiRow }
    | { type: 'delete'; prodi: ProdiRow };

export default function ProdiIndex({
    prodis,
    filters,
    perPageOptions,
    kaprodiOptions,
}: ProdiIndexPageProps) {
    const [dialog, setDialog] = useState<DialogState>({ type: 'none' });
    const list = useListQuery({
        url: akademik.prodi.index.url(),
        filters,
        defaultSort: { key: 'nama', direction: 'asc' },
    });

    const columns = prodiColumns({
        onEdit: (prodi) => setDialog({ type: 'edit', prodi }),
        onDelete: (prodi) => setDialog({ type: 'delete', prodi }),
    });

    const closeDialog = () => setDialog({ type: 'none' });

    return (
        <>
            <Head title="Akademik - Prodi" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="text-2xl font-bold">Prodi</h1>
                        <p className="text-muted-foreground text-sm">
                            Program studi beserta kaprodinya.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ type: 'create' })}>
                        <Plus />
                        Tambah prodi
                    </Button>
                </div>

                <section className="flex flex-col gap-4">
                    <form
                        role="search"
                        className="relative w-full sm:max-w-xs"
                        onSubmit={(e) => {
                            e.preventDefault();
                            list.submitSearch();
                        }}
                    >
                        <Label htmlFor="prodi-search" className="sr-only">
                            Cari prodi berdasarkan nama atau kaprodi
                        </Label>
                        <Search
                            aria-hidden="true"
                            className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                        />
                        <Input
                            id="prodi-search"
                            type="search"
                            name="search"
                            placeholder="Cari nama prodi atau kaprodi..."
                            value={list.search}
                            onChange={(e) =>
                                list.onSearchChange(e.target.value)
                            }
                            className="pl-9"
                        />
                    </form>

                    <DataTable
                        caption="Daftar prodi"
                        columns={columns}
                        data={prodis.data}
                        getRowKey={(p) => p.id}
                        sort={{
                            key: filters.sort,
                            direction: filters.direction,
                        }}
                        onSort={list.onSort}
                        isLoading={list.loading}
                        emptyState={
                            filters.search ? (
                                <div className="flex flex-col items-center gap-3">
                                    <p>
                                        Tidak ada prodi yang cocok dengan “
                                        {filters.search}”.
                                    </p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={list.resetSearch}
                                    >
                                        Reset pencarian
                                    </Button>
                                </div>
                            ) : (
                                <div className="flex flex-col items-center gap-1">
                                    <p>Belum ada prodi.</p>
                                    <p className="text-muted-foreground text-sm">
                                        Pilih “Tambah prodi” untuk membuat prodi
                                        pertama.
                                    </p>
                                </div>
                            )
                        }
                    />

                    <TablePagination
                        meta={prodis}
                        perPageOptions={perPageOptions}
                        unit="prodi"
                        onPageChange={(page) => list.visit({ page })}
                        onPerPageChange={(per_page) => list.visit({ per_page })}
                    />
                </section>
            </div>

            {dialog.type === 'create' && (
                <ProdiFormDialog
                    key="create"
                    prodi={null}
                    kaprodiOptions={kaprodiOptions}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'edit' && (
                <ProdiFormDialog
                    key={dialog.prodi.id}
                    prodi={dialog.prodi}
                    kaprodiOptions={kaprodiOptions}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'delete' && (
                <ProdiDeleteDialog
                    key={dialog.prodi.id}
                    prodi={dialog.prodi}
                    onClose={closeDialog}
                />
            )}
        </>
    );
}

ProdiIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
        },
        {
            title: 'Prodi',
            href: akademik.prodi.index.url(),
        },
    ],
});
