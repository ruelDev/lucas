import { useMemo, useState } from 'react';

export function useReceiptData(rows, fullSummary) {
    const [filterStatus, setFilterStatus] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [currentPage, setCurrentPage] = useState(1);
    const [pageSize, setPageSize] = useState(50);

    // Sort, Filter, and Search
    const sortedAndFilteredRows = useMemo(() => {
        let filtered = [...rows];

        if (filterStatus === 'valid') {
            filtered = filtered.filter((r) => r.valid);
        } else if (filterStatus === 'invalid') {
            filtered = filtered.filter((r) => !r.valid);
        }

        // Apply search filter
        if (searchQuery.trim()) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter((r) => {
                // Search in row number
                if (r.row.toString().includes(query)) return true;

                // Search in all row data fields
                const searchableFields = Object.values(r).filter((val) => typeof val === 'string' || typeof val === 'number');

                return searchableFields.some((val) => val?.toString().toLowerCase().includes(query));
            });
        }

        filtered.sort((a, b) => {
            if (a.valid === b.valid) return a.row - b.row;
            return a.valid ? 1 : -1;
        });

        return filtered;
    }, [rows, filterStatus, searchQuery]);

    // For Pagination
    const totalPages = Math.ceil(sortedAndFilteredRows.length / pageSize);
    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = startIndex + pageSize;
    const currentRows = sortedAndFilteredRows.slice(startIndex, endIndex);

    const goToPage = (page) => {
        setCurrentPage(Math.max(1, Math.min(page, totalPages)));
    };

    // Summary Details
    const summary = useMemo(() => {
        if (!fullSummary) {
            const total = rows.length;
            const valid = rows.filter((r) => r.valid).length;
            const selected = rows.filter((r) => r.valid && r.checked).length;
            const invalid = total - valid;
            return { total, valid, invalid, selected };
        }

        const selected = rows.filter((r) => r.valid && r.checked).length;

        return {
            total: fullSummary.total,
            valid: fullSummary.valid,
            invalid: fullSummary.invalid,
            selected: selected,
            previewCount: fullSummary.previewCount,
            allInvalidShown: fullSummary.allInvalidShown,
        };
    }, [rows, fullSummary]);

    // Reset Page
    const handleFilterChange = (val) => {
        setFilterStatus(val);
        setCurrentPage(1);
    };

    const handleSearchChange = (val) => {
        setSearchQuery(val);
        setCurrentPage(1);
    };

    const clearSearch = () => {
        setSearchQuery('');
        setCurrentPage(1);
    };

    const handlePageSizeChange = (val) => {
        setPageSize(Number(val));
        setCurrentPage(1);
    };

    return {
        // data
        currentRows,
        sortedAndFilteredRows,
        summary,
        totalPages,
        startIndex,
        endIndex,

        // state
        filterStatus,
        searchQuery,
        currentPage,
        pageSize,

        // handlers
        handleFilterChange,
        handleSearchChange,
        clearSearch,
        handlePageSizeChange,
        goToPage,

        // setters
        setCurrentPage,
        setFilterStatus,
        setSearchQuery,
    }
}
