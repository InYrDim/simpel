import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';

/**
 * Kontrak tabel generik: kolom mendefinisikan label dan cara merender selnya.
 * Modul yang memakainya menentukan tipe barisnya sendiri.
 *
 * `sortKey` menandai kolom yang bisa diurutkan; nilainya dikirim ke `onSort`.
 */
export type Column<T> = {
    key: string;
    label: string;
    sortKey?: string;
    render?: (row: T) => React.ReactNode;
};

export type SortState = {
    key: string;
    direction: 'asc' | 'desc';
};

type DataTableProps<T> = {
    columns: Column<T>[];
    data: T[];
    getRowKey: (row: T) => string | number;
    caption?: string;
    sort?: SortState;
    onSort?: (sortKey: string) => void;
    emptyState?: React.ReactNode;
    isLoading?: boolean;
};

export function DataTable<T>({
    columns,
    data,
    getRowKey,
    caption,
    sort,
    onSort,
    emptyState,
    isLoading = false,
}: DataTableProps<T>) {
    return (
        <div
            className={cn(
                'rounded-md border transition-opacity',
                isLoading && 'opacity-60',
            )}
            aria-busy={isLoading}
        >
            <Table>
                {caption && (
                    <TableCaption className="sr-only">{caption}</TableCaption>
                )}
                <TableHeader>
                    <TableRow>
                        {columns.map((col) => (
                            <TableHead
                                key={col.key}
                                aria-sort={ariaSort(col, sort)}
                            >
                                {col.sortKey && onSort ? (
                                    <SortButton
                                        label={col.label}
                                        active={sort?.key === col.sortKey}
                                        direction={sort?.direction}
                                        onClick={() => onSort(col.sortKey!)}
                                    />
                                ) : (
                                    col.label
                                )}
                            </TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {data.length === 0 ? (
                        <TableRow>
                            <TableCell
                                colSpan={columns.length}
                                className="h-24 text-center"
                            >
                                {emptyState ?? 'Tidak ada data.'}
                            </TableCell>
                        </TableRow>
                    ) : (
                        data.map((row) => (
                            <TableRow key={getRowKey(row)}>
                                {columns.map((col) => (
                                    <TableCell key={col.key}>
                                        {col.render
                                            ? col.render(row)
                                            : ((row as Record<string, unknown>)[
                                                  col.key
                                              ] as React.ReactNode)}
                                    </TableCell>
                                ))}
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}

function ariaSort<T>(
    col: Column<T>,
    sort?: SortState,
): 'ascending' | 'descending' | undefined {
    if (!col.sortKey || sort?.key !== col.sortKey) {
        return undefined;
    }

    return sort.direction === 'asc' ? 'ascending' : 'descending';
}

function SortButton({
    label,
    active,
    direction,
    onClick,
}: {
    label: string;
    active: boolean;
    direction?: 'asc' | 'desc';
    onClick: () => void;
}) {
    const Icon = !active
        ? ArrowUpDown
        : direction === 'asc'
          ? ArrowUp
          : ArrowDown;

    return (
        <button
            type="button"
            onClick={onClick}
            className="hover:text-foreground focus-visible:ring-ring/50 -ml-1 inline-flex items-center gap-1 rounded-sm px-1 font-medium focus-visible:ring-[3px] focus-visible:outline-none"
        >
            {label}
            <Icon
                aria-hidden="true"
                className={cn('size-3.5', !active && 'opacity-50')}
            />
        </button>
    );
}
