import axios from 'axios'
import { useState } from 'react';
import Swal from "sweetalert2";
import withReactContent from "sweetalert2-react-content";

const ReactSwal = withReactContent(Swal)

export function useReceiptConversion({ rows, source, file, reset }) {
    const [committing, setCommitting] = useState(false);

    const exportFile = (fileData) => {
        const blob = new Blob([fileData.data], {
            type: fileData.headers['content-type'] || 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;

        // Extract filename from Content-Disposition header if available
        const contentDisposition = fileData.headers['content-disposition'];
        let filename = `receipts_${source}_${Date.now()}.xlsx`;

        if (contentDisposition) {
            const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
            if (filenameMatch?.[1]) {
                filename = filenameMatch[1].replace(/['"]/g, '');
            }
        }

        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);

        return filename;
    }

    const processInvalidRows = async (invalidData, originalFilename) => {
        ReactSwal.fire({
            title: 'Converting Invalid Rows...',
            html: `
            <div style="text-align: center; padding: 20px;">
                <div style="margin-bottom: 15px;">
                    <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-solid border-orange-600 border-r-transparent"></div>
                </div>
                <p style="margin-bottom: 10px;">Processing <strong>${invalidData.length.toLocaleString()}</strong> invalid rows</p>
                <p style="color: var(--muted-foreground); font-size: 0.875rem;">Please wait while we generate your file...</p>
                <p style="color: var(--muted-foreground); font-size: 0.875rem; margin-top: 10px;">Do not close or refresh this page</p>
            </div>
        `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            background: 'var(--background)',
            color: 'var(--foreground)',
        });

        try {
            const response = await axios.post(
                '/receipt-converter/bulkConvert',
                {
                    rows: invalidData,
                    source: source,
                    type: 'invalid',
                    originalFilename: originalFilename,
                },
                {
                    responseType: 'blob',
                    timeout: 600000,
                },
            );

            exportFile(response);

            await ReactSwal.fire({
                icon: 'success',
                iconColor: '#1B4298',
                title: 'All Conversions Complete!',
                html: `
                <div style="text-align: center; padding: 10px;">
                    <p style="margin-bottom: 15px;">Both files have been downloaded successfully</p>
                    <div style="background: #eff6ff; padding: 12px; border-radius: 6px; margin-bottom: 10px; border-left: 4px solid #3b82f6;">
                        <p style="color: #1e40af; font-weight: 600; margin-bottom: 3px;">✓ Valid Rows</p>
                        <p style="color: #1e40af; font-size: 0.875rem;">Downloaded successfully</p>
                    </div>
                    <div style="background: #fff7ed; padding: 12px; border-radius: 6px; border-left: 4px solid #f97316;">
                        <p style="color: #9a3412; font-weight: 600; margin-bottom: 3px;">✓ Invalid Rows</p>
                        <p style="color: #9a3412; font-size: 0.875rem;"><strong>${invalidData.length.toLocaleString()}</strong> rows converted</p>
                    </div>
                </div>
            `,
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: true,
                confirmButtonText: 'Done',
                background: 'var(--background)',
                confirmButtonColor: '#1B4298',
                color: 'var(--foreground)',
            });
        } catch (err) {
            console.error('Invalid rows export error:', err);
            const msg = err?.response?.data?.error ?? err?.message ?? 'Something went wrong during invalid rows export.';

            ReactSwal.fire({
                icon: 'error',
                title: 'Invalid Rows Export Failed',
                html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin-bottom: 10px;"><strong>Error:</strong> ${msg}</p>
                    <p style="color: #22c55e; margin-top: 15px;">✓ Valid rows were exported successfully</p>
                    <p style="color: var(--muted-foreground); font-size: 0.875rem; margin-top: 10px;">Please try again or contact support if the issue persists</p>
                </div>
            `,
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#1B4298',
                background: 'var(--background)',
                color: 'var(--foreground)',
            });
        }
    }

    const handleConvert = async () => {
        const validData = rows.filter((r) => r.valid && r.checked);
        const invalidData = rows.filter((r) => !r.valid);

        const category = 'valid';
        const originalFilename = file?.name;

        if (!validData.length) {
            ReactSwal.fire({
                icon: 'info',
                iconColor: '#1B4298',
                title: 'Nothing to convert',
                text: 'Select at least one valid row to convert',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        const { isConfirmed } = await ReactSwal.fire({
            title: `Generate file with ${validData.length.toLocaleString()} converted rows?`,
            html: `
            <div style="text-align: left; padding: 10px;">
                <p>This will convert <strong>${validData.length.toLocaleString()}</strong> ${category} rows to ${source} format.</p>
                ${validData.length > 1000 ? '<p style="color: orange; margin-top: 10px">Large Dataset: This may take several minutes</p>' : ''}
                <p style="margin-top: 10px;">Do not close this window during this process</p>
            </div>
        `,
            icon: 'question',
            iconColor: '#1B4298',
            showCancelButton: true,
            confirmButtonText: 'Yes, convert',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#1B4298',
            reverseButtons: true,
            background: 'var(--background)',
            color: 'var(--foreground)',
        });

        if (!isConfirmed) return;

        setCommitting(true);

        ReactSwal.fire({
            title: 'Converting...',
            html: `
            <div style="text-align: center; padding: 20px;">
                <div style="margin-bottom: 15px;">
                    <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-solid border-blue-600 border-r-transparent"></div>
                </div>
                <p style="margin-bottom: 10px;">Processing <strong>${validData.length.toLocaleString()}</strong> rows</p>
                <p style="color: var(--muted-foreground); font-size: 0.875rem;">Please wait while we generate your file...</p>
                <p style="color: var(--muted-foreground); font-size: 0.875rem; margin-top: 10px;">Do not close or refresh this page</p>
            </div>
        `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            background: 'var(--background)',
            color: 'var(--foreground)',
        });

        try {
            const response = await axios.post(
                '/receipt-converter/bulkConvert',
                {
                    rows: validData,
                    source: source,
                    type: category,
                    originalFilename: originalFilename,
                },
                {
                    responseType: 'blob',
                    timeout: 600000,
                },
            );

            const filename = exportFile(response);

            if (invalidData.length > 0) {
                // Close loading and show success
                await ReactSwal.fire({
                    icon: 'success',
                    title: 'Valid Rows Converted!',
                    html: `
                    <div style="text-align: center; padding: 10px;">
                        <p style="margin-bottom: 10px;">Your valid rows file has been downloaded successfully</p>
                        <p style="color: var(--muted-foreground); font-size: 0.875rem;"><strong>Filename:</strong> ${filename}</p>
                        <p style="color: var(--muted-foreground); font-size: 0.875rem;"><strong>Rows converted:</strong> ${validData.length.toLocaleString()}</p>
                        <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <p style="color: #1e40af; font-weight: 600; margin-bottom: 5px;">⏳ Processing Invalid Rows Next</p>
                            <p style="color: #1e40af; font-size: 0.875rem;">Converting ${invalidData.length.toLocaleString()} invalid rows...</p>
                        </div>
                    </div>
                `,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    background: 'var(--background)',
                    color: 'var(--foreground)',
                });

                await processInvalidRows(invalidData, originalFilename);
            } else {
                // Close loading and show success
                await ReactSwal.fire({
                    icon: 'success',
                    title: 'Conversion Complete!',
                    html: `
                <div style="text-align: center; padding: 10px;">
                    <p style="margin-bottom: 10px;">Your file has been downloaded successfully</p>
                    <p style="color: var(--muted-foreground); font-size: 0.875rem;"><strong>Filename:</strong> ${filename}</p>
                    <p style="color: var(--muted-foreground); font-size: 0.875rem;"><strong>Rows converted:</strong> ${validData.length.toLocaleString()}</p>
                </div>
            `,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: true,
                    confirmButtonColor: '#1B4298',
                    confirmButtonText: 'Done',
                    background: 'var(--background)',
                    color: 'var(--foreground)',
                });
            }

            reset();
        } catch (err) {
            console.error('Export error:', err);
            const msg = err?.response?.data?.error ?? err?.message ?? 'Something went wrong during export.';

            // Close loading and show error
            ReactSwal.fire({
                icon: 'error',
                title: 'Export Failed',
                html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin-bottom: 10px;"><strong>Error:</strong> ${msg}</p>
                    <p style="color: var(--muted-foreground); font-size: 0.875rem;">Please try again or contact support if the issue persists</p>
                </div>
            `,
                showConfirmButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: '#1B4298',
                background: 'var(--background)',
                color: 'var(--foreground)',
            });
        } finally {
            setCommitting(false);
        }
    }

    return {
        committing,
        handleConvert
    }
}
