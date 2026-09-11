import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { flexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useReactTable } from "@tanstack/react-table"
import * as React from "react"
import { Input } from "./ui/input"
import { Search } from "lucide-react"
import DataTablePagination from "@/components/rp-data-table-pagination"

export default function DataTable({ columns, data, action }) {

    const [sorting, setSorting] = React.useState([])
    const [globalFilter, setGlobalFilter] = React.useState('')

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        onSortingChange: setSorting,
        getSortedRowModel: getSortedRowModel(),
        onGlobalFilterChange: setGlobalFilter,
        getFilteredRowModel: getFilteredRowModel(), 
        state: {
            sorting,
            globalFilter,
        },
        
    })

    return (
        <div className="m-4">
            <div className="flex justify-between items-center">
                <div className="flex relative items-center py-4">
                    <Input type="search"
                        placeholder="Search..."
                        value={globalFilter ?? ""}
                        onChange={e => table.setGlobalFilter(String(e.target.value))}
                        className="pl-7 h-8 w-[150px] lg:w-[250px]"
                    />
                    <span className="flex absolute inset-y-0 justify-center items-center px-2 start-0">
                        <Search className="size-4 text-muted-foreground" />
                    </span>
                </div>
                {action}
            </div>
            <div className="p-2 rounded-md border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(
                                                    header.column.columnDef.header,
                                                    header.getContext()
                                                )}
                                        </TableHead>
                                    )
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </TableCell>
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
            <DataTablePagination table={table} />
        </div >
    )
}
