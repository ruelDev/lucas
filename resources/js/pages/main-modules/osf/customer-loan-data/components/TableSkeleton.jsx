/* eslint-disable react/prop-types */
import { Skeleton } from '@/components/ui/skeleton';
import { TableBody, TableCell, TableRow } from '@/components/ui/table';

/**
 * TableSkeleton
 * Renders `rows` skeleton rows, each with `colCount` cells.
 */
export default function TableSkeleton({ rows = 3, colCount = 5 }) {
    return (
        <TableBody>
            {Array.from({ length: rows }).map((_, i) => (
                <TableRow key={i}>
                    {Array.from({ length: colCount }).map((_, j) => (
                        <TableCell key={j} className="px-4 py-3">
                            <Skeleton className="h-4 w-full" />
                        </TableCell>
                    ))}
                </TableRow>
            ))}
        </TableBody>
    );
}