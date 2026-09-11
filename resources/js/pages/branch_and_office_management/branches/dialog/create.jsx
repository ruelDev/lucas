import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { can } from '@/lib/can';
import { useForm } from '@inertiajs/react';
import { LoaderCircle, Plus } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function CreateBranchDialog() {
    const [isOpen, setIsOpen] = React.useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        name: '',
        branch_code: '',
        location: '',
    });

    const ReactSwal = withReactContent(Swal);

    const successSwal = (response) => {
        ReactSwal.fire({
            title: 'Success',
            text: response.props.flash.success,
            icon: 'success',
            iconColor: '#1B4298',
            confirmButtonText: 'Ok, got it!',
            confirmButtonColor: '#1B4298',
        });
    };

    const errorSwal = () => {
        ReactSwal.fire({
            title: 'Error',
            text: 'Failed to submit request. Please Try again',
            icon: 'error',
            confirmButtonText: 'Ok, got it!',
            confirmButtonColor: '#1B4298',
        }).then(() => {
            setIsOpen(true);
        });
    };

    const handleBranchSubmit = (e) => {
        e.preventDefault();

        setIsOpen(false);

        ReactSwal.fire({
            title: 'Confirm Submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new Branch?</p>`,
            icon: 'question',
            iconColor: '#1B4298',
            cancelButtonText: 'Cancel',
            confirmButtonText: 'Confirm Submit',
            confirmButtonColor: '#1B4298',
            showCancelButton: true,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                ReactSwal.fire({
                    title: <p>Saving...</p>,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        ReactSwal.showLoading();
                    },
                });
                post(route('branch-management.store'), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (response) => {
                        successSwal(response);
                        reset();
                        setIsOpen(false);
                    },
                    onError: () => {
                        errorSwal();
                    },
                    onFinish: () => {
                        setIsOpen(false);
                    },
                });
            } else {
                setIsOpen(false);
            }
        });
    };

    const cancelCreateBranch = () => {
        reset();
        clearErrors();
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('branch_management.create') && (
                <DialogTrigger asChild>
                    <Button className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                        <Plus className="h-4 w-4" />
                        Create Branch
                    </Button>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create Branch</DialogTitle>
                    <DialogDescription>Please fill in the required details to create a new branch</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleBranchSubmit} className="space-y-4">
                    <div>
                        <Label html="name" className="mb-2">
                            Branch Name <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Enter Branch Name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <p className="text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label html="branch_code" className="mb-2">
                            Branch Code <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="branch_code"
                            name="branch_code"
                            type="text"
                            placeholder="Enter Branch Code"
                            value={data.branch_code}
                            onChange={(e) => setData('branch_code', e.target.value)}
                            required
                        />
                        {errors.branch_code && <p className="text-red-500">{errors.branch_code}</p>}
                    </div>
                    <div>
                        <Label html="location" className="mb-2">
                            Branch Location <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="location"
                            name="location"
                            placeholder="Enter Branch Location"
                            value={data.location}
                            onChange={(e) => setData('location', e.target.value)}
                            required
                        />
                        {errors.location && <p className="text-red-500">{errors.location}</p>}
                    </div>
                    <div className="mt-4 text-center">
                        <Button
                            type="button"
                            variant="outline"
                            className="mx-1 h-10 border-blue-800 bg-linear-to-r transition duration-300 hover:from-blue-950 hover:to-blue-900 hover:text-white"
                            onClick={cancelCreateBranch}
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            className="mx-1 h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                            disabled={processing}
                        >
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            {processing ? 'Saving...' : 'Save'}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
