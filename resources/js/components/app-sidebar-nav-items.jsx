import { canAny } from '@/lib/can';
import { Building2, ChartColumn, FilesIcon, FileText, Pin, ReceiptText, Settings, Settings2 } from 'lucide-react';
import { PERMISSIONS } from './app-sidebar-permissions';

const { MAIN_MODULE, MASTER_SETUP, REPORTS, USER_SETTINGS } = PERMISSIONS;

const includeIf = (condition, items) => (condition ? items : []);

const all = (group) =>
    Object.values(group).flatMap((value) => {
        if (Array.isArray(value)) {
            return value;
        }

        if (typeof value === 'object' && value !== null) {
            return all(value);
        }

        return [value];
    });

const mainModuleItems = () => [
    ...includeIf(canAny(all(MAIN_MODULE)), [
        {
            title: 'Main Module',
            icon: FilesIcon,
            items: [
                ...includeIf(canAny(MAIN_MODULE.CFP), [
                    {
                        title: 'Certificate of Fullpayment',
                        href: '/certificate-of-full-payment',
                        match: '/certificate-of-full-payment*',
                        permission: 'certificate_fullpayment.view',
                    },
                ]),
                ...includeIf(canAny(MAIN_MODULE.OSF), [
                    {
                        title: 'Offline Search Facility',
                        href: '/offline-search-facility',
                        match: '/offline-search-facility*',
                        permission: 'offline_search_facility.view',
                    },
                ]),
                ...includeIf(canAny(MAIN_MODULE.OC), [
                    {
                        title: 'Out Collection',
                        href: '/out-collection',
                        match: '/out-collection*',
                        permission: 'out_collection.view',
                    },
                ]),
            ],
        },
    ]),
];

const reportItems = () => [
    ...includeIf(canAny(all(REPORTS)), [
        {
            title: 'Reports',
            icon: FileText,
            items: [
                ...includeIf(canAny(all(REPORTS.OC)), [
                    {
                        title: 'Out Collection',
                        icon: ReceiptText,
                        items: [
                            ...includeIf(canAny(REPORTS.OC.DCR), [
                                {
                                    title: 'Daily Out Collection',
                                    href: '/out-collection-report/daily-collection',
                                    match: '/out-collection-report/daily-collection*',
                                    permission: 'daily_collection_report.view',
                                },
                            ]),
                            ...includeIf(canAny(REPORTS.OC.OCR), [
                                {
                                    title: 'Authorized Report',
                                    href: '/out-collection-report/authorized',
                                    match: '/out-collection-report/authorized*',
                                    permission: 'out_collection_report.view',
                                },
                                {
                                    title: 'Unauthorized Report',
                                    href: '/out-collection-report/unauthorized',
                                    match: '/out-collection-report/unauthorized*',
                                    permission: 'out_collection_report.view',
                                },
                            ]),
                            ...includeIf(canAny(REPORTS.OC.LPR), [
                                {
                                    title: 'LMS Posting',
                                    icon: Pin,
                                    items: [
                                        {
                                            title: 'Finnone UA',
                                            href: '/out-collection-report/lms-posting/finnone-ua',
                                            match: '/out-collection-report/lms-posting/finnone-ua*',
                                            permission: 'lms_posting_report.view',
                                        },
                                        {
                                            title: 'Finnone QR',
                                            href: '/out-collection-report/lms-posting/finnone-qr',
                                            match: '/out-collection-report/lms-posting/finnone-qr*',
                                            permission: 'lms_posting_report.view',
                                        },
                                        {
                                            title: 'Newgen QR/UA',
                                            href: '/out-collection-report/lms-posting/newgen-qr-ua',
                                            match: '/out-collection-report/lms-posting/newgen-qr-ua*',
                                            permission: 'lms_posting_report.view',
                                        },
                                    ],
                                },
                            ]),
                        ],
                    },
                ]),
                ...includeIf(canAny(REPORTS.RC), [
                    {
                        title: 'Receipt Converter',
                        href: '/receipt-converter',
                        match: '/receipt-converter*',
                        permission: 'receipt_converter.view',
                    },
                ]),
                ...includeIf(canAny(REPORTS.UR), [
                    {
                        title: 'User Reports',
                        href: '/user-reports',
                        match: '/user-reports*',
                        permission: 'user_management.export',
                    },
                ]),
            ],
        },
    ]),
];

const masterSetupItems = () => [
    ...includeIf(canAny(all(MASTER_SETUP)), [
        {
            title: 'Master Setup',
            icon: Settings2,
            items: [
                ...includeIf(canAny(all(MASTER_SETUP.ORG)), [
                    {
                        title: 'Organization',
                        icon: Building2,
                        items: [
                            ...includeIf(canAny(MASTER_SETUP.ORG.BM), [
                                {
                                    title: 'Branch',
                                    href: '/branch-management',
                                    match: '/branch-management*',
                                    permission: 'branch_management.view',
                                },
                            ]),
                            ...includeIf(canAny(MASTER_SETUP.ORG.DLM), [
                                {
                                    title: 'Dealer',
                                    href: '/dealer-management',
                                    match: '/dealer-management*',
                                    permission: 'dealer_management.view',
                                },
                            ]),
                            ...includeIf(canAny(MASTER_SETUP.ORG.GM), [
                                {
                                    title: 'Group',
                                    href: '/group-management',
                                    match: '/group-management*',
                                    permission: 'group_management.view',
                                },
                            ]),
                            ...includeIf(canAny(MASTER_SETUP.ORG.DVM), [
                                {
                                    title: 'Division',
                                    href: '/division-management',
                                    match: '/division-management*',
                                    permission: 'division_management.view',
                                },
                            ]),
                            ...includeIf(canAny(MASTER_SETUP.ORG.DPM), [
                                {
                                    title: 'Department',
                                    href: '/department-management',
                                    match: '/department-management*',
                                    permission: 'department_management.view',
                                },
                            ]),
                            ...includeIf(canAny(MASTER_SETUP.ORG.SM), [
                                {
                                    title: 'Section',
                                    href: '/section-management',
                                    match: '/section-management*',
                                    permission: 'section_management.view',
                                },
                            ]),
                        ],
                    },
                ]),
                ...includeIf(canAny(MASTER_SETUP.BM), [
                    {
                        title: 'Bank Management',
                        href: '/bank-management',
                        match: '/bank-management*',
                        permission: 'bank_management.view',
                    },
                ]),
            ],
        },
    ]),
];

const userSettingsItems = () => [
    ...includeIf(canAny(all(USER_SETTINGS)), [
        {
            title: 'User Settings',
            icon: Settings,
            items: [
                ...includeIf(canAny(USER_SETTINGS.UM), [
                    {
                        title: 'User Management',
                        href: '/user-management',
                        match: '/user-management*',
                        permission: 'user_management.view',
                    },
                ]),
                ...includeIf(canAny(USER_SETTINGS.RM), [
                    {
                        title: 'Role Management',
                        href: '/role-management',
                        match: '/role-management*',
                        permission: 'role_management.view',
                    },
                ]),
                ...includeIf(canAny(USER_SETTINGS.AL), [
                    {
                        title: 'Audit Logs',
                        href: '/audit-logs',
                        permission: 'audit_logs.view',
                    },
                ]),
            ],
        },
    ]),
];

const navItems = () => [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: ChartColumn,
    },
    ...mainModuleItems(),
    ...reportItems(),
    ...masterSetupItems(),
    ...userSettingsItems(),
];

export default navItems;
