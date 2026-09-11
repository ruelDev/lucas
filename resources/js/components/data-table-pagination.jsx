/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';

export const DataTablePagination = ({ pagination, onPageChange, onPerPageChange, loading }) => {
    return (
        <div className="flex flex-wrap items-center justify-center sm:justify-between gap-2 py-4">
            <div className="flex items-center gap-2">
                <Select
                    value={pagination.per_page.toString()}
                    onValueChange={onPerPageChange}
                >
                    <SelectTrigger className="w-32">
                        <SelectValue placeholder="Rows per page" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="10">10 rows</SelectItem>
                        <SelectItem value="25">25 rows</SelectItem>
                        <SelectItem value="50">50 rows</SelectItem>
                        <SelectItem value="100">100 rows</SelectItem>
                    </SelectContent>
                </Select>
                <div className="text-sm text-muted-foreground">
                    Showing {pagination.from || 0} to {pagination.to || 0} of{' '}
                    {pagination.total.toLocaleString()} results
                </div>
            </div>

            <div className="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(1)}
                    disabled={pagination.current_page === 1 || loading}
                >
                    <ChevronsLeft className="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(pagination.current_page - 1)}
                    disabled={pagination.current_page === 1 || loading}
                >
                    <ChevronLeft className="h-4 w-4" />
                </Button>

                <span className="text-sm font-medium">
                    Page {pagination.current_page} of {pagination.last_page}
                </span>

                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(pagination.current_page + 1)}
                    disabled={pagination.current_page === pagination.last_page || loading}
                >
                    <ChevronRight className="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(pagination.last_page)}
                    disabled={pagination.current_page === pagination.last_page || loading}
                >
                    <ChevronsRight className="h-4 w-4" />
                </Button>
            </div>
        </div>
    );
};
