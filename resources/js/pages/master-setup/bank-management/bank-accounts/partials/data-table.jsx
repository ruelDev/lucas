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
import { DataTableControls } from './data-table-controls';

const Datatable = ({ bank, bank_accounts, pagination, filters, columns }) => {
    const [searchTerm, setSearchTerm] = React.useState(filters?.search || '');

    const { makeRequest, loading } = useUrlParams();

    // Debounced search function using lodash
    const debouncedSearch = React.useCallback(
        _.debounce((term) => {
            makeRequest({ search: term, page: 1 });
        }, 1000),
        [makeRequest],
    );

    const handleSearch = (value) => {
        setSearchTerm(value);
        debouncedSearch(value);
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
        [pagination.per_page, makeRequest],
    );

    const table = useReactTable({
        data: bank_accounts,
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

    return (
        <div className="space-y-4 px-6">
            <Card>
                <CardContent>
                    {/* Controls */}
                    <DataTableControls searchTerm={searchTerm} onSearchChange={handleSearch} bank={bank} />

                    {/* Table */}
                    <div className="relative">
                        {loading && (
                            <div className="bg-background/50 absolute inset-0 z-10 flex items-center justify-center">
                                <Loader2 className="size-8 animate-spin" />
                            </div>
                        )}

                        <ScrollArea className="rounded-md border">
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
                                <TableBody>
                                    {table.getRowModel().rows?.length ? (
                                        table.getRowModel().rows.map((row) => (
                                            <TableRow key={row.id}>
                                                {row.getVisibleCells().map((cell) => (
                                                    <TableCell key={cell.id} className="px-4 py-3 text-sm">
                                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                                    </TableCell>
                                                ))}
                                            </TableRow>
                                        ))
                                    ) : (
                                        <TableRow>
                                            <TableCell colSpan={columns.length} className="h-24 text-center">
                                                No data found.
                                            </TableCell>
                                        </TableRow>
                                    )}
                                </TableBody>
                            </Table>
                            <ScrollBar orientation="horizontal" />
                        </ScrollArea>
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

export default Datatable;
