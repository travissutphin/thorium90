import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, Edit, Calendar, User, Clock, Eye, Share2 } from 'lucide-react';

interface Page {
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
    schema_type?: string;
    published_at: string | null;
    created_at: string;
    updated_at: string;
    reading_time: number;
    url?: string;
    full_meta_title?: string;
    user: {
        id: number;
        name: string;
    };
}

interface Props {
    page: Page;
    schemaData: Record<string, unknown>;
}

export default function ShowPage({ page, schemaData }: Props) {
    const { auth } = usePage<{ auth: { user: any } }>().props;
    const user = auth?.user;

    // Add error boundary for missing page data
    if (!page) {
        return (
            <AppLayout breadcrumbs={[]}>
                <Head title="Page Not Found" />
                <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-center">
                                <h1 className="text-2xl font-bold text-destructive">Page Not Found</h1>
                                <p className="text-muted-foreground mt-2">The requested page could not be loaded.</p>
                                <Button asChild className="mt-4">
                                    <Link href="/content/pages">
                                        <ArrowLeft className="h-4 w-4 mr-2" />
                                        Back to Pages
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Content',
            href: '/content',
        },
        {
            title: 'Pages',
            href: '/content/pages',
        },
        {
            title: page.title || 'Untitled Page',
            href: `/content/pages/${page.id}`,
        },
    ];

    const canEdit = user && (
        user.permission_names?.includes('edit pages') || 
        (user.permission_names?.includes('edit own pages') && page.user?.id === user.id)
    );

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'published':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            case 'private':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    const shareUrl = `${window.location.origin}/pages/${page.slug}`;

    const handleShare = async () => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: page.title,
                    text: page.excerpt,
                    url: shareUrl,
                });
            } catch (err) {
                console.log('Error sharing:', err);
            }
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(shareUrl);
            // You could show a toast notification here
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head>
                <title>{page.meta_title || page.title}</title>
                <meta name="description" content={page.meta_description || page.excerpt} />
                {page.meta_keywords && <meta name="keywords" content={page.meta_keywords} />}
                
                {/* Open Graph / Facebook */}
                <meta property="og:type" content="article" />
                <meta property="og:title" content={page.meta_title || page.title} />
                <meta property="og:description" content={page.meta_description || page.excerpt} />
                <meta property="og:url" content={shareUrl} />
                
                {/* Twitter */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={page.meta_title || page.title} />
                <meta name="twitter:description" content={page.meta_description || page.excerpt} />
                
                {/* Schema.org structured data */}
                {schemaData && (
                    <script type="application/ld+json">
                        {JSON.stringify(schemaData)}
                    </script>
                )}
                
                {/* Canonical URL */}
                <link rel="canonical" href={shareUrl} />
            </Head>

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="outline" asChild>
                            <Link href="/content/pages">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Pages
                            </Link>
                        </Button>
                        
                        <div className="flex items-center gap-2">
                            <Badge className={getStatusColor(page.status)}>
                                {page.status.charAt(0).toUpperCase() + page.status.slice(1)}
                            </Badge>
                            {page.is_featured && (
                                <Badge variant="secondary">Featured</Badge>
                            )}
                        </div>
                    </div>
                    
                    <div className="flex items-center gap-2">
                        <Button variant="outline" onClick={handleShare}>
                            <Share2 className="h-4 w-4 mr-2" />
                            Share
                        </Button>
                        
                        {canEdit && (
                            <Button asChild>
                                <Link href={`/content/pages/${page.id}/edit`}>
                                    <Edit className="h-4 w-4 mr-2" />
                                    Edit Page
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                {/* Page Content */}
                <div className="grid gap-6 lg:grid-cols-4">
                    {/* Main Content */}
                    <div className="lg:col-span-3">
                        <Card>
                            <CardHeader>
                                <div className="space-y-4">
                                    <CardTitle className="text-3xl font-bold leading-tight">
                                        {page.title}
                                    </CardTitle>
                                    
                                    {page.excerpt && (
                                        <CardDescription className="text-lg text-muted-foreground">
                                            {page.excerpt}
                                        </CardDescription>
                                    )}
                                    
                                    <div className="flex items-center gap-6 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-2">
                                            <User className="h-4 w-4" />
                                            <span>By {page.user.name}</span>
                                        </div>
                                        
                                        {page.published_at && (
                                            <div className="flex items-center gap-2">
                                                <Calendar className="h-4 w-4" />
                                                <span>Published {new Date(page.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</span>
                                            </div>
                                        )}
                                        
                                        <div className="flex items-center gap-2">
                                            <Clock className="h-4 w-4" />
                                            <span>{page.reading_time} min read</span>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            
                            <CardContent>
                                <div 
                                    className="prose prose-gray dark:prose-invert max-w-none"
                                    dangerouslySetInnerHTML={{ __html: page.content }}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Page Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Page Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Status</label>
                                    <div className="mt-1">
                                        <Badge className={getStatusColor(page.status)}>
                                            {page.status.charAt(0).toUpperCase() + page.status.slice(1)}
                                        </Badge>
                                    </div>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Author</label>
                                    <p className="mt-1 text-sm">{page.user.name}</p>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Created</label>
                                    <p className="mt-1 text-sm">{new Date(page.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                                    <p className="mt-1 text-sm">{new Date(page.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                                </div>
                                
                                {page.published_at && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Published</label>
                                        <p className="mt-1 text-sm">{new Date(page.published_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}</p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* SEO Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">SEO Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Meta Title</label>
                                    <p className="mt-1 text-sm">{page.meta_title || page.title}</p>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Meta Description</label>
                                    <p className="mt-1 text-sm">{page.meta_description || page.excerpt}</p>
                                </div>
                                
                                {page.meta_keywords && (
                                    <div>
                                        <label className="text-sm font-medium text-muted-foreground">Keywords</label>
                                        <p className="mt-1 text-sm">{page.meta_keywords}</p>
                                    </div>
                                )}
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Schema Type</label>
                                    <p className="mt-1 text-sm">{page.schema_type}</p>
                                </div>
                                
                                <div>
                                    <label className="text-sm font-medium text-muted-foreground">Reading Time</label>
                                    <p className="mt-1 text-sm">{page.reading_time} minutes</p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Public URL */}
                        {page.status === 'published' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Public URL</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center gap-2">
                                        <Eye className="h-4 w-4 text-muted-foreground" />
                                        <a 
                                            href={`/pages/${page.slug}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 break-all"
                                        >
                                            /pages/{page.slug}
                                        </a>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
