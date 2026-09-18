import type { Column, UserRow } from '@/components/users/user-columns';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type DataTableProps<T extends UserRow> = {
    columns: Column[];
    data: T[];
};

export function DataTable<T extends UserRow>({ columns, data }: DataTableProps<T>) {
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
                            <TableRow key={row.id}>
                                {columns.map((col) => (
                                    <TableCell key={col.key}>{col.render ? col.render(row) : (row as never)[col.key] as React.ReactNode}</TableCell>
                                ))}
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
