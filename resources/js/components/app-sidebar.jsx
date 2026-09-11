import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/react';
import AppLogo from './app-logo';
import AppLogoBanner from './app-logo-banner';
import navItems from './app-sidebar-nav-items';
import { SearchDialog } from './search-dialog';

export function AppSidebar() {
    const { open, toggleSidebar } = useSidebar();
    const { auth } = usePage().props;
    const company = auth.user.company;
    const isBFC = company === 'BFC';

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" className="p-0" asChild>
                            <Link href="/dashboard" prefetch className={isBFC ? 'h-18 dark:hover:bg-neutral-800' : ''}>
                                <div className="md:hidden">
                                    <AppLogoBanner sidebarOpen={open} />
                                </div>
                                <div className="hidden md:block">
                                    <AppLogo sidebarOpen={open} company={company} />
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <SearchDialog sidebarOpen={open} />
            </SidebarHeader>

            <SidebarContent>
                <NavMain toggleSidebar={toggleSidebar} sidebarOpen={open} items={navItems()} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
