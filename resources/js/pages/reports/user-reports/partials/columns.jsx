'use client';
/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
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
    const { name } = row.original;
    return (
        <div className="flex flex-col">
            <div>{name}</div>
        </div>
    );
};

const RoleCell = ({ row }) => {
    const { role } = row.original;
    return (
        <div className="flex flex-wrap">
            {role && (
                <Badge variant="outline" className="mr-1 bg-[#1B4298] text-white">
                    {role}
                </Badge>
            )}
        </div>
    );
};

const AreaCell = ({ row }) => {
    const { area } = row.original;

    return (
        <div className="flex flex-col">
            <div className="text-muted-foreground text-sm">{area}</div>
        </div>
    );
};

const DateTimeCell = ({ date }) => {
    return (
        <div>
            <div className="text-muted-foreground text-sm">{date ?? 'N/A'}</div>
        </div>
    );
};

const StatusDisplayCell = ({ row }) => {
    const { account_status, remarks } = row.original;

    return (
        <div className="flex flex-wrap text-left font-medium">
            <Badge className={account_status === 'active' ? 'bg-blue-500!' : 'bg-gray-400!'}>{account_status}</Badge>
            {account_status === 'active' ? '' : (<span className="text-xs text-gray-600">Remarks: {remarks}</span>)}
        </div>
    );
};

const TableColumns = (sort, handleSort) => [
    {
        accessorKey: 'employee_id',
        header: () => <DataTableColumnHeader sort={sort} title={'Employee ID'} handleSort={handleSort} columnName={'employee_id'} />,
        cell: ({ row }) => <EmployeeIDCell row={row} />,
    },
    {
        accessorKey: 'name',
        header: () => <DataTableColumnHeader sort={sort} title={'Name'} handleSort={handleSort} columnName={'lname'} />,
        cell: ({ row }) => <NameCell row={row} />,
    },
    {
        accessorKey: 'role',
        header: () => <DataTableColumnHeader sort={sort} title={'Role'} handleSort={handleSort} columnName={'role'} />,
        cell: ({ row }) => <RoleCell row={row} />,
    },
    {
        accessorKey: 'area',
        header: () => <DataTableColumnHeader sort={sort} title={'Branch/Dealer/Department'} handleSort={handleSort} columnName={'area'} />,
        cell: ({ row }) => <AreaCell row={row} />,
    },
    {
        accessorKey: 'date_created',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Created'} handleSort={handleSort} columnName={'date_created'} />,
        cell: ({ row }) => <DateTimeCell date={row.original.date_created} />,
    },
    {
        accessorKey: 'last_login',
        header: () => <DataTableColumnHeader sort={sort} title={'Last Login Date'} handleSort={handleSort} columnName={'last_login_date'} />,
        cell: ({ row }) => <DateTimeCell date={row.original.last_login_date} />,
    },
    {
        accessorKey: 'last_logout',
        header: () => <DataTableColumnHeader sort={sort} title={'Last Logout Date'} handleSort={handleSort} columnName={'last_logout_date'} />,
        cell: ({ row }) => <DateTimeCell date={row.original.last_logout_date} />,
    },
    {
        accessorKey: 'last_password_change',
        header: () => <DataTableColumnHeader sort={sort} title={'Last Password Change'} handleSort={handleSort} columnName={'last_password_change'} />,
        cell: ({ row }) => <DateTimeCell date={row.original.last_password_change} />,
    },
    {
        accessorKey: 'account_status',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Status'} handleSort={handleSort} columnName={'account_status'} />,
        cell: ({ row }) => <StatusDisplayCell row={row} />,
    },
    {
        accessorKey: 'account_expiration_date',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Expires on'} handleSort={handleSort} columnName={'account_expiration_date'} />,
        cell: ({ row }) => <DateTimeCell date={row.original.account_expiration_date} />,
    },
];

export default TableColumns;
