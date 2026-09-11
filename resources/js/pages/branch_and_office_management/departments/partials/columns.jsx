'use client';
/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTimeAgo } from '@/hooks/use-time-ago';
import { canAny } from '@/lib/can';
import { MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import DeleteDepartmentDialog from '../dialog/delete';
import EditDepartmentDialog from '../dialog/edit';
import DataTableColumnHeader from './data-table-column-header';

const DepartmentNameCell = ({ row }) => {
    const { name, code } = row.original;
    return (
        <div>
            <div className="text-left font-medium">{name}</div>
            <div className="text-left text-xs text-slate-700">({code})</div>
        </div>
    );
};

const GroupNameCell = ({ row }) => {
    const { group, division } = row.original;
    return (
        <div>
            <div className="text-left text-xs font-medium">{division === 'N/A' ? '' : division}</div>
            <div className="text-left text-xs text-slate-700">{group}</div>
        </div>
    );
};

const DescriptionCell = ({ row }) => {
    const description = row.getValue('description');
    return <div className="text-left text-xs whitespace-pre-line">{description}</div>;
};

const StatusDisplayCell = ({ row }) => {
    const status = row.getValue('status');
    return (
        <div className="flex flex-col items-center">
            <Badge className={status === 'active' ? 'rounded-full bg-linear-to-r! from-blue-900 to-blue-800 dark:text-white' : 'rounded-full bg-gray-400!'}>
                {status}
            </Badge>
        </div>
    );
};

const TimeAgoCell = ({ row }) => {
    const { updated_at } = row.original;
    const date = new Date(updated_at);

    const timeAgoString = useTimeAgo(date);

    const formattedDate = new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
        hour12: true,
    }).format(date);

    return (
        <>
            <div className="text-muted-foreground text-sm">{formattedDate}</div>
            <div className="text-muted-foreground text-xs">{timeAgoString}</div>
        </>
    );
};

const ActionsCell = ({ row }) => {
    const [dropdownOpen, setDropdownOpen] = useState(false);

    return (
        <DropdownMenu open={dropdownOpen} onOpenChange={setDropdownOpen}>
            {canAny(['branch_management.edit', 'branch_management.delete'])}
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-8 w-8 p-0">
                    <span className="sr-only">Open menu</span>
                    <MoreHorizontal className="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <div className="flex flex-col space-y-1">
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <EditDepartmentDialog department={row.original} />
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <DeleteDepartmentDialog department={row.original} closeDropdown={() => setDropdownOpen(false)} />
                    </DropdownMenuItem>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

const TableColumns = (sort, handleSort) => [
    {
        accessorKey: 'name',
        header: () => <DataTableColumnHeader sort={sort} title={'Department Name'} handleSort={handleSort} columnName={'name'} />,
        cell: ({ row }) => <DepartmentNameCell row={row} />,
    },
    {
        accessorKey: 'group',
        header: () => <div className="h-auto p-0 text-sm font-semibold">Group</div>,
        cell: ({ row }) => <GroupNameCell row={row} />,
    },
    {
        accessorKey: 'description',
        header: () => <DataTableColumnHeader sort={sort} title={'Description'} handleSort={handleSort} columnName={'description'} />,
        cell: ({ row }) => <DescriptionCell row={row} />,
    },
    {
        accessorKey: 'status',
        header: () => <DataTableColumnHeader sort={sort} title={'Status'} handleSort={handleSort} columnName={'status'} />,
        cell: ({ row }) => <StatusDisplayCell row={row} />,
    },
    {
        accessorKey: 'updated_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Modified'} handleSort={handleSort} columnName={'updated_at'} />,
        cell: ({ row }) => <TimeAgoCell row={row} />,
    },
    ...(canAny(['department_management.edit', 'department_management.delete'])
        ? [
              {
                  accessorKey: 'actions',
                  header: () => <span className="h-auto p-0 font-semibold">Actions</span>,
                  cell: ({ row }) => <ActionsCell row={row} />,
              },
          ]
        : []),
];

export default TableColumns;
