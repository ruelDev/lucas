/* eslint-disable react/prop-types */
'use client';

import TruncatedText from '@/components/truncated-text';
import DataTableColumnHeader from './data-table-column-header';

export const Columns = (sort, handleSort) => [
    {
        accessorKey: 'LOAN_NO',
        header: () => <DataTableColumnHeader sort={sort} title={'LOAN NO'} handleSort={handleSort} columnName={'LOAN_NO'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('LOAN_NO')} maxWidth="max-w-[150px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_MODE',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT MODE'} handleSort={handleSort} columnName={'RECEIPT_MODE'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_MODE')} maxWidth="max-w-[200px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'INSTRUMENT_NO',
        header: () => <DataTableColumnHeader sort={sort} title={'INSTRUMENT NO'} handleSort={handleSort} columnName={'INSTRUMENT_NO'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('INSTRUMENT_NO')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'BANK_ID',
        header: () => <DataTableColumnHeader sort={sort} title={'BANK ID'} handleSort={handleSort} columnName={'BANK_ID'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('BANK_ID')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'BRANCH_ID',
        header: () => <DataTableColumnHeader sort={sort} title={'BRANCH ID'} handleSort={handleSort} columnName={'BRANCH_ID'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('BRANCH_ID')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'BANK_ACCOUNT',
        header: () => <DataTableColumnHeader sort={sort} title={'BANK ACCOUNT'} handleSort={handleSort} columnName={'BANK_ACCOUNT'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('BANK_ACCOUNT')} maxWidth="max-w-[150px]" className="text-sm" />
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
        accessorKey: 'INSTRUMENT_DATE',
        header: () => <DataTableColumnHeader sort={sort} title={'INSTRUMENT DATE'} handleSort={handleSort} columnName={'INSTRUMENT_DATE'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('INSTRUMENT_DATE')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_AMOUNT',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT MOUNT'} handleSort={handleSort} columnName={'RECEIPT_AMOUNT'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_AMOUNT')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'TDS_AMOUNT',
        header: () => <DataTableColumnHeader sort={sort} title={'TDS AMOUNT'} handleSort={handleSort} columnName={'TDS_AMOUNT'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('TDS_AMOUNT')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIPT_NO',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIPT NO'} handleSort={handleSort} columnName={'RECEIPT_NO'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIPT_NO')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'DEFAULT_BRANCH',
        header: () => <DataTableColumnHeader sort={sort} title={'DEFAULT BRANCH'} handleSort={handleSort} columnName={'DEFAULT_BRANCH'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('DEFAULT_BRANCH')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'DEPOSIT_BANK',
        header: () => <DataTableColumnHeader sort={sort} title={'DEPOSIT BANK'} handleSort={handleSort} columnName={'DEPOSIT_BANK'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('DEPOSIT_BANK')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'DEPOSIT_BANK_BRANCH',
        header: () => <DataTableColumnHeader sort={sort} title={'DEPOSIT BANK BRANCH'} handleSort={handleSort} columnName={'DEPOSIT_BANK_BRANCH'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('DEPOSIT_BANK_BRANCH')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'DEPOSIT_BANK_ACCOUNT',
        header: () => (
            <DataTableColumnHeader sort={sort} title={'DEPOSIT BANK ACCOUNT'} handleSort={handleSort} columnName={'DEPOSIT_BANK_ACCOUNT'} />
        ),
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('DEPOSIT_BANK_ACCOUNT')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'MAKER_REMARKS',
        header: () => <DataTableColumnHeader sort={sort} title={'MAKER REMARKS'} handleSort={handleSort} columnName={'MAKER_REMARKS'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('MAKER_REMARKS')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'MIS_NUMBER',
        header: () => <DataTableColumnHeader sort={sort} title={'MIS NUMBER'} handleSort={handleSort} columnName={'MIS_NUMBER'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('MIS_NUMBER')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'CUSTOMER_NAME',
        header: () => <DataTableColumnHeader sort={sort} title={'CUSTOMER NAME'} handleSort={handleSort} columnName={'CUSTOMER_NAME'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('CUSTOMER_NAME')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'RECEIVED_FROM',
        header: () => <DataTableColumnHeader sort={sort} title={'RECEIVED FROM'} handleSort={handleSort} columnName={'RECEIVED_FROM'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('RECEIVED_FROM')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'ACK_RECEIPT_NO',
        header: () => <DataTableColumnHeader sort={sort} title={'ACK RECEIPT NO'} handleSort={handleSort} columnName={'ACK_RECEIPT_NO'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('ACK_RECEIPT_NO')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
    {
        accessorKey: 'PDC_FLAG',
        header: () => <DataTableColumnHeader sort={sort} title={'PDC FLAG'} handleSort={handleSort} columnName={'PDC_FLAG'} />,
        cell: ({ row }) => {
            return (
                <div className="pl-4">
                    <TruncatedText text={row.getValue('PDC_FLAG')} maxWidth="max-w-[120px]" className="text-sm" />
                </div>
            );
        },
    },
];
