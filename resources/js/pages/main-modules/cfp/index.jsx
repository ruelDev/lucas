/* eslint-disable react/prop-types */
import PageTitle from '@/components/page-title';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import ServerSideDataTable from './cfp-server-side-data-table';


const breadcrumbs = [
    {
        title: 'Certificate of Fullpayment',
        href: '/certificate-of-full-payment',
    },
];

export default function Cfp({ data, pagination, filters, sort }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Certificate of Fullpayment" />
            <PageTitle title="Certificate of Full Payment" description="Manage all CFP in the system" />
            <ServerSideDataTable data={data} pagination={pagination} showSearchBar={true} filters={filters} sort={sort} />
        </AppLayout>
    );
}
