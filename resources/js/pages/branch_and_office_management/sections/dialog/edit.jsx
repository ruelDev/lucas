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

export default function EditSectionDialog({ section }) {
    const isInitialLoad = React.useRef(true);
    const abortControllerRef = React.useRef(null);
    const isDivisionResetRef = React.useRef(false);

    const [isOpen, setIsOpen] = React.useState(false);
    const [processing, setProcessing] = React.useState(false);
    const [errors, setErrors] = React.useState(false);

    const { data, setData } = useForm({
        name: section.name,
        code: section.code,
        description: section.description,
        group: section.group ?? '',
        division: section.division ?? '',
        department: section.department ?? '',
        status: section.status,
        remarks: section.remarks,
    });

    const ReactSwal = withReactContent(Swal);

    const [cbGroupOpen, setCbGroupOpen] = React.useState(false);
    const [cbGroupValue, setCbGroupValue] = React.useState(section.group);

    const [cbDivisionOpen, setCbDivisionOpen] = React.useState(false);
    const [cbDivisionValue, setCbDivisionValue] = React.useState(section.division);

    const [cbDepartmentOpen, setCbDepartmentOpen] = React.useState(false);
    const [cbDepartmentValue, setCbDepartmentValue] = React.useState(section.department);

    const [groupOptions, setGroupOptions] = React.useState([]);

    const [divisionsLoading, setDivisionsLoading] = React.useState(false);
    const [departmentsLoading, setDepartmentsLoading] = React.useState(false);

    React.useEffect(() => {
        axios.get('/get-group-options').then((res) => {
            setGroupOptions(res.data);
        });
    }, []);

    const [divisionOptions, setDivisionOptions] = React.useState([]);
    const [departmentOptions, setDepartmentOptions] = React.useState([]);

    React.useEffect(() => {
        if (!data.group) {
            setDivisionOptions([]);
            setDepartmentOptions([]);
            setCbDivisionValue('');
            setCbDepartmentValue('');
            return;
        }

        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        const controller = new AbortController();
        abortControllerRef.current = controller;

        const fetchGroupData = async (divisionForRequest) => {
            try {
                setDivisionsLoading(true);

                const divRes = await axios.get('/get-division-options', {
                    params: { group: data.group },
                    signal: controller.signal,
                });

                if (controller.signal.aborted) return;

                const divisions = Array.isArray(divRes.data) ? divRes.data : [];

                if (!Array.isArray(divRes.data)) {
                    console.warn('Unexpected /get-division-options response shape:', divRes.data);
                }

                setDivisionOptions(divisions);

                setDivisionsLoading(false);

                setDepartmentsLoading(true);

                const deptRes = await axios.get('/get-department-options', {
                    params: {
                        group: data.group,
                        ...(divisionForRequest ? { division: data.division } : {}),
                    },
                    signal: controller.signal,
                });

                if (controller.signal.aborted) return;

                setDepartmentOptions(deptRes.data);
                setDepartmentsLoading(false);
            } catch (err) {
                setDivisionsLoading(false);
                setDepartmentsLoading(false);

                if (err.name !== 'CanceledError' && err.code !== 'ERR_CANCELED') {
                    console.error('Error loading group data:', err);
                }
            }
        };

        let divisionForRequest = null;

        if (isInitialLoad.current) {
            divisionForRequest = data.division;
        } else {
            if (data.division !== '') {
                isDivisionResetRef.current = true;
            }

            setDivisionOptions([]);
            setDepartmentOptions([]);

            setCbDivisionValue('');
            setCbDepartmentValue('');

            setData((prev) => ({
                ...prev,
                division: '',
                department: '',
            }));
        }

        fetchGroupData(divisionForRequest);
    }, [data.group]);

    React.useEffect(() => {
        if (isInitialLoad.current) return;

        if (isDivisionResetRef.current) {
            isDivisionResetRef.current = false;
            return;
        }

        if (!data.group) return;

        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        const controller = new AbortController();
        abortControllerRef.current = controller;

        setDepartmentOptions([]);
        setCbDepartmentValue('');

        setData((prev) => ({
            ...prev,
            department: '',
        }));

        setDepartmentsLoading(true);

        const fetchDeptData = async () => {
            try {
                const res = await axios.get('/get-department-options', {
                    params: {
                        group: data.group,
                        ...(data.division ? { division: data.division } : {}),
                    },
                    signal: controller.signal,
                });

                if (controller.signal.aborted) return;

                setDepartmentOptions(res.data);
                setDepartmentsLoading(false);
            } catch (err) {
                setDepartmentsLoading(false);

                if (err.name !== 'CanceledError' && err.code !== 'ERR_CANCELED') {
                    console.error('Error loading department options:', err);
                }
            }
        };

        fetchDeptData();
    }, [data.division]);

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

    const selectedDepartmentLabel = cbDepartmentValue ? departmentOptions.find((d) => d.name === cbDepartmentValue)?.name : 'Select Department...';

    const departmentButtonLabel = departmentsLoading ? (
        <span className="text-muted-foreground flex items-center gap-2">
            <LoaderCircle className="h-4 w-4 animate-spin" />
            Loading...
        </span>
    ) : (
        selectedDepartmentLabel
    );

    const handleSectionSubmit = async (e) => {
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
            const response = await axios.put(route('section-management.update', section.id), data);
            ReactSwal.fire({
                title: 'Success',
                text: response.data.message,
                icon: 'success',
                iconColor: '#1B4298',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            setIsOpen(false);
            router.get('/section-management');
        } catch (err) {
            setProcessing(false);
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors);
                ReactSwal.close();
            }
        }
    };

    const cancelCreateSection = () => {
        setData('name', '');
        setData('description', '');
        setData('group', '');
        setData('division', '');
        setIsOpen(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            {can('section_management.edit') && (
                <DialogTrigger asChild>
                    <div className="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1 hover:bg-blue-100 hover:text-blue-600">
                        <Pencil className="h-4 w-4" />
                        <span className="text-sm">Edit</span>
                    </div>
                </DialogTrigger>
            )}
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Section</DialogTitle>
                    <DialogDescription>Edit a Section.</DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSectionSubmit} className="space-y-4">
                    <div>
                        <Label html="status" className="mb-2">
                            Section Status <span className="text-red-500">*</span>
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
                            Section Name <span className="text-red-500">*</span>
                        </Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            placeholder="Enter Section Name"
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
                            placeholder="Enter Section Code"
                            value={data.code}
                            onChange={(e) => setData('code', e.target.value)}
                            required
                        />
                        {errors.code && <p className="text-red-500">{errors.code}</p>}
                    </div>
                    <div className="grid grid-cols-1 gap-2 space-y-2 md:grid-cols-1">
                        <div>
                            <Label html="group" className="mb-2">
                                Group
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
                        <div>
                            <Label htmlFor="division" className={`mb-2`}>
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
                        <div>
                            <Label htmlFor="department" className={`mb-2`}>
                                Department
                            </Label>
                            <Popover open={cbDepartmentOpen} onOpenChange={setCbDepartmentOpen} className="w-full">
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        aria-expanded={cbDivisionOpen}
                                        className="w-full justify-between"
                                        disabled={!data.group || divisionsLoading}
                                    >
                                        {departmentButtonLabel}
                                        <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className={'w-60 xl:w-120!'} onWheel={(e) => e.stopPropagation()}>
                                    <Command>
                                        <CommandInput placeholder="No Department Selected" />
                                        <CommandList>
                                            <CommandEmpty>No department found</CommandEmpty>
                                            <CommandGroup>
                                                {departmentOptions.map((department) => (
                                                    <CommandItem
                                                        key={department.name}
                                                        value={department.name}
                                                        onSelect={(currentValue) => {
                                                            setData('department', currentValue === cbDepartmentValue ? '' : currentValue);
                                                            setCbDepartmentValue(currentValue === cbDepartmentValue ? '' : currentValue);
                                                            setCbDepartmentOpen(false);
                                                        }}
                                                    >
                                                        <CheckIcon
                                                            className={cn(
                                                                'mr-2 h-4 w-4',
                                                                cbDepartmentValue === department.name ? 'opacity-100' : 'opacity-0',
                                                            )}
                                                        />
                                                        {department.name}
                                                    </CommandItem>
                                                ))}
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            {errors.department && <p className="text-red-500">{errors.department}</p>}
                        </div>
                    </div>
                    <div>
                        <Label html="description" className="mb-2">
                            Section description <span className="text-red-500">*</span>
                        </Label>
                        <Textarea
                            id="description"
                            name="description"
                            placeholder="Enter Section Description"
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
                            onClick={cancelCreateSection}
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
