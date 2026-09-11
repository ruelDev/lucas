/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { can } from '@/lib/can';
import { useForm } from '@inertiajs/react';
import { Key, LoaderCircle } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function ResetUserDialog({ user }) {
    const [isOpen, setIsOpen] = React.useState(false);

    const { post, processing } = useForm();

    const ReactSwal = withReactContent(Swal);

    const handleUserSubmit = (e) => {
        e.preventDefault();
        post(route('user-management.resetPassword', user.id), {
            preserveState: true,
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
                setIsOpen(false);
            },
            onError: () => {
                ReactSwal.fire({
                    title: 'Error',
                    text: 'Failed to submit request. Please Try again',
                    icon: 'error',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });
            },
            onFinish: () => {
                setIsOpen(false);
            },
        });
    };

    const cancelDeleteUser = () => {
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('user_management.reset') && (
                <DialogTrigger>
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-blue-100 hover:text-blue-600">
                        <Key className="h-4 w-4" />
                        <span className="text-sm">Reset Password</span>
                    </div>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <form onSubmit={handleUserSubmit}>
                        <DialogTitle> Reset User </DialogTitle>
                        <DialogDescription className="mt-3">
                            Resetting the password will send a new one via email and cannot be undone.
                        </DialogDescription>
                        <Separator className="mt-3" />

                        <div className="mt-5">
                            <h2 className="mb-2 text-sm font-semibold">User Details</h2>
                            <div className="overflow-x-auto">

                                <div className="w-full space-y-2 text-sm">
                                    <div className="grid grid-cols-4 gap-2">
                                        <div className="col-span-1 font-medium">Name:</div>
                                        <div className="col-span-3">{user.name}</div>
                                    </div>

                                    <div className="grid grid-cols-4 gap-2">
                                        <div className="col-span-1 font-medium">Employee ID:</div>
                                        <div className="col-span-3">{user.employee_id}</div>
                                    </div>

                                    <div className="grid grid-cols-4 gap-2">
                                        <div className="col-span-1 font-medium">Email:</div>
                                        <div className="col-span-3">{user.email}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div className="mt-6 text-center">
                            <Button
                                type="button"
                                variant="outline"
                                className="mx-1 h-10 border-blue-800 bg-gradient-to-r transition duration-300 hover:from-blue-950 hover:to-blue-900 hover:text-white"
                                onClick={cancelDeleteUser}
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                className="mx-1 h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                disabled={processing}
                            >
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                {processing ? 'Resetting...' : 'Send Email Request'}
                            </Button>
                        </div>
                    </form>
                </DialogHeader>
            </DialogContent>
        </Dialog>
    );
}
