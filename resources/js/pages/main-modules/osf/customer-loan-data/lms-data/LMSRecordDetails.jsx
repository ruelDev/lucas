/* eslint-disable react/prop-types */
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { FileCheck } from 'lucide-react';

const formatCurrency = (value) =>
    value == null
        ? '—'
        : Number(value).toLocaleString('en-PH', {
              style: 'currency',
              currency: 'PHP',
              maximumFractionDigits: 2,
          });

function LMSRecordFields({ row }) {
    console.log(row);

    const fields = [
        { label: 'MIS No', value: row.mis_no },
        { label: 'Date Sold', value: row.date_sold },
        { label: 'First Due Date', value: row.first_due_date },
        { label: 'Maturity Date', value: row.maturity_date },
        { label: 'Last Payment Date', value: row.last_payment_date },
        { label: 'Loan Amount', value: formatCurrency(row.loan_amount) },
        {
            label: 'Loan Term',
            value: row.loan_term == null ? '—' : `${row.loan_term} mos.`,
        },
        { label: 'EMI', value: formatCurrency(row.emi) },
        { label: 'NPA Stage', value: row.npa_stage },
    ];

    return (
        <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
            {fields.map((f) => (
                <div key={f.label}>
                    <dt className="text-xs font-medium text-gray-500 dark:text-gray-400">{f.label}</dt>
                    <dd className="mt-0.5 text-gray-800 dark:text-gray-200">{f.value ?? '—'}</dd>
                </div>
            ))}
        </dl>
    );
}

export default function LMSRecordDetails({ row, open, onOpenChange }) {
    if (!row) return null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="p-5">
                <DialogHeader>
                    <DialogTitle>
                        <div className="flex items-center gap-4">
                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                                <span className="text-indigo-700 dark:text-indigo-400">
                                    <FileCheck className="h-4 w-4" />
                                </span>
                            </div>
                            <div>LMS Record — {row.agreement_no}</div>
                        </div>
                    </DialogTitle>
                    <DialogDescription>Full details for this loan record.</DialogDescription>
                </DialogHeader>
                <Separator />
                <div className="mt-3">
                    <LMSRecordFields row={row} />
                </div>
            </DialogContent>
        </Dialog>
    );
}
