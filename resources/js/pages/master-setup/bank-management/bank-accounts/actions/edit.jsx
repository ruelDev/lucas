/* eslint-disable react/prop-types */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import useSwalOptions from '@/hooks/useSwalOptions';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Landmark } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function Edit({ bank, bankAccount }) {
    const breadcrumbs = [
        {
            title: 'Bank Management',
            href: '/bank-management',
        },
        {
            title: 'Bank Accounts',
            href: `/bank-management/${bank.id}/bank-accounts`,
        },
        {
            title: 'Edit Bank Account',
        },
    ];

    const ReactSwal = withReactContent(Swal);

    const { data, setData, put, reset, processing, errors } = useForm({
        id: bankAccount.id,
        bank_id: bankAccount.bank_id,
        account_number: bankAccount.account_number,
        depository_remarks: bankAccount.depository_remarks,
    });

    const { swalLoading, onSuccess } = useSwalOptions(reset);

    const handleSubmit = async (e) => {
        e.preventDefault();

        put(route('bank-management.bank-accounts.update', { bank: bank.id, bankAccount: bankAccount.id }), {
            preserveScroll: true,
            preserveState: true,
            onStart: () => swalLoading('Saving'),
            onSuccess: (res) => {
                onSuccess(res);
            },
            onError: () => ReactSwal.close(),
        });
    };

    const bankName = `${bank.name} - ${bank.abbreviation}`;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Bank Account" />

            <div className="mx-auto w-full max-w-3xl p-6">
                <Link
                    href={route('bank-management.bank-accounts.index', { bank: bank.id })}
                    className="mb-4 inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-900"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Bank Accounts
                </Link>

                <Card className="p-0">
                    <CardHeader className="flex flex-row items-start gap-3 space-y-0">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <Landmark className="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle>Edit Bank Account</CardTitle>
                            <CardDescription>
                                Update the account details for <span className="font-semibold text-neutral-700">{bankName}</span>.
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="flex flex-col gap-5 pt-2">
                            <Field>
                                <Label htmlFor="account_number" className="after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Account Number
                                </Label>
                                <Input
                                    id="account_number"
                                    name="account_number"
                                    value={data.account_number}
                                    onChange={(e) => setData('account_number', e.target.value)}
                                    placeholder="Enter Account Number"
                                    autoFocus
                                />
                                <InputError message={errors.account_number} />
                            </Field>
                            <Field>
                                <Label htmlFor="depository_remarks" className="after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Bank Depository Remarks
                                </Label>
                                <Input
                                    id="depository_remarks"
                                    name="depository_remarks"
                                    value={data.depository_remarks}
                                    onChange={(e) => setData('depository_remarks', e.target.value)}
                                    placeholder="Enter Bank Depository Remarks"
                                />
                                <InputError message={errors.depository_remarks} />
                            </Field>
                        </FieldGroup>
                    </CardContent>

                    <CardFooter className="flex justify-between gap-2 border-t bg-neutral-50/50 pt-6">
                        <Button variant="outline" asChild>
                            <Link href={route('bank-management.bank-accounts.index', { bank: bank.id })}>Cancel</Link>
                        </Button>
                        <Button onClick={handleSubmit} disabled={processing} className="submit-button">
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
