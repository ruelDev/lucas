import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";

/* eslint-disable react/prop-types */
export default function ResultsTable({ columns = [], data = [], loading = false, emptyMessage = 'No records found.', rowKey = 'id' }) {
    if (loading) {
        return null;
    }

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div className="overflow-x-auto dark:border-gray-700">
                <Table className="min-w-full divide-y divide-gray-100 text-sm">
                    <TableHeader>
                        <TableRow className="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/60">
                            {columns.map((column) => (
                                <TableHead
                                    key={column.key}
                                    className="px-4 py-3 text-left text-xs font-medium tracking-wide whitespace-nowrap text-gray-700 dark:text-gray-100 uppercase"
                                >
                                    {column.title}
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>

                    <TableBody className="divide-y divide-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        {data.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={columns.length} className="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    {emptyMessage}
                                </TableCell>
                            </TableRow>
                        ) : (
                            data.map((row, index) => (
                                <TableRow
                                    key={row.customerID}
                                    style={{
                                        animation: `fadeSlideUp 0.35s ease-out ${index * 50}ms both`,
                                    }}
                                    className="hover:bg-brand-primary/5 transition-all duration-200 border-b border-gray-50 hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
                                >
                                    {columns.map((column) => (
                                        <TableCell key={column.key} className="px-6 py-3.5 whitespace-nowrap text-gray-700 dark:text-gray-100">
                                            {column.render ? column.render(row) : row[column.key]}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
