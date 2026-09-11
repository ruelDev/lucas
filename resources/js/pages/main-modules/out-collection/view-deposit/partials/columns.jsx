'use client';
/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { formatCurrency } from '@/utils/format';
import { Link } from '@inertiajs/react';
import { Edit } from 'lucide-react';
import Delete from '../actions/delete';
import DataTableColumnHeader from './data-table-column-header';

export const Columns = (sort, handleSort, canAuthorize) => [
    ...(canAuthorize
        ? [
              {
                  id: 'select',
                  header: ({ table }) => (
                      <Checkbox
                          checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                          onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                          aria-label="Select all"
                      />
                  ),
                  cell: ({ row }) => (
                      <Checkbox checked={row.getIsSelected()} onCheckedChange={(value) => row.toggleSelected(!!value)} aria-label="Select row" />
                  ),
                  enableSorting: false,
                  enableHiding: false,
              },
          ]
        : []),
    {
        accessorKey: 'referenceNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Reference Number'} handleSort={handleSort} columnName={'referenceNumber'} />,
        cell: ({ row }) => {
            return <div className="w-50 pl-4 text-sm">{row.getValue('referenceNumber')}</div>;
        },
    },
    {
        accessorKey: 'agreementNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Agreement Number'} handleSort={handleSort} columnName={'agreementNumber'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('agreementNumber')}</div>;
        },
    },
    {
        accessorKey: 'misNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'MIS Number'} handleSort={handleSort} columnName={'misNumber'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('misNumber')}</div>;
        },
    },
    {
        accessorKey: 'customerName',
        header: () => <DataTableColumnHeader sort={sort} title={'Customer Name'} handleSort={handleSort} columnName={'customerName'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('customerName')}</div>;
        },
    },
    {
        accessorKey: 'aoc',
        header: () => <DataTableColumnHeader sort={sort} title={'AOC'} handleSort={handleSort} columnName={'aoc'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('aoc')}</div>;
        },
    },
    {
        accessorKey: 'npaStage',
        header: () => <DataTableColumnHeader sort={sort} title={'NPA Stage'} handleSort={handleSort} columnName={'npaStage'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('npaStage') ?? '-'}</div>;
        },
    },
    {
        accessorKey: 'arNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'AR Number'} handleSort={handleSort} columnName={'arNumber'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('arNumber')}</div>;
        },
    },
    {
        accessorKey: 'arAmount',
        header: () => <DataTableColumnHeader sort={sort} title={'AR Amount'} handleSort={handleSort} columnName={'arAmount'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{formatCurrency(row.getValue('arAmount'))}</div>;
        },
    },
    {
        accessorKey: 'paymentType',
        header: () => <DataTableColumnHeader sort={sort} title={'Payment Type'} handleSort={handleSort} columnName={'paymentType'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('paymentType')}</div>;
        },
    },
    {
        accessorKey: 'reason',
        header: () => <DataTableColumnHeader sort={sort} title={'Reason'} handleSort={handleSort} columnName={'reason'} />,
        cell: ({ row }) => {
            return <div className="w-50 pl-4 text-sm capitalize">{row.getValue('reason')}</div>;
        },
    },
    {
        accessorKey: 'remarks',
        header: () => <span className="pl-4 font-medium">Remarks</span>,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('remarks') ?? '-'}</div>;
        },
    },
    ...(!canAuthorize
        ? [
              {
                  id: 'actions',
                  header: () => <span className="pl-4 font-medium">Actions</span>,
                  cell: ({ row }) => {
                      const { deposit_id, id, status } = row.original;

                      const canEditOrDelete = status === 'Draft' || status === 'Sent Back';

                      return (
                          <div className="flex items-center gap-2">
                              <Button
                                  variant="ghost"
                                  disabled={!canEditOrDelete}
                                  asChild={canEditOrDelete}
                                  size="icon"
                                  className="text-blue-500 hover:bg-blue-100 hover:text-blue-500"
                              >
                                  <Link href={route('out-collection.view-deposit.edit-receipt', { deposit: deposit_id, payment: id })}>
                                      <Edit className={`size-5 ${!canEditOrDelete ? 'opacity-50' : ''}`} />
                                  </Link>
                              </Button>

                              <Delete id={id} deposit_id={deposit_id} disabled={!canEditOrDelete} />
                          </div>
                      );
                  },
              },
          ]
        : []),
];
