import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

export const useUrlParams = (options = {}) => {
    const [loading, setLoading] = useState(false);

    const makeRequest = useCallback(
        (params = {}) => {
            setLoading(true);

            const currentUrl = new URL(window.location);
            const searchParams = new URLSearchParams(currentUrl.search);

            // Preserve existing params and override with new ones
            Object.entries(params).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    searchParams.set(key, value.toString());
                } else {
                    searchParams.delete(key);
                }
            });

            const finalUrl = `${currentUrl.pathname}?${searchParams.toString()}`;

            router.get(
                finalUrl,
                {},
                {
                    preserveState: true,
                    preserveScroll: true,
                    onFinish: () => setLoading(false),
                    onError: () => setLoading(false),
                    ...options, // Allow override of Inertia options
                },
            );
        },
        [options],
    );

    return { makeRequest, loading };
};
