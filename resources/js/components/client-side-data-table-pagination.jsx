import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-react"
import { Button } from "./ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select"
import { Input } from './ui/input';

export default function ClientSideDataTablePagination({ table }) {
    const pageIndex = table.getState().pagination.pageIndex
    const pageSize = table.getState().pagination.pageSize
    const totalRows = table.getFilteredRowModel().rows.length
    const pageCount = table.getPageCount()
    const from = pageIndex * pageSize + 1
    const to = Math.min((pageIndex + 1) * pageSize, totalRows)

    return (
        <div className="py-2">
            <div className="flex items-center justify-between space-x-6 lg:space-x-8">
                <div className="flex items-center">
                    <div className="md:flex w-[100px] items-center text-sm font-medium hidden">
                        Page {table.getState().pagination.pageIndex + 1} of{" "}
                        {table.getPageCount()}
                    </div>
                    <div className="flex items-center space-x-2">
                        <p className="text-sm font-medium">Rows per page</p>
                        <Select
                            value={`${table.getState().pagination.pageSize}`}
                            onValueChange={(value) => {
                                table.setPageSize(Number(value))
                            }}
                        >
                            <SelectTrigger className="h-8 w-[70px]">
                                <SelectValue placeholder={table.getState().pagination.pageSize} />
                            </SelectTrigger>
                            <SelectContent side="top">
                                {[10, 20, 30, 40, 50].map((pageSize) => (
                                    <SelectItem key={pageSize} value={`${pageSize}`}>
                                        {pageSize}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div className="flex items-center space-x-2">
                    <Button
                        variant="outline"
                        className="hidden w-8 h-8 p-0 lg:flex"
                        onClick={() => table.setPageIndex(0)}
                        disabled={!table.getCanPreviousPage()}
                    >
                        <span className="sr-only">Go to first page</span>
                        <ChevronsLeft />
                    </Button>
                    <Button
                        variant="outline"
                        className="w-8 h-8 p-0"
                        onClick={() => table.previousPage()}
                        disabled={!table.getCanPreviousPage()}
                    >
                        <span className="sr-only">Go to previous page</span>
                        <ChevronLeft />
                    </Button>
                    <Button
                        variant="outline"
                        className="w-8 h-8 p-0"
                        onClick={() => table.nextPage()}
                        disabled={!table.getCanNextPage()}
                    >
                        <span className="sr-only">Go to next page</span>
                        <ChevronRight />
                    </Button>
                    <Button
                        variant="outline"
                        className="hidden w-8 h-8 p-0 lg:flex"
                        onClick={() => table.setPageIndex(table.getPageCount() - 1)}
                        disabled={!table.getCanNextPage()}
                    >
                        <span className="sr-only">Go to last page</span>
                        <ChevronsRight />
                    </Button>
                </div>
            </div>
        </div>

        // <div className="flex flex-col items-center justify-between gap-4 py-4 sm:flex-row">
        //     <div className="flex items-center space-x-2 text-sm text-muted-foreground">
        //         <span>
        //             Showing <span className="font-medium">{from}</span>-
        //             <span className="font-medium">{to}</span> of {" "}
        //             <span className="font-medium">{totalRows}</span>
        //         </span>
        //     </div>

        //     <div className="flex items-center space-x 2">
        //         <p className="text-sm font-medium">Rows per page</p>
        //         <Select
        //             value={`${pageSize}`}
        //             onValueChange={(value) => {
        //                 table.setPageSize(Number(value))
        //             }}
        //         >
        //             <SelectTrigger className="h-8 w-[70px]">
        //                 <SelectValue placeholder={pageSize} />
        //             </SelectTrigger>
        //             <SelectContent side="top">
        //                 {[5,10,20,30,40,50].map((size) => (
        //                     <SelectItem key={size} value={`${size}`}>
        //                         {size}
        //                     </SelectItem>
        //                 ))}
        //             </SelectContent>
        //         </Select>
        //     </div>

        //     <div className="flex items-center space-x-2">
        //         <Button
        //             variant="outline"
        //             className="hidden h-8 w-8 p-0 sm:flex"
        //             onClick={() => table.setPageIndex(0)}
        //             disabled={!table.getCanPreviousPage()}
        //         >
        //             <span className="sr-only">Go to first page</span>
        //             <ChevronsLeft className="h-4 w-4" />
        //         </Button>
        //         <Button
        //             variant="outline"
        //             className="hidden h-8 w-8 p-0 sm:flex"
        //             onClick={() => table.previousPage()}
        //             disabled={!table.getCanPreviousPage()}
        //         >
        //             Go to previous page
        //             <ChevronLeft className="h-4 w-4" />
        //         </Button>

        //             <div className="flex items-center gap-1 text-sm">
        //                 <span>Page</span>
        //                 <Input
        //                     type='number'
        //                     min={1}
        //                     max={pageCount}
        //                     value={pageIndex + 1}
        //                     onChange={(e) => {
        //                         const value = e.target.value ? Number(e.target.value) - 1 : 0
        //                         table.setPageIndex(value)
        //                     }}
        //                     className='h-8 w-14'
        //                 />
        //                 <span>of {pageCount}</span>
        //             </div>

        //         <Button
        //             variant="outline"
        //             className="hidden h-8 w-8 p-0 sm:flex"
        //             onClick={() => table.nextPage()}
        //             disabled={!table.getCanNextPage()}
        //         >
        //             <span className="sr-only">Go to next page</span>
        //             <ChevronRight className="h-4 w-4" />
        //         </Button>
        //         <Button
        //             variant="outline"
        //             className="hidden h-8 w-8 p-0 sm:flex"
        //             onClick={() => table.setPageIndex(pageCount - 1)}
        //             disabled={!table.getCanNextPage()}
        //         >
        //             Go to last page
        //             <ChevronsRight className="h-4 w-4" />
        //         </Button>
        //     </div>
        // </div>
    )
}
