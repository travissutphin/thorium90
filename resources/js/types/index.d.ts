import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions: Permission[];
    created_at: string;
    updated_at: string;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    roles: Role[];
    permissions: Permission[];
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface AuthUser extends User {
    can(permission: string): boolean;
    hasRole(role: string): boolean;
    hasAnyRole(roles: string[]): boolean;
    hasPermissionTo(permission: string): boolean;
    hasAnyPermission(permissions: string[]): boolean;
}

export interface Auth {
    user: AuthUser;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    permission?: string;
    role?: string;
    roles?: string[];
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}
