/* eslint-disable react/prop-types */
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { flexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useReactTable } from '@tanstack/react-table';
import { Search } from 'lucide-react';
import * as React from 'react';
import { Input } from './ui/input';

export default function DataTable({ columns, data, action, hasExport, globalSearch, searchBy, cfpSearch }) {
    const [sorting, setSorting] = React.useState([]);
    const [globalFilter, setGlobalFilter] = React.useState('');

    const table = useReactTable({
        data,
        columns,
        state: {
            sorting,
            globalFilter,
        },
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        onSortingChange: setSorting,
        getSortedRowModel: getSortedRowModel(),
        onGlobalFilterChange: setGlobalFilter,
        getFilteredRowModel: getFilteredRowModel(),
    });

    const exportData = {
        searchVal: globalFilter,
        sorting: sorting,
    };

    return (
        <div className="m-4">
            <div className="flex items-center justify-between">
                <div className="relative flex items-center py-4">
                    <Input
                        type="search"
                        placeholder="Search..."
                        value={globalFilter ?? ''}
                        onChange={(e) => table.setGlobalFilter(String(e.target.value))}
                        className="h-8 w-[150px] pl-7 lg:w-[250px]"
                    />
                    <span className="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                        <Search className="text-muted-foreground size-4" />
                    </span>
                </div>
                <div className="flex space-x-1">
                    {action}
                    {hasExport && React.cloneElement(hasExport, exportData)}
                </div>
            </div>
            {globalSearch && (
                <div className="flex items-center justify-between">
                    <div className="relative flex items-center py-4">
                        <Input
                            type="search"
                            placeholder="Search..."
                            value={globalFilter ?? ''}
                            onChange={(e) => table.setGlobalFilter(String(e.target.value))}
                            className="h-8 w-[150px] pl-7 lg:w-[250px]"
                        />
                        <span className="absolute inset-y-0 start-0 flex items-center justify-center px-2">
                            <Search className="text-muted-foreground size-4" />
                        </span>
                    </div>
                    {action}
                    {hasExport && React.cloneElement(hasExport, exportData)}
                </div>
            )}

            <div className="rounded-md border p-2">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
