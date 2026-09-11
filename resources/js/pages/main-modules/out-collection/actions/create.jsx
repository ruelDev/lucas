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
import { Check, CloudUpload, FileText, Info, Landmark, Paperclip } from 'lucide-react';
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
        title: 'Add New Deposit',
    },
];

export default function Create({ banks }) {
    const [selectedBankId, setSelectedBankId] = React.useState('');
    const ReactSwal = withReactContent(Swal);

    const { data, setData, post, reset, processing, errors } = useForm({
        bank_id: '',
        depositSlip: null,
        depositDate: '',
        depositAmount: '',
        depositCharge: '',
        referenceNumber: '',
        depositoryRemarks: '',
    });

    const { swalLoading, onSuccess } = useSwalOptions(reset);

    const handleSubmit = (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: 'Confirm submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new deposit?</p>`,
            icon: 'question',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm submit',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                post(route('out-collection.store'), {
                    preserveState: true,
                    preserveScroll: true,
                    onStart: () => swalLoading('Creating'),
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
            <Head title="Add New Deposit" />

            <div className="m-6">
                {/* Page header */}
                <div className="mb-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-lg font-medium">New deposit</h1>
                        <p className="text-muted-foreground text-sm">Fill in all required fields to record a deposit.</p>
                    </div>
                    <span className="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                        Creating
                    </span>
                </div>

                {/* Hint bar */}
                <div className="mb-4 flex items-center gap-2 rounded-md border border-l-4 border-blue-200 border-l-blue-500 bg-blue-50 px-3 py-2 text-xs text-blue-700 dark:border-blue-800 dark:border-l-blue-500 dark:bg-blue-950 dark:text-blue-300">
                    <Info className="size-4" />
                    Select a bank first — depository remarks will filter to that bank's accounts.
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
                    <SectionCard step={3} icon={Paperclip} title="Deposit slip" description="Attach a scanned copy of the deposit slip">
                        <label
                            className="flex cursor-pointer flex-col items-center gap-2 rounded-md border border-dashed p-6 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-900"
                            onDragEnter={(e) => e.preventDefault()}
                            onDragOver={(e) => e.preventDefault()}
                            onDrop={(e) => {
                                e.preventDefault();
                                const file = e.dataTransfer.files[0];
                                if (file) setData('depositSlip', file);
                            }}
                        >
                            <CloudUpload size={32} className="text-muted-foreground" />
                            <span className="text-sm font-medium">Click to upload or drag and drop</span>
                            <span className="text-muted-foreground text-xs">PNG, JPG, PDF — max 10 MB</span>
                            <Input
                                type="file"
                                accept="image/*,.pdf"
                                className="hidden"
                                onChange={(e) => {
                                    if (e.target.files?.length) {
                                        setData('depositSlip', e.target.files[0]);
                                    }
                                }}
                            />
                        </label>
                        {data.depositSlip && (
                            <p className="mt-2 flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400">
                                <Check className="h-3 w-3" />
                                {data.depositSlip.name}
                            </p>
                        )}
                        <InputError message={errors.depositSlip} />
                    </SectionCard>

                    {/* Footer actions */}
                    <div className="flex items-center justify-between rounded-lg border bg-neutral-50 px-4 py-3 dark:bg-neutral-900">
                        <Button type="button" variant="outline" asChild>
                            <Link href={route('out-collection.index')}>Cancel</Link>
                        </Button>
                        <div className="flex items-center gap-3">
                            <span className="text-muted-foreground text-xs">3 sections · 6 fields</span>
                            <Button type="submit" disabled={processing} className="submit-button gap-1.5">
                                <Check className="h-4 w-4" />
                                Save deposit
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
