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
                    <TruncatedText text={row.getValue('agreementNumber')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'misNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Number'} handleSort={handleSort} columnName={'misNumber'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('misNumber')} className="text-sm" />
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
                    <TruncatedText text={row.getValue('arNumber')} className="text-sm" />
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
                    <TruncatedText text={row.getValue('arDate')} className="text-sm" />
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
                    <TruncatedText text={row.getValue('arAmount')} className="text-sm" />
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
                    <TruncatedText text={row.getValue('makerId')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: () => <DataTableColumnHeader sort={sort} title={'Make Date'} handleSort={handleSort} columnName={'created_at'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('created_at')} className="text-sm" />
                </div>
            );
        },
    },
];
