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

// Schema.org Type Definitions
export interface BaseSchema {
    '@context': 'https://schema.org';
    '@type': string;
    name?: string;
    description?: string;
    url?: string;
    datePublished?: string;
    dateModified?: string;
    author?: {
        '@type': 'Person';
        name: string;
    };
    publisher?: {
        '@type': 'Organization';
        name: string;
        url: string;
    };
}

export interface WebPageSchema extends BaseSchema {
    '@type': 'WebPage';
    mainEntityOfPage?: string;
    breadcrumb?: string;
    primaryImageOfPage?: string;
}

export interface ArticleSchema extends BaseSchema {
    '@type': 'Article';
    headline: string;
    articleBody: string;
    wordCount?: number;
    articleSection?: string;
    keywords?: string;
    inLanguage?: string;
}

export interface BlogPostingSchema extends ArticleSchema {
    '@type': 'BlogPosting';
    blogCategory?: string;
    tags?: string[];
}

export interface NewsArticleSchema extends ArticleSchema {
    '@type': 'NewsArticle';
    dateline?: string;
    printColumn?: string;
    printEdition?: string;
    printPage?: string;
    printSection?: string;
}

export interface FAQPageSchema extends BaseSchema {
    '@type': 'FAQPage';
    mainEntity: Array<{
        '@type': 'Question';
        name: string;
        acceptedAnswer: {
            '@type': 'Answer';
            text: string;
        };
    }>;
}

export type SchemaData = WebPageSchema | ArticleSchema | BlogPostingSchema | NewsArticleSchema | FAQPageSchema;

export type SchemaType = 'WebPage' | 'Article' | 'BlogPosting' | 'NewsArticle' | 'FAQPage';

export interface SchemaTypeConfig {
    value: SchemaType;
    label: string;
    description?: string;
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
    schema_type: SchemaType;
    schema_data?: SchemaData;
    // AEO Enhancement fields
    topics?: string[];
    keywords?: string[];
    faq_data?: FAQItem[];
    reading_time?: number;
    content_type?: string;
    content_score?: number;
    user_id: number;
    user: User;
    published_at?: string;
    created_at: string;
    updated_at: string;
}

export interface FAQItem {
    id: string;
    question: string;
    answer: string;
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
