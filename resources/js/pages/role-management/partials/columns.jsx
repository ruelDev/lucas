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
import { can, canAny } from '@/lib/can';
import { router } from '@inertiajs/react';
import { MoreHorizontal, Pencil } from 'lucide-react';
import { useState } from 'react';
import DeleteRoleDialog from '../dialog/delete';
import DataTableColumnHeader from './data-table-column-headers';

const RoleNameCell = ({ row }) => {
    const name = row.getValue('name');
    return <div className="text-left font-medium">{name}</div>;
};

const DescriptionCell = ({ row }) => {
    const description = row.getValue('description');
    return <div className="text-left">{description}</div>;
};

const PermissionCell = ({ row }) => {
    const { permissions } = row.original;

    return (
        <div className="flex flex-col items-center">
            <Badge variant="outline" className="rounded-full bg-linear-to-r from-blue-900 to-blue-800 px-3 py-1 font-medium text-white">
                {permissions.length} permissions
            </Badge>
        </div>
    );
};

const UserCountCell = ({ row }) => {
    const { users_count } = row.original;

    const label = `${users_count} user${users_count === 1 ? '' : 's'}`;

    return (
        <div className="flex flex-col items-center">
            <Badge className="rounded-full bg-linear-to-r from-blue-900 to-blue-800 px-3 py-1 font-medium text-white">{label}</Badge>
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

    const handleClickEdit = () => {
        router.post(route('role-management.view'), { id: row.original.id });
    };

    return (
        <DropdownMenu open={dropdownOpen} onOpenChange={setDropdownOpen}>
            {canAny(['role_management.edit', 'role_management.delete']) && (
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="h-8 w-8 p-0">
                        <span className="sr-only">Open menu</span>
                        <MoreHorizontal className="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
            )}
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                <div className="flex flex-col space-y-1">
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        {can('role_management.edit') && (
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
                        <DeleteRoleDialog role={row.original} closeDropdown={() => setDropdownOpen(false)} />
                    </DropdownMenuItem>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
};

const TableColumns = (sort, handleSort) => [
    {
        accessorKey: 'name',
        header: () => <DataTableColumnHeader sort={sort} title={'Name'} handleSort={handleSort} columnName={'name'} />,
        cell: ({ row }) => <RoleNameCell row={row} />,
    },
    {
        accessorKey: 'description',
        header: () => <DataTableColumnHeader sort={sort} title={'Description'} handleSort={handleSort} columnName={'description'} />,
        cell: ({ row }) => <DescriptionCell row={row} />,
    },
    {
        accessorKey: 'permissions',
        header: () => <DataTableColumnHeader sort={sort} title={'Permissions'} handleSort={handleSort} columnName={'permissions'} />,
        cell: ({ row }) => <PermissionCell row={row} />,
    },
    {
        accessorKey: 'users_count',
        header: () => <DataTableColumnHeader sort={sort} title={'Users'} handleSort={handleSort} columnName={'users_count'} />,
        cell: ({ row }) => <UserCountCell row={row} />,
    },
    {
        accessorKey: 'updated_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Modified'} handleSort={handleSort} columnName={'updated_at'} />,
        cell: ({ row }) => <TimeAgoCell row={row} />,
    },
    ...(canAny(['role_management.edit', 'role_management.delete'])
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
