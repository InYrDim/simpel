import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { SortState } from '@/components/data-table';

const SEARCH_DEBOUNCE_MS = 300;

export type ListFilters = {
    search: string;
    sort: string;
    direction: 'asc' | 'desc';
    per_page: number;
};

type ExtraParams = Record<string, string | number | null | undefined>;

type ListQueryOptions<F extends ListFilters> = {
    url: string;
    filters: F;
    defaultSort: SortState;
    defaultPerPage?: number;
    /** Filter tambahan halaman ini; nilai null/kosong dibuang dari URL. */
    extraParams?: (filters: F) => ExtraParams;
};

type Overrides<F extends ListFilters> = Partial<F> & { page?: number };

/**
 * Status daftar admin yang hidup di query string: pencarian (debounce),
 * urutan, ukuran halaman, halaman, dan filter tambahan. Nilai kosong/default
 * dibuang dari URL.
 */
export function useListQuery<F extends ListFilters>({
    url,
    filters,
    defaultSort,
    defaultPerPage = 10,
    extraParams,
}: ListQueryOptions<F>) {
    const [search, setSearch] = useState(filters.search);
    const [loading, setLoading] = useState(false);
    const timer = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    useEffect(() => () => clearTimeout(timer.current), []);

    const visit = (overrides: Overrides<F>) => {
        const merged: F = { ...filters, ...overrides };
        const query: Record<string, string | number> = {};

        if (merged.search) {
            query.search = merged.search;
        }

        for (const [key, value] of Object.entries(
            extraParams?.(merged) ?? {},
        )) {
            if (value !== null && value !== undefined && value !== '') {
                query[key] = value;
            }
        }

        if (
            merged.sort !== defaultSort.key ||
            merged.direction !== defaultSort.direction
        ) {
            query.sort = merged.sort;
            query.direction = merged.direction;
        }
        if (merged.per_page !== defaultPerPage) {
            query.per_page = merged.per_page;
        }
        if (overrides.page && overrides.page > 1) {
            query.page = overrides.page;
        }

        router.get(url, query, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const onSearchChange = (value: string) => {
        setSearch(value);
        clearTimeout(timer.current);
        timer.current = setTimeout(
            () => visit({ search: value.trim() } as Overrides<F>),
            SEARCH_DEBOUNCE_MS,
        );
    };

    const submitSearch = () => {
        clearTimeout(timer.current);
        visit({ search: search.trim() } as Overrides<F>);
    };

    /** Kosongkan kolom cari dan batalkan pencarian yang tertunda. */
    const clearSearchInput = () => {
        clearTimeout(timer.current);
        setSearch('');
    };

    const resetSearch = () => {
        clearSearchInput();
        visit({ search: '' } as Overrides<F>);
    };

    const onSort = (key: string) => {
        const direction =
            filters.sort === key && filters.direction === 'asc'
                ? 'desc'
                : 'asc';

        visit({ sort: key, direction } as Overrides<F>);
    };

    return {
        search,
        loading,
        visit,
        onSearchChange,
        submitSearch,
        clearSearchInput,
        resetSearch,
        onSort,
    };
}
