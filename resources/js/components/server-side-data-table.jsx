/* eslint-disable react/prop-types */
import { DataTableControls } from '@/components/data-table-controls';
import { DataTablePagination } from '@/components/data-table-pagination';
import { Card, CardContent } from '@/components/ui/card';
import { useUrlParams } from '@/hooks/use-url-params';
import { flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import _ from 'lodash';
import { Loader2 } from 'lucide-react';
import { useCallback, useState } from 'react';

const ServerSideDataTable = ({
    data,
    pagination,
    filters,
    columns,
    showSearchBar = false,
    showDateFilters = false,
    dateFromLabel = 'Date From',
    dateToLabel = 'Date To',
    onExportAction,
    exportProps = {},
    onCreateAction,
    onDepositAction,
    rp_searchFilter = false,
    userReportsControls = false,
}) => {
    const [searchTerm, setSearchTerm] = useState(filters?.search || '');
    const [columnFilters] = useState(filters?.filters || {});

    const [dateFrom, setDateFrom] = useState(filters?.date_from || '');
    const [dateTo, setDateTo] = useState(filters?.date_to || '');

    const { makeRequest, loading } = useUrlParams();

    // Debounced search function using lodash
    const debouncedSearch = useCallback(
        _.debounce((term) => {
            makeRequest({ search: term, page: 1 });
        }, 1000),
        [makeRequest],
    );

    const debouncedDateFilter = useCallback(
        _.debounce((dateFromVal, dateToVal) => {
            makeRequest({
                date_from: dateFromVal,
                date_to: dateToVal,
                page: 1,
            });
        }, 1000),
        [makeRequest],
    );

    const handleSearch = (value) => {
        setSearchTerm(value);
        debouncedSearch(value);
    };

    const handleDateFromChange = (value) => {
        setDateFrom(value);
        debouncedDateFilter(value, dateTo);
    };

    const handleDateToChange = (value) => {
        setDateTo(value);
        debouncedDateFilter(dateFrom, value);
    };

    const handleClearDateFilters = () => {
        setDateFrom('');
        setDateTo('');
        makeRequest({ date_from: '', date_to: '', page: 1 });
    };

    const handlePageChange = useCallback(
        (page) => {
            makeRequest({ page });
        },
        [makeRequest],
    );

    const handlePerPageChange = useCallback(
        (perPage) => {
            makeRequest({ per_page: parseInt(perPage), page: 1 });
        },
        [pagination.per_page, makeRequest],
    );

    const handleExport = useCallback(() => {
        // Trigger export with current filters
        const params = new URLSearchParams({
            search: searchTerm,
            ...columnFilters,
            export: 'true',
        });

        window.open(`/export?${params.toString()}`, '_blank');
    }, [searchTerm, columnFilters]);

    const table = useReactTable({
        data,
        columns,
        manualPagination: true,
        manualSorting: true,
        manualFiltering: true,
        pageCount: pagination.last_page,
        getCoreRowModel: getCoreRowModel(),
        state: {
            pagination: {
                pageIndex: pagination.current_page - 1,
                pageSize: pagination.per_page,
            },
        },
    });

    return (
        <div className="space-y-4 px-6">
            <Card>
                <CardContent>
                    {/* Controls */}
                    <DataTableControls
                        searchTerm={searchTerm}
                        onSearchChange={handleSearch}
                        onExport={handleExport}
                        onExportAction={onExportAction}
                        exportProps={exportProps}
                        onCreateAction={onCreateAction}
                        showSearchBar={showSearchBar}
                        showDateFilters={showDateFilters}
                        dateFrom={dateFrom}
                        dateTo={dateTo}
                        onDateFromChange={handleDateFromChange}
                        onDateToChange={handleDateToChange}
                        onClearDateFilters={handleClearDateFilters}
                        dateFromLabel={dateFromLabel}
                        dateToLabel={dateToLabel}
                        onDepositAction={onDepositAction}
                        rp_searchFilter={rp_searchFilter}
                        userReportsControls={userReportsControls}
                    />

                    {/* Table */}
                    <div className="relative">
                        {loading && (
                            <div className="bg-background/50 absolute inset-0 z-10 flex items-center justify-center">
                                <Loader2 className="size-8 animate-spin" />
                            </div>
                        )}

                        <div className="overflow-hidden rounded-md border">
                            <table className="w-full">
                                <thead className="border-b bg-neutral-50 dark:bg-neutral-800">
                                    {table.getHeaderGroups().map((headerGroup) => (
                                        <tr key={headerGroup.id}>
                                            {headerGroup.headers.map((header) => (
                                                <th key={header.id} className="px-4 py-3 text-left font-medium text-neutral-900 dark:text-white">
                                                    {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                                </th>
                                            ))}
                                        </tr>
                                    ))}
                                </thead>
                                <tbody className="divide-y divide-neutral-200">
                                    {table.getRowModel().rows?.length ? (
                                        table.getRowModel().rows.map((row) => (
                                            <tr key={row.id} className="transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-700">
                                                {row.getVisibleCells().map((cell) => (
                                                    <td key={cell.id} className="px-4 py-3 text-sm">
                                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                                    </td>
                                                ))}
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan={columns.length} className="px-4 py-12 text-center text-sm text-neutral-500">
                                                No data found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Pagination */}
                    <DataTablePagination
                        pagination={pagination}
                        onPageChange={handlePageChange}
                        onPerPageChange={handlePerPageChange}
                        loading={loading}
                    />
                </CardContent>
            </Card>
        </div>
    );
};

export default ServerSideDataTable;
