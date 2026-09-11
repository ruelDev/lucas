import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { flexRender, getCoreRowModel, getFilteredRowModel, getPaginationRowModel, getSortedRowModel, useReactTable } from "@tanstack/react-table"
import * as React from "react"
import { Input } from "./ui/input"
import { Search } from "lucide-react"
import ClientSideDataTablePagination from "./client-side-data-table-pagination"


export default function DataTable({ columns, data, meta, action, onRpRequestAuthorization }) {

    const [sorting, setSorting] = React.useState([])
    const [globalFilter, setGlobalFilter] = React.useState('')
    const [rowSelection, setRowSelection] = React.useState({})

    const table = useReactTable({
        data,
        columns,
        meta,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        onSortingChange: setSorting,
        onGlobalFilterChange: setGlobalFilter,
        onRowSelectionChange: setRowSelection,
        state: {
            sorting,
            globalFilter,
            rowSelection,
        },
    })

    return (
        <div className="m-4">
            <div className="flex justify-between items-center">
                <div className="flex relative items-center py-4">
                    <div>
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
                </div>
                <div className="flex items-center space-x-2">
                    {/* {onRpRequestAuthorization && React.cloneElement(onRpRequestAuthorization, rpRequestAuthorizationProps)} */}
                    {onRpRequestAuthorization && typeof onRpRequestAuthorization === "function" ? onRpRequestAuthorization({table}) : onRpRequestAuthorization}
                    {action && typeof action === "function" ? action({table}) : action}
                </div>
            </div>
            <div className="p-2 rounded-md border">
                <Table>
                    <TableHeader className='bg-gray-200'>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => {
                                    return (
                                        <TableHead className='border' key={header.id}>
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
                                    className={
                                        row.original.status === 'Sent Back'
                                        ? "bg-red-100 hover:!text-black"
                                        : "bg-gray-50"
                                    }
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell className='border' key={cell.id}>
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
            <div className="text-muted-foreground flex-1 text-sm">
                {table.getFilteredSelectedRowModel().rows.length} of{" "}
                {table.getFilteredRowModel().rows.length} rows(s) selected.
            </div>
            <ClientSideDataTablePagination table={table} />
        </div >
    )
}
