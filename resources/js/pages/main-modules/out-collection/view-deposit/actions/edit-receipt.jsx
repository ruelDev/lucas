/* eslint-disable react/prop-types */
import CurrencyInput from '@/components/currency-input';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import useSwalOptions from '@/hooks/useSwalOptions';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';
import { ArDate } from '../partials/ar-date';
import { InfoRow } from '../partials/info-row';
import { MakerRow } from '../partials/maker-row';
import { NpaBadge } from '../partials/npa-badge';
import { Reason } from '../partials/reason';

export default function EditReceipt({ payments }) {
    const ReactSwal = withReactContent(Swal);

    const breadcrumbs = [
        {
            title: 'Out Collection',
            href: '/out-collection',
        },
        {
            title: 'View Deposit',
            href: `/out-collection/${payments.deposit_id}/view-deposit`,
        },
        {
            title: 'Edit Payment',
        },
    ];

    const { data, setData, put, reset, processing, errors } = useForm({
        deposit_id: payments?.deposit_id,
        makerId: payments?.makerId,
        makerName: payments?.makerName,
        agreementNumber: payments?.agreementNumber,
        referenceNumber: payments?.referenceNumber,
        misNumber: payments?.misNumber,
        customerName: payments?.customerName,
        npaStage: payments?.npaStage,
        aoc: payments?.aoc,
        paymentType: 'CASH',
        arNumber: payments?.arNumber,
        arAmount: payments?.arAmount,
        arDate: payments?.arDate,
        reason: payments?.reason,
        status: payments?.status,
        source: payments?.source,
        company: payments?.company,
    });

    const { swalLoading, onSuccess } = useSwalOptions(reset);

    const onSubmit = async (e) => {
        e.preventDefault();

        if (isNaN(data.arAmount)) {
            ReactSwal.fire({
                title: 'Invalid Amount',
                text: 'Please enter a valid payment amount.',
                icon: 'error',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        const result = await ReactSwal.fire({
            title: 'Are you sure you want to update payment?',
            text: undefined,
            icon: 'info',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, update it!',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            put(route('out-collection.view-deposit.update-payment', { deposit: payments.deposit_id, payment: payments.id }), {
                onStart: () => swalLoading('Updating'),
                onSuccess: (res) => onSuccess(res),
                onError: () => ReactSwal.close(),
            });
        }
    };

    const handleCancel = () => {
        ReactSwal.fire({
            title: 'Discard changes?',
            text: 'Any unsaved data will be lost.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel',
            cancelButtonText: 'Stay',
            confirmButtonColor: '#1B4298',
        }).then((result) => {
            if (result.isConfirmed) {
                router.visit(`/out-collection/${payments.deposit_id}/view-deposit`);
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Out Collection - Edit Payment" />

            <div className="p-6">
                <Card className="overflow-hidden pb-0">
                    {/* ── Card header ── */}
                    <div className="flex items-center gap-3 border-b px-6 py-4">
                        <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950">
                            <Pencil className="size-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p className="text-sm leading-none font-medium">Edit payment</p>
                            <p className="text-muted-foreground mt-0.5 text-xs">Acknowledgement receipt</p>
                        </div>
                    </div>
                    <CardContent className="p-0">
                        <form onSubmit={onSubmit}>
                            {/* ── Two-panel body ── */}
                            <div className="flex divide-x">
                                {/* Left panel — read-only loan details */}
                                <div className="w-1/2 px-6 py-5">
                                    <p className="text-muted-foreground mb-4 text-[11px] font-medium tracking-widest uppercase">Loan details</p>

                                    <MakerRow name={payments.makerName} makerId={payments.makerId} />

                                    <InfoRow label="Agreement number" value={payments.agreementNumber} />
                                    <InfoRow label="Reference number" value={payments.referenceNumber} />
                                    <InfoRow label="Customer name" value={payments.customerName} />
                                    <InfoRow label="MIS number" value={payments.misNumber} />
                                    <InfoRow label="NPA stage" value={<NpaBadge stage={payments.npaStage} />} />
                                    <InfoRow label="AOC" value={payments.aoc} />
                                    <InfoRow label="Source" value={payments.source} />
                                </div>
                                {/* Right panel — editable receipt fields */}
                                <div className="w-1/2 px-6 py-5">
                                    <p className="text-muted-foreground mb-4 text-[11px] font-medium tracking-widest uppercase">
                                        Receipt information
                                    </p>

                                    <div className="space-y-4">
                                        {/* AR Date */}
                                        <div className="space-y-1.5">
                                            <ArDate value={data.arDate} onChange={(date) => setData('arDate', date)} />
                                            <InputError message={errors.arDate} />
                                        </div>

                                        {/* AR Number */}
                                        <div className="space-y-1.5">
                                            <Label htmlFor="arNumber" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                                                AR number
                                            </Label>
                                            <Input
                                                id="arNumber"
                                                type="number"
                                                value={data.arNumber}
                                                onChange={(e) => setData('arNumber', e.target.value)}
                                                placeholder="AR Number"
                                            />
                                            <InputError message={errors.arNumber} />
                                        </div>

                                        {/* AR Amount */}
                                        <div className="space-y-1.5">
                                            <Label htmlFor="arAmount" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                                                AR amount
                                            </Label>
                                            <CurrencyInput
                                                id="arAmount"
                                                value={data.arAmount}
                                                onChange={(val) => setData((prev) => ({ ...prev, arAmount: val }))}
                                                placeholder="AR Amount"
                                                className="w-full rounded-md border px-3 py-2 dark:bg-neutral-900"
                                            />
                                            <InputError message={errors.arAmount} />
                                        </div>

                                        {/* Reason */}
                                        <div className="space-y-1.5">
                                            <Label className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">Reason</Label>
                                            <Reason value={data.reason} onValueChange={(value) => setData('reason', value)} />
                                            <InputError message={errors.reason} />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* ── Footer actions ── */}
                            <div className="bg-muted/40 flex justify-end gap-2 border-t px-6 py-4">
                                <Button type="button" variant="outline" onClick={handleCancel} disabled={processing}>
                                    Cancel
                                </Button>
                                <Button type="submit" className="submit-button" disabled={processing}>
                                    {processing && <Spinner />}
                                    Update
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
