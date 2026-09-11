/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { canAny } from '@/lib/can';
import { Head } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import Columns from './partials/columns';
import Datatable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Out Collection',
        href: '/out-collection',
    },
];

export default function OutCollection({ data, pagination, filters, sort }) {
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

    const canAuthorize = canAny(['out_collection.authorize']);

    const columns = useMemo(() => Columns(sort, handleSort, canAuthorize), [sort, handleSort, canAuthorize]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Out Collection" />
            <PageTitle title="Out Collection" description="Manage all out collection in the system" />
            <Datatable data={data} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
