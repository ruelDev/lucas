import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { useIdleTimer } from '@/hooks/use-idle-timer';
import { usePage } from '@inertiajs/react';
import { useCallback } from 'react';

export default function AppSidebarLayout({ children, breadcrumbs = [] }) {
    const { sso, auth } = usePage().props;

    // Redirect to SSO after the configured idle timeout. The server-side
    // SessionTimeoutMiddleware enforces the same limit on every request, but
    // without a frontend timer the user is never proactively redirected while
    // sitting on a page — they would only be kicked on the next interaction,
    // which can cause a confusing 419 / stale-session state.
    const handleIdleTimeout = useCallback(() => {
        if (sso?.url) {
            window.location.href = sso.url;
        }
    }, [sso?.url]);

    useIdleTimer(auth?.user ? sso?.idle_timeout_ms : 0, handleIdleTimeout);

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
