/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import Columns from './partials/columns';
import Datatable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Bank Management',
        href: '/bank-management',
    },
];

export default function BankManagement({ data, pagination, filters, sort }) {
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

    const columns = useMemo(() => Columns(sort, handleSort), [sort, handleSort]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Bank Management" />
            <PageTitle title="Bank Management" description="Manage all banks in the system" />
            <Datatable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
