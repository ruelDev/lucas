/* eslint-disable react/prop-types */
import AppLayout from '@/layouts/app-layout';
import SearchBar from './components/SearchBar';
import SearchResults from './components/SearchResults';

import PageTitle from '@/components/page-title';
import { Card, CardContent } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { useEffect } from 'react';
import Swal from 'sweetalert2';
import fields from './config/searchFields';
import columns from './config/tableColumns';
import useRecordSearch from './hooks/useRecordSearch';

const breadcrumbs = [
    {
        title: 'Offline Search Facility',
        href: '/offline-search-facility',
    },
];

export default function RecordsSearch() {
    const { form, loading, validation, docked, hasSearched, lastQuery, results, search, reset, updateField } = useRecordSearch();
    
    useEffect(() => {
        const handleRecordInfo = (event) => {
            const record = event.detail;
            const details = document.createElement('div');
            details.style.textAlign = 'left';
            details.style.fontSize = '14px';
            details.style.lineHeight = '1.7';

            [
                ['First Name', record.firstName],
                ['Middle Name', record.middleName || '-'],
                ['Last Name', record.lastName],
                ['Date of Birth', record.dateOfBirth],
                ['Address', record.address],
            ].forEach(([label, value]) => {
                const row = document.createElement('p');
                const strong = document.createElement('strong');

                strong.textContent = `${label}: `;
                row.append(strong, document.createTextNode(value || '-'));
                details.append(row);
            });

            Swal.fire({
                icon: 'info',
                iconColor: '#1B4298',
                title: 'Record Information',
                html: details,
                confirmButtonText: 'Ok, got it!',
                confirmButtonColor: '#1B4298',
            });
        };

        globalThis.addEventListener('osf:record-info', handleRecordInfo);

        return () => {
            globalThis.removeEventListener('osf:record-info', handleRecordInfo);
        };
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Offline Search Facility" />
            <PageTitle title="Offline Search Facility" description="Verify existing loan data of a customer" />

            <div className="px-6">
                <Card className='dark:border-gray-700 dark:bg-gray-900'>
                    <CardContent>
                        <div className="mx-auto w-full">
                            <SearchBar
                                fields={fields}
                                form={form}
                                loading={loading}
                                validation={validation}
                                docked={docked}
                                onChange={updateField}
                                onSearch={search}
                                onReset={reset}
                            />

                            <div>
                                <SearchResults
                                    loading={loading}
                                    hasSearched={hasSearched}
                                    results={results}
                                    columns={columns}
                                    lastQuery={lastQuery}
                                    onReset={reset}
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
