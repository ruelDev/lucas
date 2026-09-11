/* eslint-disable react/prop-types */
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { useState } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function ForgotPassword({ status }) {
    const { props } = usePage();
    const alertDialog = props.alertDialog || {};

    const [verifyIdDialogOpen, setVerifyIdDialogOpen] = useState(false);
    const [verifiedData, setVerifiedData] = useState(null);

    const ReactSwal = withReactContent(Swal);

    const { data, setData, post, processing, errors } = useForm({
        employee_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'), {
            onSuccess: (page) => {
                const result = page.props.alertDialog?.showVerifyEidModal;

                if (result === 'none') {
                    ReactSwal.fire({
                        title: 'User not found',
                        text: 'The Employee ID might be incorrect or the user does not exist. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'Ok, got it!',
                        confirmButtonColor: '#1B4298',
                    });
                } else if (result) {
                    setVerifiedData(result);
                    setVerifyIdDialogOpen(true);
                }
            },
        });
    };

    const closeVerifiedDialog = (e) => {
        setData('employee_id', '');
        setVerifyIdDialogOpen(false);
    };

    const sendPasswordResetMail = (e) => {
        e.preventDefault();
        post(route('send.password.reset.mail'), {
            user: alertDialog.showVerifyEidModal,
            preserveState: true,
            preserveScroll: true,
            onSuccess: (response) => {
                ReactSwal.fire({
                    title: 'Success',
                    text: response.props.flash.success,
                    icon: 'success',
                    iconColor: '#1B4298',
                    confirmButtonText: 'Proceed to Login',
                    confirmButtonColor: '#1B4298',
                }).then((result) => {
                    if (result.isConfirmed) {
                        router.get(route('login'));
                    }
                });
            },
            onError: () => {
                ReactSwal.fire({
                    title: 'Error',
                    text: 'Failed to submit request. Please Try again',
                    icon: 'error',
                    confirmationButtonText: 'OK',
                    confirmButtonColor: '#1B4298',
                });
            },
            onFinish: () => {
                closeVerifiedDialog();
            },
        });
    };

    return (
        <AuthLayout title="Forgot password" description="Enter your Employee ID to receive a password reset link">
            <Head title="Forgot password" />

            {/* <Toaster position="top-right" richColors theme="system" /> */}

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="space-y-6">
                <form onSubmit={submit}>
                    <div className="grid gap-2">
                        <Label htmlFor="employee_id">Employee ID</Label>
                        <Input
                            id="employee_id"
                            type="text"
                            name="employee_id"
                            autoComplete="off"
                            value={data.employee_id}
                            autoFocus
                            onChange={(e) => setData('employee_id', e.target.value)}
                        />

                        <InputError message={errors.employee_id} />
                    </div>

                    <div className="my-6 flex items-center justify-start">
                        <Button
                            className="w-full bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                            disabled={processing}
                        >
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Verify Employee ID
                        </Button>
                    </div>
                </form>

                <div className="text-muted-foreground space-x-1 text-center text-sm">
                    <span>Or, return to</span>
                    <TextLink href={route('login')}>log in</TextLink>
                </div>
            </div>

            {verifiedData && (
                <AlertDialog open={verifyIdDialogOpen} onOpenChange={setVerifyIdDialogOpen}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Check Employee Details</AlertDialogTitle>
                            <AlertDialogDescription>Please Check and Confirm the following Employee Details</AlertDialogDescription>
                            <hr />
                            <AlertDialogDescription>
                                Employee ID: <span className="font-medium">{verifiedData.employee_id}</span>
                            </AlertDialogDescription>
                            <AlertDialogDescription>
                                Name: <span>{`${verifiedData.fname} ${verifiedData.mname || ''} ${verifiedData.lname}`}</span>
                            </AlertDialogDescription>
                            <AlertDialogDescription>Email: {verifiedData.email}</AlertDialogDescription>
                            <span className="mt-5 text-sm text-red-500 italic">
                                Note: If you did not receive an email, please contact SAPS for assistance to your account.
                            </span>
                            <hr />
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <form onSubmit={sendPasswordResetMail} className="space-x-2">
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction
                                    className="ml-1 bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                    type="submit"
                                    disabled={processing}
                                >
                                    {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                    {processing ? 'Resetting...' : 'Send Email Request'}
                                </AlertDialogAction>
                            </form>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            )}
        </AuthLayout>
    );
}
