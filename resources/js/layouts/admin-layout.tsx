import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface AdminLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default function AdminLayout({ children, breadcrumbs, ...props }: AdminLayoutProps) {
    const adminBreadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Admin', href: '/admin' },
        ...(breadcrumbs || [])
    ];

    return (
        <AppLayout breadcrumbs={adminBreadcrumbs} {...props}>
            {children}
        </AppLayout>
    );
}
