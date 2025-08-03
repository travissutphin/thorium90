import { usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';

export function usePermissions() {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user;

    const hasPermission = (permission: string): boolean => {
        if (!user) return false;
        const userPermissions = user.permission_names as string[] || [];
        return userPermissions.includes(permission);
    };

    const hasAnyPermission = (permissions: string[]): boolean => {
        if (!user) return false;
        const userPermissions = user.permission_names as string[] || [];
        return permissions.some(permission => userPermissions.includes(permission));
    };

    const hasAllPermissions = (permissions: string[]): boolean => {
        if (!user) return false;
        const userPermissions = user.permission_names as string[] || [];
        return permissions.every(permission => userPermissions.includes(permission));
    };

    const hasRole = (role: string): boolean => {
        if (!user) return false;
        const userRoles = user.role_names as string[] || [];
        return userRoles.includes(role);
    };

    const hasAnyRole = (roles: string[]): boolean => {
        if (!user) return false;
        const userRoles = user.role_names as string[] || [];
        return roles.some(role => userRoles.includes(role));
    };

    const hasAllRoles = (roles: string[]): boolean => {
        if (!user) return false;
        const userRoles = user.role_names as string[] || [];
        return roles.every(role => userRoles.includes(role));
    };

    // Convenience methods for common role checks
    const isAdmin = (): boolean => hasAnyRole(['Super Admin', 'Admin']);
    const isSuperAdmin = (): boolean => hasRole('Super Admin');
    const isContentManager = (): boolean => hasAnyRole(['Super Admin', 'Admin', 'Editor']);
    const isContentCreator = (): boolean => hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']);

    return {
        user,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasAnyRole,
        hasAllRoles,
        isAdmin,
        isSuperAdmin,
        isContentManager,
        isContentCreator,
        permissions: user?.permission_names as string[] || [],
        roles: user?.role_names as string[] || [],
    };
}

export function useAuth() {
    const { auth } = usePage<SharedData>().props;
    return {
        user: auth?.user,
        isAuthenticated: !!auth?.user,
    };
}
