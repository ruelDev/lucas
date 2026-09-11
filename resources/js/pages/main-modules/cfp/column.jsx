'use client';
/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronUp } from 'lucide-react';
import InfoTemp from './partials/info';

const ActionsCell = ({ row }) => {
    const { agreementnumber } = row.original;

    return (
        <div className="flew-row mr-10 flex justify-end">
            <InfoTemp customer={agreementnumber} />
        </div>
    );
};
const column_cfp = (sort, handleSort) => [
    {
        accessorKey: 'agreementnumber',
        header: () => (
            <Button variant="ghost" onClick={() => handleSort('agreementnumber')} className="h-auto p-0 font-semibold">
                Agreement Number
                {sort?.column === 'agreementnumber' && (
                    <span className="ml-1">{sort.direction === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}</span>
                )}
            </Button>
        ),
        cell: ({ row }) => <div className="text-sm">{row.getValue('agreementnumber')}</div>,
    },
    {
        accessorKey: 'customername',
        header: () => (
            <Button variant="ghost" onClick={() => handleSort('customersdetails.customername')} className="h-auto p-0 font-semibold">
                Customer Name
                {sort?.column === 'customersdetails.customername' && (
                    <span className="ml-1">{sort.direction === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}</span>
                )}
            </Button>
        ),
        cell: ({ row }) => <div className="text-sm">{row.getValue('customername')}</div>,
    },
    {
        accessorKey: 'address',
        header: () => (
            <Button variant="ghost" onClick={() => handleSort('address')} className="h-auto p-0 font-semibold">
                Customer Address
                {sort?.column === 'address' && (
                    <span className="ml-1">{sort.direction === 'asc' ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}</span>
                )}
            </Button>
        ),
        cell: ({ row }) => <div className="w-52 truncate text-sm">{row.getValue('address')}</div>,
    },
    {
        id: 'actions',
        header: () => <div className="flew-row flex justify-end px-6">Actions</div>,
        cell: ({ row }) => <ActionsCell row={row} />,
    },
];

export default column_cfp;
