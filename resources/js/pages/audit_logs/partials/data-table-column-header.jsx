/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronUp } from 'lucide-react';

export default function DataTableColumnHeader({ sort, title, columnName, handleSort }) {
    const chevronDirection = (sort) => {
        if (sort.direction === 'asc') {
            return <ChevronUp className="w-4 h-4" />;
        } else {
            return <ChevronDown className="w-4 h-4" />;
        }
    };

    return (
        <Button variant="ghost" onClick={() => handleSort(columnName)} className="font-semibold">
            {title}
            {sort?.column === columnName && <span className="ml-1">{chevronDirection(sort)}</span>}
        </Button>
    );
}
