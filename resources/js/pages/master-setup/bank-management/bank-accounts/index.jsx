/* eslint-disable react/prop-types */
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Landmark } from 'lucide-react';
import * as React from 'react';
import Columns from './partials/columns';
import Datatable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Bank Management',
        href: '/bank-management',
    },
    {
        title: 'Bank Accounts',
        href: '/bank-account',
    },
];

export default function BankAccounts({ bank, bank_accounts, pagination, filters, sort }) {
    const { makeRequest } = useUrlParams();

    const banks = bank;

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
            <Head title="Bank Accounts" />
            <div className="w-1/3 p-6">
                <div className="bg-background border-border flex items-center gap-6 rounded-lg border px-6 py-5">
                    <div className="flex size-12 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-600">
                        <Landmark className="size-5" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <p className="text-foreground text-base font-medium">{banks.name}</p>
                        <p className="text-muted-foreground mt-0.5 text-sm">{banks.description}</p>
                    </div>
                    <span className="text-muted-foreground border-border bg-muted rounded-md border px-2.5 py-1 text-xs font-medium">
                        {banks.abbreviation}
                    </span>
                </div>
            </div>
            <Datatable bank={bank} bank_accounts={bank_accounts} pagination={pagination} filters={filters} sort={sort} columns={columns} />
        </AppLayout>
    );
}
