/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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

export default function EditDealerDialog({ dealer }) {
    const [isOpen, setIsOpen] = React.useState(false);
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState([]);
    const [hasBankDetails, setHasBankDetails] = React.useState(!!dealer.bank_account);

    const { data, setData } = useForm({
        name: dealer.name,
        dealer_code: dealer.dealer_code,
        location: dealer.location,
        bank_account_name: dealer.bank_account_name,
        bank_account: dealer.bank_account,
        status: dealer.status,
    });

    const ReactSwal = withReactContent(Swal);

    const handleDealerSubmit = async (e) => {
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
            const response = await axios.put(route('dealer-management.update', dealer.id), data);
            ReactSwal.fire({
                title: 'Success',
                text: response.data.message,
                icon: 'success',
                iconColor: '#1B4298',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            setIsOpen(false);
            router.get('/dealer-management');
        } catch (err) {
            setProcessing(false);
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors);
                ReactSwal.close();
            }
        }
    };

    const cancelEditDealer = () => {
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('dealer_management.edit') && (
                <DialogTrigger asChild>
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-blue-100 hover:text-blue-600">
                        <Pencil className="h-4 w-4" />
                        <span className="text-sm">Edit</span>
                    </div>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Dealer</DialogTitle>
                    <DialogDescription>Edit a dealer.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleDealerSubmit} className="space-y-4">
                    <div>
                        <Label html="status" className="mb-2">
                            Dealer Status <span className="text-red-500">*</span>
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
                        {errors.name && <p className="text-sm text-red-500">{errors.name}</p>}
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
                        {errors.dealer_code && <p className="text-sm text-red-500">{errors.dealer_code}</p>}
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
                        {errors.location && <p className="text-sm text-red-500">{errors.location}</p>}
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
                                {errors.bank_account_name && <p className="text-sm text-red-500">{errors.bank_account_name}</p>}
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
                                {errors.bank_account && <p className="text-sm text-red-500">{errors.bank_account}</p>}
                            </div>
                        </div>
                    )}
                    <div className="mt-4 text-center">
                        <Button
                            type="button"
                            variant="outline"
                            className="mx-1 h-10 border-blue-800 bg-linear-to-r transition duration-300 hover:from-blue-950 hover:to-blue-900 hover:text-white"
                            onClick={cancelEditDealer}
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
