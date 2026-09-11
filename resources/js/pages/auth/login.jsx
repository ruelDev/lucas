/* eslint-disable react/prop-types */
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Alert, AlertTitle } from '@/components/ui/alert';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import AuthLayout from '@/layouts/auth-layout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AlertTriangleIcon, InfoIcon, LoaderCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function Login({ appVersion, canResetPassword, status, ssoUnavailable = false }) {
    const currentVersion = appVersion[0] ?? null;
    const previousVersions = appVersion.slice(1);

    const { props } = usePage();
    const alertDialog = props.alertDialog || {};
    const sessionTimeoutError = props.flash?.error;

    const [warningDialogOpen, setWarningDialogOpen] = useState(false);
    const [lockDialogOpen, setLockDialogOpen] = useState(false);
    const [multiSessionDialogOpen, setMultiSessionDialogOpen] = useState(false);
    const [releaseNotesDialogOpen, setReleaseNotesDialogOpen] = useState(false);

    useEffect(() => {
        if (sessionTimeoutError) {
            Swal.fire({
                icon: 'warning',
                title: 'Session Expired',
                text: sessionTimeoutError,
            })
        }
    }, [sessionTimeoutError])

    useEffect(() => {
        if (alertDialog.showAccountWarningModal) {
            setWarningDialogOpen(true);
        }
    }, [alertDialog.showAccountWarningModal]);

    useEffect(() => {
        if (alertDialog.showAccountLockedModal) {
            setLockDialogOpen(true);
        }
    }, [alertDialog.showAccountLockedModal]);

    useEffect(() => {
        if (alertDialog.showMultiSessionModal) {
            setMultiSessionDialogOpen(true);
        }
    }, [alertDialog.showMultiSessionModal]);

    const dateFormat = (datetime) => {
        return new Date(datetime).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        employee_id: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    const killSession = (e) => {
        e.preventDefault();
        post(route('kill-session'));
    };

    return (
        <AuthLayout title="Log in to your account" description="Enter your employee id and password below to log in">
            <Head title="Log in" />

            {ssoUnavailable &&
                <div className="grid w-full max-w-md items-start gap-4 mb-8">
                    <Alert className="flex items-center gap-2 max-w-md border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-50">
                        <div className="text-amber-900">
                            <AlertTriangleIcon size={15}/>
                        </div>
                        <AlertTitle className="mb-0">SSO Portal Temporarily Unavailable</AlertTitle>
                    </Alert>
                </div>
            }

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="employee_id">Employee ID</Label>
                        <Input
                            id="employee_id"
                            required
                            autoFocus
                            autoComplete="employee_id"
                            value={data.employee_id}
                            onChange={(e) => setData('employee_id', e.target.value)}
                        />
                        <InputError message={errors.employee_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <PasswordInput
                            id="password"
                            required
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Password"
                        />
                        <div className="flex justify-end">
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ml-auto text-sm">
                                    Forgot password?
                                </TextLink>
                            )}
                        </div>
                    </div>

                    <Button
                        type="submit"
                        className="mt-2 h-10 w-full bg-linear-to-r from-blue-900 to-blue-800 px-4 py-2 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                        disabled={processing}
                    >
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Log in
                    </Button>
                </div>

                {currentVersion && (
                    <div className="mt-6 text-center text-xs">
                        © Bank of Makati 2026 • V{currentVersion.version} •{' '}
                        <button onClick={() => setReleaseNotesDialogOpen(true)} className="font-semibold text-blue-800 hover:underline">
                            What's New
                        </button>
                    </div>
                )}
            </form>

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

            <AlertDialog open={warningDialogOpen} onOpenChange={setWarningDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Account Attempt Warning</AlertDialogTitle>
                        <AlertDialogDescription>
                            You've already attempted to log in to your account {alertDialog.failedAttempts ?? 3} times. Your Account will be locked after {alertDialog.remainingAttempts ?? 2} more wrong {(alertDialog.remainingAttempts ?? 2) === 1 ? 'attempt' : 'attempts'}.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogAction className="bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                            Continue
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog open={lockDialogOpen} onOpenChange={setLockDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Account Locked</AlertDialogTitle>
                        <AlertDialogDescription>
                            Your account is now locked. Please Contact <strong>SAPS</strong> for Inquiry and Assistance in recovering your account.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogAction className="bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                            Continue
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog open={multiSessionDialogOpen} onOpenChange={setMultiSessionDialogOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Multiple Session Detected</AlertDialogTitle>
                        <AlertDialogDescription>
                            Your account has been detected to have multiple session from multiple browsers/devices. Would you like to log out on other
                            devices and proceed?
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <form onSubmit={killSession} className="space-x-2">
                            <AlertDialogCancel>Don't Proceed</AlertDialogCancel>
                            <AlertDialogAction
                                className="ml-1 bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                type="submit"
                                disabled={processing}
                            >
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                Yes, Log out on other devices
                            </AlertDialogAction>
                        </form>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <AlertDialog open={releaseNotesDialogOpen && !!currentVersion} onOpenChange={setReleaseNotesDialogOpen}>
                <AlertDialogContent className="max-w-7xl min-w-3xl">
                    {currentVersion && (<>
                    <AlertDialogHeader className="space-y-4">
                        <div>
                            <AlertDialogTitle className="text-xl font-bold text-neutral-800">What’s New</AlertDialogTitle>
                            <p className="text-muted-foreground text-sm">Latest application updates and improvements</p>
                        </div>

                        <Separator />
                        {/* Version Info Card */}
                        <div className="grid grid-cols-2 gap-6 text-sm">
                            <div className="space-y-1">
                                <p className="text-muted-foreground">Current Version</p>
                                <p className="font-semibold text-blue-800">v {currentVersion.version}</p>
                            </div>

                            <div className="space-y-1">
                                <p className="text-muted-foreground">Release Date</p>
                                <p className="font-semibold text-neutral-700">{dateFormat(currentVersion.released_at)}</p>
                            </div>
                        </div>
                    </AlertDialogHeader>
                    <ScrollArea className="bg-muted/30 h-100 rounded-lg border px-4 py-2">
                        {/* Current Release Notes Section */}
                        <div className="mt-6 space-y-4">
                            <div className="flex items-center justify-between">
                                <h4 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                                    {currentVersion.version} {currentVersion.release_type} Update Release Notes
                                </h4>
                            </div>

                            <div className="space-y-6 px-5">
                                {currentVersion.release_notes.map((note) => (
                                    <div key={note.id} className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <h5 className="text-sm font-semibold text-blue-800">{note.title}</h5>
                                            <Badge className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                                {note.category}
                                            </Badge>
                                        </div>

                                        <p className="text-muted-foreground text-sm leading-relaxed">{note.description}</p>

                                        <Separator />
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Previous App Versions Section */}
                        <div className="mt-8 space-y-3">
                            <div>
                                <div className="flex items-center justify-between">
                                    <h4 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                                        Previous Version Releases History
                                    </h4>
                                </div>
                            </div>

                            {previousVersions.map((version) => (
                                <>
                                    <Separator />
                                    <div key={version.id} className="text-muted-foreground">
                                        <div className="grid grid-cols-2 gap-6 text-sm">
                                            <div className="space-y-1">
                                                <p className="text-muted-foreground">Version</p>
                                                <p className="text-muted-foreground font-semibold">v {version.version}</p>
                                            </div>

                                            <div className="space-y-1">
                                                <p className="text-muted-foreground">Release Date</p>
                                                <p className="text-muted-foreground font-semibold">{dateFormat(version.released_at)}</p>
                                            </div>
                                        </div>

                                        {/* Previous Version Release Notes Section */}
                                        <div className="mt-6 space-y-4">
                                            <div className="flex items-center justify-between">
                                                <h4 className="text-muted-foreground text-sm font-semibold tracking-wide uppercase">
                                                    {version.version} {version.release_type} UPDATE Release Notes
                                                </h4>
                                            </div>
                                            <div className="space-y-6">
                                                {version.release_notes.map((note) => (
                                                    <div key={note.id} className="space-y-2 px-4">
                                                        <div className="flex items-center justify-between">
                                                            <h5 className="text-sm font-semibold">{note.title}</h5>
                                                            <Badge className="rounded-full bg-gray-400 px-2 py-0.5 text-xs font-medium">
                                                                {note.category}
                                                            </Badge>
                                                        </div>
                                                        <p className="text-muted-foreground text-xs leading-relaxed">{note.description}</p>
                                                        <Separator />
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                </>
                            ))}
                        </div>
                    </ScrollArea>

                    <AlertDialogFooter className="mt-3">
                        <AlertDialogAction className="bg-linear-to-r from-blue-900 to-blue-800 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                            Close
                        </AlertDialogAction>
                    </AlertDialogFooter>
                    </>)}
                </AlertDialogContent>
            </AlertDialog>
        </AuthLayout>
    );
}
