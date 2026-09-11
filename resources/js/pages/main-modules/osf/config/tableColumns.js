import { createElement } from 'react';
import { Info } from 'lucide-react';
import { router } from '@inertiajs/react';

const handleInfoClick = (row) => {
    router.get(route('offline-search-facility.viewCustomer', {
        customerID: row.customerID
    }));
};

const renderInfoButton = (row) =>
    createElement(
        'button',
        {
            type: 'button',
            title: 'View record information',
            'aria-label': `View information for ${row.firstName} ${row.lastName}`,
            onClick: () => handleInfoClick(row),
            className:
                'inline-flex items-center justify-center text-blue-600 transition-colors hover:bg-brand-primary/10 hover:text-brand-primary focus:outline-none focus:ring-2 focus:ring-blue-100',
        },
        createElement(Info, { className: 'h-4 w-4' }),
    );

export default [
    {
        key: 'firstName',
        title: 'First Name',
    },

    {
        key: 'middleName',
        title: 'Middle Name',
    },

    {
        key: 'lastName',
        title: 'Last Name',
    },

    {
        key: 'dateOfBirth',
        title: 'Date of Birth',
    },

    {
        key: 'address',
        title: 'Address',
    },

    {
        key: 'actions',
        title: 'Actions',
        render: (row) => renderInfoButton(row),
    },
];
