import { usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { ReactNode } from 'react';

interface CanAccessProps {
    permission?: string;
    permissions?: string[];
    role?: string;
    roles?: string[];
    requireAll?: boolean;
    fallback?: ReactNode;
    children: ReactNode;
}

export function CanAccess({
    permission,
    permissions,
    role,
    roles,
    requireAll = false,
    fallback = null,
    children,
}: CanAccessProps) {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user;

    if (!user) {
        return <>{fallback}</>;
    }

    let hasAccess = false;

    // Check single permission
    if (permission) {
        hasAccess = (user.permission_names as string[])?.includes(permission) || false;
    }

    // Check multiple permissions
    if (permissions && permissions.length > 0) {
        const userPermissions = user.permission_names as string[] || [];
        if (requireAll) {
            hasAccess = permissions.every(perm => userPermissions.includes(perm));
        } else {
            hasAccess = permissions.some(perm => userPermissions.includes(perm));
        }
    }

    // Check single role
    if (role) {
        hasAccess = (user.role_names as string[])?.includes(role) || false;
    }

    // Check multiple roles
    if (roles && roles.length > 0) {
        const userRoles = user.role_names as string[] || [];
        if (requireAll) {
            hasAccess = roles.every(r => userRoles.includes(r));
        } else {
            hasAccess = roles.some(r => userRoles.includes(r));
        }
    }

    // If no specific checks are provided, check if user is authenticated
    if (!permission && !permissions && !role && !roles) {
        hasAccess = true;
    }

    return hasAccess ? <>{children}</> : <>{fallback}</>;
}

// Convenience components for common use cases
export function CanAccessAdmin({ children, fallback = null }: { children: ReactNode; fallback?: ReactNode }) {
    return (
        <CanAccess roles={['Super Admin', 'Admin']} fallback={fallback}>
            {children}
        </CanAccess>
    );
}

export function CanAccessContentManager({ children, fallback = null }: { children: ReactNode; fallback?: ReactNode }) {
    return (
        <CanAccess roles={['Super Admin', 'Admin', 'Editor']} fallback={fallback}>
            {children}
        </CanAccess>
    );
}

export function CanAccessContentCreator({ children, fallback = null }: { children: ReactNode; fallback?: ReactNode }) {
    return (
        <CanAccess roles={['Super Admin', 'Admin', 'Editor', 'Author']} fallback={fallback}>
            {children}
        </CanAccess>
    );
}

export function CanAccessSuperAdmin({ children, fallback = null }: { children: ReactNode; fallback?: ReactNode }) {
    return (
        <CanAccess role="Super Admin" fallback={fallback}>
            {children}
        </CanAccess>
    );
}
