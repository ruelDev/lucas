/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Filter, Search, X } from 'lucide-react';

/**
 * DataTableToolbar
 * Renders the search bar, per-column filter dropdowns, page-size selector,
 * and active-filter chips. Purely presentational — all state lives in useDataTable.
 */
export default function DataTableToolbar({
    columns,
    searchQuery,
    onSearch,
    onClearSearch,
    columnFilters,
    columnUniqueValues,
    onToggleColumnFilter,
    onClearColumnFilter,
    onClearAll,
    activeFilterCount,
    pageSize,
    onPageSizeChange,
    totalRows,
    processedRows,
}) {
    const filterableColumns = columns.filter((c) => c.filterable);
    const hasActiveFilters = activeFilterCount > 0;

    return (
        <div className="flex flex-col gap-3 px-4 pt-4 pb-3">
            {/* Row 1: Search + Filter popovers + Page size */}
            <div className="flex flex-wrap items-center gap-2">
                {/* Global search */}
                <div className="relative min-w-[200px] flex-1">
                    <Search className="absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                    <Input
                        type="text"
                        placeholder="Search all columns..."
                        value={searchQuery}
                        onChange={(e) => onSearch(e.target.value)}
                        className="h-8 border-gray-200 pr-8 pl-8 text-xs dark:border-gray-700"
                    />
                    {searchQuery && (
                        <button
                            onClick={onClearSearch}
                            className="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <X className="h-3.5 w-3.5" />
                        </button>
                    )}
                </div>

                {/* Per-column filter popovers */}
                {filterableColumns.map((col) => {
                    const selected = columnFilters[col.key] ?? new Set();
                    const options = columnUniqueValues[col.key] ?? [];
                    const isActive = selected.size > 0;

                    return (
                        <Popover key={col.key}>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className={`h-8 gap-1.5 px-2.5 text-xs ${
                                        isActive
                                            ? 'border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                            : 'border-gray-200 dark:border-gray-700'
                                    }`}
                                >
                                    <Filter className="h-3 w-3" />
                                    {col.label}
                                    {isActive && (
                                        <Badge className="ml-0.5 h-4 min-w-4 rounded-full bg-blue-600 px-1 py-0 text-[10px] text-white">
                                            {selected.size}
                                        </Badge>
                                    )}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-52 p-2" align="start">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {col.label}
                                    </span>
                                    {isActive && (
                                        <button
                                            onClick={() => onClearColumnFilter(col.key)}
                                            className="text-[10px] text-blue-600 hover:underline dark:text-blue-400"
                                        >
                                            Clear
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-48 space-y-0.5 overflow-y-auto">
                                    {options.length === 0 ? (
                                        <p className="py-2 text-center text-xs text-gray-400">No values</p>
                                    ) : (
                                        options.map((val) => (
                                            <label
                                                key={val}
                                                className="flex cursor-pointer items-center gap-2 rounded px-1.5 py-1 text-xs hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={selected.has(val)}
                                                    onChange={() => onToggleColumnFilter(col.key, val)}
                                                    className="h-3.5 w-3.5 rounded accent-blue-700"
                                                />
                                                <span className="truncate text-gray-700 dark:text-gray-300">{val}</span>
                                            </label>
                                        ))
                                    )}
                                </div>
                            </PopoverContent>
                        </Popover>
                    );
                })}

                {/* Clear all */}
                {hasActiveFilters && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={onClearAll}
                        className="h-8 gap-1 px-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <X className="h-3 w-3" />
                        Clear all
                    </Button>
                )}

                {/* Spacer */}
                <div className="flex-1" />

                {/* Result count */}
                <span className="text-xs text-gray-500 dark:text-gray-400">
                    {processedRows.length.toLocaleString()} of {totalRows.toLocaleString()} rows
                </span>

                {/* Page size */}
                <div className="flex items-center gap-1.5">
                    <span className="text-xs text-gray-500 dark:text-gray-400">Per page:</span>
                    <Select value={String(pageSize)} onValueChange={onPageSizeChange}>
                        <SelectTrigger className="h-8 w-16 text-xs">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {[10, 25, 50, 100].map((n) => (
                                <SelectItem key={n} value={String(n)} className="text-xs">
                                    {n}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>

            {/* Row 2: Active filter chips */}
            {hasActiveFilters && (
                <div className="flex flex-wrap gap-1.5">
                    {searchQuery.trim() && (
                        <span className="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] text-blue-700 dark:border-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            Search: <strong>{searchQuery}</strong>
                            <button onClick={onClearSearch} className="ml-0.5 hover:text-blue-900 dark:hover:text-blue-100">
                                <X className="h-2.5 w-2.5" />
                            </button>
                        </span>
                    )}
                    {Object.entries(columnFilters).map(([key, selected]) => {
                        if (!selected || selected.size === 0) return null;
                        const col = columns.find((c) => c.key === key);
                        return [...selected].map((val) => (
                            <span
                                key={`${key}-${val}`}
                                className="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            >
                                {col?.label}: <strong>{val}</strong>
                                <button
                                    onClick={() => onToggleColumnFilter(key, val)}
                                    className="ml-0.5 hover:text-gray-900 dark:hover:text-gray-100"
                                >
                                    <X className="h-2.5 w-2.5" />
                                </button>
                            </span>
                        ));
                    })}
                </div>
            )}
        </div>
    );
}