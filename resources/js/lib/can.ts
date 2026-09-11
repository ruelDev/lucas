import { usePage } from "@inertiajs/react";

interface AuthProps {
    auth: {
        permissions? : string[];
        roles? : string[];
    };
}

export function can(permission: string): boolean {
    const { auth } = usePage().props as AuthProps;
    return (auth.permissions ?? []).includes(permission);
}

export function canAny(permissions: string[]): boolean {
    const { auth } = usePage().props as AuthProps;
    const userPermissions = auth.permissions ?? [];
    return permissions.some(permission => userPermissions.includes(permission));
}

export function hasRole(role: string): boolean {
    const { auth } = usePage().props as AuthProps;
    return (auth.roles ?? []).includes(role);
}

export function hasAnyRole(roles: string[]): boolean {
    const { auth } = usePage().props as AuthProps;
    const userRoles = auth.roles ?? [];
    return roles.some(role => userRoles.includes(role));
}