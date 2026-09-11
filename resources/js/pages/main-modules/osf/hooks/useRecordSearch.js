import { useState } from 'react';
import normalize from '../utils/normalize';
import axios from 'axios';

export default function useRecordSearch() {
    const [form, setForm] = useState({
        firstName: '',
        middleName: '',
        lastName: '',
    });

    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const [validation, setValidation] = useState('');
    const [docked, setDocked] = useState(false);
    const [hasSearched, setHasSearched] = useState(false);
    const [lastQuery, setLastQuery] = useState('');

    const updateField = (field, value) => {
        setForm((prev) => ({
            ...prev,
            [field]: value,
        }));

        if (validation) {
            setValidation('');
        }
    };

    const validate = () => {
        if (!form.firstName.trim()) {
            setValidation('First name is required.');
            return false;
        }

        // if (!form.lastName.trim()) {
        //     setValidation('Last name is required.');
        //     return false;
        // }

        setValidation('');

        return true;
    };

    const reset = () => {
        setForm({
            firstName: '',
            middleName: '',
            lastName: '',
        });

        setHasSearched(false);
        setResults([]);
        setDocked(false);
        setValidation('');
        setLastQuery('');
    };

    const filterRecords = (records, form) => {
        const firstName = normalize(form.firstName);
        const middleName = normalize(form.middleName);
        const lastName = normalize(form.lastName);

        return records.filter((record) => {
            const firstMatches = normalize(record.firstName).includes(firstName);

            const middleMatches = middleName === '' || normalize(record.middleName).includes(middleName);

            const lastMatches = normalize(record.lastName).includes(lastName);

            return firstMatches && middleMatches && lastMatches;
        });
    };

    const search = async () => {
        if (!validate()) {
            return;
        }

        setLoading(true);

        try {
            const matchData = await axios.get('/offline-search-facility/search', {
                params: form
            });
            
            const resultData = matchData.data.map((user) => ({
                customerID: user.CUSTOMER_ID,
                firstName: user.FIRST_NAME,
                middleName: user.MIDDLE_NAME,
                lastName: user.LAST_NAME,
                dateOfBirth: user.DATE_OF_BIRTH,
                address: user.ADDRESS1
            }));
            
            const matches = filterRecords(resultData, form);
            const query = [form.firstName, form.middleName, form.lastName]
                .map((value) => value.trim())
                .filter(Boolean)
                .join(' ');
            
            setResults(matches);
            setLastQuery(query);

            setHasSearched(true);
            setDocked(true);
        } finally {
            setLoading(false);
        }
    };

    return {
        form,
        results,
        loading,
        validation,
        docked,
        hasSearched,
        lastQuery,

        updateField,
        search,
        reset,

        setResults,
        setDocked,
    };
}
