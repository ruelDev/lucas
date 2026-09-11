'use client';
/* eslint-disable react/prop-types */
import { useTimeAgo } from '@/hooks/use-time-ago';
import DataTableColumnHeader from './data-table-column-header';

const TimestampCell = ({ row }) => {
    const { created_at } = row.original;
    const date = new Date(created_at);

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

const NameCell = ({ row }) => {
    const { name, employee_id, position } = row.original;
    return (
        <div className="flex flex-col">
            <div>{name}</div>
            <div className="text-muted-foreground text-xs">
                {employee_id} - {position}
            </div>
        </div>
    );
};

const ModuleCell = ({ row }) => {
    const { module } = row.original;
    return (
        <div className="flex flex-col">
            <div>{module}</div>
        </div>
    );
};

const ActionCell = ({ row }) => {
    const { action } = row.original;
    return (
        <div className="flex flex-col">
            <div>{action}</div>
        </div>
    );
};

const RoleCell = ({ row }) => {
    const { role } = row.original;
    return (
        <div className="flex flex-col">
            <div>{role}</div>
        </div>
    );
};

const IPAdressCell = ({ row }) => {
    const { ip_address } = row.original;
    return (
        <div className="flex flex-col">
            <div>{ip_address}</div>
        </div>
    );
};

const TableColumns = (sort, handleSort) => [
    {
        accessorKey: 'created_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Timestamp'} handleSort={handleSort} columnName={'created_at'} />,
        cell: ({ row }) => <TimestampCell row={row} />,
    },
    {
        accessorKey: 'name',
        header: () => <DataTableColumnHeader sort={sort} title={'Name'} handleSort={handleSort} columnName={'user.fname'} />,
        cell: ({ row }) => <NameCell row={row} />,
    },
    {
        accessorKey: 'role',
        header: () => <DataTableColumnHeader sort={sort} title={'Role'} handleSort={handleSort} columnName={'user.roles'} />,
        cell: ({ row }) => <RoleCell row={row} />,
    },
    {
        accessorKey: 'module',
        header: () => <DataTableColumnHeader sort={sort} title={'Module'} handleSort={handleSort} columnName={'module'} />,
        cell: ({ row }) => <ModuleCell row={row} />,
    },
    {
        accessorKey: 'event',
        header: () => <DataTableColumnHeader sort={sort} title={'Event'} handleSort={handleSort} columnName={'event'} />,
        cell: ({ row }) => <ActionCell row={row} />,
    },
    {
        accessorKey: 'ip_address',
        header: () => <DataTableColumnHeader sort={sort} title={'IP Address'} handleSort={handleSort} columnName={'ip_address'} />,
        cell: ({ row }) => <IPAdressCell row={row} />,
    },
];

export default TableColumns;
