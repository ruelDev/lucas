/* eslint-disable react/prop-types */
'use client';

import TruncatedText from '@/components/truncated-text';
import DataTableColumnHeader from './data-table-column-header';

export const Columns = (sort, handleSort) => [
    {
        accessorKey: 'AGREEMENTNO',
        header: () => <DataTableColumnHeader sort={sort} title={'AGREEMENT NUMBER'} handleSort={handleSort} columnName={'AGREEMENTNO'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('AGREEMENTNO')} maxWidth="max-w-[150px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'PAYMENT_MODE',
        header: () => <DataTableColumnHeader sort={sort} title={'PAYMENT MODE'} handleSort={handleSort} columnName={'PAYMENT_MODE'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('PAYMENT_MODE')} maxWidth="max-w-[200px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_DATE',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT DATE'} handleSort={handleSort} columnName={'RECEIPT_DATE'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_DATE')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_NUM',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT NUMBER'} handleSort={handleSort} columnName={'RECEIPT_NUM'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_NUM')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'CHECK_NUMBER',
        header: () => <DataTableColumnHeader sort={sort} title={'CHECK NUMBER'} handleSort={handleSort} columnName={'CHECK_NUMBER'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('CHECK_NUMBER')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_CHANNEL',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT CHANNEL'} handleSort={handleSort} columnName={'RECEIPT_CHANNEL'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_CHANNEL')} maxWidth="max-w-[150px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_AMT',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT AMT'} handleSort={handleSort} columnName={'RECEIPT_AMT'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_AMT')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'DEALING_BANKID',
        header: () => <DataTableColumnHeader sort={sort} title={'DEALING BANKID'} handleSort={handleSort} columnName={'DEALING_BANKID'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('DEALING_BANKID')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'REMARKS',
        header: () => <DataTableColumnHeader sort={sort} title={'REMARKS'} handleSort={handleSort} columnName={'REMARKS'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('REMARKS')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'MIS_ACCOUNT',
        header: () => <DataTableColumnHeader sort={sort} title={'MIS ACCOUNT'} handleSort={handleSort} columnName={'MIS_ACCOUNT'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('MIS_ACCOUNT')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'CUSTOMERNAME',
        header: () => <DataTableColumnHeader sort={sort} title={'CUSTOMER NAME'} handleSort={handleSort} columnName={'CUSTOMERNAME'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('CUSTOMERNAME')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
];
