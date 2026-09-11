/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import * as React from 'react';
import { Columns } from './partials/columns';
import DataTable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Out Collection',
    },
    {
        title: 'Finnone QR Report',
    },
];

export default function FinnoneQr({ data, pagination, filters, sort }) {
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
            <Head title="Finnone QR Report" />
            <PageTitle title="Finnone QR Report" description="Manage all finnone qr reports in the system" />
            <DataTable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
