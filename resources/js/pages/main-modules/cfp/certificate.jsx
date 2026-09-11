/* eslint-disable react/prop-types */
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import axios from 'axios';
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react';
import { useEffect, useState } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const BREADCRUMBS = [
    {
        title: 'Certificate of Fullpayment',
        href: '/certificate-of-full-payment',
    },
    {
        title: 'Generate',
        href: '/reports/generate',
    },
];

const BUTTON_STYLES = {
    primary:
        'mt-5 h-10 bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900',
    secondary: 'mt-5 mr-2 h-10 border-3 border-blue-900 text-blue-900',
};

const SWAL_CONFIG = {
    iconColor: '#1B4298',
    confirmButtonColor: '#1B4298',
};

// Utility Functions
const getDayWithSuffix = (day) => {
    const dayNum = parseInt(day, 10);
    if (dayNum >= 11 && dayNum <= 13) return `${dayNum}th`;

    const suffixes = { 1: 'st', 2: 'nd', 3: 'rd' };
    return `${dayNum}${suffixes[dayNum % 10] || 'th'}`;
};

const getCurrentDate = () => {
    const date = new Date();
    return {
        day: date.getDate().toString(),
        month: date.toLocaleString('default', { month: 'long' }),
        year: date.getFullYear().toString(),
    };
};

const formatCurrentDate = () => {
    const { day, month, year } = getCurrentDate();
    return {
        month,
        year,
        dayWithSuffix: getDayWithSuffix(parseInt(day)),
    };
};

// Custom Hooks
const useSignatory = (cbValue) => {
    const [signatory, setSignatory] = useState({
        branch: '',
        signatory: '',
        code: '',
        sss_id: '',
        tin_id: '',
        position: '',
    });

    useEffect(() => {
        if (!cbValue) return;
        axios
            .get('/certificate-of-full-payment/get-cfp-signatory', {
                params: { signatory_value: cbValue },
            })
            .then((res) => setSignatory(res.data[0]))
            .catch((error) => console.error('Error fetching signatory:', error));
    }, [cbValue]);

    return signatory;
};

const usePdfHandler = (flash) => {
    const ReactSwal = withReactContent(Swal);

    useEffect(() => {
        if (flash.success) {
            ReactSwal.close();
            ReactSwal.fire({
                title: 'Success',
                text: flash.success,
                icon: 'success',
                ...SWAL_CONFIG,
                confirmButtonText: 'View PDF',
            }).then((result) => {
                if (result.isConfirmed && flash.pdf_file) {
                    loadPdf(flash.pdf_file);
                }
            });
        }

        if (flash.error_generate_report) {
            ReactSwal.close();
            ReactSwal.fire({
                title: 'Error',
                text: flash.error_generate_report,
                icon: 'error',
                ...SWAL_CONFIG,
                confirmButtonText: 'Ok, got it!',
            });
        }
    }, [flash.success, flash.error_generate_report]);

    const loadPdf = (filename) => {
        axios
            .get(route('cfp.viewCfp'), {
                params: { filename },
                responseType: 'blob',
            })
            .then((response) => {
                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const pdfViewer = document.getElementById('pdfviewer');

                if (pdfViewer) {
                    pdfViewer.src = url;
                    setTimeout(() => window.URL.revokeObjectURL(url), 10000);
                }
            })
            .catch((error) => console.error('Error fetching PDF:', error));
    };

    return ReactSwal;
};

// Sub-components
const InfoRow = ({ label, value, className = '' }) => (
    <div className={`flex flex-row ${className}`}>
        <span className="w-24">{label}:</span>
        <span className="max-w-xs truncate">{value}</span>
    </div>
);

const ClientDetails = ({ customer }) => (
    <div className="mb-8">
        <p className="pb-6 font-bold">AGREEMENT NO.: {customer.loan_no}</p>
        <p className="pb-2 font-bold">Client Details</p>
        <InfoRow label="Name" value={customer.fullname} className="ml-12 pb-2" />
        <InfoRow label="Address" value={customer.address_details} className="ml-12 pb-2" />
    </div>
);

const AssetDetails = ({ customer }) => (
    <div className="mb-12">
        <p className="pb-2 font-bold">Asset Details</p>
        <InfoRow label="Brand" value={customer.brand} className="ml-12 pb-2" />
        <InfoRow label="Model" value={customer.model} className="ml-12 pb-2" />
        <InfoRow label="Engine No" value={customer.engineno} className="ml-12 pb-2" />
        <InfoRow label="Chassis No" value={customer.chassisno} className="ml-12 pb-2" />
        <InfoRow label="Color" value={customer.color} className="ml-12 pb-2" />
    </div>
);

const SignatoryCombobox = ({ open, onOpenChange, value, onValueChange, signatories, width = 'w-[300px]', setData }) => (
    <Popover open={open} onOpenChange={onOpenChange}>
        <PopoverTrigger asChild>
            <Button variant="outline" aria-expanded={open} className={`${width} justify-between`}>
                {value || 'Select Signatory...'}
                <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent className="w-[500px] p-0">
            <Command>
                <CommandInput placeholder="Search signatory..." />
                <CommandList>
                    <CommandEmpty>No signatory found</CommandEmpty>
                    <CommandGroup>
                        {signatories.map((sig) => (
                            <CommandItem
                                key={sig.value}
                                value={sig.value}
                                onSelect={(currentValue) => {
                                    onValueChange(currentValue === value ? '' : currentValue);
                                    setData('signatory', currentValue);
                                }}
                            >
                                <CheckIcon className={cn('mr-2 h-4 w-4', value === sig.value ? 'opacity-100' : 'opacity-0')} />
                                {sig.value}
                            </CommandItem>
                        ))}
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
);

const SignatoryDetails = ({ signatory }) => {
    if (!signatory.signatory) return null;

    return (
        <>
            <InfoRow label="Position" value={signatory.position} className="ml-12 pb-2" />
            <InfoRow label="Branch" value={signatory.branch} className="ml-12 pb-2" />
        </>
    );
};

const SignatoryForm = ({ company, signatories, cbOpen, setCbOpen, cbValue, onSignatorySelect, signatory, onSubmit, setData }) => {
    const companyTitle = company === 'BMI' ? 'Bmi' : 'Bfc';
    const comboboxWidth = company === 'BMI' ? 'w-[300px]' : 'w-[500px]';

    return (
        <form onSubmit={onSubmit} className="grid grid-cols-2">
            <div>
                <p className="pb-2 font-bold">{companyTitle} Authorized Signatory</p>
                <div className="ml-12 flex flex-row pb-4">
                    <p className="mr-2">Signatory:</p>
                    <SignatoryCombobox
                        open={cbOpen}
                        onOpenChange={setCbOpen}
                        value={cbValue}
                        onValueChange={onSignatorySelect}
                        signatories={signatories}
                        width={comboboxWidth}
                        setData={setData}
                    />
                </div>
                <SignatoryDetails signatory={signatory} />
                <div className="mt-5 flex gap-2">
                    <Button asChild variant="outline" className={BUTTON_STYLES.secondary}>
                        <Link href={route('cfp.index')}>Back</Link>
                    </Button>

                    <Button type="submit" className={BUTTON_STYLES.primary}>
                        Generate
                    </Button>
                </div>
            </div>
        </form>
    );
};

export default function Certificate({ selectedCustomers, selectedSignatory }) {
    const { props } = usePage();

    const { auth } = usePage().props;
    const company = auth.user.company;

    const selectedCustomer = selectedCustomers[0] || {};

    const [cbOpen, setCbOpen] = useState(false);
    const [cbValue, setCbValue] = useState('');

    const signatory = useSignatory(cbValue);
    const ReactSwal = usePdfHandler(props.flash);

    const { month, year, dayWithSuffix } = formatCurrentDate();

    const { data, setData } = useForm({
        loan: selectedCustomer.loan_no ?? '',
        fullname: selectedCustomer.fullname ?? '',
        address: selectedCustomer.address_details ?? '',
        brand: selectedCustomer.brand ?? '',
        model: selectedCustomer.model ?? '',
        engineno: selectedCustomer.engineno ?? '',
        chassis: selectedCustomer.chassisno ?? '',
        color: selectedCustomer.color ?? '',
        signatory: '',
        position: '',
        branch: '',
        currentmonth: month ?? '',
        currentyear: year ?? '',
        daysuffix: dayWithSuffix ?? '',
    });

    // Update form data when signatory changes
    useEffect(() => {
        if (signatory.signatory) {
            setData((prevData) => ({
                ...prevData,
                signatory: signatory.signatory,
                position: signatory.position,
                branch: signatory.branch,
            }));
        }
    }, [signatory]);

    const handleGenerateReport = (e) => {
        e.preventDefault();

        if (!data.signatory.trim()) {
            ReactSwal.fire({
                title: 'Validation Error',
                text: 'Please select a signatory',
                icon: 'error',
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
            return;
        }

        ReactSwal.fire({
            title: <p>Generating...</p>,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                ReactSwal.showLoading();
            },
        });

        axios
            .post(route('cfp.generateJasperPdfCfp'), data, { responseType: 'blob' })
            .then((response) => {
                ReactSwal.fire({
                    title: 'Success',
                    text: 'CFP File generated successfully',
                    icon: 'success',
                    iconColor: '#1B4298',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                });

                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const pdfViewer = document.getElementById('pdfviewer');

                if (pdfViewer) {
                    pdfViewer.src = url;
                    setTimeout(() => window.URL.revokeObjectURL(url), 10000);
                }
            })
            .catch((error) =>
                ReactSwal.fire({
                    title: 'Error',
                    text: 'Failed to submit request. Please Try again',
                    icon: 'error',
                    confirmButtonText: 'Ok, got it!',
                    confirmButtonColor: '#1B4298',
                }),
            );
    };

    const handleSignatorySelect = (value) => {
        setCbValue(value);
        setCbOpen(false);
    };

    const authorizedSignatories = selectedSignatory.map((Signatory) => ({
        value: Signatory,
    }));

    return (
        <AppLayout breadcrumbs={BREADCRUMBS}>
            <Head title="Certificate" />

            <div className="flex space-x-3">
                <div className="pt-8 pr-0 pl-35 text-sm">
                    <div className="col-span-1 text-left">
                        <ClientDetails customer={selectedCustomer} />
                        <AssetDetails customer={selectedCustomer} />
                        <SignatoryForm
                            company={company}
                            signatories={authorizedSignatories}
                            cbOpen={cbOpen}
                            setCbOpen={setCbOpen}
                            cbValue={cbValue}
                            onSignatorySelect={handleSignatorySelect}
                            signatory={signatory}
                            onSubmit={handleGenerateReport}
                            setData={setData}
                        />
                    </div>
                </div>
                <div className="h-200 w-400 px-5 md:px-8 lg:px-16">
                    <iframe id="pdfviewer" className="h-full w-full" title="PDF Viewer"></iframe>
                </div>
            </div>
        </AppLayout>
    );
}
