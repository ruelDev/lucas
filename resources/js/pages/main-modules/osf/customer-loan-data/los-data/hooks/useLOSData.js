import axios from 'axios';
import { useEffect, useState } from 'react';

export function useLOSData(applicationId) {
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const handleCustomerLosRecords = (los) => {
        const losData = los.map((record) => ({
            financing_bank: record.FINANCING_BANK,
            rlos_id: record.RLOS_ID,
            status: record.STATUS,
            date_encoded: record.DATE_ENCODED,
            decision_date: record.DECISION_DATE,
            remarks: record.REMARKS,
        }));

        setRows(losData);
    };

    useEffect(() => {
        if (!applicationId) return

        axios
            .get('/offline-search-facility/customer-los-data', {
                params: {
                    applicationId: applicationId,
                },
            })
            .then(({ data }) => handleCustomerLosRecords(data))
            .catch((err) => setError(err?.response?.data?.message ?? err.message))
            .finally(() => setLoading(false));

        const timer = setTimeout(() => {
            setLoading(false);
        }, 1400);

        return () => clearTimeout(timer);
    }, [applicationId]);

    return { rows, loading, error };
}
