/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { can } from '@/lib/can';
import axios from 'axios';
import { ChevronDown, Download, FileJson, FileSpreadsheet, FileText, LoaderCircle } from 'lucide-react';
import { useState } from 'react';

export default function ExportDialog({ searchVal, sorting, dateFrom, dateTo, moduleSelect }) {
    const [processing, setProcessing] = useState(false);

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    const pickExportType = async (type) => {
        const sort = sorting?.[0] || {};

        const exportPayload = {
            searchVal,
            sortBy: sort.id || '',
            sortDir: sort.desc ?? true,
            exportType: type,
            dateFrom: dateFrom ? formatDate(dateFrom) : '',
            dateTo: dateTo ? formatDate(dateTo) : '',
            moduleSelect,
        };

        setProcessing(true);

        try {
            if (type === 'pdf') {
                const response = await axios.post(route('audit-logs.export'), exportPayload, {
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], {
                    type: 'application/pdf',
                });

                const url = window.URL.createObjectURL(blob);

                window.open(url, '_blank');

                setTimeout(() => {
                    window.URL.revokeObjectURL(url);
                }, 10000);
            } else {
                const response = await axios.post(route('audit-logs.export'), exportPayload, {
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], {
                    type: response.headers['content-type'],
                });

                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `logs.${type}`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            }
        } catch (error) {
            console.error('Export failed:', error);
        }
        setProcessing(false);
    };

    return {
        ...(can('audit_logs.export') ? (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                        disabled={processing}
                    >
                        <Download className="h-4 w-4" />
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        {processing ? 'Exporting...' : 'Export'}
                        <ChevronDown />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent>
                    <DropdownMenuLabel>Export as</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onClick={() => pickExportType('pdf')}>
                        <FileText /> PDF
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => pickExportType('csv')}>
                        <FileJson /> CSV
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => pickExportType('xlsx')}>
                        <FileSpreadsheet /> XLXS
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        ) : (
            <></>
        )),
    };
}
