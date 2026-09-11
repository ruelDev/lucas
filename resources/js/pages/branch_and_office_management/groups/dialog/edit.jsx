/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { can } from '@/lib/can';
import { router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { LoaderCircle, Pencil } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function EditGroupDialog({ group }) {
    const [isOpen, setIsOpen] = React.useState(false);
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState([]);

    const { data, setData } = useForm({
        name: group.name,
        code: group.code,
        description: group.description,
        status: group.status,
        remarks: group.remarks,
    });

    const ReactSwal = withReactContent(Swal);

    const handleGroupSubmit = async (e) => {
        e.preventDefault();
        setProcessing(true);

        ReactSwal.fire({
            title: <p>Saving...</p>,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                ReactSwal.showLoading();
            },
        });

        try {
            const response = await axios.put(route('group-management.update', group.id), data);

            console.log(response.data);
            
            ReactSwal.fire({
                title: 'Success',
                text: response.data.message,
                icon: 'success',
                iconColor: '#1B4298',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            setIsOpen(false);
            router.get('/group-management');
        } catch (err) {
            setProcessing(false);
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors);
                ReactSwal.close();
            }
        }
    };

    const cancelEditGroup = () => {
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('group_management.edit') && (
                <DialogTrigger asChild>
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-blue-100 hover:text-blue-600">
                        <Pencil className="h-4 w-4" />
                        <span className="text-sm">Edit</span>
                    </div>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Group</DialogTitle>
                    <DialogDescription>Edit a Group.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleGroupSubmit} className="space-y-4">
                    <div>
                        <Label html="status" className="mb-2">
                            Group Status <span className="text-red-500">*</span>
                        </Label>
                        <RadioGroup defaultValue={data.status} onValueChange={(value) => setData('status', value)} className="grid grid-cols-2 gap-2">
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="active" id="active" />
                                <Label htmlFor="active">Active</Label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <RadioGroupItem value="inactive" id="inactive" />
                                <Label htmlFor="inactive">Inactive</Label>
                            </div>
                        </RadioGroup>
                        {errors.status && <p className="text-red-500">{errors.status}</p>}
                    </div>

                    <Separator />
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
                            onClick={cancelEditGroup}
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
