/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import TableColumns from './columns';
import DataTable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Branches',
        href: '/branch-management',
    },
];

export default function Branches({ data, pagination, filters, sort }) {
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
            <Head title="Branches" />
            <PageTitle title="Branch Management" description="Manage all branches in the system" />

            <DataTable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
