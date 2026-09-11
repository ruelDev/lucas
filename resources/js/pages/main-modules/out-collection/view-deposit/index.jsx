/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useUrlParams } from '@/hooks/use-url-params';
import AppLayout from '@/layouts/app-layout';
import { canAny } from '@/lib/can';
import { formatCurrency, formatDate } from '@/utils/format';
import { Head } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import * as React from 'react';
import { Columns } from './partials/columns';
import DataTable from './partials/data-table';

const breadcrumbs = [
    {
        title: 'Out Collection',
        href: '/out-collection',
    },
    {
        title: 'View Deposit',
    },
];

export default function ViewDeposit({ deposits, payments, pagination, filters, sort }) {
    const { makeRequest } = useUrlParams();

    const remainingClass = (value) => {
        if (value > 0) {
            return 'text-yellow-500';
        } else if (value === 0) {
            return 'text-green-500';
        } else {
            return 'text-red-500';
        }
    };

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

    const canAuthorize = canAny(['out_collection.authorize']) && !payments.some((payment) => payment.status.includes('Authorized'));

    const columns = React.useMemo(() => Columns(sort, handleSort, canAuthorize), [sort, handleSort, canAuthorize]);

    const colorClass = (value) => {
        if (value === 'Draft') return 'bg-neutral-500 hover:text-neutral-500';
        if (value === 'Sent to Author') return 'bg-blue-500 hover:text-blue-500';
        if (value === 'Sent Back') return 'bg-yellow-500 hover:text-yellow-500';
        if (value === 'Authorized') return 'bg-green-500 hover:text-green-500';
        return 'bg-gray-400';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="View Deposit" />

            <div className="flex flex-wrap p-6">
                <div className="w-1/2">
                    <div>
                        <div className="grid grid-cols-2">
                            <div>Status:</div>
                            <div className="flex">
                                <Badge variant="secondary" className={`text-white ${colorClass(deposits.status)}`}>
                                    {deposits.status}
                                </Badge>
                            </div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Bank:</div>
                            <div>{deposits.bank}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Reference Number:</div>
                            <div>{deposits.referenceNumber}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Deposit Date:</div>
                            <div>{deposits.depositDate}</div>
                        </div>
                        <div className="grid grid-cols-2 items-center">
                            <div>Uploaded Deposit Slip:</div>
                            <div>
                                <Button variant="link" className="px-0 text-blue-500 hover:text-blue-700" asChild>
                                    <a href={route('out-collection.view-deposit.file', deposits.id)} target="_blank">
                                        View File <ExternalLink className="size-4" />
                                    </a>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="w-1/2">
                    <div>
                        <div className="grid grid-cols-2">
                            <div>Make Date:</div>
                            <div>{formatDate(deposits.created_at)}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Deposit Amount:</div>
                            <div>{formatCurrency(deposits.depositAmount)}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Charge:</div>
                            <div>{formatCurrency(deposits.depositCharge)}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Total Amount:</div>
                            <div>{formatCurrency(deposits.total)}</div>
                        </div>
                        <div className="grid grid-cols-2">
                            <div>Remaining:</div>
                            <div className={remainingClass(deposits.remaining)}>
                                {formatCurrency(deposits.remaining)} {deposits.remaining < 0 && <span>(Over)</span>}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <DataTable payments={payments} deposits={deposits} pagination={pagination} filters={filters} columns={columns} />
        </AppLayout>
    );
}
