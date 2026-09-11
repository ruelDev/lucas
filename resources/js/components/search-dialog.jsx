/* eslint-disable react/prop-types */
'use client';
import { CommandDialog, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { usePage } from '@inertiajs/react';
import {
    ArrowLeftRight,
    BadgeCheck,
    Building2,
    CarFront,
    FileBox,
    FolderKanban,
    GitBranch,
    Home,
    Layers,
    Network,
    Receipt,
    ScrollText,
    Search,
    SearchX,
    Signature,
    User2,
    UserCog2,
    UserX,
} from 'lucide-react';
import * as React from 'react';
import { PERMISSIONS } from './app-sidebar-permissions';
import { Button } from './ui/button';

export function SearchDialog({ sidebarOpen }) {
    const [open, setOpen] = React.useState(false);

    const { permissions } = usePage().props.auth;

    React.useEffect(() => {
        const down = (e) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setOpen((open) => !open);
            }
        };

        document.addEventListener('keydown', down);
        return () => document.removeEventListener('keydown', down);
    }, []);

    const hasPermission = (permissions, required) => {
        if (required === null) return true;
        if (Array.isArray(required)) return required.some((p) => permissions.includes(p));

        return permissions.includes(required);
    };

    const { MAIN_MODULE, MASTER_SETUP, REPORTS, USER_SETTINGS } = PERMISSIONS;

    const menuGroups = [
        {
            heading: 'General',
            items: [{ name: 'Dashboard', icon: <Home className="h-4 w-4" />, href: '/dashboard', permission: null }],
        },
        {
            heading: 'Main Module',
            items: [
                {
                    name: 'Certificate of Fullpayment',
                    icon: <BadgeCheck className="h-4 w-4" />,
                    href: '/certificate-of-full-payment',
                    permission: MAIN_MODULE.CFP,
                },
                {
                    name: 'Offline Search Facility',
                    icon: <SearchX className="h-4 w-4" />,
                    href: '/offline-search-facility',
                    permission: MAIN_MODULE.OSF,
                },
                {
                    name: 'Out Collection',
                    icon: <Receipt className="h-4 w-4" />,
                    href: '/out-collection',
                    permission: MAIN_MODULE.OC,
                },
            ],
        },
        {
            heading: 'Reports',
            items: [
                {
                    name: 'Receipt Converter',
                    icon: <ArrowLeftRight className="h-4 w-4" />,
                    href: '/receipt-converter',
                    permission: REPORTS.RC,
                },
            ],
        },
        {
            heading: 'Out Collection Reports',
            items: [
                {
                    name: 'Daily Collection',
                    icon: <FileBox className="h-4 w-4" />,
                    href: '/out-collection-report/daily-collection',
                    permission: REPORTS.RPR,
                },
                {
                    name: 'Authorized',
                    icon: <Signature className="h-4 w-4" />,
                    href: '/out-collection-report/authorized',
                    permission: REPORTS.RPR,
                },
                {
                    name: 'Unauthorized',
                    icon: <UserX className="h-4 w-4" />,
                    href: '/out-collection-report/unauthorized',
                    permission: REPORTS.RPR,
                },
            ],
        },
        {
            heading: 'Organizations',
            items: [
                {
                    name: 'Branch',
                    icon: <GitBranch className="h-4 w-4" />,
                    href: '/branch-management',
                    permission: MASTER_SETUP.ORG,
                },
                {
                    name: 'Dealer',
                    icon: <CarFront className="h-4 w-4" />,
                    href: '/dealer-management',
                    permission: MASTER_SETUP.ORG,
                },
                {
                    name: 'Group',
                    icon: <Layers className="h-4 w-4" />,
                    href: '/group-management',
                    permission: MASTER_SETUP.ORG,
                },
                {
                    name: 'Division',
                    icon: <Network className="h-4 w-4" />,
                    href: '/division-management',
                    permission: MASTER_SETUP.ORG,
                },
                {
                    name: 'Department',
                    icon: <Building2 className="h-4 w-4" />,
                    href: '/department-management',
                    permission: MASTER_SETUP.ORG,
                },
                {
                    name: 'Section',
                    icon: <FolderKanban className="h-4 w-4" />,
                    href: '/section-management',
                    permission: MASTER_SETUP.ORG,
                },
            ],
        },
        {
            heading: 'User Settings',
            items: [
                { name: 'User Management', icon: <User2 className="h-4 w-4" />, href: '/user-management', permission: USER_SETTINGS.UM },
                { name: 'Role Management', icon: <UserCog2 className="h-4 w-4" />, href: '/role-management', permission: USER_SETTINGS.RM },
                { name: 'Audit Logs', icon: <ScrollText className="h-4 w-4" />, href: '/audit-logs', permission: USER_SETTINGS.AL },
            ],
        },
    ];

    // Filter items per group, then remove empty groups
    const filteredGroups = menuGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => hasPermission(permissions, item.permission)),
        }))
        .filter((group) => group.items.length > 0);

    return (
        <>
            <Button
                variant="outline"
                className={[
                    'mt-1 flex text-zinc-500 dark:bg-neutral-700! dark:hover:bg-neutral-900!',
                    sidebarOpen ? 'justify-between' : 'justify-between md:justify-center',
                ]}
                onClick={() => {
                    setOpen(!open);
                }}
            >
                <div className="flex items-center gap-2">
                    <Search />
                    {sidebarOpen ? 'Search Menu' : <span className="md:hidden">Search Menu</span>}
                </div>
                {sidebarOpen ? (
                    <kbd className="bg-muted pointer-events-none inline-flex h-5 items-center gap-1 rounded border px-1.5 font-mono text-[10px] font-medium opacity-100 select-none">
                        <span className="text-xs">CTRL</span>K
                    </kbd>
                ) : (
                    <kbd className="bg-muted pointer-events-none inline-flex h-5 items-center gap-1 rounded border px-1.5 font-mono text-[10px] font-medium opacity-100 select-none md:hidden">
                        <span className="text-xs">CTRL</span>K
                    </kbd>
                )}
            </Button>

            <CommandDialog open={open} onOpenChange={setOpen}>
                <CommandInput placeholder="Type a command or search..." />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    {filteredGroups.map((group) => (
                        <CommandGroup key={group.heading} heading={group.heading}>
                            {group.items.map((item) => (
                                <CommandItem key={item.name} onSelect={() => (window.location.href = item.href)}>
                                    {item.icon}
                                    <span>{item.name}</span>
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    ))}
                </CommandList>
            </CommandDialog>
        </>
    );
}
