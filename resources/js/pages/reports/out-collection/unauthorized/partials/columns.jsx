/* eslint-disable react/prop-types */
'use client';

import TruncatedText from '@/components/truncated-text';
import DataTableColumnHeader from './data-table-column-header';

export const Columns = (sort, handleSort) => [
    {
        accessorKey: 'agreementNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Agreement Number'} handleSort={handleSort} columnName={'agreementNumber'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('agreementNumber')} maxWidth="max-w-[150px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'customerName',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Name'} handleSort={handleSort} columnName={'customerName'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('customerName')} maxWidth="max-w-[200px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'arNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'AR Number'} handleSort={handleSort} columnName={'arNumber'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('arNumber')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'arDate',
        header: () => <DataTableColumnHeader sort={sort} title={'AR Date'} handleSort={handleSort} columnName={'arDate'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('arDate')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'arAmount',
        header: () => <DataTableColumnHeader sort={sort} title={'AR Amount'} handleSort={handleSort} columnName={'arAmount'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('arAmount')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'makerId',
        header: () => <DataTableColumnHeader sort={sort} title={'Maker ID'} handleSort={handleSort} columnName={'makerId'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('makerId')} maxWidth="max-w-[150px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'makeDate',
        header: () => <DataTableColumnHeader sort={sort} title={'Make Date'} handleSort={handleSort} columnName={'created_at'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('makeDate')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
];
