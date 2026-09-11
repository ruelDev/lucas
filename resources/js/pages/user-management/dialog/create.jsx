/* eslint-disable react/prop-types */
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Head, Link, useForm } from '@inertiajs/react';
import axios from 'axios';
import { format } from 'date-fns';
import { CalendarIcon, CheckIcon, ChevronDownIcon, ChevronsUpDownIcon, LoaderCircle } from 'lucide-react';
import { useEffect, useState } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const breadcrumbs = [
    {
        title: 'Users',
        href: '/user-management',
    },
    {
        title: 'Create User',
        href: '/user-management/create',
    },
];

export default function CreateUser({ roles }) {
    const ReactSwal = withReactContent(Swal);

    const { data, setData, post, processing, errors } = useForm({
        employee_id: '',
        fname: '',
        mname: '',
        lname: '',
        email: '',
        company: '',
        position: '',
        isBranchDealer: '',
        isAlternateUser: 0,
        location: '',
        branchDealerId: '',
        organization: '',
        role: '',
        expiration_date: null,
    });

    const [cbRoleOpen, setCbRoleOpen] = useState(false);
    const [cbRoleValue, setCbRoleValue] = useState('');

    const [cbBranchOpen, setCbBranchOpen] = useState(false);
    const [cbBranchValue, setCbBranchValue] = useState('');

    const [cbDealerOpen, setCbDealerOpen] = useState(false);
    const [cbDealerValue, setCbDealerValue] = useState('');

    const [cbOrgOpen, setCbOrgOpen] = useState(false);
    const [cbOrgValue, setCbOrgValue] = useState('');

    const [open, setOpen] = useState(false);
    const [selectDate, setSelectDate] = useState(null);

    const handleDateSelect = (date) => {
        setSelectDate(date);
        if (date) {
            setData('expiration_date', format(date, 'yyyy-MM-dd'));
        } else {
            setData('expiration_date', '');
        }
    };

    const handleAlternateUserCheck = (alternateUser) => {
        if (alternateUser) {
            setData('isAlternateUser', 1);
            setData('position', 'ALTERNATE USER');
        } else {
            setData('isAlternateUser', 0);
            setData('position', '');
        }
    };

    const toTitleCase = (value) => {
        return value.toLowerCase().replace(/\b\w/g, (char) => char.toUpperCase());
    };

    // if isBranchDealer is 1
    const [branchOptions, setBranchOptions] = useState([]);
    const [dealerOptions, setDealerOptions] = useState([]);

    useEffect(() => {
        if (data.isBranchDealer !== '1') {
            setBranchOptions([]);
            setDealerOptions([]);
        }

        setData('location', '');
        setData('organization', '');

        const fetchBranchData = async () => {
            try {
                const branchRes = await axios.get('/get-branch-options');
                setBranchOptions(branchRes.data);
            } catch (err) {
                console.error('Error loading branch data:', err);
            }
        };

        fetchBranchData();
    }, []);

    useEffect(() => {
        setData('location', '');
        setData('organization', '');

        const fetchDealerData = async () => {
            try {
                const dealerRes = await axios.get('/get-dealer-options');
                setDealerOptions(dealerRes.data);
            } catch (err) {
                console.error('Error loading dealer data:', err);
            }
        };

        fetchDealerData();
    }, [data.company]);

    // if isBranchDealer is 2
    const [orgOptions, setOrgOptions] = useState([]);

    useEffect(() => {
        if (!data.isBranchDealer == '2') {
            setOrgOptions([]);
            return;
        }

        setData('branchDealerId', '');

        const fetchOrgData = async () => {
            try {
                const { data: orgData } = await axios.get(route('getOrganizationOptions'));
                setOrgOptions(orgData.data);
            } catch (err) {
                console.error('Error loading data: ', err);
            }
        };

        fetchOrgData();
    }, [data.isBranchDealer]);

    // Custom handler for DEALER user
    useEffect(() => {
        if (data.company === 'DEALER') {
            setData('isBranchDealer', '1');
        }
    }, [data.company]);

    const handleUserSubmit = (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: 'Confirm Submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new user?</p>`,
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

                post(route('user-management.store'), {
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
                        setData('employee_id', '');
                        setData('fname', '');
                        setData('mname', '');
                        setData('lname', '');
                        setData('email', '');
                        setData('password', '');
                        setData('company', '');
                        setData('position', '');
                        setData('isBranchDealer', '');
                        setData('branchDealerId', '');
                        setData('location', '');
                        setData('organization', '');
                        setData('role', '');
                        setData('expiration_date', '');
                        setSelectDate(null);
                    },
                    onError: () => {
                        ReactSwal.fire({
                            title: 'Error',
                            text: 'Failed to create User',
                            icon: 'error',
                            confirmButtonText: 'Ok, got it!',
                            confirmButtonColor: '#1B4298',
                        });
                    },
                });
            }
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create User" />
            <div className="space-y-4 p-6">
                <Card>
                    <CardContent>
                        <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                            <div>
                                <HeadingSmall title="Create New User" />
                                <Separator />
                                <form onSubmit={handleUserSubmit} className="mx-3 mt-5">
                                    <div>
                                        <div>
                                            <Label html="employee_id" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                Employee ID
                                            </Label>
                                            <Input
                                                id="employee_id"
                                                name="employee_id"
                                                type="text"
                                                placeholder="Enter Employee ID"
                                                value={data.employee_id}
                                                onChange={(e) => setData('employee_id', e.target.value)}
                                                required
                                            />
                                            {errors.employee_id && <p className="mt-1 text-sm text-red-500">{errors.employee_id}</p>}
                                        </div>
                                        <div className="mt-5 grid grid-cols-1 gap-2 space-y-3 lg:grid-cols-3">
                                            <div>
                                                <Label html="lname" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    Last Name
                                                </Label>
                                                <Input
                                                    id="lname"
                                                    name="lname"
                                                    type="text"
                                                    placeholder="Enter Last Name"
                                                    value={data.lname}
                                                    onChange={(e) => setData('lname', toTitleCase(e.target.value))}
                                                    required
                                                />
                                                {errors.lname && <p className="mt-1 text-sm text-red-500">{errors.lname}</p>}
                                            </div>
                                            <div>
                                                <Label html="fname" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    First Name
                                                </Label>
                                                <Input
                                                    id="fname"
                                                    name="fname"
                                                    type="text"
                                                    placeholder="Enter First Name"
                                                    value={data.fname}
                                                    onChange={(e) => setData('fname', toTitleCase(e.target.value))}
                                                    required
                                                />
                                                {errors.fname && <p className="mt-1 text-sm text-red-500">{errors.fname}</p>}
                                            </div>
                                            <div>
                                                <Label html="mname" className="mb-2">
                                                    Middle Name
                                                </Label>
                                                <Input
                                                    id="mname"
                                                    name="mname"
                                                    type="text"
                                                    placeholder="Enter Middle Name"
                                                    value={data.mname}
                                                    onChange={(e) => setData('mname', toTitleCase(e.target.value))}
                                                />
                                                {errors.mname && <p className="mt-1 text-sm text-red-500">{errors.mname}</p>}
                                            </div>
                                        </div>
                                        <div className="mt-5">
                                            <Label html="email" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                Email Address
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                placeholder="Enter Email Address"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                required
                                            />
                                            {errors.email && <p className="mt-1 text-sm text-red-500">{errors.email}</p>}
                                        </div>
                                    </div>
                                    <div className="mt-5">
                                        <div className="grid grid-cols-1 gap-2 lg:grid-cols-2">
                                            <div>
                                                <Label html="position" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    Position
                                                </Label>
                                                <Input
                                                    id="position"
                                                    name="position"
                                                    type="text"
                                                    placeholder="Enter Position"
                                                    value={data.position}
                                                    disabled={Boolean(data.isAlternateUser)}
                                                    onChange={(e) => {
                                                        const value = e.target.value;

                                                        if (value.trim().toUpperCase() === 'ALTERNATE USER') {
                                                            return;
                                                        }

                                                        setData('position', value);
                                                    }}
                                                    required
                                                />
                                                <div className="flex items-center gap-2 pt-2 pl-2 text-neutral-800">
                                                    <Checkbox
                                                        id="isAlternateUser"
                                                        className="data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600"
                                                        checked={Boolean(data.isAlternateUser)}
                                                        onCheckedChange={(checked) => handleAlternateUserCheck(checked)}
                                                    />
                                                    <Label htmlFor="isAlternateUser" className="dark:text-white">
                                                        Set into Alternate User
                                                    </Label>
                                                </div>
                                                {errors.position && <p className="mt-1 text-sm text-red-500">{errors.position}</p>}
                                                {errors.isAlternateUser && <p className="mt-1 text-sm text-red-500">{errors.isAlternateUser}</p>}
                                            </div>
                                            <div>
                                                <Label html="company" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    Company Access
                                                </Label>
                                                <Select value={String(data.company)} onValueChange={(value) => setData('company', value)}>
                                                    <SelectTrigger className="w-full!">
                                                        <SelectValue placeholder="Company Access" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="BMI">BMI</SelectItem>
                                                        <SelectItem value="BFC">BFC</SelectItem>
                                                        <SelectItem value="BOTH">BOTH</SelectItem>
                                                        <SelectItem value="DEALER">DEALER</SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                {errors.company && <p className="mt-1 text-sm text-red-500">{errors.company}</p>}
                                            </div>
                                        </div>
                                        {data.company !== 'DEALER' && (
                                            <div className="mt-5">
                                                <Label html="Office" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                    Office
                                                </Label>
                                                <RadioGroup
                                                    value={data.isBranchDealer}
                                                    onValueChange={(value) => setData('isBranchDealer', value)}
                                                    className="flex w-full! justify-evenly"
                                                >
                                                    <div className="flex items-center space-x-2">
                                                        <RadioGroupItem value="1" id="Branch" />
                                                        <Label htmlFor="Branch">Branch</Label>
                                                    </div>
                                                    <div className="flex items-center space-x-2">
                                                        <RadioGroupItem value="2" id="Head Office" />
                                                        <Label htmlFor="Head Office">Head Office</Label>
                                                    </div>
                                                </RadioGroup>
                                                {errors.isBranchDealer && <p className="mt-1 text-sm text-red-500">{errors.isBranchDealer}</p>}
                                            </div>
                                        )}
                                        <div>
                                            {data.isBranchDealer === '1' && data.company !== 'DEALER' && (
                                                <div className="mt-5">
                                                    <Label html="branchDealerId" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                        Branch
                                                    </Label>
                                                    <Popover open={cbBranchOpen} onOpenChange={setCbBranchOpen} className="w-full">
                                                        <PopoverTrigger asChild>
                                                            <Button variant="outline" aria-expanded={cbBranchOpen} className="w-full justify-between">
                                                                {cbBranchValue
                                                                    ? branchOptions.find((branch) => branch.name === cbBranchValue)?.name
                                                                    : 'Select Branch...'}
                                                                <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                            </Button>
                                                        </PopoverTrigger>
                                                        <PopoverContent className={'w-60 xl:w-260!'}>
                                                            <Command>
                                                                <CommandInput placeholder="Search Branch..." />
                                                                <CommandList>
                                                                    <CommandEmpty>No branch found</CommandEmpty>
                                                                    <CommandGroup>
                                                                        {branchOptions.map((branch) => (
                                                                            <CommandItem
                                                                                key={branch.name}
                                                                                value={branch.name}
                                                                                onSelect={(currentValue) => {
                                                                                    setData(
                                                                                        'branchDealerId',
                                                                                        currentValue === cbBranchValue ? '' : currentValue,
                                                                                    );
                                                                                    setCbBranchValue(
                                                                                        currentValue === cbBranchValue ? '' : currentValue,
                                                                                    );
                                                                                    setCbBranchOpen(false);
                                                                                }}
                                                                            >
                                                                                <CheckIcon
                                                                                    className={cn(
                                                                                        'mr-2 h-4 w-4',
                                                                                        cbBranchValue === branch.name ? 'opacity-100' : 'opacity-0',
                                                                                    )}
                                                                                />
                                                                                {branch.name}
                                                                            </CommandItem>
                                                                        ))}
                                                                    </CommandGroup>
                                                                </CommandList>
                                                            </Command>
                                                        </PopoverContent>
                                                    </Popover>
                                                    {errors.branchDealerId && <p className="mt-1 text-sm text-red-500">{errors.branchDealerId}</p>}
                                                </div>
                                            )}
                                            {data.company === 'DEALER' && (
                                                <div className="mt-5">
                                                    <Label html="branchDealerId" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                        Dealer Branch
                                                    </Label>
                                                    <Popover open={cbDealerOpen} onOpenChange={setCbDealerOpen} className="w-full">
                                                        <PopoverTrigger asChild>
                                                            <Button variant="outline" aria-expanded={cbDealerOpen} className="w-full justify-between">
                                                                {cbDealerValue
                                                                    ? dealerOptions.find((dealer) => dealer.name === cbDealerValue)?.name
                                                                    : 'Select Dealer Branch...'}
                                                                <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                            </Button>
                                                        </PopoverTrigger>
                                                        <PopoverContent className={'w-60 xl:w-260!'}>
                                                            <Command>
                                                                <CommandInput placeholder="Search Dealer..." />
                                                                <CommandList>
                                                                    <CommandEmpty>No dealer branch found</CommandEmpty>
                                                                    <CommandGroup>
                                                                        {dealerOptions.map((dealer) => (
                                                                            <CommandItem
                                                                                key={dealer.name}
                                                                                value={dealer.name}
                                                                                onSelect={(currentValue) => {
                                                                                    setData(
                                                                                        'branchDealerId',
                                                                                        currentValue === cbDealerValue ? '' : currentValue,
                                                                                    );
                                                                                    setCbDealerValue(
                                                                                        currentValue === cbDealerValue ? '' : currentValue,
                                                                                    );
                                                                                    setCbDealerOpen(false);
                                                                                }}
                                                                            >
                                                                                <CheckIcon
                                                                                    className={cn(
                                                                                        'mr-2 h-4 w-4',
                                                                                        cbDealerValue === dealer.name ? 'opacity-100' : 'opacity-0',
                                                                                    )}
                                                                                />
                                                                                {dealer.name}
                                                                            </CommandItem>
                                                                        ))}
                                                                    </CommandGroup>
                                                                </CommandList>
                                                            </Command>
                                                        </PopoverContent>
                                                    </Popover>
                                                    {errors.branchDealerId && <p className="mt-1 text-sm text-red-500">{errors.branchDealerId}</p>}
                                                </div>
                                            )}
                                            {data.isBranchDealer === '2' && data.company !== 'DEALER' && (
                                                <div>
                                                    <div className="mt-5">
                                                        <Label html="location" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                            Location
                                                        </Label>
                                                        <Select value={String(data.location)} onValueChange={(value) => setData('location', value)}>
                                                            <SelectTrigger className="w-full!">
                                                                <SelectValue placeholder="Location" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="BUENDIA">Buendia Office</SelectItem>
                                                                <SelectItem value="AYALA">Ayala Office</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                        {errors.location && <p className="mt-1 text-sm text-red-500">{errors.location}</p>}
                                                    </div>
                                                    <div className="grid grid-cols-1 gap-2 lg:grid-cols-1">
                                                        <div className="mt-5">
                                                            <Label
                                                                html="organization"
                                                                className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']"
                                                            >
                                                                Organization
                                                            </Label>
                                                            <Popover open={cbOrgOpen} onOpenChange={setCbOrgOpen} className="w-full">
                                                                <PopoverTrigger asChild>
                                                                    <Button
                                                                        variant="outline"
                                                                        aria-expanded={cbOrgOpen}
                                                                        className="w-full justify-between"
                                                                    >
                                                                        {cbOrgValue
                                                                            ? orgOptions.find((org) => org.name === cbOrgValue)?.name
                                                                            : 'Select Organization...'}
                                                                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                                    </Button>
                                                                </PopoverTrigger>
                                                                <PopoverContent className={'w-60 xl:w-180!'}>
                                                                    <Command>
                                                                        <CommandInput placeholder="Search Organization..." />
                                                                        <CommandList>
                                                                            <CommandEmpty>No organization found</CommandEmpty>
                                                                            <CommandGroup>
                                                                                {orgOptions.map((org) => (
                                                                                    <CommandItem
                                                                                        key={org.name}
                                                                                        value={org.name}
                                                                                        onSelect={(currentValue) => {
                                                                                            setData(
                                                                                                'organization',
                                                                                                currentValue === cbOrgValue ? '' : currentValue,
                                                                                            );
                                                                                            setCbOrgValue(
                                                                                                currentValue === cbOrgValue ? '' : currentValue,
                                                                                            );
                                                                                            setCbOrgOpen(false);
                                                                                        }}
                                                                                    >
                                                                                        <CheckIcon
                                                                                            className={cn(
                                                                                                'mr-2 h-4 w-4',
                                                                                                cbOrgValue === org.name ? 'opacity-100' : 'opacity-0',
                                                                                            )}
                                                                                        />
                                                                                        {org.name}
                                                                                    </CommandItem>
                                                                                ))}
                                                                            </CommandGroup>
                                                                        </CommandList>
                                                                    </Command>
                                                                </PopoverContent>
                                                            </Popover>
                                                            {errors.organization && (
                                                                <p className="mt-1 text-sm text-red-500">{errors.organization}</p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div className="mt-5">
                                        <div>
                                            <Label html="role_id" className="mb-2 after:ml-0.5 after:text-red-500 after:content-['*']">
                                                Role
                                            </Label>
                                            <Popover open={cbRoleOpen} onOpenChange={setCbRoleOpen} className="w-full">
                                                <PopoverTrigger asChild>
                                                    <Button variant="outline" aria-expanded={cbRoleOpen} className="w-full justify-between">
                                                        {cbRoleValue ? roles.find((role) => role.name === cbRoleValue)?.name : 'Select Role...'}
                                                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                                    </Button>
                                                </PopoverTrigger>
                                                <PopoverContent className={'w-60 xl:w-260!'}>
                                                    <Command>
                                                        <CommandInput placeholder="Search role..." />
                                                        <CommandList>
                                                            <CommandEmpty>No role found</CommandEmpty>
                                                            <CommandGroup>
                                                                {roles.map((role) => (
                                                                    <CommandItem
                                                                        key={role.id}
                                                                        value={role.name}
                                                                        onSelect={(currentValue) => {
                                                                            setData('role', currentValue === cbRoleValue ? '' : currentValue);
                                                                            setCbRoleValue(currentValue === cbRoleValue ? '' : currentValue);
                                                                            setCbRoleOpen(false);
                                                                        }}
                                                                    >
                                                                        <CheckIcon
                                                                            className={cn(
                                                                                'mr-2 h-4 w-4',
                                                                                cbRoleValue === role.name ? 'opacity-100' : 'opacity-0',
                                                                            )}
                                                                        />
                                                                        {role.name}
                                                                    </CommandItem>
                                                                ))}
                                                            </CommandGroup>
                                                        </CommandList>
                                                    </Command>
                                                </PopoverContent>
                                            </Popover>
                                            {errors.role && <p className="mt-1 text-sm text-red-500">{errors.role}</p>}
                                        </div>
                                    </div>
                                    <div className="mt-5 mb-1">
                                        <div>
                                            <Label htmlFor="expiration_date" className="mb-2 block">
                                                Date of Expiration <span className="text-gray-500">(optional)</span>
                                            </Label>
                                            <Popover open={open} onOpenChange={setOpen} className="w-full">
                                                <PopoverTrigger asChild>
                                                    <Button
                                                        id="expiration_date"
                                                        variant="outline"
                                                        className={cn('w-full justify-between font-normal', !selectDate && 'text-muted-foreground')}
                                                    >
                                                        <div className="flex items-center gap-2">
                                                            <CalendarIcon className="h-4 w-4" />
                                                            {selectDate ? format(selectDate, 'PPP') : 'Pick a date'}
                                                        </div>
                                                        <ChevronDownIcon className="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </PopoverTrigger>

                                                <PopoverContent className="w-auto overflow-hidden p-0" align="center">
                                                    <Calendar
                                                        mode="single"
                                                        selected={selectDate}
                                                        captionLayout="dropdown"
                                                        onSelect={(date) => {
                                                            handleDateSelect(date);
                                                            setOpen(false);
                                                        }}
                                                        disabled={(date) => {
                                                            const today = new Date();
                                                            today.setHours(0, 0, 0, 0);
                                                            return date <= today;
                                                        }}
                                                        className="w-[300px]"
                                                        initialFocus
                                                    />
                                                </PopoverContent>
                                            </Popover>

                                            {errors?.expiration_date && <p className="mt-1 text-sm text-red-500">{errors.expiration_date}</p>}
                                        </div>
                                    </div>
                                    <Separator className="mt-5" />
                                    <div>
                                        <span className="mt-3 text-xs text-neutral-600 italic">
                                            Note: In case that the creation of user is unsuccessful, the possible reason is that it might be soft
                                            deleted in the system.
                                        </span>
                                    </div>
                                    <div className="mt-5 text-center">
                                        <Button
                                            asChild
                                            variant="outline"
                                            className="h-10 border-blue-800 bg-white px-6 py-4 text-blue-600 transition duration-300 hover:bg-linear-to-r hover:from-blue-950 hover:to-blue-900 hover:text-white"
                                        >
                                            <Link href="/user-management">Back</Link>
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
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
