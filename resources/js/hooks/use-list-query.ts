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

type ListQueryOptions = {
    url: string;
    filters: ListFilters;
    defaultSort: SortState;
    defaultPerPage?: number;
};

type Overrides = Partial<ListFilters> & { page?: number };

/**
 * Status daftar admin yang hidup di query string: pencarian (debounce),
 * urutan, ukuran halaman, dan halaman. Nilai kosong/default dibuang dari URL.
 */
export function useListQuery({
    url,
    filters,
    defaultSort,
    defaultPerPage = 10,
}: ListQueryOptions) {
    const [search, setSearch] = useState(filters.search);
    const [loading, setLoading] = useState(false);
    const timer = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    useEffect(() => () => clearTimeout(timer.current), []);

    const visit = (overrides: Overrides) => {
        const merged = { ...filters, ...overrides };
        const query: Record<string, string | number> = {};

        if (merged.search) {
            query.search = merged.search;
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
            () => visit({ search: value.trim() }),
            SEARCH_DEBOUNCE_MS,
        );
    };

    const submitSearch = () => {
        clearTimeout(timer.current);
        visit({ search: search.trim() });
    };

    const resetSearch = () => {
        clearTimeout(timer.current);
        setSearch('');
        visit({ search: '' });
    };

    const onSort = (key: string) => {
        const direction =
            filters.sort === key && filters.direction === 'asc'
                ? 'desc'
                : 'asc';

        visit({ sort: key, direction });
    };

    return {
        search,
        loading,
        visit,
        onSearchChange,
        submitSearch,
        resetSearch,
        onSort,
    };
}
