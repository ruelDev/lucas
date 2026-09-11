import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatCurrency } from '@/utils/format';
import { Link } from '@inertiajs/react';
import { Edit, Info } from 'lucide-react';
import Delete from '../actions/delete';
import DataTableColumnHeader from './data-table-column-header';

const Columns = (sort, handleSort, canAuthorize) => [
    ...(canAuthorize
        ? [
              {
                  accessorKey: 'makerId',
                  header: () => <DataTableColumnHeader sort={sort} title={'Maker'} handleSort={handleSort} columnName={'makerId'} />,
                  cell: ({ row }) => {
                      const { makerId, makerName } = row.original;

                      return (
                          <div className="pl-4 text-sm">
                              <div className="font-semibold">{makerName}</div>
                              <div className="text-neutral-500">{makerId}</div>
                          </div>
                      );
                  },
              },
          ]
        : []),
    {
        accessorKey: 'referenceNumber',
        header: () => <DataTableColumnHeader sort={sort} title={'Reference Number'} handleSort={handleSort} columnName={'referenceNumber'} />,
        cell: ({ row }) => {
            return <div className="pl-4 text-sm">{row.getValue('referenceNumber')}</div>;
        },
    },
    {
        accessorKey: 'bankName',
        header: () => <DataTableColumnHeader sort={sort} title={'Bank'} handleSort={handleSort} columnName={'bankName'} />,
        cell: ({ row }) => <div className="pl-4 text-sm">{row.getValue('bankName')}</div>,
    },
    {
        accessorKey: 'depositAmount',
        header: () => <DataTableColumnHeader sort={sort} title={'Deposit Amount'} handleSort={handleSort} columnName={'depositAmount'} />,
        cell: ({ row }) => <div className="pl-4 text-sm">{formatCurrency(row.getValue('depositAmount'))}</div>,
    },
    {
        accessorKey: 'depositDate',
        header: () => <DataTableColumnHeader sort={sort} title={'Deposit Date'} handleSort={handleSort} columnName={'depositDate'} />,
        cell: ({ row }) => <div className="pl-4 text-sm">{row.getValue('depositDate')}</div>,
    },
    {
        accessorKey: 'status',
        header: () => <DataTableColumnHeader sort={sort} title={'Status'} handleSort={handleSort} columnName={'status'} />,
        cell: ({ row }) => {
            const { status } = row.original;

            const colorClass = (value) => {
                if (value === 'Draft') return 'bg-neutral-500 hover:text-neutral-500 dark:hover:text-white';
                if (value === 'Sent to Author') return 'bg-blue-500 hover:text-blue-500';
                if (value === 'Sent Back') return 'bg-yellow-500 hover:text-yellow-500';
                if (value === 'Authorized') return 'bg-green-500 hover:text-green-500';
                return 'bg-gray-400';
            };

            return (
                <div className="flex pl-4">
                    <Badge variant="secondary" className={`text-white ${colorClass(status)}`}>
                        {status}
                    </Badge>
                </div>
            );
        },
    },
    {
        id: 'actions',
        header: () => <span className="pl-4 font-medium">Actions</span>,

        cell: ({ row }) => {
            const { id, status, hasDeposits } = row.original;

            return (
                <div className="flex items-center gap-2">
                    {(status === 'Draft' || status === 'Sent Back') && (
                        <Button variant="ghost" size="icon" className="text-blue-500 hover:bg-blue-100 hover:text-blue-500" asChild>
                            <Link href={route('out-collection.edit', id)} title="Edit">
                                <Edit className="size-5" />
                            </Link>
                        </Button>
                    )}

                    {(status === 'Draft' || status === 'Sent Back') && !hasDeposits && <Delete id={id} />}

                    <Button variant="ghost" size="icon" className="text-blue-800 hover:bg-blue-100 hover:text-blue-800" asChild>
                        <Link href={route('out-collection.view-deposit.index', id)} title="Info">
                            <Info className="size-5" />
                        </Link>
                    </Button>
                </div>
            );
        },
    },
];

export default Columns;
