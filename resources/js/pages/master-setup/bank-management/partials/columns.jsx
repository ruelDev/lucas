/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { useTimeAgo } from '@/hooks/use-time-ago';
import { Link } from '@inertiajs/react';
import { CreditCard, SquarePen } from 'lucide-react';
import Delete from '../actions/delete';
import DataTableColumnHeader from './data-table-column-header';

const Columns = (sort, handleSort) => [
    {
        accessorKey: 'name',
        header: () => <DataTableColumnHeader sort={sort} title={'Bank Name'} handleSort={handleSort} columnName={'name'} />,
        cell: ({ row }) => <div className="pl-4 text-sm">{row.getValue('name')}</div>,
    },
    {
        accessorKey: 'abbreviation',
        header: () => <DataTableColumnHeader sort={sort} title={'Abbreviation'} handleSort={handleSort} columnName={'abbreviation'} />,
        cell: ({ row }) => (
            <div className="pl-4 text-sm">{row.getValue('abbreviation') ?? <span className="text-neutral-500 italic">Not Available</span>}</div>
        ),
    },
    {
        accessorKey: 'description',
        header: () => <DataTableColumnHeader sort={sort} title={'Description'} handleSort={handleSort} columnName={'description'} />,
        cell: ({ row }) => (
            <div className="pl-4 text-sm">{row.getValue('description') ?? <span className="text-neutral-500 italic">Not Available</span>}</div>
        ),
    },
    {
        accessorKey: 'created_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Created At'} handleSort={handleSort} columnName={'created_at'} />,
        cell: ({ row }) => {
            return <CreatedAtCell created_at={row.getValue('created_at')} />;
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => <ActionCell row={row.original} />,
    },
];

const ActionCell = ({ row }) => {
    const { id } = row;

    return (
        <div className="flex items-center gap-2">
            <Button variant="ghost" size="icon" className="text-blue-500 hover:bg-blue-100 hover:text-blue-500" asChild>
                <Link href={route('bank-management.edit', { bank: id })} title="Edit">
                    <SquarePen className="h-4 w-4" />
                </Link>
            </Button>

            <Delete banks={row} />

            <Button variant="ghost" size="icon" className="text-green-500 hover:bg-green-100 hover:text-green-500" asChild>
                <Link href={route('bank-management.bank-accounts.index', { bank: id })} title="Accounts">
                    <CreditCard className="h-4 w-4" />
                </Link>
            </Button>
        </div>
    );
};

function CreatedAtCell({ created_at }) {
    const date = new Date(created_at);
    const timeAgo = useTimeAgo(date);

    const formattedDate = new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
        hour12: true,
    }).format(date);

    return (
        <>
            <div className="text-muted-foreground pl-4 text-sm">{formattedDate}</div>
            <div className="text-muted-foreground pl-4 text-xs">{timeAgo}</div>
        </>
    );
}

export default Columns;
