/* eslint-disable react/prop-types */
import { router } from '@inertiajs/react';
import { CalendarDays, Info, MapPin } from 'lucide-react';

const formatName = (record) => [record.firstName, record.middleName, record.lastName].filter(Boolean).join(' ');

const handleInfoClick = (record) => {
    router.get(
        route('offline-search-facility.viewCustomer', {
            customerID: record.customerID,
        }),
    );
};

export default function ResultsCards({ data = [], emptyMessage = 'No records found.', onSelect }) {
    if (data.length === 0) {
        return <div className="rounded-xl border border-gray-200 bg-white p-10 text-center text-sm text-gray-500 shadow-sm">{emptyMessage}</div>;
    }

    return (
        <div className="space-y-4">
            {data.map((record, index) => (
                <button
                    type="button"
                    key={record.customerID}
                    onClick={() => onSelect?.(record)}
                    style={{
                        animation: `fadeSlideUp 0.35s ease-out ${index * 50}ms both`,
                    }}
                    className="group hover:border-brand-primary/30 hover:bg-brand-primary/5 w-full rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm transition-all duration-200 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div className="flex items-start justify-between gap-4">
                        <div className="min-w-0">
                            <h3 className="truncate text-base font-semibold text-gray-900 dark:text-gray-100">{formatName(record)}</h3>

                            <p className="mt-1 font-mono text-xs text-gray-500 dark:text-gray-300">{record.id}</p>
                        </div>

                        <button
                            type="button"
                            title="View record information"
                            onClick={(event) => {
                                event.stopPropagation();
                                handleInfoClick(record);
                            }}
                            className="text-brand-primary hover:bg-brand-primary/10 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 transition-colors focus:ring-2 focus:ring-blue-100 focus:outline-none dark:hover:bg-white dark:hover:text-gray-700"
                        >
                            <Info className="h-4 w-4" />
                        </button>
                    </div>

                    <div className="mt-4 space-y-2">
                        <div className="flex items-center gap-3">
                            <CalendarDays className="h-4 w-4 text-gray-400 dark:text-gray-300" />

                            <span className="truncate text-sm text-gray-700 dark:text-gray-300">{record.dateOfBirth}</span>
                        </div>

                        <div className="flex items-center gap-3">
                            <MapPin className="h-4 w-4 text-gray-400 dark:text-gray-300" />

                            <span className="truncate text-sm text-gray-700 dark:text-gray-300">{record.address}</span>
                        </div>
                    </div>
                </button>
            ))}
        </div>
    );
}
