import { useState, useEffect, useCallback } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Save, Eye, Trash2, BarChart3 } from 'lucide-react';
import { type BreadcrumbItem, type FAQItem } from '@/types';
import { AEOFaqEditor, TopicSelector, KeywordManager, ReadingTimeDisplay, ContentAnalysisPanel } from '@/components/aeo';
import BlogFeaturedImageSelector from '@/components/blog/forms/BlogFeaturedImageSelector';

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    description?: string;
}

interface BlogTag {
    id: number;
    name: string;
    slug: string;
    color?: string;
}

interface BlogPost {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt: string;
    status: 'draft' | 'published' | 'scheduled';
    is_featured: boolean;
    blog_category_id: number | null;
    featured_image: string | null;
    featured_image_alt: string | null;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    schema_type: string | null;
    topics: string[] | null;
    keywords: string[] | null;
    published_at: string | null;
    // AEO Enhancement fields
    faq_data: FAQItem[] | null;
    reading_time: number | null;
    content_type: string | null;
    content_score: number | null;
    blog_tags: BlogTag[];
    blog_category: BlogCategory | null;
}

interface Props {
    post: BlogPost;
    categories: BlogCategory[];
    tags: BlogTag[];
    seoSuggestions: string[];
    config: {
        features: Record<string, boolean>;
        settings: Record<string, any>;
    };
}

interface FormData {
    title: string;
    slug: string;
    content: string;
    excerpt: string;
    status: 'draft' | 'published' | 'scheduled';
    is_featured: boolean;
    blog_category_id: string;
    featured_image: string;
    featured_image_alt: string;
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    schema_type: string;
    topics: string[];
    keywords: string[];
    tags: number[];
    published_at: string;
    // AEO Enhancement fields
    faq_data: FAQItem[];
    reading_time: number | null;
    content_type: string;
    content_score: number | null;
}

export default function EditBlogPost({ post, categories, tags, seoSuggestions, config }: Props) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [notification, setNotification] = useState<{type: 'success' | 'error', message: string} | null>(null);
    
    // Auto-hide notification after 5 seconds
    useEffect(() => {
        if (notification) {
            const timer = setTimeout(() => {
                setNotification(null);
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [notification]);

    const showNotification = (type: 'success' | 'error', message: string) => {
        setNotification({ type, message });
    };

    const getValidationErrorMessage = (errors: Record<string, string>) => {
        const errorMessages = Object.entries(errors).map(([field, message]) => {
            const friendlyFieldNames: Record<string, string> = {
                'meta_description': 'Meta Description',
                'meta_title': 'Meta Title',
                'title': 'Title',
                'content': 'Content',
                'excerpt': 'Excerpt',
                'slug': 'URL Slug',
                'blog_category_id': 'Category',
                'featured_image': 'Featured Image',
                'featured_image_alt': 'Image Alt Text'
            };
            const friendlyName = friendlyFieldNames[field] || field.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            return `${friendlyName}: ${message}`;
        });
        
        return errorMessages.length === 1 
            ? errorMessages[0]
            : `Please fix the following errors:\n• ${errorMessages.join('\n• ')}`;
    };
    
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: route('admin.dashboard'),
        },
        {
            title: 'Blog',
            href: route('admin.blog.posts.index'),
        },
        {
            title: 'Posts',
            href: route('admin.blog.posts.index'),
        },
        {
            title: `Edit: ${post.title}`,
            href: route('admin.blog.posts.edit', { post: post.id }),
        },
    ];
    
    const [formData, setFormData] = useState<FormData>({
        title: post.title || '',
        slug: post.slug || '',
        content: post.content || '',
        excerpt: post.excerpt || '',
        status: post.status || 'draft',
        is_featured: post.is_featured || false,
        blog_category_id: post.blog_category_id?.toString() || '0',
        featured_image: post.featured_image || '',
        featured_image_alt: post.featured_image_alt || '',
        meta_title: post.meta_title || '',
        meta_description: post.meta_description || '',
        meta_keywords: post.meta_keywords || '',
        schema_type: post.schema_type || 'BlogPosting',
        topics: post.topics || [],
        keywords: post.keywords || [],
        tags: post.blog_tags?.map(tag => tag.id) || [],
        published_at: post.published_at ? new Date(post.published_at).toISOString().slice(0, 16) : '',
        // AEO Enhancement fields
        faq_data: post.faq_data || [],
        reading_time: post.reading_time || null,
        content_type: post.content_type || 'blog_post',
        content_score: post.content_score || null,
    });

    const handleInputChange = (field: keyof FormData, value: any) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));

        // Clear errors for this field
        if (errors[field]) {
            setErrors(prev => ({
                ...prev,
                [field]: ''
            }));
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        setErrors({});

        try {
            await router.put(route('admin.blog.posts.update', { post: post.id }), prepareFormData(formData), {
                onError: (errors) => {
                    console.error('Validation errors:', JSON.stringify(errors, null, 2));
                    setErrors(errors);
                    showNotification('error', getValidationErrorMessage(errors));
                },
                onSuccess: () => {
                    console.log('Post updated successfully');
                    showNotification('success', 'Blog post updated successfully!');
                }
            });
        } catch (error) {
            console.error('Error updating post:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleSaveAsDraft = async () => {
        const draftData = { ...formData, status: 'draft' as const };
        setIsSubmitting(true);
        
        try {
            await router.put(route('admin.blog.posts.update', { post: post.id }), prepareFormData(draftData), {
                onError: (errors) => {
                    console.error('Draft validation errors:', JSON.stringify(errors, null, 2));
                    setErrors(errors);
                    showNotification('error', getValidationErrorMessage(errors));
                },
                onSuccess: () => {
                    console.log('Draft saved successfully');
                    showNotification('success', 'Draft saved successfully!');
                }
            });
        } catch (error) {
            console.error('Error saving draft:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handlePublish = async () => {
        const publishData = { ...formData, status: 'published' as const };
        if (!publishData.published_at) {
            publishData.published_at = new Date().toISOString().slice(0, 16);
        }
        setIsSubmitting(true);
        
        try {
            await router.put(route('admin.blog.posts.update', { post: post.id }), prepareFormData(publishData), {
                onError: (errors) => {
                    console.error('Publish validation errors:', JSON.stringify(errors, null, 2));
                    setErrors(errors);
                    showNotification('error', getValidationErrorMessage(errors));
                },
                onSuccess: () => {
                    console.log('Post published successfully');
                    showNotification('success', 'Post published successfully!');
                }
            });
        } catch (error) {
            console.error('Error publishing post:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleDelete = async () => {
        if (confirm('Are you sure you want to delete this blog post? This action cannot be undone.')) {
            setIsSubmitting(true);
            try {
                await router.delete(route('admin.blog.posts.destroy', { post: post.id }));
            } catch (error) {
                console.error('Error deleting post:', error);
            } finally {
                setIsSubmitting(false);
            }
        }
    };

    const handleTagToggle = (tagId: number) => {
        setFormData(prev => ({
            ...prev,
            tags: prev.tags.includes(tagId)
                ? prev.tags.filter(id => id !== tagId)
                : [...prev.tags, tagId]
        }));
    };

    const prepareFormData = (data: FormData) => {
        const prepared = {
            ...data,
            // Convert category ID properly
            blog_category_id: data.blog_category_id === '0' || data.blog_category_id === '' ? null : parseInt(data.blog_category_id),
            // Ensure arrays are properly formatted
            topics: Array.isArray(data.topics) ? data.topics : [],
            keywords: Array.isArray(data.keywords) ? data.keywords : [],
            tags: Array.isArray(data.tags) ? data.tags.map(id => parseInt(id.toString())) : [],
            faq_data: Array.isArray(data.faq_data) ? data.faq_data : [],
            // Convert boolean properly
            is_featured: Boolean(data.is_featured)
        };
        
        // Remove empty string values and convert to null
        Object.keys(prepared).forEach(key => {
            if (prepared[key as keyof typeof prepared] === '') {
                (prepared as any)[key] = null;
            }
        });
        
        // Debug logging
        console.log('Submitting form data:', prepared);
        
        return prepared;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Blog Post: ${post.title}`} />

            {/* Notification Toast */}
            {notification && (
                <div className={`fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-md ${
                    notification.type === 'success' 
                        ? 'bg-green-500 text-white' 
                        : 'bg-red-500 text-white'
                }`}>
                    <div className="flex items-start justify-between">
                        <div className="flex-1">
                            <p className="font-medium">
                                {notification.type === 'success' ? '✅ Success' : '❌ Error'}
                            </p>
                            <p className="text-sm mt-1 whitespace-pre-line">
                                {notification.message}
                            </p>
                        </div>
                        <button 
                            onClick={() => setNotification(null)}
                            className="ml-3 text-white hover:text-gray-200"
                        >
                            ×
                        </button>
                    </div>
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={route('admin.blog.posts.index')}>
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Posts
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                Edit Blog Post
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Update your blog post content and settings
                            </p>
                        </div>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <Button 
                            type="button" 
                            variant="destructive"
                            onClick={handleDelete}
                            disabled={isSubmitting}
                            size="sm"
                        >
                            <Trash2 className="h-4 w-4 mr-2" />
                            Delete
                        </Button>
                        <Button 
                            type="button" 
                            variant="outline" 
                            onClick={handleSaveAsDraft}
                            disabled={isSubmitting}
                        >
                            <Save className="h-4 w-4 mr-2" />
                            Save Draft
                        </Button>
                        <Button 
                            type="button"
                            onClick={handlePublish}
                            disabled={isSubmitting || !formData.title}
                        >
                            <Eye className="h-4 w-4 mr-2" />
                            {post.status === 'published' ? 'Update' : 'Publish'}
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Post Content</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="title">Title *</Label>
                                    <Input
                                        id="title"
                                        value={formData.title}
                                        onChange={(e) => handleInputChange('title', e.target.value)}
                                        placeholder="Enter post title..."
                                        className={errors.title ? 'border-red-500' : ''}
                                    />
                                    {errors.title && <p className="text-sm text-red-500 mt-1">{errors.title}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={formData.slug}
                                        onChange={(e) => handleInputChange('slug', e.target.value)}
                                        placeholder="post-url-slug"
                                        className={errors.slug ? 'border-red-500' : ''}
                                    />
                                    {errors.slug && <p className="text-sm text-red-500 mt-1">{errors.slug}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="excerpt">Excerpt</Label>
                                    <Textarea
                                        id="excerpt"
                                        rows={3}
                                        value={formData.excerpt}
                                        onChange={(e) => handleInputChange('excerpt', e.target.value)}
                                        placeholder="Brief description of the post..."
                                        className={errors.excerpt ? 'border-red-500' : ''}
                                    />
                                    {errors.excerpt && <p className="text-sm text-red-500 mt-1">{errors.excerpt}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="content">Content</Label>
                                    <Textarea
                                        id="content"
                                        rows={12}
                                        value={formData.content}
                                        onChange={(e) => handleInputChange('content', e.target.value)}
                                        placeholder="Write your blog post content here..."
                                        className={errors.content ? 'border-red-500' : ''}
                                    />
                                    <p className={`text-xs mt-1 ${
                                        formData.content.split(' ').filter(word => word.length > 0).length < 300 
                                            ? 'text-amber-500' 
                                            : 'text-green-600'
                                    }`}>
                                        {formData.content.split(' ').filter(word => word.length > 0).length} words
                                        {formData.content.split(' ').filter(word => word.length > 0).length < 300 && ' (Recommend 300+ for SEO)'}
                                    </p>
                                    {errors.content && <p className="text-sm text-red-500 mt-1">{errors.content}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* SEO Settings */}
                        {config.features.seo && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>SEO Settings</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div>
                                        <Label htmlFor="meta_title">Meta Title</Label>
                                        <Input
                                            id="meta_title"
                                            value={formData.meta_title}
                                            onChange={(e) => handleInputChange('meta_title', e.target.value)}
                                            placeholder="SEO-optimized title..."
                                            maxLength={60}
                                        />
                                        <p className={`text-xs mt-1 font-medium ${
                                            formData.meta_title.length > 60 
                                                ? 'text-red-500' 
                                                : formData.meta_title.length < 30 
                                                    ? 'text-amber-500' 
                                                    : 'text-green-600'
                                        }`}>
                                            {formData.meta_title.length}/60 characters
                                            {formData.meta_title.length > 60 && ' ⚠️ TOO LONG - Will prevent saving!'}
                                            {formData.meta_title.length < 30 && formData.meta_title.length > 0 && ' (Recommended: 30-60)'}
                                        </p>
                                        {formData.meta_title.length > 60 && (
                                            <div className="bg-red-50 border border-red-200 rounded p-2 mt-2">
                                                <p className="text-red-700 text-xs">
                                                    <strong>⚠️ Character limit exceeded!</strong> Please reduce by {formData.meta_title.length - 60} characters to save your post.
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="meta_description">Meta Description</Label>
                                        <Textarea
                                            id="meta_description"
                                            rows={2}
                                            value={formData.meta_description}
                                            onChange={(e) => handleInputChange('meta_description', e.target.value)}
                                            placeholder="SEO meta description..."
                                            maxLength={160}
                                        />
                                        <p className={`text-xs mt-1 font-medium ${
                                            formData.meta_description.length > 160 
                                                ? 'text-red-500' 
                                                : formData.meta_description.length < 120 
                                                    ? 'text-amber-500' 
                                                    : 'text-green-600'
                                        }`}>
                                            {formData.meta_description.length}/160 characters
                                            {formData.meta_description.length > 160 && ' ⚠️ TOO LONG - Will prevent saving!'}
                                            {formData.meta_description.length < 120 && formData.meta_description.length > 0 && ' (Recommended: 120-160)'}
                                        </p>
                                        {formData.meta_description.length > 160 && (
                                            <div className="bg-red-50 border border-red-200 rounded p-2 mt-2">
                                                <p className="text-red-700 text-xs">
                                                    <strong>⚠️ Character limit exceeded!</strong> Please reduce by {formData.meta_description.length - 160} characters to save your post.
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    <div>
                                        <Label htmlFor="meta_keywords">Meta Keywords</Label>
                                        <Input
                                            id="meta_keywords"
                                            value={formData.meta_keywords}
                                            onChange={(e) => handleInputChange('meta_keywords', e.target.value)}
                                            placeholder="keyword1, keyword2, keyword3"
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* AEO Enhancement Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    AEO Optimization
                                </CardTitle>
                                <CardDescription>
                                    Enhance your content for AI search engines and voice search with structured data and FAQ content.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Topic Selector */}
                                <TopicSelector
                                    value={formData.topics || []}
                                    onChange={(topics) => handleInputChange('topics', topics)}
                                    maxTopics={5}
                                    disabled={isSubmitting}
                                />

                                {/* Keyword Manager */}
                                <KeywordManager
                                    value={formData.keywords || []}
                                    onChange={(keywords) => handleInputChange('keywords', keywords)}
                                    maxKeywords={10}
                                    disabled={isSubmitting}
                                />

                                {/* Reading Time Display */}
                                {formData.content && (
                                    <ReadingTimeDisplay
                                        content={formData.content}
                                        readingTime={formData.reading_time}
                                        showWordCount={true}
                                    />
                                )}

                                {/* FAQ Editor */}
                                <AEOFaqEditor
                                    value={formData.faq_data || []}
                                    onChange={(faqs) => handleInputChange('faq_data', faqs)}
                                    maxItems={10}
                                    disabled={isSubmitting}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* AI Content Analysis */}
                        <ContentAnalysisPanel
                            title={formData.title}
                            content={formData.content}
                            onTagsSelected={useCallback((selectedTags) => {
                                // Schedule update for next tick to avoid render-phase setState
                                setTimeout(() => {
                                    // Find matching tag IDs from the available tags
                                    const matchingTagIds = selectedTags
                                        .map(suggestionTag => {
                                            const existingTag = tags.find(availableTag => 
                                                availableTag.name.toLowerCase() === suggestionTag.name.toLowerCase()
                                            );
                                            return existingTag?.id;
                                        })
                                        .filter(id => id !== undefined) as number[];
                                    
                                    // Update form tags
                                    handleInputChange('tags', [...formData.tags, ...matchingTagIds.filter(id => !formData.tags.includes(id))]);
                                }, 0);
                            }, [tags, formData.tags])}
                            onKeywordsSelected={useCallback((keywords) => {
                                setTimeout(() => {
                                    handleInputChange('keywords', keywords);
                                }, 0);
                            }, [])}
                            onTopicsSelected={useCallback((topics) => {
                                setTimeout(() => {
                                    handleInputChange('topics', topics);
                                }, 0);
                            }, [])}
                            onFAQsSelected={useCallback((faqs) => {
                                setTimeout(() => {
                                    handleInputChange('faq_data', faqs);
                                }, 0);
                            }, [])}
                            onContentTypeSelected={useCallback((contentType) => {
                                setTimeout(() => {
                                    handleInputChange('content_type', contentType);
                                }, 0);
                            }, [])}
                        />
                        
                        {/* SEO Analysis */}
                        {seoSuggestions && seoSuggestions.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center">
                                        <BarChart3 className="h-5 w-5 mr-2 text-blue-600" />
                                        SEO Analysis
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        {seoSuggestions.map((suggestion, index) => (
                                            <div key={index} className="flex items-start space-x-2 text-sm">
                                                <div className="w-2 h-2 bg-amber-500 rounded-full mt-2 flex-shrink-0"></div>
                                                <span className="text-gray-700 dark:text-gray-300">{suggestion}</span>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Status & Publishing */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Publishing</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="status">Status</Label>
                                    <Select
                                        value={formData.status}
                                        onValueChange={(value) => handleInputChange('status', value as FormData['status'])}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">Draft</SelectItem>
                                            <SelectItem value="published">Published</SelectItem>
                                            <SelectItem value="scheduled">Scheduled</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {(formData.status === 'scheduled' || formData.status === 'published') && (
                                    <div>
                                        <Label htmlFor="published_at">Publish Date & Time</Label>
                                        <Input
                                            id="published_at"
                                            type="datetime-local"
                                            value={formData.published_at}
                                            onChange={(e) => handleInputChange('published_at', e.target.value)}
                                        />
                                    </div>
                                )}

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_featured"
                                        checked={formData.is_featured}
                                        onCheckedChange={(checked) => handleInputChange('is_featured', checked)}
                                    />
                                    <Label htmlFor="is_featured">Featured Post</Label>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Category */}
                        {config.features.categories && categories.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Category</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <Select
                                        value={formData.blog_category_id}
                                        onValueChange={(value) => handleInputChange('blog_category_id', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="0">No Category</SelectItem>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </CardContent>
                            </Card>
                        )}

                        {/* Tags */}
                        {config.features.tags && tags.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Tags</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex flex-wrap gap-2 max-h-48 overflow-y-auto">
                                        {tags.map((tag) => (
                                            <Badge
                                                key={tag.id}
                                                variant={formData.tags.includes(tag.id) ? "default" : "outline"}
                                                className="cursor-pointer"
                                                onClick={() => handleTagToggle(tag.id)}
                                                style={tag.color && formData.tags.includes(tag.id) ? {
                                                    backgroundColor: tag.color,
                                                    borderColor: tag.color,
                                                    color: 'white'
                                                } : {}}
                                            >
                                                #{tag.name}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Schema Type */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Schema Type</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div>
                                    <Label htmlFor="schema_type">Schema Markup Type</Label>
                                    <Select
                                        value={formData.schema_type}
                                        onValueChange={(value) => handleInputChange('schema_type', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="BlogPosting">Blog Post (Default)</SelectItem>
                                            <SelectItem value="Article">Article</SelectItem>
                                            <SelectItem value="NewsArticle">News Article</SelectItem>
                                            <SelectItem value="Review">Review</SelectItem>
                                            <SelectItem value="HowTo">How-To Guide</SelectItem>
                                            <SelectItem value="FAQPage">FAQ Page</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p className="text-xs text-gray-500 mt-2">
                                        Choose the most appropriate schema type for better SEO and rich snippets. 
                                        <strong>Blog Post</strong> is recommended for most content.
                                    </p>
                                </div>
                                
                                {/* Schema Type Descriptions */}
                                <div className="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <div className="text-xs text-gray-600 dark:text-gray-400">
                                        {formData.schema_type === 'BlogPosting' && (
                                            <span><strong>Blog Post:</strong> Standard blog content with author, date, and engagement metrics</span>
                                        )}
                                        {formData.schema_type === 'Article' && (
                                            <span><strong>Article:</strong> Editorial content like journalism, research, or in-depth analysis</span>
                                        )}
                                        {formData.schema_type === 'NewsArticle' && (
                                            <span><strong>News Article:</strong> Time-sensitive news content with journalistic standards</span>
                                        )}
                                        {formData.schema_type === 'Review' && (
                                            <span><strong>Review:</strong> Product, service, or media reviews with ratings and opinions</span>
                                        )}
                                        {formData.schema_type === 'HowTo' && (
                                            <span><strong>How-To Guide:</strong> Step-by-step tutorials and instructional content</span>
                                        )}
                                        {formData.schema_type === 'FAQPage' && (
                                            <span><strong>FAQ Page:</strong> Question and answer format content</span>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Enhanced Featured Image with Media Library */}
                        {config.features.featured_images && (
                            <BlogFeaturedImageSelector
                                imageUrl={formData.featured_image}
                                altText={formData.featured_image_alt}
                                onChange={(url, alt) => {
                                    setFormData(prev => ({
                                        ...prev,
                                        featured_image: url,
                                        featured_image_alt: alt
                                    }));
                                }}
                                error={errors.featured_image}
                            />
                        )}
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}