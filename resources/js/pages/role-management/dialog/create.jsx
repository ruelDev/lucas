/* eslint-disable react/prop-types */
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm } from '@inertiajs/react';
import { LoaderCircle, Search } from 'lucide-react';
import { useMemo, useState } from 'react';
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

const breadcrumbs = [
    {
        title: 'Roles',
        href: '/role-management',
    },
    {
        title: 'Create Roles',
        href: '/role-management/create',
    },
];

export default function CreateRole({ permissions }) {
    const permissionMap = {};
    const allActions = new Set();

    permissions.forEach((perm) => {
        const [module, action] = perm.split('.');
        if (!permissionMap[module]) permissionMap[module] = {};
        permissionMap[module][action] = true;
        allActions.add(action);
    });

    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        permissions: [],
    });

    const [searchTerm, setSearchTerm] = useState('');

    const filteredModules = useMemo(() => {
        if (!searchTerm.trim()) return Object.entries(permissionMap);

        return Object.entries(permissionMap).filter(([module]) => module.toLowerCase().includes(searchTerm.toLowerCase().replace(' ', '_')));
    }, [permissionMap, searchTerm]);

    const handleCheckboxChange = (permission, checked) => {
        if (checked) {
            setData('permissions', [...data.permissions, permission]);
        } else {
            setData(
                'permissions',
                data.permissions.filter((p) => p !== permission),
            );
        }
    };

    const handleSelectAll = (module, perms) => {
        const newPerms = Object.keys(perms).map((a) => `${module}.${a}`);
        setData('permissions', [...new Set([...data.permissions, ...newPerms])]);
    };

    const handleRemoveAll = (module, perms) => {
        const removePerms = Object.keys(perms).map((a) => `${module}.${a}`);
        setData(
            'permissions',
            data.permissions.filter((p) => !removePerms.includes(p)),
        );
    };

    const handleSelectAllModules = () => {
        const allFilteredPerms = [];
        filteredModules.forEach(([module, perms]) => {
            Object.keys(perms).forEach((action) => {
                allFilteredPerms.push(`${module}.${action}`);
            });
        });
        setData('permissions', [...new Set([...data.permissions, ...allFilteredPerms])]);
    };

    const handleRemoveAllModules = () => {
        const removePerms = [];
        filteredModules.forEach(([module, perms]) => {
            Object.keys(perms).forEach((action) => {
                removePerms.push(`${module}.${action}`);
            });
        });
        setData(
            'permissions',
            data.permissions.filter((p) => !removePerms.includes(p)),
        );
    };

    const ReactSwal = withReactContent(Swal);

    const handlRoleSubmit = (e) => {
        e.preventDefault();

        ReactSwal.fire({
            title: 'Confirm Submit?',
            html: `<p style="font-size: 16px">Are you sure you want to save this new role?</p>`,
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
                
                post(route('role-management.store'), {
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
                    },
                    onError: () => {
                        ReactSwal.fire({
                            title: 'Error',
                            text: 'Failed to create Role',
                            icon: 'error',
                            confirmButtonText: 'Ok, got it!',
                            confirmButtonColor: '#1B4298',
                        });
                    },
                });
            }
        });
    };

    const sortedActions = Array.from(allActions);

    const formatModule = (mod) => mod.replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase());

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />
            <div className="space-y-4 p-6">
                <Card>
                    <CardContent>
                        <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                            <div>
                                <HeadingSmall title="Create New Role" />
                                <Separator />
                                <form onSubmit={handlRoleSubmit} className="mt-10 space-y-6">
                                    <div>
                                        <Label htmlFor="name" className="font-bold">
                                            Role Name <span className="text-red-500">*</span>
                                        </Label>
                                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="mt-2" />
                                        {errors.name && <p className="mt-1 text-sm text-red-500">{errors.name}</p>}
                                    </div>
                                    <div>
                                        <Label html="description" className="mb-2 font-bold">
                                            Role Description <span className="text-red-500">*</span>
                                        </Label>
                                        <Textarea
                                            id="description"
                                            name="description"
                                            placeholder="Enter Role description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            required
                                        />
                                        {errors.description && <p className="mt-1 text-sm text-red-500">{errors.description}</p>}
                                    </div>

                                    <Separator />

                                    <div className="ga flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Search className="h-4 w-4 text-gray-500" />
                                            <Input
                                                type="text"
                                                placeholder="Search Modules..."
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                className="w-64"
                                            />
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={handleSelectAllModules}
                                                className="border-blue-600 text-blue-600 hover:bg-blue-50"
                                            >
                                                Select All Modules
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                onClick={handleRemoveAllModules}
                                                className="border-blue-600 text-blue-600 hover:bg-blue-50"
                                            >
                                                Remove All Modules
                                            </Button>
                                        </div>
                                    </div>

                                    {searchTerm && (
                                        <p className="text-sm text-gray-600">
                                            Showing {filteredModules.length} module(s) matching "{searchTerm}"
                                        </p>
                                    )}

                                    <div className="overflow-auto">
                                        <table className="mt-4 w-full border-collapse text-sm">
                                            <thead>
                                                <tr>
                                                    <th className="border-b p-2 text-left">
                                                        Module <span className="text-red-500">*</span>
                                                    </th>
                                                    {sortedActions.map((action) => (
                                                        <th key={action} className="border-b p-2 text-center capitalize">
                                                            {action}
                                                        </th>
                                                    ))}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {filteredModules.length === 0 ? (
                                                    <tr>
                                                        <td colSpan={sortedActions.length + 2} className="p-4 text-center text-gray-500">
                                                            No Modules found matching your search
                                                        </td>
                                                    </tr>
                                                ) : (
                                                    filteredModules.map(([module, perms]) => (
                                                        <tr key={module} className="border-b hover:bg-gray-50 dark:hover:bg-gray-900">
                                                            <td className="p-2 font-medium">{formatModule(module)}</td>
                                                            {sortedActions.map((action) => {
                                                                const permKey = `${module}.${action}`;
                                                                return (
                                                                    <td key={action} className="p-2 text-center">
                                                                        {perms[action] ? (
                                                                            <Checkbox
                                                                                id={permKey}
                                                                                checked={data.permissions.includes(permKey)}
                                                                                onCheckedChange={(checked) => handleCheckboxChange(permKey, checked)}
                                                                                className="mx-auto border-gray-500 bg-gray-100 text-white data-[state=checked]:border-blue-600 data-[state=checked]:bg-blue-600"
                                                                            />
                                                                        ) : (
                                                                            <span className="text-gray-400">-</span>
                                                                        )}
                                                                    </td>
                                                                );
                                                            })}
                                                            <td className="p-2 text-center">
                                                                <div className="flex justify-center gap-2">
                                                                    <Button
                                                                        type="button"
                                                                        size="sm"
                                                                        variant="outline"
                                                                        onClick={() => handleSelectAll(module, perms)}
                                                                    >
                                                                        Select All
                                                                    </Button>
                                                                    <Button
                                                                        type="button"
                                                                        size="sm"
                                                                        variant="outline"
                                                                        onClick={() => handleRemoveAll(module, perms)}
                                                                    >
                                                                        Remove All
                                                                    </Button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    ))
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                    {errors.permissions && <p className="text-sm text-red-500">{errors.permissions}</p>}
                                    <Separator />
                                    <div className="flex items-center justify-center gap-4">
                                        <Button
                                            asChild
                                            variant="outline"
                                            className="h-10 border-blue-800 bg-white px-6 py-4 text-blue-600 transition duration-300 hover:bg-linear-to-r hover:from-blue-950 hover:to-blue-900 hover:text-white"
                                        >
                                            <Link href="/role-management">Back</Link>
                                        </Button>
                                        <Button
                                            type="submit"
                                            className="h-10 bg-linear-to-r from-blue-900 to-blue-800 px-6 py-4 text-white transition duration-300 hover:from-blue-950 hover:to-blue-900"
                                            disabled={processing}
                                        >
                                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                            {processing ? 'Submitting...' : 'Submit'}
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
