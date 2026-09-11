import axios from 'axios';
import { useEffect, useState } from 'react';

export function useLMSData(customerID) {
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [applicationId, setApplicationId] = useState([]);

    const handleCustomerLmsRecords = (lms) => {

        const lmsData = lms.map((record) => ({
            agreement_no: record.AGREEMENT_NO,
            mis_no: record.MIS_NO,
            agreement_id: record.AGREEMENT_ID,
            date_sold: record.DATE_SOLD,
            first_due_date: record.FIRST_DUE_DATE,
            maturity_date: record.MATURITY_DATE,
            last_payment_date: record.LAST_PAYMENT_DATE,
            loan_amount: record.LOAN_AMOUNT,
            loan_term: record.LOAN_TENURE,
            emi: record.EMI,
            loan_status: 'TEST',
            npa_stage: record.NPA_STAGEID,
            account_rating: 'test',
        }));

        const applicationId = lms.map(record => record.LOAN_APPLICATION_ID);

        setApplicationId(applicationId);
        setRows(lmsData);
    };

    useEffect(() => {
        axios
            .get('/offline-search-facility/customer-lms-data', {
                params: {
                    customerID: customerID,
                },
            })
            .then(({ data }) => handleCustomerLmsRecords(data))
            .catch((err) => setError(err?.response?.data?.message ?? err.message))
            .finally(() => setLoading(false));

        const timer = setTimeout(() => {
            setLoading(false);
        }, 2000);

        return () => clearTimeout(timer);
    }, [customerID]);

    return { rows, applicationId, loading, error };
}
