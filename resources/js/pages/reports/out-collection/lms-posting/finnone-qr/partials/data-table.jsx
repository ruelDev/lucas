/* eslint-disable react/prop-types */
import { DataTablePagination } from '@/components/data-table-pagination';
import { Card, CardContent } from '@/components/ui/card';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useUrlParams } from '@/hooks/use-url-params';
import { flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import _ from 'lodash';
import { Loader2 } from 'lucide-react';
import * as React from 'react';
import DataTableControls from './data-table-controls';

export default function DataTable({ data, pagination, filters, columns }) {
    const [searchTerm, setSearchTerm] = React.useState(filters?.search || '');
    const [dateFrom, setDateFrom] = React.useState(filters?.date_from || '');
    const [dateTo, setDateTo] = React.useState(filters?.date_to || '');

    const { makeRequest, loading } = useUrlParams();

    const hasFiltered = filters.search !== '' || filters.date_from !== '';

    const debouncedSearch = React.useCallback(
        _.debounce((search, range) => {
            makeRequest({ search, date_from: range?.from, date_to: range?.to, page: 1 });
        }, 1000),
        [makeRequest],
    );

    const handleSearchTerm = (value) => {
        setSearchTerm(value);
        debouncedSearch(value, { from: dateFrom, to: dateTo });
    };

    const handleDateFromChange = (newFrom) => {
        setDateFrom(newFrom);
    };

    const handleDateToChange = (newTo, newFrom) => {
        setDateTo(newTo);
        const resolvedFrom = newFrom ?? dateFrom;
        if (resolvedFrom && newTo) {
            debouncedSearch(searchTerm, { from: resolvedFrom, to: newTo });
        }
    };

    const handleExport = () => {
        const params = new URLSearchParams({
            ...(searchTerm && { search: searchTerm }),
            ...(dateFrom && { date_from: dateFrom }),
            ...(dateTo && { date_to: dateTo }),
        });

        window.open(`/out-collection-report/lms-posting/finnone-qr/export-finnone-qr?${params.toString()}`, '_blank');
    };

    const handlePageChange = React.useCallback(
        (page) => {
            makeRequest({ page });
        },
        [makeRequest],
    );

    const handlePerPageChange = React.useCallback(
        (perPage) => {
            makeRequest({ per_page: parseInt(perPage), page: 1 });
        },
        [makeRequest],
    );

    const columnLabels = {
        AGREEMENTNO: 'AGREEMENT NO',
        PAYMENT_MODE: 'PAYMENT MODE',
        RECEIPT_DATE: 'RECEIPT DATE',
        RECEIPT_NUM: 'RECEIPT NUM',
        RECEIPT_CHANNEL: 'RECEIPT CHANNEL',
        RECEIPT_AMT: 'RECEIPT AMT',
        DEALING_BANKID: 'DEALING BANKID',
    };

    const table = useReactTable({
        data,
        columns,
        manualSorting: true,
        manualFiltering: true,
        manualPagination: true,
        pageCount: pagination.last_page,
        getCoreRowModel: getCoreRowModel(),
        state: {
            pagination: {
                pageIndex: pagination.current_page - 1,
                pageSize: pagination.per_page,
            },
        },
    });

    const rows = table.getRowModel().rows;

    const renderTableBody = () => {
        if (!hasFiltered) {
            return (
                <TableRow>
                    <TableCell colSpan={columns.length} className="text-muted-foreground h-24 text-center">
                        <div className="flex flex-col items-center gap-1">
                            <span>Enter a search or select a date range to load records.</span>
                        </div>
                    </TableCell>
                </TableRow>
            );
        }

        if (!rows?.length) {
            return (
                <TableRow>
                    <TableCell colSpan={columns.length} className="text-muted-foreground h-24 text-center">
                        No data found.
                    </TableCell>
                </TableRow>
            );
        }

        return (
            <>
                {rows.map((row) => (
                    <TableRow key={row.id}>
                        {row.getVisibleCells().map((cell) => (
                            <TableCell key={cell.id} className="px-4 py-3 text-sm">
                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                            </TableCell>
                        ))}
                    </TableRow>
                ))}
            </>
        );
    };

    if (!pagination) return null;

    return (
        <div className="px-6">
            <Card>
                <CardContent>
                    <DataTableControls
                        searchTerm={searchTerm}
                        onSearchTermChange={handleSearchTerm}
                        dateFrom={dateFrom}
                        onDateFromChange={handleDateFromChange}
                        dateTo={dateTo}
                        onDateToChange={handleDateToChange}
                        onExport={handleExport}
                        hasRows={table.getRowModel().rows?.length}
                        table={table}
                        columnLabels={columnLabels}
                    />

                    <ScrollArea className="relative pb-3">
                        {loading && (
                            <div className="bg-background/50 absolute inset-0 z-10 flex items-center justify-center">
                                <Loader2 className="size-8 animate-spin" />
                            </div>
                        )}

                        <Table>
                            <TableHeader>
                                {table.getHeaderGroups().map((headerGroup) => (
                                    <TableRow key={headerGroup.id}>
                                        {headerGroup.headers.map((header) => (
                                            <TableHead key={header.id}>
                                                {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                            </TableHead>
                                        ))}
                                    </TableRow>
                                ))}
                            </TableHeader>
                            <TableBody>{renderTableBody()}</TableBody>
                        </Table>
                        <ScrollBar orientation="horizontal" />
                    </ScrollArea>

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
}
