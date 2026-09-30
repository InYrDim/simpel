import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type PaginationMeta = {
    total: number;
    from: number | null;
    to: number | null;
    per_page: number;
    current_page: number;
    last_page: number;
};

type TablePaginationProps = {
    meta: PaginationMeta;
    perPageOptions: number[];
    /** Kata benda untuk ringkasan, mis. "mahasiswa". */
    unit: string;
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
};

export function TablePagination({
    meta,
    perPageOptions,
    unit,
    onPageChange,
    onPerPageChange,
}: TablePaginationProps) {
    const summary =
        meta.total === 0
            ? `Tidak ada ${unit}`
            : `Menampilkan ${meta.from}–${meta.to} dari ${meta.total} ${unit}`;

    return (
        <div className="flex flex-wrap items-center justify-between gap-x-6 gap-y-3 text-sm">
            <p role="status" className="text-muted-foreground">
                {summary}
            </p>

            <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                <div className="flex items-center gap-2">
                    <span id="per-page-label" className="text-muted-foreground">
                        Baris per halaman
                    </span>
                    <Select
                        value={String(meta.per_page)}
                        onValueChange={(value) =>
                            onPerPageChange(Number(value))
                        }
                    >
                        <SelectTrigger
                            aria-labelledby="per-page-label"
                            className="w-20"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {perPageOptions.map((size) => (
                                <SelectItem key={size} value={String(size)}>
                                    {size}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <nav
                    aria-label="Navigasi halaman"
                    className="flex items-center gap-2"
                >
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        aria-label="Halaman sebelumnya"
                        disabled={meta.current_page <= 1}
                        onClick={() => onPageChange(meta.current_page - 1)}
                    >
                        <ChevronLeft />
                    </Button>
                    <span aria-current="page" className="tabular-nums">
                        Halaman {meta.current_page} dari {meta.last_page}
                    </span>
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        aria-label="Halaman berikutnya"
                        disabled={meta.current_page >= meta.last_page}
                        onClick={() => onPageChange(meta.current_page + 1)}
                    >
                        <ChevronRight />
                    </Button>
                </nav>
            </div>
        </div>
    );
}
