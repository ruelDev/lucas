/* eslint-disable react/prop-types */
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import CertificateOfFullPaymentCharts from './certificate-of-full-payment/certificate-of-full-payment-charts';
import ReceiptConverterCharts from './receipt-converter/receipt-converter-charts';
import RoleCharts from './role-management/role-charts';
import UserCharts from './user-management/user-charts';
import MasterSetupCharts from './master-setup/master-setup-charts';
import { can, canAny } from '@/lib/can';

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ data }) { 
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {can('user_management.view') && <UserCharts data={data} />}

                {can('role_management.view') && <RoleCharts data={data} />}

                {canAny([
                    'branch_management.view',
                    'dealer_management.view',
                    'group_management.view',
                    'division_management.view',
                    'department_management.view',
                    'section_management.view'
                ]) && <MasterSetupCharts data={data} />}

                {can('certificate_fullpayment.view') && <CertificateOfFullPaymentCharts data={data} />}

                {can('receipt_converter.view') && <ReceiptConverterCharts data={data} />}
            </div>
        </AppLayout>
    );
}
