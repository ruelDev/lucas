import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Field, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import useSwalOptions from '@/hooks/useSwalOptions';
import AppLayout from '@/layouts/app-layout'; // adjust to your actual layout
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Landmark } from 'lucide-react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const breadcrumbs = [
    {
        title: 'Bank Management',
        href: '/bank-management',
    },
    {
        title: 'Add New Bank',
    },
];

export default function Create() {
    const { data, setData, post, reset, processing, errors } = useForm({
        name: '',
        abbreviation: '',
        description: '',
    });

    const ReactSwal = withReactContent(Swal);

    const { swalLoading, onSuccess } = useSwalOptions(reset);

    const handleSubmit = (e) => {
        e.preventDefault();

        post(route('bank-management.store'), {
            onStart: () => swalLoading('Adding'),
            onSuccess: (res) => onSuccess(res),
            onError: () => ReactSwal.close(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add New Bank" />

            <div className="mx-auto w-full max-w-3xl p-6">
                <Link
                    href={route('bank-management.index')}
                    className="mb-4 inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-900"
                >
                    <ArrowLeft className="h-4 w-4" />
                    Back to Bank Management
                </Link>

                <Card className="p-0">
                    <CardHeader className="flex flex-row items-start gap-3 space-y-0">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600">
                            <Landmark className="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle>Add New Bank</CardTitle>
                            <CardDescription>Register a new bank in the system.</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <FieldGroup className="flex flex-col gap-5">
                            <Field>
                                <Label htmlFor="bank" className="after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Bank Name
                                </Label>
                                <Input id="bank" value={data.name} onChange={(e) => setData('name', e.target.value)} placeholder="Enter Bank Name" />
                                <InputError message={errors.name} />
                            </Field>

                            <Field>
                                <Label htmlFor="abbreviation" className="after:ml-0.5 after:text-red-500 after:content-['*']">
                                    Abbreviation
                                </Label>
                                <Input
                                    id="abbreviation"
                                    value={data.abbreviation}
                                    onChange={(e) => setData('abbreviation', e.target.value)}
                                    placeholder="Enter Abbreviation"
                                />
                                <InputError message={errors.abbreviation} />
                            </Field>

                            <Field>
                                <Label htmlFor="description">
                                    Description <span className="text-neutral-500">(Optional)</span>
                                </Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Type your description here."
                                />
                                <InputError message={errors.description} />
                            </Field>
                        </FieldGroup>
                    </CardContent>

                    <CardFooter className="flex justify-between gap-2 border-t bg-neutral-50/50 pt-6">
                        <Button variant="outline" asChild>
                            <Link href={route('bank-management.index')}>Cancel</Link>
                        </Button>
                        <Button onClick={handleSubmit} disabled={processing} className="submit-button">
                            {processing ? 'Submitting...' : 'Submit'}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
