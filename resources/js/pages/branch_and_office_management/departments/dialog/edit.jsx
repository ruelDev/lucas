/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { can } from '@/lib/can';
import { cn } from '@/lib/utils';
import { router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { CheckIcon, ChevronsUpDownIcon, LoaderCircle, Pencil } from 'lucide-react';
import * as React from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

export default function EditDepartmentDialog({ department }) {
    const isInitialLoad = React.useRef(true);
    const abortControllerRef = React.useRef(null);

    const [isOpen, setIsOpen] = React.useState(false);
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState([]);

    const { data, setData } = useForm({
        name: department.name,
        code: department.code,
        description: department.description,
        group: department.group,
        division: department.division ?? '',
        status: department.status,
        remarks: department.remarks,
    });

    const ReactSwal = withReactContent(Swal);

    const [cbGroupOpen, setCbGroupOpen] = React.useState(false);
    const [cbGroupValue, setCbGroupValue] = React.useState(department.group);

    const [cbDivisionOpen, setCbDivisionOpen] = React.useState(false);
    const [cbDivisionValue, setCbDivisionValue] = React.useState(department.division);

    const [groupOptions, setGroupOptions] = React.useState([]);

    const [divisionsLoading, setDivisionsLoading] = React.useState(false);

    React.useEffect(() => {
        axios.get('/get-group-options').then((res) => {
            setGroupOptions(res.data);
        });
    }, []);

    const [divisionOptions, setDivisionOptions] = React.useState([]);

    React.useEffect(() => {
        if (!data.group) {
            setDivisionOptions([]);
            setCbDivisionValue('');
            return;
        }

        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        const controller = new AbortController();
        abortControllerRef.current = controller;

        const fetchDivisionData = async () => {
            try {
                setDivisionsLoading(true);

                const res = await axios.get('/get-division-options', {
                    params: {
                        group: data.group,
                    },
                    signal: controller.signal,
                });

                if (controller.signal.aborted) return;

                const divisions = Array.isArray(res.data) ? res.data : [];

                if (!Array.isArray(res.data)) {
                    console.warn(
                        'Unexpected /get-division-options response shape:',
                        res.data
                    );
                }

                setDivisionOptions(divisions);
                setDivisionsLoading(false);
            } catch (err) {
                setDivisionsLoading(false);

                if (
                    err.name !== 'CanceledError' &&
                    err.code !== 'ERR_CANCELED'
                ) {
                    console.error('Error loading division options:', err)
                }
            }
        };

        if (!isInitialLoad.current) {
            setDivisionOptions([]);
            setCbDivisionValue('');

            setData((prev) => ({
                ...prev,
                division: '',
            }));
        }

        fetchDivisionData();
    }, [data.group]);

    React.useEffect(() => {
        isInitialLoad.current = false;
    }, []);

    const selectedDivisionLabel = cbDivisionValue ? divisionOptions.find((d) => d.name === cbDivisionValue)?.name : 'Select Division...';

    const divisionButtonLabel = divisionsLoading ? (
        <span className="text-muted-foreground flex items-center gap-2">
            <LoaderCircle className="h-4 w-4 animate-spin" />
            Loading...
        </span>
    ) : (
        selectedDivisionLabel
    );

    const handleDepartmentSubmit = async (e) => {
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
            const response = await axios.put(route('department-management.update', department.id), data);
            ReactSwal.fire({
                title: 'Success',
                text: response.data.message,
                icon: 'success',
                iconColor: '#1B4298',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            setIsOpen(false);
            router.get('/department-management');
        } catch (err) {
            setProcessing(false);
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors);

                // console.log(err.response.data.errors)
                ReactSwal.close();
            }
        }
    };

    const cancelCreateDepartment = () => {
        setData('name', '');
        setData('description', '');
        setData('group', '');
        setData('division', '');
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('department_management.edit') && (
                <DialogTrigger asChild>
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-blue-100 hover:text-blue-600">
                        <Pencil className="h-4 w-4" />
                        <span className="text-sm">Edit</span>
                    </div>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Department</DialogTitle>
                    <DialogDescription>Edit a Department.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleDepartmentSubmit} className="space-y-4">
                    <div>
                        <Label html="status" className="mb-2">
                            Department Status <span className="text-red-500">*</span>
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
                            Department Name <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Enter Department Name"
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
                            placeholder="Enter Department Code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            required
                        />
                        {errors.code && <p className="text-red-500">{errors.code}</p>}
                    </div>
                    <div className="md:grid-cols-w grid grid-cols-1 gap-2">
                        <div>
                            <Label html="group" className="mb-2">
                                Group <span className="text-red-500">*</span>
                            </Label>
                            <Popover open={cbGroupOpen} onOpenChange={setCbGroupOpen} className="w-full">
                                <PopoverTrigger asChild>
                                    <Button variant="outline" aria-expanded={cbGroupOpen} className="w-full justify-between">
                                        {cbGroupValue ? groupOptions.find((group) => group.name === cbGroupValue)?.name : 'Select Group...'}
                                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className={'w-60 xl:w-120!'} onWheel={(e) => e.stopPropagation()}>
                                    <Command>
                                        <CommandInput placeholder="Search Group..." />
                                        <CommandList>
                                            <CommandEmpty>No group found</CommandEmpty>
                                            <CommandGroup>
                                                {groupOptions.map((group) => (
                                                    <CommandItem
                                                        key={group.name}
                                                        value={group.name}
                                                        onSelect={(currentValue) => {
                                                            setData('group', currentValue === cbGroupValue ? '' : currentValue);
                                                            setCbGroupValue(currentValue === cbGroupValue ? '' : currentValue);
                                                            setCbGroupOpen(false);
                                                        }}
                                                    >
                                                        <CheckIcon
                                                            className={cn('mr-2 h-4 w-4', cbGroupValue === group.name ? 'opacity-100' : 'opacity-0')}
                                                        />
                                                        {group.name}
                                                    </CommandItem>
                                                ))}
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            {errors.group && <p className="text-red-500">{errors.group}</p>}
                        </div>
                        {data.group && (
                            <div>
                                <Label html="division" className="mb-2">
                                    Division
                                </Label>
                                <Popover open={cbDivisionOpen} onOpenChange={setCbDivisionOpen} className="w-full">
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            aria-expanded={cbDivisionOpen}
                                            className="w-full justify-between"
                                            disabled={!data.group || divisionsLoading}
                                        >
                                            {divisionButtonLabel}
                                            <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className={'w-60 xl:w-120!'} onWheel={(e) => e.stopPropagation()}>
                                        <Command>
                                            <CommandInput placeholder="No Division Selected" />
                                            <CommandList>
                                                <CommandEmpty>No division found</CommandEmpty>
                                                <CommandGroup>
                                                    {divisionOptions.map((division) => (
                                                        <CommandItem
                                                            key={division.name}
                                                            value={division.name}
                                                            onSelect={(currentValue) => {
                                                                setData('division', currentValue === cbDivisionValue ? '' : currentValue);
                                                                setCbDivisionValue(currentValue === cbDivisionValue ? '' : currentValue);
                                                                setCbDivisionOpen(false);
                                                            }}
                                                        >
                                                            <CheckIcon
                                                                className={cn(
                                                                    'mr-2 h-4 w-4',
                                                                    cbDivisionValue === division.name ? 'opacity-100' : 'opacity-0',
                                                                )}
                                                            />
                                                            {division.name}
                                                        </CommandItem>
                                                    ))}
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                {errors.division && <p className="text-red-500">{errors.division}</p>}
                            </div>
                        )}
                    </div>
                    <div>
                        <Label html="description" className="mb-2">
                            Department description <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="description"
                            name="description"
                            placeholder="Enter Department Description"
                            rows="5"
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
                            onClick={cancelCreateDepartment}
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
