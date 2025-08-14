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

export interface Page {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt?: string;
    status: 'draft' | 'published' | 'private';
    is_featured: boolean;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    schema_type: string;
    schema_data?: any;
    user_id: number;
    user: User;
    published_at?: string;
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export interface PageStats {
    total: number;
    published: number;
    drafts: number;
    featured: number;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}
