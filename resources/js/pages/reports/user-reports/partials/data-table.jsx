/* eslint-disable react/prop-types */
import { Card, CardContent } from '@/components/ui/card';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useUrlParams } from '@/hooks/use-url-params';
import { flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import _ from 'lodash';
import { Loader2 } from 'lucide-react';
import { useCallback, useState } from 'react';
import DataTableControls from './data-table-controls';
import { DataTablePagination } from './data-table-pagination';

export default function DataTable({ data, pagination, filters, columns }) {
    const [roleSelect, setRoleSelect] = useState(filters?.role || '');
    const [areaSearch, setAreaSearch] = useState(filters?.area || '');

    const [dateFrom, setDateFrom] = useState(filters?.date_from ? new Date(filters.date_from) : null);
    const [dateTo, setDateTo] = useState(filters?.date_to ? new Date(filters.date_to) : null);

    const { makeRequest, loading } = useUrlParams();

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const debouncedAreaSearch = useCallback(
        _.debounce((term) => {
            makeRequest({
                area: term,
                page: 1,
            });
        }, 1000),
        [makeRequest],
    );

    const debounceDateSearch = useCallback(
        _.debounce((dateFrom, dateTo) => {
            makeRequest({
                date_from: dateFrom ? formatDate(dateFrom) : '',
                date_to: dateTo ? formatDate(dateTo) : '',
                page: 1,
            });
        }, 1000),
        [makeRequest],
    );

    const handleRoleSelect = (value) => {
        setRoleSelect(value);

        makeRequest({
            role: value === 'all' ? '' : value,
            page: 1,
        });
    };

    const handleAreaSearch = (value) => {
        setAreaSearch(value);
        debouncedAreaSearch(value);
    };

    const handleDateFromChange = (value) => {
        setDateFrom(value);
    };

    const handleDateToChange = (value) => {
        setDateTo(value);

        if (dateFrom && value) {
            debounceDateSearch(dateFrom, value);
        } else if (!dateFrom && !value) {
            debounceDateSearch(null, null);
        }
    };

    const handlePageChange = useCallback(
        (page) => {
            makeRequest({ page });
        },
        [makeRequest],
    );

    const handlePerPageChange = useCallback(
        (perPage) => {
            makeRequest({ per_page: Number.parseInt(perPage), page: 1 });
        },
        [pagination.per_page, makeRequest],
    );

    const handleExport = () => {
        const payload = {
            role: roleSelect,
            area: areaSearch,
            date_from: dateFrom,
            date_to: dateTo,
        };

        window.open(route('user-reports.generate', payload));
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
                pageSzie: pagination.per_page,
            },
        },
    });

    return (
        <div className="px-6">
            <Card>
                <CardContent>
                    <DataTableControls
                        roleSelect={roleSelect}
                        onRoleSelectChange={handleRoleSelect}
                        areaSearch={areaSearch}
                        onAreaSearchChange={handleAreaSearch}
                        dateFrom={dateFrom}
                        onDateFromChange={handleDateFromChange}
                        dateTo={dateTo}
                        onDateToChange={handleDateToChange}
                        onExport={handleExport}
                    />

                    <ScrollArea className="relative">
                        {loading && (
                            <div className="bg-gackground/50 absolute inset-0 z-10 flex items-center justify-center">
                                <Loader2 className="size-8 animate-spin"></Loader2>
                            </div>
                        )}

                        <Table>
                            <TableHeader>
                                {table.getHeaderGroups().map((headerGroup) => (
                                    <TableRow key={headerGroup.id}>
                                        {headerGroup.headers.map((header) => (
                                            <TableHead key={header.id}>
                                                {header.isPlaceHolder ? null : flexRender(header.column.columnDef.header, header.getContext())}
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
