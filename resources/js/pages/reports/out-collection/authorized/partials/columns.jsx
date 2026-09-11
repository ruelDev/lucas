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
        accessorKey: 'customerName',
        header: () => <DataTableColumnHeader sort={sort} title={'Account Name'} handleSort={handleSort} columnName={'customerName'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('customerName')} className="text-sm" />
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
        accessorKey: 'makeDate',
        header: () => <DataTableColumnHeader sort={sort} title={'Make Date'} handleSort={handleSort} columnName={'created_at'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('makeDate')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'bank_name',
        header: () => <DataTableColumnHeader sort={sort} title={'Bank'} handleSort={handleSort} columnName={'bank_name'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('bank_name')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'bankAccountNo',
        header: () => (
            <DataTableColumnHeader sort={sort} title={'Bank Account No_BYC Ref Number'} handleSort={handleSort} columnName={'bankAccountNo'} />
        ),
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('bankAccountNo')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'dateDeposited',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Deposited'} handleSort={handleSort} columnName={'dateDeposited'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('dateDeposited')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'amount',
        header: () => <DataTableColumnHeader sort={sort} title={'Amount'} handleSort={handleSort} columnName={'amount'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('amount')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'authorizerId',
        header: () => <DataTableColumnHeader sort={sort} title={'Authorizer ID'} handleSort={handleSort} columnName={'authorizerId'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('authorizerId')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'dateAuthor',
        header: () => <DataTableColumnHeader sort={sort} title={'Date Author'} handleSort={handleSort} columnName={'dateAuthor'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('dateAuthor')} className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'referenceNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Reference Number'} handleSort={handleSort} columnName={'referenceNumber'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('referenceNumber')} className="text-sm" />
                </div>
            );
        },
    },
];
