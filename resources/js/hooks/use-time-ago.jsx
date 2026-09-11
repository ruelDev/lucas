import { useState, useEffect } from 'react';

const TIME_UNITS = [
    { unit: 'year', seconds: 31536000 },
    { unit: 'month', seconds: 2592000 },
    { unit: 'day', seconds: 86400 },
    { unit: 'hour', seconds: 3600 },
    { unit: 'minute', seconds: 60 },
    { unit: 'second', seconds: 1 }
];

const timeAgo = (date) => {
    const secondsElapsed = Math.floor((new Date() - date) / 1000);

    for (const { unit, seconds } of TIME_UNITS) {
        const interval = Math.floor(secondsElapsed / seconds);
        if (interval >= 1) {
            return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`;
        }
    }

    return 'just now';
};

export const useTimeAgo = (date, updateInterval = 60000) => {
    const [timeString, setTimeString] = useState(() => timeAgo(new Date(date)));

    useEffect(() => {
        setTimeString(timeAgo(new Date(date)));

        const interval = setInterval(() => {
            setTimeString(timeAgo(new Date(date)));
        }, updateInterval);

        return () => clearInterval(interval);
    }, [date, updateInterval]);

    return timeString;
};
