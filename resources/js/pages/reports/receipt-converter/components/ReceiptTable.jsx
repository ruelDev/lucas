/* eslint-disable react/prop-types */

import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { calculateTableWidth, getColumnWidth } from '../utils/columnConfig';
import { getRowValue, hasColumnData } from '../utils/rowValueResolver';

export default function ReceiptTable({ headers, rows, source, searchQuery }) {
    return (
        <div className="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <div className="max-w-full overflow-x-auto">
                <Table
                    className="text-xs"
                    style={{
                        tableLayout: 'fixed',
                        width: `${calculateTableWidth(source, headers)}px`,
                        minWidth: '100%',
                    }}
                >
                    <TableHeader>
                        <TableRow className="border-b-2 border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                            <TableHead style={{ width: '60px' }} className="px-3 py-2 whitespace-nowrap">
                                Row
                            </TableHead>
                            <TableHead style={{ width: '70px' }} className="px-3 py-2 whitespace-nowrap">
                                Status
                            </TableHead>
                            {headers.map((header) => {
                                const isEmpty = !hasColumnData(source, header);
                                return (
                                    <TableHead
                                        key={header}
                                        style={{ width: `${getColumnWidth(source, header)}px` }}
                                        className={`px-2 py-2 break-words whitespace-normal ${isEmpty ? 'bg-gray-100 dark:bg-gray-900' : ''}`}
                                    >
                                        {header.replace(/_/g, ' ')}
                                    </TableHead>
                                );
                            })}
                            <TableHead style={{ width: '100px' }} className="px-3 py-2 whitespace-nowrap">
                                Remarks
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {rows.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={headers.length + 3} className="py-8 text-center text-sm text-gray-500">
                                    {searchQuery ? 'No rows match your search' : 'No data available'}
                                </TableCell>
                            </TableRow>
                        ) : (
                            rows.map((r, i) => {
                                const hasErrors = !r.valid && r.errors?.length;
                                return (
                                    <TableRow key={`${r.row}-${i}`} className={hasErrors ? 'bg-red-50 dark:bg-red-900/20' : ''}>
                                        <TableCell style={{ width: '100px' }} className="truncate px-3 py-2" title={r.row?.toString()}>
                                            {r.row}
                                        </TableCell>
                                        <TableCell style={{ width: '90px' }} className="px-3 py-2">
                                            {r.valid ? (
                                                <Badge
                                                    variant="outline"
                                                    className="border-green-200 bg-green-50 px-1.5 py-0.5 text-[10px] text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300"
                                                >
                                                    Valid
                                                </Badge>
                                            ) : (
                                                <Badge
                                                    variant="outline"
                                                    className="border-red-200 bg-red-50 px-1.5 py-0.5 text-[10px] text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300"
                                                >
                                                    Invalid
                                                </Badge>
                                            )}
                                        </TableCell>
                                        {headers.map((header) => {
                                            const value = getRowValue(source, r, header);
                                            const isEmpty = !hasColumnData(source, header);
                                            return (
                                                <TableCell
                                                    key={header}
                                                    style={{ width: `${getColumnWidth(source, header)}px` }}
                                                    className={`px-2 py-2 ${isEmpty ? 'bg-gray-50 dark:bg-gray-900/50' : ''}`}
                                                    title={value?.toString()}
                                                >
                                                    <div className="break-all whitespace-normal">{value || (isEmpty ? '—' : '')}</div>
                                                </TableCell>
                                            );
                                        })}
                                        <TableCell style={{ width: '200px' }} className="px-3 py-2">
                                            {r.errors && r.errors.length > 0 && (
                                                <div
                                                    className="text-[10px] break-all whitespace-normal text-red-600 dark:text-red-400"
                                                    title={r.errors.join(', ')}
                                                >
                                                    {r.errors.map((error) => (
                                                        <li key={`${r.id}-${error}`} className="break-words">
                                                            {error}
                                                        </li>
                                                    ))}
                                                </div>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                );
                            })
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
