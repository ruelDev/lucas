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

export default function CreateGroupDialog() {
    const [isOpen, setIsOpen] = React.useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        name: '',
        code: '',
        description: '',
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

    const handleGroupSubmit = (e) => {
        e.preventDefault();

        setIsOpen(false);

        ReactSwal.fire({
            title: 'Confirm Submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new Group?</p>`,
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
                
                post(route('group-management.store'), {
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
            }
        });
    };

    const cancelCreateGroup = () => {
        reset();
        clearErrors();
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('group_management.create') && (
                <DialogTrigger asChild>
                    <Button className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                        <Plus className="h-4 w-4" />
                        Create Group
                    </Button>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create Group</DialogTitle>
                    <DialogDescription>Create a new Group.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleGroupSubmit} className="space-y-4">
                    <div>
                        <Label html="name" className="mb-2">
                            Group Name <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Enter Group Name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <p className="text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label html="code" className="mb-2">
                            Code <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="code"
                            name="code"
                            type="text"
                            placeholder="Enter Group Code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            required
                        />
                        {errors.code && <p className="text-red-500">{errors.code}</p>}
                    </div>
                    <div>
                        <Label html="desription" className="mb-2">
                            Group Description <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="description"
                            name="description"
                            placeholder="Enter Group Description"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            required
                        />
                        {errors.description && <p className="text-red-500">{errors.description}</p>}
                    </div>
                    <div className="mt-4 text-center">
                        <Button
                            type="button"
                            variant="outline"
                            className="mx-1 h-10 border-blue-800 bg-linear-to-r transition duration-300 hover:from-blue-950 hover:to-blue-900 hover:text-white"
                            onClick={cancelCreateGroup}
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
