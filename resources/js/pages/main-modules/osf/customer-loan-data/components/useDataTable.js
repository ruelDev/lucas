import { useMemo, useState } from 'react';

/**
 * useDataTable
 * Encapsulates all table state: global search, per-column filters,
 * multi-column sorting, pagination, and page size.
 *
 * @param {Array}  rows    – raw data rows
 * @param {Array}  columns – column definitions (uses key, sortType, filterable)
 */
export function useDataTable(rows = [], columns = []) {
    // ── Search ────────────────────────────────────────────────────────────────
    const [searchQuery, setSearchQuery] = useState('');

    // ── Per-column filters ────────────────────────────────────────────────────
    // { [colKey]: Set of selected values } — empty Set means "show all"
    const [columnFilters, setColumnFilters] = useState({});

    // ── Sorting ───────────────────────────────────────────────────────────────
    // [{ key, direction: 'asc' | 'desc' }]
    const [sortState, setSortState] = useState([]);

    // ── Pagination ────────────────────────────────────────────────────────────
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize, setPageSize] = useState(25);

    // ── Unique values per filterable column ───────────────────────────────────
    const columnUniqueValues = useMemo(() => {
        const result = {};
        columns.forEach((col) => {
            if (!col.filterable) return;
            const vals = new Set(
                rows
                    .map((r) => r[col.key])
                    .filter((v) => v !== null && v !== undefined && v !== '')
                    .map(String),
            );
            result[col.key] = [...vals].sort();
        });
        return result;
    }, [rows, columns]);

    // ── Filtered + sorted rows ────────────────────────────────────────────────
    const processedRows = useMemo(() => {
        let result = [...rows];

        // 1. Global search
        if (searchQuery.trim()) {
            const q = searchQuery.toLowerCase();
            result = result.filter((row) =>
                columns.some((col) => {
                    const val = row[col.key];
                    return val != null && String(val).toLowerCase().includes(q);
                }),
            );
        }

        // 2. Per-column filters
        Object.entries(columnFilters).forEach(([key, selected]) => {
            if (!selected || selected.size === 0) return;
            result = result.filter((row) => selected.has(String(row[key] ?? '')));
        });

        // 3. Sorting (multi-column: primary → secondary → ...)
        if (sortState.length > 0) {
            result.sort((a, b) => {
                for (const { key, direction } of sortState) {
                    const col = columns.find((c) => c.key === key);
                    const sortType = col?.sortType ?? 'string';
                    const aVal = a[key];
                    const bVal = b[key];

                    // Nulls always last
                    if (aVal == null && bVal == null) continue;
                    if (aVal == null) return 1;
                    if (bVal == null) return -1;

                    let cmp = 0;
                    if (sortType === 'number') {
                        cmp = Number(aVal) - Number(bVal);
                    } else if (sortType === 'date') {
                        cmp = new Date(aVal) - new Date(bVal);
                    } else {
                        cmp = String(aVal).localeCompare(String(bVal));
                    }

                    if (cmp !== 0) return direction === 'asc' ? cmp : -cmp;
                }
                return 0;
            });
        }

        return result;
    }, [rows, searchQuery, columnFilters, sortState, columns]);

    // ── Pagination math ───────────────────────────────────────────────────────
    const totalRows = processedRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));
    const safePage = Math.min(currentPage, totalPages);
    const startIndex = (safePage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalRows);
    const currentRows = processedRows.slice(startIndex, endIndex);

    // ── Handlers ──────────────────────────────────────────────────────────────
    const handleSearch = (val) => {
        setSearchQuery(val);
        setCurrentPage(1);
    };

    const clearSearch = () => {
        setSearchQuery('');
        setCurrentPage(1);
    };

    const handleSort = (key) => {
        setSortState((prev) => {
            const existing = prev.find((s) => s.key === key);
            if (!existing) {
                // New sort column — add as primary, keep others as secondary
                return [{ key, direction: 'asc' }, ...prev.filter((s) => s.key !== key)];
            }
            if (existing.direction === 'asc') {
                return prev.map((s) => (s.key === key ? { ...s, direction: 'desc' } : s));
            }
            // Remove on third click
            return prev.filter((s) => s.key !== key);
        });
        setCurrentPage(1);
    };

    const clearSort = () => {
        setSortState([]);
        setCurrentPage(1);
    };

    const toggleColumnFilter = (colKey, value) => {
        setColumnFilters((prev) => {
            const current = new Set(prev[colKey] ?? []);
            if (current.has(value)) {
                current.delete(value);
            } else {
                current.add(value);
            }
            return { ...prev, [colKey]: current };
        });
        setCurrentPage(1);
    };

    const clearColumnFilter = (colKey) => {
        setColumnFilters((prev) => {
            const next = { ...prev };
            delete next[colKey];
            return next;
        });
        setCurrentPage(1);
    };

    const clearAllFilters = () => {
        setColumnFilters({});
        setSearchQuery('');
        setSortState([]);
        setCurrentPage(1);
    };

    const handlePageSizeChange = (val) => {
        setPageSize(Number(val));
        setCurrentPage(1);
    };

    const goToPage = (page) => {
        setCurrentPage(Math.max(1, Math.min(page, totalPages)));
    };

    // Active filter count for badges
    const activeFilterCount =
        Object.values(columnFilters).filter((s) => s && s.size > 0).length +
        (searchQuery.trim() ? 1 : 0);

    return {
        // processed data
        currentRows,
        processedRows,
        totalRows,

        // pagination
        currentPage: safePage,
        pageSize,
        totalPages,
        startIndex,
        endIndex,
        goToPage,
        handlePageSizeChange,

        // search
        searchQuery,
        handleSearch,
        clearSearch,

        // sort
        sortState,
        handleSort,
        clearSort,

        // column filters
        columnFilters,
        columnUniqueValues,
        toggleColumnFilter,
        clearColumnFilter,
        clearAllFilters,
        activeFilterCount,
    };
}