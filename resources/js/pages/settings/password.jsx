/* eslint-disable react/prop-types */
import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const breadcrumbs = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

export default function Password() {
    const { props } = usePage();
    const passwordStatus = props.resetPassword || {};
    const passwordInput = useRef(null);
    const currentPasswordInput = useRef(null);

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
        mandatory_change: passwordStatus.firstReset || passwordStatus.expiredReset,
    });

    const passwordRules = {
        minLength: data.password.length >= 8,
        maxLength: data.password.length <= 20,
        hasLower: /[a-z]/.test(data.password),
        hasUpper: /[A-Z]/.test(data.password),
        hasNumber: /\d/.test(data.password),
        hasSymbol: /[^A-Za-z0-9]/.test(data.password),
    };

    const isTyping = data.password.length > 0;

    const isPasswordValid = isTyping && Object.values(passwordRules).every(Boolean);
    const hasConfirmation = data.password_confirmation.length > 0;
    const passwordsMatch = data.password === data.password_confirmation;

    let passwordStatusMessage = null;

    if (passwordStatus.firstReset) {
        passwordStatusMessage = 'This is a mandatory set up of your password for account activation';
    } else if (passwordStatus.expiredReset) {
        passwordStatusMessage = 'Your previous password has expired, please set up a new one';
    }

    const getColor = (condition) => {
        if (!isTyping) return 'text-gray-400';
        return condition ? 'text-green-600' : 'text-red-500';
    };

    const passwordMatchColor = (() => {
        if (!hasConfirmation) return 'text-gray-400';
        if (passwordsMatch) return 'text-green-600';
        return 'text-red-500';
    })();

    const ReactSwal = withReactContent(Swal);

    const updatePassword = (e) => {
        e.preventDefault();

        if (!isPasswordValid || !passwordsMatch) {
            ReactSwal.fire({
                title: 'Invalid Password',
                text: 'Please meet all password requirements and ensure passwords match.',
                icon: 'warning',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: (response) => {
                ReactSwal.fire({
                    title: 'Success',
                    text: response.props.flash.success,
                    icon: 'success',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });
                reset();
            },
            onError: (errors) => {
                ReactSwal.fire({
                    title: 'Error',
                    text: 'Failed to submit request. Please Try again',
                    icon: 'error',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });

                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="max-w-xl space-y-6">
                    <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />
                    {passwordStatusMessage && <span className="text-sm font-semibold text-red-500">* {passwordStatusMessage} *</span>}
                    <Separator />
                    <form onSubmit={updatePassword} className="mt-5 space-y-6">
                        {!passwordStatus.firstReset && !passwordStatus.expiredReset && (
                            <div className="grid gap-2">
                                <Label htmlFor="current_password">Current password</Label>

                                <PasswordInput
                                    id="current_password"
                                    ref={currentPasswordInput}
                                    value={data.current_password}
                                    onChange={(e) => setData('current_password', e.target.value)}
                                    className="mt-1 block w-full pr-10 [&::-ms-clear]:hidden [&::-ms-reveal]:hidden"
                                    autoComplete="current-password"
                                    placeholder="Current password"
                                />

                                <InputError message={errors.current_password} />
                            </div>
                        )}

                        <div className="grid gap-2">
                            <Label htmlFor="password">New password</Label>

                            <PasswordInput
                                id="password"
                                ref={passwordInput}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="mt-1 block w-full pr-10 [&::-ms-clear]:hidden [&::-ms-reveal]:hidden"
                                autoComplete="new-password"
                                placeholder="New password"
                            />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">Confirm password</Label>

                            <PasswordInput
                                id="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                className="mt-1 block w-full pr-10 [&::-ms-clear]:hidden [&::-ms-reveal]:hidden"
                                autoComplete="new-password"
                                placeholder="Confirm password"
                            />
                        </div>

                        {isTyping && (
                            <div className="mt-2 space-y-1 text-sm">
                                <p className={getColor(passwordRules.minLength)}>• At least 8 characters</p>
                                <p className={getColor(passwordRules.maxLength)}>• Maximum of 20 characters</p>
                                <p className={getColor(passwordRules.hasLower)}>• Contains lowercase letter</p>
                                <p className={getColor(passwordRules.hasUpper)}>• Contains uppercase letter</p>
                                <p className={getColor(passwordRules.hasSymbol)}>• Contains symbol</p>
                                <p className={getColor(passwordRules.hasNumber)}>• Contains number</p>
                                <p className={passwordMatchColor}>• Passwords match</p>
                            </div>
                        )}

                        {errors.password &&
                            (Array.isArray(errors.password) ? (
                                errors.password.map((err, index) => (
                                    <p key={`${err}-${index}`} className="text-sm text-red-500">
                                        {err}
                                    </p>
                                ))
                            ) : (
                                <p className="text-sm text-red-500">{errors.password}</p>
                            ))}

                        <div className="flex items-center gap-4">
                            <Button
                                className="bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                disabled={processing || !isPasswordValid || !passwordsMatch}
                            >
                                Save password
                            </Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
