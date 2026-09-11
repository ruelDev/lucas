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
import { format } from 'date-fns';
import { HandCoins } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';
import { ArDate } from '../partials/ar-date';
import { InfoRow } from '../partials/info-row';
import { MakerRow } from '../partials/maker-row';
import { Reason } from '../partials/reason';
import { Source } from '../partials/source';

export default function CreateUnappliedReceipt({ accountDetails }) {
    const ReactSwal = withReactContent(Swal);

    const breadcrumbs = [
        {
            title: 'Out Collection',
            href: '/out-collection',
        },
        {
            title: 'View Deposit',
            href: `/out-collection/${accountDetails.id}/view-deposit`,
        },
        {
            title: 'Unapplied Receipt',
        },
    ];

    const { data, setData, post, reset, processing, errors } = useForm({
        deposit_id: accountDetails?.id,
        makerId: accountDetails?.makerId,
        makerName: accountDetails?.makerName,
        referenceNumber: accountDetails?.referenceNumber,
        agreementNumber: accountDetails?.agreementNumber,
        misNumber: accountDetails?.misNumber,
        customerName: accountDetails?.customerName,
        aoc: accountDetails?.aoc,
        paymentType: 'CASH',
        npaStage: accountDetails?.npaStage,
        arDate: format(new Date(), 'yyyy-MM-dd'),
        arNumber: '',
        arAmount: '',
        reason: '',
        status: 'Draft',
        source: accountDetails?.source,
        company: accountDetails?.company,
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

        const hasDeposits = accountDetails.hasDeposits;
        const newTotal = accountDetails.total + Number(data.arAmount);
        const exceedsLimit = newTotal > accountDetails.depositAmount;

        const getConfirmationMessage = () => {
            if (!hasDeposits) {
                return {
                    title: 'Are you sure you want to submit new payment?',
                    text: undefined,
                    icon: 'info',
                };
            }

            if (exceedsLimit) {
                return {
                    title: 'Are you sure you want to save?',
                    text: 'The payment exceeds the declared total amount.',
                    icon: 'warning',
                };
            }

            return {
                title: 'Are you sure you want to submit new payment?',
                text: undefined,
                icon: 'info',
            };
        };

        const message = getConfirmationMessage();

        const result = await ReactSwal.fire({
            title: message.title,
            text: message.text,
            icon: message.icon,
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Yes, submit it!',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            post(route('out-collection.view-deposit.unapplied-receipt.store', accountDetails.id), {
                onStart: () => swalLoading('Creating'),
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
                router.visit(`/out-collection/${accountDetails.id}/view-deposit`);
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Out Collection - New Payment (Unapplied Receipt)" />

            <div className="p-6">
                <Card className="overflow-hidden pb-0">
                    {/* ── Card header ── */}
                    <div className="flex items-center gap-3 border-b px-6 py-4">
                        <div className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950">
                            <HandCoins className="size-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p className="text-sm leading-none font-medium">New payment</p>
                            <p className="text-muted-foreground mt-0.5 text-xs">Unapplied receipt</p>
                        </div>
                    </div>

                    <CardContent className="p-0">
                        <form onSubmit={onSubmit}>
                            {/* ── Two-panel body ── */}
                            <div className="flex divide-x">
                                {/* Left panel — read-only loan details */}
                                <div className="w-1/2 px-6 py-5">
                                    <p className="text-muted-foreground mb-4 text-[11px] font-medium tracking-widest uppercase">Loan details</p>

                                    <MakerRow name={accountDetails.makerName} makerId={accountDetails.makerId} />

                                    <InfoRow label="Agreement number" value={accountDetails.agreementNumber} />

                                    <div className="space-y-4">
                                        {/* MIS Number */}
                                        {accountDetails.misNumber ? (
                                            <InfoRow label="MIS number" value={accountDetails.misNumber} />
                                        ) : (
                                            <div className="space-y-1.5">
                                                <Label htmlFor="misNumber" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    MIS number
                                                </Label>
                                                <Input
                                                    id="misNumber"
                                                    type="number"
                                                    value={data.misNumber}
                                                    onChange={(e) => setData('misNumber', e.target.value)}
                                                    placeholder="Enter mis number"
                                                />
                                                <InputError message={errors.misNumber} />
                                            </div>
                                        )}
                                        {/* Customer Name */}
                                        {accountDetails.customerName ? (
                                            <InfoRow label="Customer name" value={accountDetails.customerName} />
                                        ) : (
                                            <div className="space-y-1.5">
                                                <Label htmlFor="customerName" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    Customer name
                                                </Label>
                                                <Input
                                                    id="customerName"
                                                    type="text"
                                                    value={data.customerName}
                                                    onChange={(e) => setData('customerName', e.target.value)}
                                                    placeholder="Enter customer name"
                                                />
                                                <InputError message={errors.customerName} />
                                            </div>
                                        )}
                                        {/* AOC */}
                                        {accountDetails.aoc ? (
                                            <InfoRow label="AOC" value={accountDetails.aoc} />
                                        ) : (
                                            <div className="space-y-1.5">
                                                <Label htmlFor="aoc" className="text-xs after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    AOC
                                                </Label>
                                                <Input
                                                    id="aoc"
                                                    type="text"
                                                    value={data.aoc}
                                                    onChange={(e) => setData('aoc', e.target.value)}
                                                    placeholder="Enter aoc"
                                                />
                                                <InputError message={errors.aoc} />
                                            </div>
                                        )}
                                        {/* Source */}
                                        {accountDetails.source ? (
                                            <InfoRow label="Source" value={accountDetails.source} />
                                        ) : (
                                            <div className="space-y-1.5">
                                                <Label className="after:ml-0.5 after:text-red-500 after:content-['*']">Source:</Label>
                                                <Source value={data.source} onValueChange={(value) => setData('source', value)} />
                                                <InputError className="col-span-2 col-end-4 mt-2" message={errors.source} />
                                            </div>
                                        )}
                                    </div>
                                </div>
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
                                    Submit
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
