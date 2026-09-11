/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { BarChart3, Info } from 'lucide-react';
import { createElement, useState } from 'react';
import DataTable from '../components/DataTable';
import SectionHeader from '../components/SectionHeader';
import LMSRecordDetails from './LMSRecordDetails';

const LOAN_STATUS_STYLES = {
    ACTIVE: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
    CLOSED: 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
    NPA: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
    DEFAULT: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800',
};

const loanStatusStyle = (status) => LOAN_STATUS_STYLES[status?.toUpperCase()] ?? LOAN_STATUS_STYLES.DEFAULT;

const renderInfoButton = (row, onInfoClick) =>
    createElement(
        'button',
        {
            type: 'button',
            title: 'View LMS Record Information',
            onClick: () => onInfoClick(row),
            className:
                'inline-flex items-center justify-center text-blue-600 transition-colors hover:bg-brand-primary/10 hover:text-brand-primary focus:outline-none focus:ring-2 focus:ring-blue-100',
        },
        createElement(Info, { className: 'h-4 w-4' }),
    );

const getLmsColumns = (onInfoClick) => [
    {
        key: 'agreement_no',
        label: 'Agreement No',
        minWidth: '160px',
        className: 'font-mono font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap',
        sortable: true,
        sortType: 'string',
    },
    {
        key: 'agreement_id',
        label: 'Agreement ID',
        minWidth: '130px',
        className: 'font-mono text-gray-700 dark:text-gray-300 whitespace-nowrap',
        sortable: true,
        sortType: 'string',
    },
    {
        key: 'account_rating',
        label: 'Account Rating',
        minWidth: '120px',
        className: 'whitespace-nowrap font-medium text-gray-700 dark:text-gray-300',
        sortable: true,
        sortType: 'string',
        filterable: true,
    },
    {
        key: 'loan_status',
        label: 'Loan Status',
        minWidth: '110px',
        sortable: true,
        sortType: 'string',
        filterable: true,
        render: (value) => (
            <Badge variant="outline" className={`px-2 py-0.5 text-[10px] font-medium ${loanStatusStyle(value)}`}>
                {value ?? '—'}
            </Badge>
        ),
    },
    {
        key: 'actions',
        label: 'Actions',
        render: (_, row) => renderInfoButton(row, onInfoClick),
    },
];

export default function LMSTable({ rows, loading }) {
    const [selectedRow, setSelectedRow] = useState(null);
    const [detailsOpen, setDetailsOpen] = useState(false);

    const handleInfoClick = (row) => {
        setSelectedRow(row);
        setDetailsOpen(true);
    };

    const columns = getLmsColumns(handleInfoClick);

    return (
        <div className="w-full rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <SectionHeader
                icon={<BarChart3 className="h-4 w-4" />}
                iconBg="bg-indigo-50 dark:bg-indigo-900/30"
                iconColor="text-indigo-700 dark:text-indigo-400"
                title="LMS Data"
                description="Loan Management System records"
            />
            <DataTable columns={columns} rows={rows} loading={loading} tableStyle={{ minWidth: '900px' }} emptyText="No LMS records found." />
            <LMSRecordDetails row={selectedRow} open={detailsOpen} onOpenChange={setDetailsOpen} />
        </div>
    );
}
