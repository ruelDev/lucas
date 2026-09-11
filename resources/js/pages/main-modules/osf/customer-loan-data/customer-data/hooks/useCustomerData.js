import { useEffect, useState } from 'react';
import axios from 'axios';

// const FAKE_CUSTOMER = {
//     first_name: 'Maria',
//     middle_name: 'Santos',
//     last_name: 'Reyes',
//     date_of_birth: 'March 12, 1985',
//     address: 'Block 3 Lot 7 Sampaguita St., Barangay Holy Spirit, Quezon City, Metro Manila 1127',
// };

export function useCustomerData(customerID) {
    const [customer, setCustomer] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    const handleCustomerSearchResultData = (customer) => {
        const customerDetails = {
            customerID: customer.CUSTOMER_ID,
            first_name: customer.FIRST_NAME,
            middle_name: customer.MIDDLE_NAME,
            last_name: customer.LAST_NAME,
            date_of_birth: customer.DATE_OF_BIRTH,
            address: customer.ADDRESS1
        }
        
        setCustomer(customerDetails)
    }

    useEffect(() => {
        axios.get('/offline-search-facility/customer-data', {
            params: {
                customerID: customerID
            }
        })
            .then(({ data }) => handleCustomerSearchResultData(data))
            .catch((err) => setError(err?.response?.data?.message ?? err.message))
            .finally(() => setLoading(false));

        const timer = setTimeout(() => {
            setLoading(false);
        }, 800);

        return () => clearTimeout(timer);
    }, [customerID]);

    return { customer, loading, error };
}