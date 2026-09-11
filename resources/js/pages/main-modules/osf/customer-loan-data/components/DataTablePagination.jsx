/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

/**
 * DataTablePagination
 * Renders the pagination footer: row range info, prev/next buttons, page jump.
 */
export default function DataTablePagination({
    currentPage,
    totalPages,
    startIndex,
    endIndex,
    totalRows,
    goToPage,
}) {
    if (totalPages <= 1) return null;

    return (
        <div className="flex flex-col items-center justify-between gap-3 border-t border-gray-100 px-4 py-3 sm:flex-row dark:border-gray-700">
            {/* Row info */}
            <span className="text-xs text-gray-500 dark:text-gray-400">
                Showing <strong>{startIndex + 1}</strong>–<strong>{Math.min(endIndex, totalRows)}</strong> of{' '}
                <strong>{totalRows.toLocaleString()}</strong> rows
            </span>

            {/* Nav buttons */}
            <div className="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => goToPage(1)}
                    disabled={currentPage === 1}
                    className="h-7 w-7 p-0"
                    aria-label="First page"
                >
                    <ChevronsLeft className="h-3.5 w-3.5" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => goToPage(currentPage - 1)}
                    disabled={currentPage === 1}
                    className="h-7 w-7 p-0"
                    aria-label="Previous page"
                >
                    <ChevronLeft className="h-3.5 w-3.5" />
                </Button>

                <span className="px-2 text-xs text-gray-600 dark:text-gray-400">
                    Page <strong>{currentPage}</strong> of <strong>{totalPages}</strong>
                </span>

                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => goToPage(currentPage + 1)}
                    disabled={currentPage === totalPages}
                    className="h-7 w-7 p-0"
                    aria-label="Next page"
                >
                    <ChevronRight className="h-3.5 w-3.5" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => goToPage(totalPages)}
                    disabled={currentPage === totalPages}
                    className="h-7 w-7 p-0"
                    aria-label="Last page"
                >
                    <ChevronsRight className="h-3.5 w-3.5" />
                </Button>
            </div>

            {/* Jump to page */}
            <div className="flex items-center gap-1.5">
                <span className="text-xs text-gray-500 dark:text-gray-400">Go to:</span>
                <Input
                    type="number"
                    min={1}
                    max={totalPages}
                    defaultValue={currentPage}
                    key={currentPage}
                    onBlur={(e) => {
                        const p = parseInt(e.target.value, 10);
                        if (!isNaN(p)) goToPage(p);
                    }}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') {
                            const p = parseInt(e.target.value, 10);
                            if (!isNaN(p)) goToPage(p);
                        }
                    }}
                    className="h-7 w-14 text-center text-xs"
                />
            </div>
        </div>
    );
}