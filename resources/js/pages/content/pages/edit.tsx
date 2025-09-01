import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Page, type SchemaTypeConfig, type SchemaType, type FAQItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, FileText, Globe, Search, CheckCircle, AlertCircle, Loader2, Zap } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { useSlugValidation } from '@/hooks/use-slug-validation';
import { AEOFaqEditor, TopicSelector, KeywordManager, ReadingTimeDisplay, SchemaPreview } from '@/components/aeo';

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
        title: 'Edit Page',
        href: '#',
    },
];

interface Props {
    page: Page;
    schemaTypes: SchemaTypeConfig[];
}

export default function EditPage({ page, schemaTypes }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        title: page.title || '',
        slug: page.slug || '',
        content: page.content || '',
        excerpt: page.excerpt || '',
        status: page.status || 'draft',
        is_featured: page.is_featured || false,
        meta_title: page.meta_title || '',
        meta_description: page.meta_description || '',
        meta_keywords: page.meta_keywords || '',
        schema_type: page.schema_type || 'WebPage',
        template: page.template || 'core-page',
        layout: page.layout || 'default',
        theme: page.theme || 'default',
        blocks: page.blocks || [] as any[],
        template_config: page.template_config || {} as Record<string, any>,
        schema_data: page.schema_data || {} as Record<string, any>,
        // AEO Enhancement fields
        topics: page.topics || [] as string[],
        keywords: page.keywords || [] as string[],
        faq_data: page.faq_data || [] as FAQItem[],
        content_type: page.content_type || 'general' as string,
    });

    const { 
        isChecking, 
        validationResult, 
        checkSlug, 
        generateSlug, 
        clearValidation 
    } = useSlugValidation({ excludeId: page.id });

    const [showSchemaPreview, setShowSchemaPreview] = useState(false);

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        console.log('Form data being submitted:', data);
        console.log('Route URL:', route('content.pages.update', page.id));
        put(route('content.pages.update', page.id), {
            onSuccess: () => {
                console.log('Form submitted successfully');
            },
            onError: (errors) => {
                console.error('Form submission errors:', errors);
            },
            onFinish: () => {
                console.log('Form submission finished');
            }
        });
    };

    const handleTitleChange = (title: string) => {
        setData('title', title);
        
        // Auto-generate slug from title and validate it
        const newSlug = generateSlug(title);
        setData('slug', newSlug);
        
        // Check slug availability
        if (newSlug && newSlug !== page.slug) {
            checkSlug(newSlug, (result) => {
                if (!result.available && result.suggestion) {
                    // Auto-use the suggested unique slug
                    setData('slug', result.suggestion);
                }
            });
        } else {
            clearValidation();
        }
        
        // Auto-update meta title if it matches the original or is empty
        if (!data.meta_title || data.meta_title === page.title) {
            setData('meta_title', title);
        }
    };

    const handleSlugChange = (slug: string) => {
        setData('slug', slug);
        
        // Validate the manually entered slug
        if (slug && slug !== page.slug) {
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
            <Head title={`Edit ${page.title}`} />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Edit Page</h1>
                        <p className="text-muted-foreground">Update your page content and settings</p>
                    </div>
                    
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('content.pages.show', page.id)}>
                                <FileText className="h-4 w-4 mr-2" />
                                View Page
                            </Link>
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={route('content.pages.index')}>
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Pages
                            </Link>
                        </Button>
                    </div>
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
                                            This will be the URL: /{data.slug || 'page-url-slug'}
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

                                    <div className="space-y-2">
                                        <Label htmlFor="schema_type">Schema Type</Label>
                                        <Select value={data.schema_type} onValueChange={(value) => setData('schema_type', value as SchemaType)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select schema type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {schemaTypes.map((type) => (
                                                    <SelectItem key={type.value} value={type.value}>
                                                        {type.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <p className="text-xs text-muted-foreground">
                                            Schema.org markup type for structured data
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* AEO Enhancement Section */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Zap className="h-5 w-5 text-purple-600" />
                                        AEO Optimization
                                    </CardTitle>
                                    <CardDescription>
                                        Answer Engine Optimization for AI-powered search results
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-6">
                                    {/* Topics */}
                                    <TopicSelector
                                        value={data.topics}
                                        onChange={(topics) => setData('topics', topics)}
                                        disabled={processing}
                                        error={errors.topics}
                                    />

                                    {/* Keywords */}
                                    <KeywordManager
                                        value={data.keywords}
                                        onChange={(keywords) => setData('keywords', keywords)}
                                        disabled={processing}
                                        error={errors.keywords}
                                        contentPreview={data.content}
                                    />

                                    {/* FAQ Editor (only for FAQ pages) */}
                                    {data.schema_type === 'FAQPage' && (
                                        <AEOFaqEditor
                                            value={data.faq_data}
                                            onChange={(faqData) => setData('faq_data', faqData)}
                                            disabled={processing}
                                            error={errors.faq_data}
                                        />
                                    )}

                                    {/* Reading Time Display */}
                                    <ReadingTimeDisplay
                                        content={data.content}
                                        showWordCount={true}
                                        showDetails={false}
                                    />

                                    {/* Schema Preview */}
                                    <SchemaPreview
                                        schemaType={data.schema_type}
                                        title={data.title}
                                        content={data.content}
                                        topics={data.topics}
                                        keywords={data.keywords}
                                        faqData={data.faq_data}
                                        visible={showSchemaPreview}
                                        onToggle={() => setShowSchemaPreview(!showSchemaPreview)}
                                    />
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Page Settings */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Page Settings
                                    </CardTitle>
                                    <CardDescription>
                                        Configure how your page will be displayed
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="layout">Page Layout</Label>
                                        <Select value={data.layout} onValueChange={(value) => setData('layout', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select layout" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="default">Default (Header + Content + Footer)</SelectItem>
                                                <SelectItem value="sidebar">With Sidebar (Content + Contact Info)</SelectItem>
                                                <SelectItem value="full-width">Full Width (No Container Limits)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.layout && (
                                            <p className="text-sm text-destructive">{errors.layout}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            Choose how content is arranged on the page
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>
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
                                            onCheckedChange={(checked) => setData('is_featured', Boolean(checked))}
                                        />
                                    </div>

                                    {page.published_at && (
                                        <div className="text-sm text-muted-foreground">
                                            <p>Published: {new Date(page.published_at).toLocaleDateString()}</p>
                                        </div>
                                    )}

                                    <div className="text-sm text-muted-foreground">
                                        <p>Created: {new Date(page.created_at).toLocaleDateString()}</p>
                                        <p>Last updated: {new Date(page.updated_at).toLocaleDateString()}</p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Actions */}
                            <Card>
                                <CardContent className="pt-6">
                                    <div className="space-y-4">
                                        <Button type="submit" className="w-full" disabled={processing}>
                                            <Save className="h-4 w-4 mr-2" />
                                            {processing ? 'Updating...' : 'Update Page'}
                                        </Button>
                                        
                                        <Button type="button" variant="outline" className="w-full" asChild>
                                            <Link href={route('content.pages.index')}>
                                                Cancel
                                            </Link>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Page Info */}
                            <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/20">
                                <CardContent className="pt-6">
                                    <div className="flex items-start gap-3">
                                        <FileText className="h-5 w-5 text-green-600 mt-0.5" />
                                        <div>
                                            <h3 className="font-semibold text-green-900 dark:text-green-100">Page Info</h3>
                                            <div className="text-sm text-green-700 dark:text-green-300 mt-2 space-y-1">
                                                <p>Author: {page.user.name}</p>
                                                <p>Status: {page.status}</p>
                                                {page.is_featured && <p>✨ Featured page</p>}
                                                <p>URL: /{page.slug}</p>
                                            </div>
                                        </div>
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
