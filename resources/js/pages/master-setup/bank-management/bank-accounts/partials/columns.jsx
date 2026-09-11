/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { useTimeAgo } from '@/hooks/use-time-ago';
import { Link } from '@inertiajs/react';
import { SquarePen } from 'lucide-react';
import Delete from '../actions/delete';
import DataTableColumnHeader from './data-table-column-header';

const Columns = (sort, handleSort) => [
    {
        accessorKey: 'account_number',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Number'} handleSort={handleSort} columnName={'account_number'} />,
        cell: ({ row }) => (
            <div className="pl-4 text-sm">{row.getValue('account_number') ?? <span className="text-neutral-500 italic">Not Available</span>}</div>
        ),
    },
    {
        accessorKey: 'depository_remarks',
        header: () => (
            <DataTableColumnHeader sort={sort} title={'Bank Depository Remarks'} handleSort={handleSort} columnName={'depository_remarks'} />
        ),
        cell: ({ row }) => (
            <div className="pl-4 text-sm">{row.getValue('depository_remarks') ?? <span className="text-neutral-500 italic">Not Available</span>}</div>
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
    const { id, bank_id } = row;

    return (
        <div className="flex items-center gap-2">
            <Button variant="ghost" size="icon" className="text-blue-500 hover:bg-blue-100 hover:text-blue-500" asChild>
                <Link href={route('bank-management.bank-accounts.edit', { bank: bank_id, bankAccount: id })} title="Edit">
                    <SquarePen className="h-4 w-4" />
                </Link>
            </Button>

            <Delete bankAccount={row} />
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
