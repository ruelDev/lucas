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

export default function ExportDialog({ searchVal, sorting }) {
    const [processing, setProcessing] = useState(false);

    const pickExportType = async (type) => {
        const sort = sorting?.[0] || {};

        const exportPayload = {
            searchVal,
            sortBy: sort.id || '',
            sortDir: sort.desc ?? true,
            exportType: type,
        };

        setProcessing(true);

        try {
            if (type === 'pdf') {
                // old stream pdf
                const response = await axios.post(route('user-management.export'), exportPayload, {
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
                const response = await axios.post(route('user-management.export'), exportPayload, {
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], {
                    type: response.headers['content-type'],
                });

                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `users.${type}`);
                document.body.appendChild(link);
                link.click();
                link.remove();
            }
        } catch (error) {
            console.error('Export failed:', error);
        }
        setProcessing(false);
    };

    return (
        <>
            {can('user_management.export') && (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            className="mx-3 h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
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
                            <FileSpreadsheet /> XLSX
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            )}
        </>
    );
}
