import { ApiDemo } from '@/components/api-demo';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'API Demo',
        href: '/api-demo',
    },
];

export default function ApiDemoPage() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="API Demo - Laravel Sanctum Integration" />
            <div className="flex h-full flex-1 flex-col rounded-xl overflow-x-auto">
                <ApiDemo />
            </div>
        </AppLayout>
    );
}
