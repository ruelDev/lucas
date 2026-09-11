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
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useTimeAgo } from '@/hooks/use-time-ago';
import { can, canAny } from '@/lib/can';
import { router } from '@inertiajs/react';
import { MoreHorizontal, Pencil } from 'lucide-react';
import { useState } from 'react';
import DeleteUserDialog from '../dialog/delete';
import ResetUserDialog from '../dialog/reset';
import DataTableColumnHeader from './data-table-column-header';

const EmployeeIDCell = ({ row }) => {
    const { employee_id } = row.original;

    return (
        <div className="flex flex-col">
            <div>{employee_id}</div>
        </div>
    );
};

const NameCell = ({ row }) => {
    const { name, email } = row.original;
    return (
        <div className="flex flex-col">
            <div>{name}</div>
            <div className="text-muted-foreground text-sm">{email}</div>
        </div>
    );
};

const PositionCell = ({ row }) => {
    const { position, area, company } = row.original;

    return (
        <div className="flex flex-col">
            <div>{position}</div>
            <div className="text-muted-foreground text-sm" dangerouslySetInnerHTML={{ __html: area }} />
            <div className="text-muted-foreground text-sm">{company == 'BOTH' ? 'BMI/BFC' : company}</div>
        </div>
    );
};

const shorten = (text, max = 10) => (text.length > max ? text.slice(0, max) + '...' : text);

const RoleCell = ({ row }) => {
    const { role } = row.original;

    if (!role) {
        return <div className="text-center">N/A</div>;
    }

    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <div className="w-[100px]">
                        <Badge variant="outline" className="w-full justify-center truncate bg-[#1B4298] text-white">
                            {shorten(role)}
                        </Badge>
                    </div>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{role}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
};

const StatusDisplayCell = ({ row }) => {
    const { status, remarks } = row.original;

    return (
        <div className="flex flex-col items-center">
            <div>
                <Badge className={status === 'active' ? 'rounded-full bg-linear-to-r! from-blue-900 to-blue-800 dark:text-white' : 'rounded-full bg-gray-400!'}>{status}</Badge>
            </div>
            <div>{status !== 'active' ? <span className="text-xs text-gray-600">Remarks: {remarks}</span> : ''}</div>
        </div>
    );
};

const ActionsCell = ({ row }) => {
    const [dropdownOpen, setDropdownOpen] = useState(false);

    const handleClickEdit = () => {
        router.post(route('user-management.view'), { id: row.original.id });
    };

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
                        {can('user_management.edit') && (
                            <button
                                className="flex cursor-pointer justify-start gap-2 rounded-lg px-2 py-1 hover:bg-blue-100! hover:text-blue-600!"
                                onClick={handleClickEdit}
                            >
                                <Pencil className="h-4 w-4 hover:bg-blue-100! hover:text-blue-600!" />
                                Edit
                            </button>
                        )}
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <ResetUserDialog user={row.original} />
                    </DropdownMenuItem>
                    <DropdownMenuItem asChild>
                        <DeleteUserDialog user={row.original} closeDropdown={() => setDropdownOpen(false)} />
                    </DropdownMenuItem>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

const TimeAgoCell = ({ row }) => {
    const datetime = new Date(row);

    const isValidDate = row && !Number.isNaN(datetime.getTime());
    const timeAgoString = useTimeAgo(datetime);

    if (!isValidDate) {
        return <div className="text-muted-foreground text-sm">N/A</div>;
    }

    const formattedDate = new Intl.DateTimeFormat('en-PH', {
        dateStyle: 'medium',
        timeStyle: 'short',
        hour12: true,
    }).format(datetime);

    return (
        <>
            <div className="text-muted-foreground text-sm">{formattedDate}</div>
            <div className="text-muted-foreground text-xs">{timeAgoString}</div>
        </>
    );
};

const TableColumns = (sort, handleSort) => [
    {
        accessorKey: 'employee_id',
        header: () => <DataTableColumnHeader sort={sort} title={'Employee ID'} handleSort={handleSort} columnName={'employee_id'} />,
        cell: ({ row }) => <EmployeeIDCell row={row} />,
    },
    {
        accessorKey: 'fname',
        header: () => <DataTableColumnHeader sort={sort} title={'Name'} handleSort={handleSort} columnName={'fname'} />,
        cell: ({ row }) => <NameCell row={row} />,
    },
    {
        accessorKey: 'position',
        header: () => <DataTableColumnHeader sort={sort} title={'Position'} handleSort={handleSort} columnName={'position'} />,
        cell: ({ row }) => <PositionCell row={row} />,
    },
    {
        accessorKey: 'role',
        header: () => <DataTableColumnHeader sort={sort} title={'Role'} columnName={'role'} />,
        cell: ({ row }) => <RoleCell row={row} />,
    },
    {
        accessorKey: 'status',
        header: () => <DataTableColumnHeader sort={sort} title={'Status'} handleSort={handleSort} columnName={'status'} />,
        cell: ({ row }) => <StatusDisplayCell row={row} />,
    },
    {
        accessorKey: 'last_login_date',
        header: () => <DataTableColumnHeader sort={sort} title={'Last Login Date'} handleSort={handleSort} columnName={'last_login_date'} />,
        cell: ({ row }) => <TimeAgoCell row={row.original.last_login_date} />,
    },
    {
        accessorKey: 'updated_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Modified'} handleSort={handleSort} columnName={'updated_at'} />,
        cell: ({ row }) => <TimeAgoCell row={row.original.updated_at} />,
    },
    ...(canAny(['user_management.edit', 'user_management.delete', 'user_management.reset'])
        ? [
              {
                  accessorKey: 'actions',
                  header: () => <DataTableColumnHeader sort={sort} title={'Actions'} />,
                  cell: ({ row }) => <ActionsCell row={row} />,
              },
          ]
        : []),
];

export default TableColumns;
