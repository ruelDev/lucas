/* eslint-disable react/prop-types */
import HeadingSmall from '@/components/heading-small';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ReadOnlyField from '@/components/ui/read-only-field';
import { Separator } from '@/components/ui/separator';
import { useInitials } from '@/hooks/use-initials';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AlertCircleIcon, Loader2, Upload, X } from 'lucide-react';
import { useMemo } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const breadcrumbs = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

export default function Profile({ user }) {
    const ReactSwal = withReactContent(Swal);

    const { auth } = usePage().props;

    const { data, setData, post, processing, recentlySuccessful } = useForm({
        profile_picture: null,
    });

    const getInitials = useInitials();

    const fullname = useMemo(() => {
        const { fname, mname, lname } = auth.user;
        return `${fname} ${mname ? mname + ' ' : ' '} ${lname}`;
    }, [auth.user]);

    const profilePictureUrl = useMemo(() => {
        return auth.user.profile_picture ? `/storage/profile_pictures/${auth.user.profile_picture}` : null;
    }, [auth.user.profile_picture]);

    const submit = (e) => {
        e.preventDefault();

        if (!data.profile_picture) return;

        post(route('profile.photo.update'), {
            preserveScroll: true,
            onSuccess: (response) => {
                ReactSwal.fire({
                    title: 'Success',
                    text: response.props.flash.success,
                    icon: 'success',
                    iconColor: '#1B4298',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });

                setData('profile_picture', null);
                document.getElementById('profile_picture').value = '';
            },
            onError: (errors) => {
                let errorMessage = 'Please try again.';
                let errorTitle = 'Error';

                if (errors.general) {
                    errorMessage = errors.general;
                    errorTitle = 'Update Failed';
                } else if (objectIncludes.keys(errors).length > 0) {
                    const firstErrorKey = Object.keys(errors)[0];
                    const firstError = errors[firstErrorKey];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                    errorTitle = 'Validation Error';
                }

                ReactSwal.fire({
                    title: errorTitle,
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });
            },
        });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) setData('profile_picture', file);
    };

    const handleRemovePhoto = (e) => {
        e.preventDefault();
        ReactSwal.fire({
            title: 'Remove profile photo?',
            html: `<p>This will remove your current profile picture.</p>`,
            icon: 'warning',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm Delete',
            confirmButtonColor: '#D63031',
            showCancelButton: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                ReactSwal.fire({
                    title: <p>Deleting...</p>,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        ReactSwal.showLoading();
                    },
                });

                post(route('profile.photo.remove'), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (response) => {
                        ReactSwal.fire({
                            title: 'Success',
                            icon: 'success',
                            iconColor: '#1B4298',
                            text: response.props.flash.success,
                            confirmButtonText: 'Ok, got it!',
                            confirmButtonColor: '#1B4298',
                        });
                    },
                    onError: (errors) => {
                        if (errors.general) {
                            ReactSwal.fire({
                                title: 'Error',
                                text: errors.general,
                                icon: 'error',
                                confirmationButtonText: 'Ok, got it!',
                            });
                        }
                    },
                });
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-10">
                    <Alert variant="destructive">
                        <AlertCircleIcon />
                        <AlertTitle>No Profile Updates Allowed.</AlertTitle>
                        <AlertDescription>
                            <p>To update or change information on your profile, please contact SAPS for inquiries.</p>
                        </AlertDescription>
                    </Alert>

                    <div className="grid grid-cols-1 items-center gap-10 px-10">
                        <div className="">
                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <HeadingSmall title="Profile Photo" />
                                    <Separator />
                                </div>
                                <div className="grid w-full gap-2 xl:w-1/4">
                                    <div className="grid w-full items-center gap-3">
                                        <div className="my-5 flex justify-center">
                                            <Avatar className="border-border size-32 border-2">
                                                <AvatarImage src={profilePictureUrl} alt={fullname} className="object-cover" />
                                                <AvatarFallback className="bg-muted text-lg">{getInitials(fullname)}</AvatarFallback>
                                            </Avatar>
                                        </div>
                                        <Input
                                            id="profile_picture"
                                            type="file"
                                            accept="image/*"
                                            className="w-full cursor-pointer"
                                            onChange={handleFileChange}
                                        />
                                    </div>
                                    <div className="mt-2 flex items-center justify-center gap-4">
                                        {data.profile_picture && (
                                            <>
                                                <Button
                                                    className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                                    disabled={processing || !data.profile_picture}
                                                >
                                                    <Upload className="h-4 w-4" />
                                                    {processing && <Loader2 className="size-4 animate-spin" />}
                                                    {processing ? 'Saving...' : 'Save Photo'}
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
                                            </>
                                        )}
                                        {user.profile_picture && (
                                            <Button
                                                onClick={handleRemovePhoto}
                                                className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                            >
                                                <X className="h-4 w-4" />
                                                {processing && <Loader2 className="size-4 animate-spin" />}
                                                {processing ? 'Removing...' : 'Remove Photo'}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div className="space-y-10">
                            {/* Personal Details */}
                            <div className="space-y-5">
                                <div>
                                    <HeadingSmall title="Personal Details" />
                                    <Separator />
                                </div>
                                <ReadOnlyField label="Employee ID" value={user.employee_id} />
                                <div className="grid grid-cols-1 gap-5 xl:grid-cols-3">
                                    <ReadOnlyField label="Last Name" value={user.lname} />
                                    <ReadOnlyField label="First Name" value={user.fname} />
                                    <ReadOnlyField label="Middle Name" value={user.mname} />
                                </div>
                                <ReadOnlyField label="Email Address" value={user.email} />
                            </div>
                            {/* Office Details */}
                            <div className="space-y-5">
                                <div>
                                    <HeadingSmall title="Office Details" />
                                    <Separator />
                                </div>
                                <div className="grid grid-cols-1 gap-5 xl:grid-cols-2">
                                    <ReadOnlyField label="Position" value={user.position} />
                                    <ReadOnlyField label="Company Access" value={user.company} />
                                </div>
                                {user.isBranchDealer === 1 && user.company !== 'DEALER' && (
                                    <ReadOnlyField label="Branch Location" value={user.area} />
                                )}
                                {user.isBranchDealer === 1 && user.company === 'DEALER' && (
                                    <ReadOnlyField label="Dealer Branch" value={user.area} />
                                )}
                                {user.isBranchDealer === 2 && <ReadOnlyField label="Organization" value={user.area} />}
                            </div>
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
