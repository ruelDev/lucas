/* eslint-disable react/prop-types */
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import { useCallback, useMemo } from 'react';
import TableColumns from './partials/columns';
import DataTable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'User Management',
        href: '/user-management',
    },
];

export default function UserManagement({ data, pagination, filters, sort }) {
    const { makeRequest } = useUrlParams();

    const handleSort = useCallback(
        (columnId) => {
            const isCurrentColumn = sort?.column === columnId;
            const newDirection = isCurrentColumn && sort?.direction === 'asc' ? 'desc' : 'asc';

            makeRequest({
                sort: columnId,
                direction: newDirection,
                page: 1,
            });
        },
        [sort, makeRequest],
    );

    const columns = useMemo(() => TableColumns(sort, handleSort), [sort, handleSort]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <PageTitle title="User Management" description="Manage all users in the system" />

            <DataTable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
