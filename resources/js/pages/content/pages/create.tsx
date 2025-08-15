import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, FileText, Globe, Search, CheckCircle, AlertCircle, Loader2 } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';
import { useSlugValidation } from '@/hooks/use-slug-validation';

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

interface Props {
    schemaTypes: Record<string, string>;
}

export default function CreatePage({ schemaTypes }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        slug: '',
        content: '',
        excerpt: '',
        status: 'draft' as 'draft' | 'published' | 'private',
        is_featured: false as boolean,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        schema_type: 'WebPage',
        template: 'core-page',
        layout: 'default',
        theme: 'default',
        blocks: [] as any[],
        template_config: {} as Record<string, any>,
    });

    const { 
        isChecking, 
        validationResult, 
        checkSlug, 
        generateSlug, 
        clearValidation 
    } = useSlugValidation();

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('content.pages.store'), {
            onSuccess: () => reset(),
        });
    };

    const handleTitleChange = (title: string) => {
        setData('title', title);
        
        // Auto-generate slug from title and validate it
        const newSlug = generateSlug(title);
        setData('slug', newSlug);
        
        // Check slug availability
        if (newSlug) {
            checkSlug(newSlug, (result) => {
                if (!result.available && result.suggestion) {
                    // Auto-use the suggested unique slug
                    setData('slug', result.suggestion);
                }
            });
        } else {
            clearValidation();
        }
        
        // Auto-update meta title if it's empty
        if (!data.meta_title) {
            setData('meta_title', title);
        }
    };

    const handleSlugChange = (slug: string) => {
        setData('slug', slug);
        
        // Validate the manually entered slug
        if (slug) {
            checkSlug(slug);
        } else {
            clearValidation();
        }
    };

    // Clear validation when component unmounts
    useEffect(() => {
        return () => clearValidation();
    }, [clearValidation]);

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
                                        <div className="relative">
                                            <Input
                                                id="slug"
                                                type="text"
                                                value={data.slug}
                                                onChange={(e) => handleSlugChange(e.target.value)}
                                                placeholder="page-url-slug"
                                                className={`pr-10 ${errors.slug ? 'border-destructive' : 
                                                    validationResult?.available === false ? 'border-orange-500' : 
                                                    validationResult?.available === true ? 'border-green-500' : ''}`}
                                            />
                                            <div className="absolute inset-y-0 right-0 flex items-center pr-3">
                                                {isChecking && (
                                                    <Loader2 className="h-4 w-4 animate-spin text-muted-foreground" />
                                                )}
                                                {!isChecking && validationResult && (
                                                    <>
                                                        {validationResult.available ? (
                                                            <CheckCircle className="h-4 w-4 text-green-500" />
                                                        ) : (
                                                            <AlertCircle className="h-4 w-4 text-orange-500" />
                                                        )}
                                                    </>
                                                )}
                                            </div>
                                        </div>
                                        {errors.slug && (
                                            <p className="text-sm text-destructive">{errors.slug}</p>
                                        )}
                                        {validationResult && !validationResult.available && (
                                            <div className="text-sm text-orange-600">
                                                <p>{validationResult.message}</p>
                                                {validationResult.suggestion && (
                                                    <button
                                                        type="button"
                                                        onClick={() => setData('slug', validationResult.suggestion!)}
                                                        className="text-blue-600 hover:text-blue-800 underline mt-1"
                                                    >
                                                        Use suggested: {validationResult.suggestion}
                                                    </button>
                                                )}
                                            </div>
                                        )}
                                        {validationResult && validationResult.available && (
                                            <p className="text-sm text-green-600">✓ Slug is available</p>
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
                                        <Select value={data.status} onValueChange={(value) => setData('status', value as 'draft' | 'published' | 'private')}>
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
                                            onCheckedChange={(checked) => setData('is_featured', !!checked)}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Template Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Template Settings
                                    </CardTitle>
                                    <CardDescription>
                                        Choose how your page will be displayed on the public frontend
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="template">Page Template</Label>
                                        <Select value={data.template} onValueChange={(value) => setData('template', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select template" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectLabel>Core Templates</SelectLabel>
                                                    <SelectItem value="core-page">Core Page (Default)</SelectItem>
                                                </SelectGroup>
                                                <SelectGroup>
                                                    <SelectLabel>Client Templates</SelectLabel>
                                                    <SelectItem value="client-home">Home Page Template</SelectItem>
                                                    <SelectItem value="client-about">About Page Template</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                        {errors.template && (
                                            <p className="text-sm text-destructive">{errors.template}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            Template file: resources/js/templates/public/{data.template?.replace('client-', '') || 'core-page'}.tsx
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="layout">Layout</Label>
                                        <Select value={data.layout} onValueChange={(value) => setData('layout', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select layout" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="default">Default Layout</SelectItem>
                                                <SelectItem value="sidebar">Sidebar Layout</SelectItem>
                                                <SelectItem value="full-width">Full Width Layout</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.layout && (
                                            <p className="text-sm text-destructive">{errors.layout}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="theme">Theme</Label>
                                        <Select value={data.theme} onValueChange={(value) => setData('theme', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select theme" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="default">Default Theme</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.theme && (
                                            <p className="text-sm text-destructive">{errors.theme}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="custom_class">Custom CSS Classes</Label>
                                        <Input
                                            id="custom_class"
                                            type="text"
                                            value={data.template_config?.custom_class || ''}
                                            onChange={(e) => setData('template_config', {
                                                ...data.template_config,
                                                custom_class: e.target.value
                                            })}
                                            placeholder="e.g., dark-theme, special-layout"
                                            className={errors.template_config?.custom_class ? 'border-destructive' : ''}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Add custom CSS classes to the page wrapper
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="page_scripts">Page-Specific Scripts</Label>
                                        <Textarea
                                            id="page_scripts"
                                            value={data.template_config?.page_scripts || ''}
                                            onChange={(e) => setData('template_config', {
                                                ...data.template_config,
                                                page_scripts: e.target.value
                                            })}
                                            placeholder="Add any page-specific JavaScript here"
                                            rows={4}
                                            className={errors.template_config?.page_scripts ? 'border-destructive' : ''}
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            JavaScript code will be executed on this page only
                                        </p>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="schema_type">Schema Type</Label>
                                        <Select value={data.schema_type} onValueChange={(value) => setData('schema_type', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select schema type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(schemaTypes).map(([value, label]) => (
                                                    <SelectItem key={value} value={value}>
                                                        {label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.schema_type && (
                                            <p className="text-sm text-destructive">{errors.schema_type}</p>
                                        )}
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
