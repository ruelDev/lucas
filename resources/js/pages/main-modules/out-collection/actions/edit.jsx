/* eslint-disable react/prop-types */
import CurrencyInput from '@/components/currency-input';
import InputError from '@/components/input-error';
import SectionCard from '@/components/section-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import useSwalOptions from '@/hooks/useSwalOptions';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Check, CloudUpload, ExternalLinkIcon, FileText, FileTextIcon, Landmark, Paperclip } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';
import DepositDate from '../partials/deposit-date';
import SelectBank from '../partials/select-bank';
import SelectDepositoryRemarks from '../partials/select-depository-remarks';

const breadcrumbs = [
    {
        title: 'Out Collection',
        href: '/out-collection',
    },
    {
        title: 'Edit Deposit',
    },
];

export default function Edit({ deposit, banks }) {
    const [selectedBankId, setSelectedBankId] = React.useState(deposit.bank_id ? String(deposit.bank_id) : '');
    const ReactSwal = withReactContent(Swal);

    const { data, setData, post, reset, processing, errors } = useForm({
        _method: 'PUT',
        id: deposit.id,
        bank_id: deposit.bank_id,
        depositSlip: null,
        depositDate: deposit.depositDate,
        depositAmount: deposit.depositAmount,
        depositCharge: deposit.depositCharge,
        referenceNumber: deposit.referenceNumber,
        depositoryRemarks: deposit.depositoryRemarks,
    });

    const { swalLoading, onSuccess } = useSwalOptions(reset);

    const handleSubmit = (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: 'Confirm Update?',
            html: `<p style="font-size: 16px">Are you sure you want to update this deposit?</p>`,
            icon: 'question',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                post(route('out-collection.update', deposit.id), {
                    preserveState: true,
                    preserveScroll: true,
                    onStart: () => swalLoading('Updating'),
                    onSuccess: (res) => onSuccess(res),
                    onError: () => ReactSwal.close(),
                });
            }
        });
    };

    const handleSelectedBank = React.useCallback((id) => {
        setSelectedBankId(id);
        setData('bank_id', id);
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit" />
            <div className="m-6">
                {/* Page header */}
                <div className="mb-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-lg font-medium">Edit deposit</h1>
                        <p className="text-muted-foreground text-sm">Update the details for deposit #{deposit.id}.</p>
                    </div>
                    <span className="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600 dark:bg-amber-950 dark:text-amber-400">
                        Editing
                    </span>
                </div>

                <form onSubmit={handleSubmit}>
                    {/* Section 1 — Bank & account */}
                    <SectionCard step={1} icon={Landmark} title="Bank & account" description="Choose the receiving bank and depository account">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-1.5">
                                <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Bank
                                </Label>
                                <SelectBank banks={banks} selectedBankId={selectedBankId} onSelectedBank={handleSelectedBank} />
                                <InputError message={errors.bank_id} />
                            </div>
                            <div className="grid gap-1.5">
                                <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Depository remarks
                                </Label>
                                <SelectDepositoryRemarks
                                    banks={banks}
                                    selectedBankId={selectedBankId}
                                    value={data.depositoryRemarks}
                                    onChange={(val) => setData('depositoryRemarks', val)}
                                />
                                <InputError message={errors.depositoryRemarks} />
                            </div>
                        </div>
                    </SectionCard>

                    {/* Section 2 — Transaction details */}
                    <SectionCard step={2} icon={FileText} title="Transaction details" description="Reference number, amounts, and deposit date">
                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-1.5">
                                <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Reference number
                                </Label>
                                <Input
                                    type="text"
                                    placeholder="Enter reference number"
                                    value={data.referenceNumber}
                                    onChange={(e) => setData('referenceNumber', e.target.value)}
                                />
                                <InputError message={errors.referenceNumber} />
                            </div>
                            <div className="grid gap-1.5">
                                <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Deposit amount
                                </Label>
                                <CurrencyInput
                                    value={data.depositAmount}
                                    onChange={(val) => setData((prev) => ({ ...prev, depositAmount: val }))}
                                    placeholder="0.00"
                                    className="w-full rounded-md border px-3 py-2 dark:bg-neutral-900"
                                />
                                <InputError message={errors.depositAmount} />
                            </div>
                            <div className="grid gap-1.5">
                                <DepositDate value={data.depositDate} onChange={(date) => setData('depositDate', date)} />
                                <InputError message={errors.depositDate} />
                            </div>
                            <div className="grid gap-1.5">
                                <Label className="text-muted-foreground text-xs font-medium tracking-wide uppercase after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Deposit charge
                                </Label>
                                <CurrencyInput
                                    value={data.depositCharge}
                                    onChange={(val) => setData((prev) => ({ ...prev, depositCharge: val }))}
                                    placeholder="0.00"
                                    className="w-full rounded-md border px-3 py-2 dark:bg-neutral-900"
                                />
                                <InputError message={errors.depositCharge} />
                            </div>
                        </div>
                    </SectionCard>

                    {/* Section 3 — Deposit slip */}
                    <SectionCard step={3} icon={Paperclip} title="Deposit slip" description="Replace or keep the existing deposit slip">
                        <DepositSlipUpload existingFile={deposit} newFile={data.depositSlip} onChange={(file) => setData('depositSlip', file)} />
                        <InputError message={errors.depositSlip} />
                    </SectionCard>

                    {/* Footer actions */}
                    <div className="flex items-center justify-between rounded-lg border bg-neutral-50 px-4 py-3 dark:bg-neutral-900">
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('out-collection.index')}>Cancel</Link>
                        </Button>
                        <div className="flex items-center gap-3">
                            <span className="text-muted-foreground text-xs">3 sections · 5 fields</span>
                            <Button type="submit" disabled={processing} className="submit-button gap-1.5">
                                <Check className="h-4 w-4" />
                                Save changes
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}

function DepositSlipUpload({ existingFile, newFile, onChange }) {
    const handleDrop = (e) => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) onChange(file);
    };

    return (
        <div className="grid gap-3">
            {/* Existing file */}
            {existingFile?.depositSlip && !newFile && (
                <div className="flex items-center justify-between rounded-md border bg-neutral-50 px-3 py-2.5 dark:bg-neutral-900">
                    <div className="flex min-w-0 flex-1 items-center gap-2">
                        <FileTextIcon className="h-4 w-4 shrink-0 text-red-500" />
                        <span className="truncate text-sm">{existingFile.depositSlip.split('/').pop()}</span>
                        <span className="text-muted-foreground shrink-0 text-xs">Current file</span>
                    </div>
                    <a
                        href={route('out-collection.view-deposit.file', existingFile.id)}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="ml-3 flex shrink-0 items-center gap-1 text-xs text-blue-600 hover:underline dark:text-blue-400"
                    >
                        View <ExternalLinkIcon className="h-3 w-3" />
                    </a>
                </div>
            )}

            {/* Upload area */}
            <label
                className="flex cursor-pointer flex-col items-center gap-2 rounded-md border border-dashed p-6 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900"
                onDragEnter={(e) => e.preventDefault()}
                onDragOver={(e) => e.preventDefault()}
                onDrop={handleDrop}
            >
                <CloudUpload size={32} className="text-muted-foreground" />
                <span className="text-sm font-medium">
                    {existingFile?.depositSlip ? 'Click to replace file' : 'Click to upload or drag and drop'}
                </span>
                <span className="text-muted-foreground text-xs">PNG, JPG, PDF — max 10 MB</span>
                <Input
                    type="file"
                    accept="image/*,.pdf"
                    className="hidden"
                    onChange={(e) => {
                        if (e.target.files?.length) {
                            onChange(e.target.files[0]);
                        }
                    }}
                />
            </label>

            {/* New file confirmation */}
            {newFile && (
                <p className="flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400">
                    <Check className="h-3 w-3" />
                    New file selected: {newFile.name}
                </p>
            )}
        </div>
    );
}
