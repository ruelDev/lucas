/* eslint-disable react/prop-types */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { can } from '@/lib/can';
import { router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Plus, WifiOff } from 'lucide-react';
import * as React from 'react';
import { renderToString } from 'react-dom/server';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function AddNewDeposit({ payments }) {
    const [isOpen, setIsOpen] = React.useState(false);
    const { data, setData, setError, errors } = useForm({
        searchBy: 'Agreement Number',
        value: '',
    });

    const SearchPayload = {
        searchBy: data.searchBy,
        value: data.value,
    };

    const ReactSwal = withReactContent(Swal);

    const handleAddNewDepositSubmit = async (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: <p>Searching...</p>,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                ReactSwal.showLoading();
            },
        });

        axios
            .post(route('out-collection.search.index'), SearchPayload)

            .then((response) => {
                ReactSwal.close();

                const responseData = response.data;

                setIsOpen(false);
                setData('value', '');

                // Database / server connection unavailable
                if (responseData.hasErrorCode) {
                    ReactSwal.fire({
                        iconHtml: renderToString(<WifiOff size={60} color="#EF4444" />),
                        title: 'Connection Error',
                        text: responseData.message,
                        confirmButtonColor: '#1B4298',
                        confirmButtonText: 'Okay, got it!',
                        customClass: {
                            icon: 'swal-icon',
                        },
                    });

                    return;
                }

                if (responseData.status || responseData.npaStage) {
                    // Account status is closed and npa stage is in repo or sale
                    ReactSwal.fire({
                        icon: 'warning',
                        iconColor: '#112550',
                        html: `
                            <p style="margin-bottom: 10px;">
                                ${responseData.message}
                            </p>

                            <p style="font-size: 14px; color: #6B7280;">
                                Do you want to record it as Unapplied Receipt?
                            </p>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Record it!',
                        cancelButtonText: 'Search Again',
                        confirmButtonColor: '#1B4298',
                        cancelButtonColor: '#9CA3AF',
                        reverseButtons: true,
                        focusConfirm: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            router.get(route('out-collection.view-deposit.unapplied-receipt.create', payments.id), {
                                data: responseData?.results?.[0],
                                maker: payments,
                                agreement_number: data.value,
                            });
                        } else {
                            setIsOpen(true);
                        }
                    });
                } else if (!responseData.results[0]) {
                    // Account not existing
                    ReactSwal.fire({
                        icon: 'warning',
                        iconColor: '#112550',
                        html: `
                        <p style="margin-bottom: 10px;">
                            Account not existing!
                        </p>

                        <p style="font-size: 14px; color: #6B7280;">
                            Do you want to record it as Unapplied Receipt?
                        </p>
                    `,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Record it!',
                        cancelButtonText: 'Search Again',
                        confirmButtonColor: '#1B4298',
                        cancelButtonColor: '#9CA3AF',
                        reverseButtons: true,
                        focusConfirm: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            router.get(route('out-collection.view-deposit.unapplied-receipt.create', payments.id), {
                                data: responseData?.results,
                                maker: payments,
                                agreement_number: data.value,
                            });
                        } else {
                            setIsOpen(true);
                        }
                    });
                } else {
                    // Existing account found
                    router.get(route('out-collection.view-deposit.quick-receipt.create', payments.id), {
                        data: responseData.results[0],
                        maker: payments,
                    });

                    return;
                }
            })

            .catch(() => {
                ReactSwal.close();

                if (SearchPayload.value === '') {
                    setError('value', `The ${SearchPayload.searchBy} field is required.`);

                    return;
                }

                setIsOpen(false);

                ReactSwal.fire({
                    title: 'Something went wrong.',
                    text: 'Please try again later.',
                    icon: 'error',
                    confirmButtonColor: '#1B4298',
                });
            });
    };

    const getSearchLabel = (searchBy) => {
        if (searchBy === 'AGREEMENTNO') return 'Agreement Number';
        if (searchBy === 'MIS_NO') return 'MIS Number';

        return `Search ${searchBy}`;
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('out_collection.create') && (
                <DialogTrigger asChild>
                    <Button className="submit-button">
                        <Plus className="h-4 w-4" />
                        <span className="hidden sm:block">Add New Payment</span>
                    </Button>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add New Payment</DialogTitle>
                    <DialogDescription>Enter details to add new payment</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleAddNewDepositSubmit} className="space-y-4">
                    <div className="grid w-full items-center gap-3">
                        <Label className="after:ml-0.5 after:text-red-500 after:content-['*']">Search By</Label>
                        <Select value={data.searchBy} onValueChange={(val) => setData('searchBy', val)}>
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Select Filter" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Search Filter</SelectLabel>
                                    <SelectItem value="Agreement Number">Agreement Number</SelectItem>
                                    <SelectItem value="MIS Number">MIS Number</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="grid w-full items-center gap-3">
                        <Label htmlFor="value" className="after:ml-0.5 after:text-red-500 after:content-['*']">
                            {getSearchLabel(data.searchBy)}
                        </Label>
                        <Input
                            id="value"
                            type="search"
                            placeholder={getSearchLabel(data.searchBy)}
                            value={data.value}
                            onChange={(e) => setData('value', e.target.value)}
                        />
                        <InputError message={errors.value} />
                    </div>
                    <DialogFooter className="float-start">
                        <DialogClose asChild>
                            <Button variant="outline">Cancel</Button>
                        </DialogClose>
                        <Button type="submit" className="bg-blue-700 text-white hover:bg-blue-900">
                            Search
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
