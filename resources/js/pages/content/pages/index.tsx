import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Page, type PaginatedData, type PageStats } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FileText, Plus, Search, Edit, Eye, Trash2, Calendar, User } from 'lucide-react';
import { useState } from 'react';

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

interface Props {
    pages: PaginatedData<Page>;
    stats: PageStats;
    filters: {
        search?: string;
        status?: string;
        featured?: boolean;
    };
}

export default function PagesIndex({ pages, stats, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/content/pages', { search, status: statusFilter }, { preserveState: true });
    };

    const handleStatusFilter = (status: string) => {
        setStatusFilter(status);
        router.get('/content/pages', { search, status }, { preserveState: true });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'published':
                return <Badge variant="default" className="bg-green-100 text-green-800">Published</Badge>;
            case 'draft':
                return <Badge variant="secondary">Draft</Badge>;
            case 'private':
                return <Badge variant="outline">Private</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };
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
                            <div className="text-2xl font-bold">{stats.total}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.total === 0 ? 'No pages created yet' : 'Total pages'}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Published</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.published}</div>
                            <p className="text-xs text-muted-foreground">Published pages</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Drafts</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.drafts}</div>
                            <p className="text-xs text-muted-foreground">Draft pages</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Featured</CardTitle>
                            <FileText className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.featured}</div>
                            <p className="text-xs text-muted-foreground">Featured pages</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters and Search */}
                <div className="flex items-center gap-4">
                    <form onSubmit={handleSearch} className="flex-1 max-w-sm">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Search pages..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full pl-10 pr-4 py-2 border border-input rounded-md bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                            />
                        </div>
                    </form>
                    
                    <div className="flex gap-2">
                        <Button 
                            variant={statusFilter === '' ? 'default' : 'outline'} 
                            size="sm"
                            onClick={() => handleStatusFilter('')}
                        >
                            All
                        </Button>
                        <Button 
                            variant={statusFilter === 'published' ? 'default' : 'outline'} 
                            size="sm"
                            onClick={() => handleStatusFilter('published')}
                        >
                            Published
                        </Button>
                        <Button 
                            variant={statusFilter === 'draft' ? 'default' : 'outline'} 
                            size="sm"
                            onClick={() => handleStatusFilter('draft')}
                        >
                            Drafts
                        </Button>
                    </div>
                </div>

                {/* Pages List */}
                <Card className="flex-1">
                    <CardHeader>
                        <CardTitle>All Pages ({pages.total})</CardTitle>
                        <CardDescription>
                            A list of all pages in your website
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="relative min-h-[400px]">
                        {pages.data.length === 0 ? (
                            <div className="flex flex-col items-center justify-center h-full text-center py-12">
                                <FileText className="h-12 w-12 text-muted-foreground mb-4" />
                                <h3 className="text-lg font-semibold mb-2">
                                    {search || statusFilter ? 'No pages found' : 'No pages yet'}
                                </h3>
                                <p className="text-muted-foreground mb-6 max-w-sm">
                                    {search || statusFilter 
                                        ? 'Try adjusting your search or filter criteria.'
                                        : 'Get started by creating your first page. Pages are perfect for static content like About, Contact, or Terms of Service.'
                                    }
                                </p>
                                <Button asChild>
                                    <Link href="/content/pages/create">
                                        <Plus className="h-4 w-4 mr-2" />
                                        Create Your First Page
                                    </Link>
                                </Button>
                                <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {pages.data.map((page) => (
                                    <div key={page.id} className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="font-semibold text-lg truncate">{page.title}</h3>
                                                {getStatusBadge(page.status)}
                                                {page.is_featured && (
                                                    <Badge variant="outline" className="text-yellow-600 border-yellow-600">
                                                        Featured
                                                    </Badge>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                                <div className="flex items-center gap-1">
                                                    <User className="h-4 w-4" />
                                                    {page.user.name}
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <Calendar className="h-4 w-4" />
                                                    {formatDate(page.created_at)}
                                                </div>
                                                <span className="text-xs bg-muted px-2 py-1 rounded">
                                                    /{page.slug}
                                                </span>
                                            </div>
                                            {page.excerpt && (
                                                <p className="text-sm text-muted-foreground mt-2 line-clamp-2">
                                                    {page.excerpt}
                                                </p>
                                            )}
                                        </div>
                                        <div className="flex items-center gap-2 ml-4">
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/content/pages/${page.id}`}>
                                                    <Eye className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button variant="ghost" size="sm" asChild>
                                                <Link href={`/content/pages/${page.id}/edit`}>
                                                    <Edit className="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button 
                                                variant="ghost" 
                                                size="sm"
                                                onClick={() => {
                                                    if (confirm('Are you sure you want to delete this page?')) {
                                                        router.delete(`/content/pages/${page.id}`);
                                                    }
                                                }}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                                
                                {/* Pagination */}
                                {pages.last_page > 1 && (
                                    <div className="flex items-center justify-between pt-4">
                                        <div className="text-sm text-muted-foreground">
                                            Showing {pages.from} to {pages.to} of {pages.total} results
                                        </div>
                                        <div className="flex gap-2">
                                            {pages.prev_page_url && (
                                                <Button 
                                                    variant="outline" 
                                                    size="sm"
                                                    onClick={() => router.get(pages.prev_page_url!)}
                                                >
                                                    Previous
                                                </Button>
                                            )}
                                            {pages.next_page_url && (
                                                <Button 
                                                    variant="outline" 
                                                    size="sm"
                                                    onClick={() => router.get(pages.next_page_url!)}
                                                >
                                                    Next
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
