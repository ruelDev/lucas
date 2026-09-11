/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import * as React from 'react';
import { Columns } from './partials/columns';
import Datatable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Out Collection',
    },
    {
        title: 'Daily Out Collection Report',
    },
];

export default function DailyCollection({ data, pagination, filters, sort }) {
    const { makeRequest } = useUrlParams();

    const handleSort = React.useCallback(
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

    const columns = React.useMemo(() => Columns(sort, handleSort), [sort, handleSort]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Daily Out Collection Report" />
            <PageTitle title="Daily Out Collection Report" description="Manage all daily collection reports in the system" />
            <Datatable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
