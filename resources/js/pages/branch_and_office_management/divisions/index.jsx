/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import TableColumns from './partials/columns';
import DataTable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Division',
        href: '/division-management',
    },
];

export default function Divisions({ data, pagination, filters, sort }) {
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
            <Head title="Divisions" />
            <PageTitle title="Division Management" description="Manage all divisions in the system" />

            <DataTable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
