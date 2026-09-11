import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { can } from '@/lib/can';
import { useForm } from '@inertiajs/react';
import { LoaderCircle, Plus } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function CreateDealerDialog() {
    const [isOpen, setIsOpen] = React.useState(false);
    const [hasBankDetails, setHasBankDetails] = React.useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        name: '',
        dealer_code: '',
        location: '',
        bank_account_name: '',
        bank_account: '',
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

    const handleDealerSubmit = (e) => {
        e.preventDefault();

        setIsOpen(false);

        ReactSwal.fire({
            title: 'Confirm Submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new Dealer Branch?</p>`,
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

                post(route('dealer-management.store'), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: (response) => {
                        successSwal(response);
                        reset();
                        setIsOpen(false);
                        setHasBankDetails(false);
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

    const cancelCreateDealer = () => {
        reset();
        clearErrors();
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('dealer_management.create') && (
                <DialogTrigger asChild>
                    <Button className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900">
                        <Plus className="h-4 w-4" />
                        Create Dealer
                    </Button>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Create Dealer</DialogTitle>
                    <DialogDescription>Please fill in the required details to create a new dealer</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleDealerSubmit} className="space-y-4">
                    <div>
                        <Label html="name" className="mb-2">
                            Dealer Name <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Enter Dealer Name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && <p className="text-red-500">{errors.name}</p>}
                    </div>
                    <div>
                        <Label html="dealer_code" className="mb-2">
                            Dealer Code <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="dealer_code"
                            name="dealer_code"
                            type="text"
                            placeholder="Enter Dealer Code"
                            value={data.dealer_code}
                            onChange={(e) => setData('dealer_code', e.target.value)}
                            required
                        />
                        {errors.dealer_code && <p className="text-red-500">{errors.dealer_code}</p>}
                    </div>
                    <div>
                        <Label html="location" className="mb-2">
                            Dealer Location <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="location"
                            name="location"
                            placeholder="Enter Dealer Location"
                            value={data.location}
                            onChange={(e) => setData('location', e.target.value)}
                            required
                        />
                        {errors.location && <p className="text-red-500">{errors.location}</p>}
                    </div>
                    <div className="mx-5 mt-5 flex items-center gap-3">
                        <Checkbox id="hasBankDetails" checked={hasBankDetails} onCheckedChange={(checked) => setHasBankDetails(checked)} />
                        <Label html="hasBankDetails">Has Bank Details Provided?</Label>
                    </div>
                    {hasBankDetails && (
                        <div className="mx-5 space-y-3">
                            <div>
                                <Label html="bank_account_name" className="mb-2">
                                    Bank Account Name
                                </Label>
                                <Input
                                    id="bank_account_name"
                                    name="bank_account_name"
                                    type="text"
                                    placeholder="Enter Bank Account Name"
                                    value={data.bank_account_name}
                                    onChange={(e) => setData('bank_account_name', e.target.value)}
                                />
                                {errors.bank_account_name && <p className="text-red-500">{errors.bank_account_name}</p>}
                            </div>
                            <div>
                                <Label html="bank_account" className="mb-2">
                                    Bank Account
                                </Label>
                                <Input
                                    id="bank_account"
                                    name="bank_account"
                                    type="text"
                                    placeholder="Enter Bank Account"
                                    value={data.bank_account}
                                    onChange={(e) => setData('bank_account', e.target.value)}
                                />
                                {errors.bank_account && <p className="text-red-500">{errors.bank_account}</p>}
                            </div>
                        </div>
                    )}
                    <Separator className="mt-5" />
                    <div>
                        <span className="mt-3 text-xs text-neutral-600 italic">
                            Note: Bank Details such as Bank Account and Bank Account Name are optional. However, these details will be used by
                            particular modules (e.g. Receipt Converter, etc.)
                        </span>
                    </div>
                    <div className="mt-4 text-center">
                        <Button
                            type="button"
                            variant="outline"
                            className="mx-1 h-10 border-blue-800 bg-linear-to-r transition duration-300 hover:from-blue-950 hover:to-blue-900 hover:text-white"
                            onClick={cancelCreateDealer}
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
