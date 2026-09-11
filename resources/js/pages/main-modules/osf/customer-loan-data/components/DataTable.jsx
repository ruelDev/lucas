/* eslint-disable react/prop-types */
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import DataTablePagination from './DataTablePagination';
import DataTableToolbar from './DataTableToolbar';
import TableSkeleton from './TableSkeleton';
import { useDataTable } from './useDataTable';


export default function DataTable({
    columns,
    rows,
    loading,
    skeletonRows = 3,
    emptyText = 'No records found.',
    tableStyle,
}) {
    const table = useDataTable(rows, columns);

    const alignClass = (align) => {
        if (align === 'right') return 'text-right';
        if (align === 'center') return 'text-center';
        return 'text-left';
    };

    const SortIcon = ({ colKey }) => {
        const entry = table.sortState.find((s) => s.key === colKey);
        if (!entry) return <ArrowUpDown className="ml-1 inline h-3 w-3 text-gray-400" />;
        return entry.direction === 'asc'
            ? <ArrowUp className="ml-1 inline h-3 w-3 text-blue-600 dark:text-blue-400" />
            : <ArrowDown className="ml-1 inline h-3 w-3 text-blue-600 dark:text-blue-400" />;
    };

    return (
        <div className="flex flex-col">
            {/* Toolbar: search, filters, page size */}
            <DataTableToolbar
                columns={columns}
                searchQuery={table.searchQuery}
                onSearch={table.handleSearch}
                onClearSearch={table.clearSearch}
                columnFilters={table.columnFilters}
                columnUniqueValues={table.columnUniqueValues}
                onToggleColumnFilter={table.toggleColumnFilter}
                onClearColumnFilter={table.clearColumnFilter}
                onClearAll={table.clearAllFilters}
                activeFilterCount={table.activeFilterCount}
                pageSize={table.pageSize}
                onPageSizeChange={table.handlePageSizeChange}
                totalRows={rows.length}
                processedRows={table.processedRows}
            />

            {/* Table */}
            <div className="w-full overflow-x-auto border-t border-gray-100 dark:border-gray-700">
                <Table className="text-xs" style={tableStyle}>
                    <TableHeader>
                        <TableRow className="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/60">
                            {columns.map((col) => (
                                <TableHead
                                    key={col.key}
                                    className={`whitespace-nowrap px-4 py-3 font-semibold text-gray-700 dark:text-gray-300 ${alignClass(col.align)} ${
                                        col.sortable
                                            ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700/50'
                                            : ''
                                    }`}
                                    style={col.minWidth ? { minWidth: col.minWidth } : undefined}
                                    onClick={col.sortable ? () => table.handleSort(col.key) : undefined}
                                >
                                    {col.label}
                                    {col.sortable && <SortIcon colKey={col.key} />}
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>

                    {loading ? (
                        <TableSkeleton rows={skeletonRows} colCount={columns.length} />
                    ) : table.currentRows.length === 0 ? (
                        <TableBody>
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="py-10 text-center text-sm text-gray-400 dark:text-gray-500"
                                >
                                    {table.processedRows.length === 0 && rows.length > 0
                                        ? 'No rows match your search or filters.'
                                        : emptyText}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    ) : (
                        <TableBody>
                            {table.currentRows.map((row, i) => (
                                <TableRow
                                    key={i}
                                    className="border-b border-gray-50 transition-colors hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
                                >
                                    {columns.map((col) => {
                                        const value = row[col.key] ?? null;
                                        const rendered = col.render ? col.render(value, row) : (value ?? '—');
                                        return (
                                            <TableCell
                                                key={col.key}
                                                className={`px-4 py-3 ${alignClass(col.align)} ${col.className ?? ''}`}
                                                style={col.minWidth ? { minWidth: col.minWidth } : undefined}
                                            >
                                                {rendered}
                                            </TableCell>
                                        );
                                    })}
                                </TableRow>
                            ))}
                        </TableBody>
                    )}
                </Table>
            </div>

            {/* Pagination footer */}
            <DataTablePagination
                currentPage={table.currentPage}
                totalPages={table.totalPages}
                startIndex={table.startIndex}
                endIndex={table.endIndex}
                totalRows={table.processedRows.length}
                goToPage={table.goToPage}
            />
        </div>
    );
}