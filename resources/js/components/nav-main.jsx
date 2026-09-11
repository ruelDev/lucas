'use client';
/* eslint-disable react/prop-types */
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useIsMobile } from '@/hooks/use-mobile';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import * as React from 'react';
import { Icon } from './icon';

// Helper function to get pathname without query parameters
function getPathname(url) {
    if (!url) return '';

    try {
        return new URL(url).pathname;
    } catch {
        // If URL parsing fails, try to extract pathname manually
        return url.split('?')[0].split('#')[0];
    }
}

function isUrlActive(itemHref, itemMatch, currentUrl) {
    if (!itemHref || !currentUrl) return false;

    const matchPath = itemMatch ?? itemHref;
    const currentPath = getPathname(currentUrl);

    if (matchPath.endsWith('*')) {
        const prefix = matchPath.slice(0, -1); // e.g. '/receipt-posting'
        return (
            currentPath === prefix || // exact match
            currentPath.startsWith(prefix + '/') // sub-routes only
        );
    }

    return getPathname(matchPath) === currentPath;
}

// Recursive component to handle nested menu items
function NavMenuItem({ toggleSidebar, sidebarOpen, item, level = 1 }) {
    const { url } = usePage();
    const isMobile = useIsMobile();

    const [isOpen, setIsOpen] = React.useState(() => {
        if (!item.items?.length) return false;
        return hasActiveChild(item, url);
    });

    const handleOpenChange = (open) => {
        if (!sidebarOpen && !isMobile) {
            toggleSidebar();
            setIsOpen(true);
        } else {
            setIsOpen(open);
        }
    };

    React.useEffect(() => {
        if (!sidebarOpen) {
            setIsOpen(false);
        }
    }, [sidebarOpen]);

    // Get width class based on nesting level
    const getWidthClass = (level) => {
        switch (level) {
            case 1:
                return 'w-[245px] md:w-[198px]'; // 2nd level (under top-level items)
            case 2:
                return 'w-[220px] md:w-[172px]'; // 3rd level (Branch, Group, etc.)
            case 3:
                return 'w-[147px]'; // 4th level (if needed)
            default:
                return 'w-[140px]'; // Deeper levels
        }
    };

    // If item has no sub-items, render as a simple link
    if (!item.items || item.items.length === 0) {
        const isActive = isUrlActive(item.href, item.match, url);

        return (
            <SidebarMenuSubItem>
                <SidebarMenuSubButton asChild={!!item.href} className={`${getWidthClass(level)} ${getButtonClass(isActive)}`}>
                    <Link href={item.href || '#'}>
                        <span>{item.title}</span>
                    </Link>
                </SidebarMenuSubButton>
            </SidebarMenuSubItem>
        );
    }

    const isActive = hasActiveChild(item, url);

    // If item has sub-items, render as collapsible
    return (
        <SidebarMenuSubItem>
            <Collapsible open={isOpen} onOpenChange={handleOpenChange} className="group/collapsible">
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton className={`${getWidthClass(level)} ${getButtonClass(isActive)}`}>
                        {item.icon && <Icon iconNode={item.icon} className={getIconClass(isActive)} />}
                        <span className="truncate" title={item.title}>
                            {item.title}
                        </span>
                        <ChevronRight
                            className={`ml-auto transition-transform duration-200 ${isOpen ? 'rotate-90' : ''} ${getIconClass(isActive)}`}
                        />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {item.items?.map((subItem, index) => (
                            <NavMenuItem
                                key={`${subItem.title}-${index}`}
                                item={subItem}
                                toggleSidebar={toggleSidebar}
                                sidebarOpen={sidebarOpen}
                                level={level + 1}
                            />
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
        </SidebarMenuSubItem>
    );
}

// Component to handle individual top-level items with their own state
function TopLevelNavItem({ toggleSidebar, sidebarOpen, item, index }) {
    const { url } = usePage();
    const isMobile = useIsMobile();

    const [isOpen, setIsOpen] = React.useState(() => {
        if (!item.items?.length) return false;
        return hasActiveChild(item, url);
    });

    const handleOpenChange = (open) => {
        if (!sidebarOpen && !isMobile) {
            toggleSidebar();
            setIsOpen(true);
        } else {
            setIsOpen(open);
        }
    };

    React.useEffect(() => {
        if (!sidebarOpen) {
            setIsOpen(false);
        }
    }, [sidebarOpen]);

    // Top-level items without sub-items
    if (!item.items || item.items.length === 0) {
        const isActive = isUrlActive(item.href, item.match, url);

        return (
            <SidebarMenuItem key={`${item.title}-${index}`}>
                <SidebarMenuButton asChild={!!item.href} className={getButtonClass(isActive)}>
                    <Link href={item.href || '#'}>
                        {item.icon && <Icon iconNode={item.icon} className={getIconClass(isActive)} />}
                        <span>{item.title}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        );
    }

    const isActive = hasActiveChild(item, url);

    // Top-level items with sub-items
    return (
        <Collapsible asChild open={isOpen} onOpenChange={handleOpenChange} key={`${item.title}-${index}`} className="group/collapsible">
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton className={getButtonClass(isActive)}>
                        {item.icon && <Icon iconNode={item.icon} className={getIconClass(isActive)} />}
                        <span>{item.title}</span>
                        <ChevronRight
                            className={`ml-auto transition-transform duration-200 ${isOpen ? 'rotate-90' : ''} ${getIconClass(isActive)}`}
                        />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {item.items?.map((subItem, subIndex) => (
                            <NavMenuItem
                                toggleSidebar={toggleSidebar}
                                sidebarOpen={sidebarOpen}
                                key={`${subItem.title}-${subIndex}`}
                                item={subItem}
                                level={1}
                            />
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

export function NavMain({ toggleSidebar, sidebarOpen, items }) {
    return (
        <SidebarGroup>
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item, index) => (
                    <TopLevelNavItem
                        key={`${item.title}-${index}`}
                        toggleSidebar={toggleSidebar}
                        sidebarOpen={sidebarOpen}
                        item={item}
                        index={index}
                    />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

// Helper function - Updated to use pathname comparison
function hasActiveChild(item, url) {
    if (!item.items?.length || !url) return false;
    return item.items.some((subItem) => isUrlActive(subItem.href, subItem.match, url) || (subItem.items && hasActiveChild(subItem, url)));
}

function getButtonClass(isActive) {
    const baseClass = 'h-9 bg-gradient-to-r transition duration-300';
    const activeClass = 'from-blue-950 to-blue-600 !text-white hover:!text-white';
    const inactiveClass = 'hover:from-blue-950 hover:to-blue-600 hover:!text-white';

    return `${baseClass} ${isActive ? activeClass : inactiveClass}`;
}

function getIconClass(isActive) {
    return isActive ? '!text-white' : '';
}
