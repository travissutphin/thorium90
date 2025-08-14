import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, FileText, Globe, Search } from 'lucide-react';
import { FormEventHandler } from 'react';

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
        title: 'Create Page',
        href: '/content/pages/create',
    },
];

export default function CreatePage() {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        slug: '',
        content: '',
        excerpt: '',
        status: 'draft',
        is_featured: false,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('content.pages.store'), {
            onSuccess: () => reset(),
        });
    };

    const generateSlug = (title: string) => {
        return title
            .toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    };

    const handleTitleChange = (title: string) => {
        setData('title', title);
        if (!data.slug) {
            setData('slug', generateSlug(title));
        }
        if (!data.meta_title) {
            setData('meta_title', title);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Page" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Create New Page</h1>
                        <p className="text-muted-foreground">Create a new page for your website</p>
                    </div>
                    
                    <Button variant="outline" asChild>
                        <Link href={route('content.pages.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back to Pages
                        </Link>
                    </Button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid gap-6 lg:grid-cols-3">
                        {/* Main Content */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Basic Information */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Page Content
                                    </CardTitle>
                                    <CardDescription>
                                        The main content and information for your page
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="title">Page Title</Label>
                                        <Input
                                            id="title"
                                            type="text"
                                            value={data.title}
                                            onChange={(e) => handleTitleChange(e.target.value)}
                                            placeholder="Enter page title"
                                            className={errors.title ? 'border-destructive' : ''}
                                        />
                                        {errors.title && (
                                            <p className="text-sm text-destructive">{errors.title}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="slug">URL Slug</Label>
                                        <Input
                                            id="slug"
                                            type="text"
                                            value={data.slug}
                                            onChange={(e) => setData('slug', e.target.value)}
                                            placeholder="page-url-slug"
                                            className={errors.slug ? 'border-destructive' : ''}
                                        />
                                        {errors.slug && (
                                            <p className="text-sm text-destructive">{errors.slug}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            This will be the URL: /pages/{data.slug || 'page-url-slug'}
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="excerpt">Page Excerpt</Label>
                                        <Textarea
                                            id="excerpt"
                                            value={data.excerpt}
                                            onChange={(e) => setData('excerpt', e.target.value)}
                                            placeholder="Brief description of the page content"
                                            rows={3}
                                            className={errors.excerpt ? 'border-destructive' : ''}
                                        />
                                        {errors.excerpt && (
                                            <p className="text-sm text-destructive">{errors.excerpt}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="content">Page Content</Label>
                                        <Textarea
                                            id="content"
                                            value={data.content}
                                            onChange={(e) => setData('content', e.target.value)}
                                            placeholder="Write your page content here..."
                                            rows={12}
                                            className={errors.content ? 'border-destructive' : ''}
                                        />
                                        {errors.content && (
                                            <p className="text-sm text-destructive">{errors.content}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* SEO Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Search className="h-5 w-5" />
                                        SEO Settings
                                    </CardTitle>
                                    <CardDescription>
                                        Optimize your page for search engines
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="meta_title">Meta Title</Label>
                                        <Input
                                            id="meta_title"
                                            type="text"
                                            value={data.meta_title}
                                            onChange={(e) => setData('meta_title', e.target.value)}
                                            placeholder="SEO title for search engines"
                                            className={errors.meta_title ? 'border-destructive' : ''}
                                        />
                                        {errors.meta_title && (
                                            <p className="text-sm text-destructive">{errors.meta_title}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            {data.meta_title.length}/60 characters (recommended)
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="meta_description">Meta Description</Label>
                                        <Textarea
                                            id="meta_description"
                                            value={data.meta_description}
                                            onChange={(e) => setData('meta_description', e.target.value)}
                                            placeholder="Brief description for search engine results"
                                            rows={3}
                                            className={errors.meta_description ? 'border-destructive' : ''}
                                        />
                                        {errors.meta_description && (
                                            <p className="text-sm text-destructive">{errors.meta_description}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            {data.meta_description.length}/160 characters (recommended)
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="meta_keywords">Meta Keywords</Label>
                                        <Input
                                            id="meta_keywords"
                                            type="text"
                                            value={data.meta_keywords}
                                            onChange={(e) => setData('meta_keywords', e.target.value)}
                                            placeholder="keyword1, keyword2, keyword3"
                                            className={errors.meta_keywords ? 'border-destructive' : ''}
                                        />
                                        {errors.meta_keywords && (
                                            <p className="text-sm text-destructive">{errors.meta_keywords}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            Separate keywords with commas
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Publish Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Globe className="h-5 w-5" />
                                        Publish Settings
                                    </CardTitle>
                                    <CardDescription>
                                        Control how and when your page is published
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="draft">Draft</SelectItem>
                                                <SelectItem value="published">Published</SelectItem>
                                                <SelectItem value="private">Private</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.status && (
                                            <p className="text-sm text-destructive">{errors.status}</p>
                                        )}
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div className="space-y-0.5">
                                            <Label htmlFor="is_featured">Featured Page</Label>
                                            <p className="text-xs text-muted-foreground">
                                                Mark this page as featured
                                            </p>
                                        </div>
                                        <Switch
                                            id="is_featured"
                                            checked={data.is_featured}
                                            onCheckedChange={(checked) => setData('is_featured', checked as boolean)}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Actions */}
                            <Card>
                                <CardContent className="pt-6">
                                    <div className="space-y-4">
                                        <Button type="submit" className="w-full" disabled={processing}>
                                            <Save className="h-4 w-4 mr-2" />
                                            {processing ? 'Creating...' : 'Create Page'}
                                        </Button>
                                        
                                        <Button type="button" variant="outline" className="w-full" asChild>
                                            <Link href={route('content.pages.index')}>
                                                Cancel
                                            </Link>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* SEO Tips */}
                            <Card className="border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-950/20">
                                <CardContent className="pt-6">
                                    <div className="flex items-start gap-3">
                                        <Search className="h-5 w-5 text-blue-600 mt-0.5" />
                                        <div>
                                            <h3 className="font-semibold text-blue-900 dark:text-blue-100">SEO Tips</h3>
                                            <ul className="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                                                <li>• Use descriptive, keyword-rich titles</li>
                                                <li>• Keep meta descriptions under 160 characters</li>
                                                <li>• Use clean, readable URL slugs</li>
                                                <li>• Include relevant keywords naturally</li>
                                            </ul>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
