/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import CustomerCard from './customer-data/CustomerCard';
import { useCustomerData } from './customer-data/hooks/useCustomerData';
import { useLMSData } from './lms-data/hooks/useLMSData';
import LMSTable from './lms-data/LMSTable';
import { useLOSData } from './los-data/hooks/useLOSData';
import LOSTable from './los-data/LOSTable';

const breadcrumbs = [
    {
        title: 'Offline Search Facility',
        href: '/offline-search-facility',
    },
];

export default function ViewCustomer({ customerID }) {
    const { customer, loading: loadingCustomer } = useCustomerData(customerID);
    const { rows: lmsRows, applicationId, loading: loadingLms } = useLMSData(customerID);
    const { rows: losRows, loading: loadingLos } = useLOSData(loadingLms ? null : applicationId);
    
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Offline Search Facility" />
            <PageTitle title="Optional Search Facility" description="Verify existing loan data of a customer" />
            <div className="flex flex-col gap-6 px-6 pb-10">
                <CustomerCard customer={customer} loading={loadingCustomer} />
                <LOSTable rows={losRows} loading={loadingLos} />
                <LMSTable rows={lmsRows} loading={loadingLms} />
            </div>
        </AppLayout>
    );
}
