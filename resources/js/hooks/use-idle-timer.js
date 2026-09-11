import { useEffect, useRef } from 'react';

const ACTIVITY_EVENTS = ['mousemove', 'keydown', 'mousedown', 'touchstart', 'scroll', 'click'];

/**
 * Calls `onTimeout` after `timeoutMs` milliseconds of user inactivity.
 * Resets on any mouse/keyboard/touch/scroll activity.
 *
 * @param {number} timeoutMs  Idle timeout in milliseconds. Pass 0 or falsy to disable.
 * @param {Function} onTimeout  Called when the idle threshold is reached.
 */
export function useIdleTimer(timeoutMs, onTimeout) {
    const timerRef = useRef(null);
    const onTimeoutRef = useRef(onTimeout);

    useEffect(() => {
        onTimeoutRef.current = onTimeout;
    }, [onTimeout]);

    useEffect(() => {
        if (!timeoutMs || timeoutMs <= 0) return;

        const reset = () => {
            clearTimeout(timerRef.current);
            timerRef.current = setTimeout(() => onTimeoutRef.current?.(), timeoutMs);
        };

        ACTIVITY_EVENTS.forEach((event) => window.addEventListener(event, reset, { passive: true }));
        reset();

        return () => {
            clearTimeout(timerRef.current);
            ACTIVITY_EVENTS.forEach((event) => window.removeEventListener(event, reset));
        };
    }, [timeoutMs]);
}
