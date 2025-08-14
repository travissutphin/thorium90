import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { FileText, Plus, Search, Filter } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Content',
        href: '/content',
    },
    {
        title: 'Pages',
        href: '/content/pages',
    },
];

export default function PagesIndex() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pages" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Pages</h1>
                        <p className="text-muted-foreground">Manage your website pages and content</p>
                    </div>
                    
                    <Button asChild>
                        <Link href="/content/pages/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Create Page
                        </Link>
                    </Button>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pages</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground">No pages created yet</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Published</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground">Published pages</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Drafts</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground">Draft pages</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">This Month</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">0</div>
                            <p className="text-xs text-muted-foreground">Pages created this month</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters and Search */}
                <div className="flex items-center gap-4">
                    <div className="flex-1 max-w-sm">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Search pages..."
                                className="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            />
                        </div>
                    </div>
                    
                    <Button variant="outline" size="sm">
                        <Filter className="h-4 w-4 mr-2" />
                        Filter
                    </Button>
                </div>

                {/* Pages List */}
                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle>All Pages</CardTitle>
                        <CardDescription>
                            A list of all pages in your website
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="relative min-h-[400px]">
                        <div className="flex flex-col items-center justify-center h-full text-center py-12">
                            <FileText className="h-12 w-12 text-muted-foreground mb-4" />
                            <h3 className="text-lg font-semibold mb-2">No pages yet</h3>
                            <p className="text-muted-foreground mb-6 max-w-sm">
                                Get started by creating your first page. Pages are perfect for static content like About, Contact, or Terms of Service.
                            </p>
                            <Button asChild>
                                <Link href="/content/pages/create">
                                    <Plus className="h-4 w-4 mr-2" />
                                    Create Your First Page
                                </Link>
                            </Button>
                        </div>
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
