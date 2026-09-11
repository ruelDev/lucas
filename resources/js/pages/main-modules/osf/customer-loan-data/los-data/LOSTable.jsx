/* eslint-disable react/prop-types */
import { Badge } from '@/components/ui/badge';
import { FileText } from 'lucide-react';
import DataTable from '../components/DataTable';
import SectionHeader from '../components/SectionHeader';

const STATUS_STYLES = {
    APPROVED: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
    DECLINED:  'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
    PENDING:   'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800',
    DEFAULT:   'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
};

const statusStyle = (status) =>
    STATUS_STYLES[status?.toUpperCase()] ?? STATUS_STYLES.DEFAULT;

const LOS_COLUMNS = [
    {
        key: 'financing_bank',
        label: 'Financing Bank',
        minWidth: '140px',
        className: 'font-medium text-gray-800 dark:text-gray-200 whitespace-nowrap',
        sortable: true,
        sortType: 'string',
        filterable: true,
    },
    {
        key: 'rlos_id',
        label: 'RLOS ID',
        minWidth: '160px',
        className: 'font-mono text-gray-700 dark:text-gray-300 whitespace-nowrap',
        sortable: true,
        sortType: 'string',
    },
    {
        key: 'status',
        label: 'Status',
        minWidth: '100px',
        sortable: true,
        sortType: 'string',
        filterable: true,
        render: (value) => (
            <Badge variant="outline" className={`px-2 py-0.5 text-[10px] font-medium ${statusStyle(value)}`}>
                {value ?? '—'}
            </Badge>
        ),
    },
    {
        key: 'date_encoded',
        label: 'Date Encoded',
        minWidth: '120px',
        className: 'whitespace-nowrap text-gray-600 dark:text-gray-400',
        sortable: true,
        sortType: 'date',
    },
    {
        key: 'decision_date',
        label: 'Decision Date',
        minWidth: '120px',
        className: 'whitespace-nowrap text-gray-600 dark:text-gray-400',
        sortable: true,
        sortType: 'date',
    },
    {
        key: 'remarks',
        label: 'Remarks',
        minWidth: '200px',
        className: 'text-gray-600 dark:text-gray-400',
        render: (value) => (
            <div className="break-words whitespace-normal">{value ?? '—'}</div>
        ),
    },
];

export default function LOSTable({ rows, loading }) {
    return (
        <div className="w-full rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <SectionHeader
                icon={<FileText className="h-4 w-4" />}
                iconBg="bg-blue-50 dark:bg-blue-900/30"
                iconColor="text-blue-700 dark:text-blue-400"
                title="LOS Data"
                description="Loan Origination System records"
            />
            <DataTable
                columns={LOS_COLUMNS}
                rows={rows}
                loading={loading}
                emptyText="No LOS records found."
            />
        </div>
    );
}