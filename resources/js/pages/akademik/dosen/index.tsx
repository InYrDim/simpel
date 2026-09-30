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
    dosenColumns,
    type DosenRow,
} from '@/pages/akademik/components/dosen-columns';
import { DosenDeleteDialog } from '@/pages/akademik/components/dosen-delete-dialog';
import { DosenFormDialog } from '@/pages/akademik/components/dosen-form-dialog';
import akademik from '@/routes/akademik';

type DosenIndexPageProps = {
    dosens: PaginationMeta & { data: DosenRow[] };
    filters: ListFilters;
    perPageOptions: number[];
};

type DialogState =
    | { type: 'none' }
    | { type: 'create' }
    | { type: 'edit'; dosen: DosenRow }
    | { type: 'delete'; dosen: DosenRow };

export default function DosenIndex({
    dosens,
    filters,
    perPageOptions,
}: DosenIndexPageProps) {
    const [dialog, setDialog] = useState<DialogState>({ type: 'none' });
    const list = useListQuery({
        url: akademik.dosen.index.url(),
        filters,
        defaultSort: { key: 'nama', direction: 'asc' },
    });

    const columns = dosenColumns({
        onEdit: (dosen) => setDialog({ type: 'edit', dosen }),
        onDelete: (dosen) => setDialog({ type: 'delete', dosen }),
    });

    const closeDialog = () => setDialog({ type: 'none' });

    return (
        <>
            <Head title="Akademik - Dosen" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h1 className="text-2xl font-bold">Dosen</h1>
                        <p className="text-muted-foreground text-sm">
                            Referensi dosen untuk PA, validator, pembimbing, dan
                            penguji.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ type: 'create' })}>
                        <Plus />
                        Tambah dosen
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
                        <Label htmlFor="dosen-search" className="sr-only">
                            Cari dosen berdasarkan nama, NIP, atau bidang
                        </Label>
                        <Search
                            aria-hidden="true"
                            className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2"
                        />
                        <Input
                            id="dosen-search"
                            type="search"
                            name="search"
                            placeholder="Cari nama, NIP, atau bidang..."
                            value={list.search}
                            onChange={(e) =>
                                list.onSearchChange(e.target.value)
                            }
                            className="pl-9"
                        />
                    </form>

                    <DataTable
                        caption="Daftar dosen"
                        columns={columns}
                        data={dosens.data}
                        getRowKey={(d) => d.id}
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
                                        Tidak ada dosen yang cocok dengan “
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
                                    <p>Belum ada dosen.</p>
                                    <p className="text-muted-foreground text-sm">
                                        Pilih “Tambah dosen” untuk membuat dosen
                                        pertama.
                                    </p>
                                </div>
                            )
                        }
                    />

                    <TablePagination
                        meta={dosens}
                        perPageOptions={perPageOptions}
                        unit="dosen"
                        onPageChange={(page) => list.visit({ page })}
                        onPerPageChange={(per_page) => list.visit({ per_page })}
                    />
                </section>
            </div>

            {dialog.type === 'create' && (
                <DosenFormDialog
                    key="create"
                    dosen={null}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'edit' && (
                <DosenFormDialog
                    key={dialog.dosen.id}
                    dosen={dialog.dosen}
                    onClose={closeDialog}
                />
            )}
            {dialog.type === 'delete' && (
                <DosenDeleteDialog
                    key={dialog.dosen.id}
                    dosen={dialog.dosen}
                    onClose={closeDialog}
                />
            )}
        </>
    );
}

DosenIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Akademik',
        },
        {
            title: 'Dosen',
            href: akademik.dosen.index.url(),
        },
    ],
});
