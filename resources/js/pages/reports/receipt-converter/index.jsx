import PageTitle from '@/components/page-title';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Select } from '@radix-ui/react-select';
import axios from 'axios';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Loader2, Search, X } from 'lucide-react';
import { useState } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';
import ReceiptTable from './components/ReceiptTable';
import UploadCard from './components/UploadCard';
import { useReceiptConversion } from './hooks/useReceiptConversion';
import { useReceiptData } from './hooks/useReceiptData';

const breadcrumbs = [
    {
        title: 'Reports - Receipt Converter',
    },
];

export default function ReceiptConverter() {
    const [file, setFile] = useState();
    const [rows, setRows] = useState([]);
    const [headers, setHeaders] = useState([]);
    const [source, setSource] = useState(null);
    const [fullSummary, setFullSummary] = useState(null);

    const [loading, setLoading] = useState(false);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [dragActive, setDragActive] = useState(false);

    const ReactSwal = withReactContent(Swal);

    const {
        currentRows,
        sortedAndFilteredRows,
        summary,
        totalPages,
        startIndex,
        endIndex,
        filterStatus,
        searchQuery,
        currentPage,
        pageSize,
        handleFilterChange,
        handleSearchChange,
        clearSearch,
        handlePageSizeChange,
        goToPage,
        setCurrentPage,
        setFilterStatus,
        setSearchQuery,
    } = useReceiptData(rows, fullSummary);

    const reset = () => {
        // Reset state
        setFile(null);
        setRows([]);
        setHeaders([]);
        setSource(null);
        setUploadProgress(0);
    };

    const { committing, handleConvert } = useReceiptConversion({
        rows,
        source,
        file,
        reset,
    });

    const handleDrag = (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (e.type === 'dragenter' || e.type === 'dragover') {
            setDragActive(true);
        } else if (e.type === 'dragleave') {
            setDragActive(false);
        }
    };

    const handleDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setDragActive(false);

        if (e.dataTransfer?.files[0]) {
            const droppedFile = e.dataTransfer?.files[0] ?? null;
            checkUploadedFile(droppedFile);
            if (droppedFile.name.match(/\.(xlsx|xls|csv)$/i)) {
                setFile(droppedFile);
                setRows([]);
                setHeaders([]);
                setSource(null);
                setUploadProgress(0);
                setCurrentPage(1);
                setFilterStatus('all');
                setSearchQuery('');
            }
        }
    };

    function checkUploadedFile(f) {
        const ext = f.name.slice(f.name.lastIndexOf('.') + 1);

        const acceptedFileFormats = ['csv'];
        const MAX_FILE_SIZE = 5 * 1024 * 1024;

        let isFileValid = true;

        if (!acceptedFileFormats.includes(ext)) {
            ReactSwal.fire({
                icon: 'error',
                iconColor: '#1B4298',
                title: 'File name not accepted',
                text: 'File name should be csv format only',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            isFileValid = false;
        }

        if (f.size > MAX_FILE_SIZE) {
            ReactSwal.fire({
                icon: 'error',
                iconColor: '#1B4298',
                title: 'File too large',
                text: 'File must not exceed 5MB.',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            isFileValid = false;
        }

        return isFileValid;
    }

    function onFileChange(e) {
        const f = e.target.files?.[0] ?? null;

        const isFileValid = checkUploadedFile(f);

        if (isFileValid) {
            setFile(f);
            setRows([]);
            setHeaders([]);
            setSource(null);
            setUploadProgress(0);
            setCurrentPage(1);
            setFilterStatus('all');
            setSearchQuery('');
        }
    }

    async function handlePreview(e) {
        e.preventDefault();

        if (!file) {
            ReactSwal.fire({
                icon: 'warning',
                title: 'No File selected',
                text: 'Please choose an .csv file first.',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        const fileSizeMB = file.size / (1024 * 1024);
        if (fileSizeMB > 10) {
            const { isConfirmed } = await ReactSwal.fire({
                icon: 'warning',
                title: 'Large File Detected',
                text: `This file is ${fileSizeMB.toFixed(2)} MB. Processing may take several minutes. Continue?`,
                showCancelButton: true,
                confirmButtonText: 'Yes, continue',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#1B4298',
            });
            if (!isConfirmed) return;
        }

        const form = new FormData();
        form.append('file', file);

        setLoading(true);
        setUploadProgress(0);

        try {
            const { data } = await axios.post('/receipt-converter/bulkUpload', form, {
                headers: { 'Content-Type': 'multipart/form-data' },
                timeout: 300000,
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    setUploadProgress(percentCompleted);
                },
            });

            const withCheck = data.rows.map((r) => ({ ...r, checked: r.valid }));
            setRows(withCheck);
            setFullSummary(data.summary);
            setHeaders(data.returnHeader || []);
            setSource(data.source);
            setCurrentPage(1);

            if (data.summary.invalid > 0) {
                setFilterStatus('invalid');
            }

            const totalRows = data.totalRows || data.summary.total;

            ReactSwal.fire({
                icon: 'success',
                iconColor: '#1B4298',
                title: 'File loaded',
                html: `
                    <div style="text-align: left; padding: 15px">
                        <h4 style="margin-bottom: 10px; font-size: 24px; font-weight: bold;">File Summary</h4>
                        <p style="font-size: 14px;"><strong>Filename: </strong> ${file.name}</p>
                        <p style="font-size: 14px;"><strong>Source: </strong> ${data.source}</p>
                        <p style="font-size: 14px;"><strong>Total Rows: </strong> ${totalRows.toLocaleString()}</p>
                        <p style="font-size: 14px;"><strong>Valid Rows: </strong> ${data.summary.valid.toLocaleString()}</p>
                        <p style="font-size: 14px;"><strong>Invalid Rows: </strong> ${data.summary.invalid.toLocaleString()}</p>
                        <hr style="margin: 15px 0;" />
                        <p style="color: green; font-weight:500;">All rows loaded and validated</p>
                        ${
                            data.summary.invalid > 0
                                ? '<p style="color: orange;">Invalid rows will be shown first for review</p>'
                                : '<p style="color: green;">All rows are valid</p>'
                        }
                    </div>
                        `,
                timer: 3000,
                width: 600,
                showConfirmButton: false,
                background: 'var(--background)',
                color: 'var(--foreground)',
            });
        } catch (err) {
            const msg = err?.response?.data?.error ?? err?.response?.data?.message ?? err?.message ?? 'Preview Failed';

            ReactSwal.fire({
                icon: 'error',
                title: 'Preview Failed',
                text: msg,
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
                footer: 'Please check file format and try again',
            });
        } finally {
            setLoading(false);
            setUploadProgress(0);
        }
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Receipt Converter" />
            <PageTitle title="Receipt Converter" description="Validates imported CSV files and export the converted format" />

            {/* File Upload Section */}
            <UploadCard
                file={file}
                loading={loading}
                committing={committing}
                uploadProgress={uploadProgress}
                dragActive={dragActive}
                onFileChange={onFileChange}
                onDrop={handleDrop}
                onDrag={handleDrag}
                onClearFile={reset}
                onPreview={handlePreview}
            />

            {/* after uploading */}
            {rows.length > 0 && headers.length > 0 && (
                <div className="mt-10 mb-6 w-full px-6">
                    <Card className="w-full overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <CardContent className="w-full p-4">
                            <div className="mb-4 flex w-full flex-wrap items-center justify-between gap-3">
                                <div className="flex flex-col gap-2">
                                    <div className="">
                                        <Badge className="bg-blue-800 px-2 py-1 text-xs text-white dark:bg-blue-900/30 dark:text-white hover:bg-blue-200 hover:text-blue-800">
                                            Filename: {file?.name}
                                        </Badge>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <Badge className="bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 hover:bg-blue-800 hover:text-white">
                                            Source: {source}
                                        </Badge>
                                        <Badge className="bg-slate-100 px-2 py-1 text-xs text-slate-800 dark:bg-slate-700 dark:text-slate-200 hover:bg-slate-800 hover:text-white">
                                            Total: {summary.total.toLocaleString()}
                                        </Badge>
                                        <Badge className="bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900/30 dark:text-green-300 hover:bg-green-800 hover:text-white">
                                            Valid: {summary.valid.toLocaleString()}
                                        </Badge>
                                        <Badge className="bg-red-100 px-2 py-1 text-xs text-red-800 dark:bg-red-900/30 dark:text-red-300 hover:bg-red-800 hover:text-white">
                                            Invalid: {summary.invalid.toLocaleString()}
                                        </Badge>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <span className="text-xs text-gray-600 dark:text-gray-400">Filter:</span>
                                        <Select value={filterStatus} onValueChange={handleFilterChange}>
                                            <SelectTrigger className="h-8 w-40 text-xs">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Rows ({rows.length})</SelectItem>
                                                <SelectItem value="invalid">Invalid ({summary.invalid})</SelectItem>
                                                <SelectItem value="valid">Valid ({summary.valid})</SelectItem>
                                            </SelectContent>
                                        </Select>

                                        <span className="ml-2 text-xs text-gray-600 dark:text-gray-400">Per Page:</span>
                                        <Select value={pageSize.toString()} onValueChange={handlePageSizeChange}>
                                            <SelectTrigger className="h-8 w-20 text-xs">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="25">25</SelectItem>
                                                <SelectItem value="50">50</SelectItem>
                                                <SelectItem value="100">100</SelectItem>
                                                <SelectItem value="200">200</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>

                            {/* Search Bar */}
                            <div className="mb-4 flex items-center gap-2">
                                <div className="relative max-w-md flex-1">
                                    <Search className="absolute top-1/2 left-2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                                    <Input
                                        type="text"
                                        placeholder="Search rows by any field..."
                                        value={searchQuery}
                                        onChange={(e) => handleSearchChange(e.target.value)}
                                        className="h-9 border-gray-200 pr-8 pl-8 text-sm dark:border-gray-700"
                                    />
                                    {searchQuery && (
                                        <button
                                            onClick={clearSearch}
                                            className="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <X className="h-3.5 w-3.5" />
                                        </button>
                                    )}
                                </div>
                                {searchQuery && (
                                    <span className="text-xs text-gray-600 dark:text-gray-400">
                                        Found {sortedAndFilteredRows.length.toLocaleString()} result
                                        {sortedAndFilteredRows.length !== 1 ? 's' : ''}
                                    </span>
                                )}
                            </div>

                            {/* Table Container with proper scrolling */}
                            <ReceiptTable headers={headers} rows={currentRows} source={source} searchQuery={searchQuery} />

                            {/* Pagination */}
                            {totalPages > 1 && (
                                <div className="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
                                    <div className="text-xs text-gray-600 dark:text-gray-400">
                                        Showing {startIndex + 1} to {Math.min(endIndex, sortedAndFilteredRows.length)} of{' '}
                                        {sortedAndFilteredRows.length.toLocaleString()} rows
                                    </div>

                                    <div className="flex items-center gap-1.5">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => goToPage(1)}
                                            disabled={currentPage === 1}
                                            className="h-8 w-8 p-0"
                                        >
                                            <ChevronsLeft className="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => goToPage(currentPage - 1)}
                                            disabled={currentPage === 1}
                                            className="h-8 w-8 p-0"
                                        >
                                            <ChevronLeft className="h-3.5 w-3.5" />
                                        </Button>
                                        <span className="px-2 text-xs text-gray-600 dark:text-gray-400">
                                            Page {currentPage} of {totalPages}
                                        </span>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => goToPage(currentPage + 1)}
                                            disabled={currentPage === totalPages}
                                            className="h-8 w-8 p-0"
                                        >
                                            <ChevronRight className="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => goToPage(totalPages)}
                                            disabled={currentPage === totalPages}
                                            className="h-8 w-8 p-0"
                                        >
                                            <ChevronsRight className="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs text-gray-600 dark:text-gray-400">Go to:</span>
                                        <Input
                                            type="number"
                                            min="1"
                                            max={totalPages}
                                            value={currentPage}
                                            onChange={(e) => {
                                                const page = parseInt(e.target.value);
                                                if (page >= 1 && page <= totalPages) {
                                                    goToPage(page);
                                                }
                                            }}
                                            className="h-8 w-16 text-center text-xs"
                                        />
                                    </div>
                                </div>
                            )}

                            <div className="mt-4 space-y-5 border-t border-gray-200 pt-4 text-center sm:flex-row sm:items-center dark:border-gray-700">
                                <div className="text-xs text-gray-600 dark:text-gray-400">
                                    <p>
                                        Only <strong>valid</strong> rows can be selected for conversion. A separate file will be generated for{' '}
                                        <strong>invalid</strong> rows.
                                    </p>
                                    {summary.selected > 0 && summary.selected < summary.valid && (
                                        <p className="mt-1 text-orange-600 dark:text-orange-400">
                                            You're converting {summary.selected.toLocaleString()} of {summary.valid.toLocaleString()} valid rows.
                                        </p>
                                    )}
                                    {summary.selected === summary.valid && summary.valid > 0 && (
                                        <>
                                            <span className="mt-1 text-green-600 dark:text-green-400">All valid rows are selected. </span>
                                            <span className="mt-1 text-yellow-600 dark:text-yellow-400">
                                                Once valid rows are generated, invalid rows will be selected automatically.
                                            </span>
                                        </>
                                    )}
                                </div>
                                <Button
                                    onClick={handleConvert}
                                    disabled={summary.selected === 0 || committing}
                                    className="bg-linear-to-r from-blue-900 to-blue-800 px-6 py-2 text-sm text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                >
                                    {committing ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Converting...
                                        </>
                                    ) : (
                                        `Generate File`
                                    )}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            )}
        </AppLayout>
    );
}
