import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

/**
 * Kontrak tabel generik: kolom mendefinisikan label dan cara merender selnya.
 * Modul yang memakainya menentukan tipe barisnya sendiri.
 */
export type Column<T> = {
    key: string;
    label: string;
    render?: (row: T) => React.ReactNode;
};

type DataTableProps<T> = {
    columns: Column<T>[];
    data: T[];
    getRowKey: (row: T) => string | number;
};

export function DataTable<T>({ columns, data, getRowKey }: DataTableProps<T>) {
    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        {columns.map((col) => (
                            <TableHead key={col.key}>{col.label}</TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {data.length === 0 ? (
                        <TableRow>
                            <TableCell className="h-24 text-center">
                                Tidak ada data.
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
