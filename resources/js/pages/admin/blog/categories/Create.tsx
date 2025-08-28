import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Save, Eye, Palette } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin',
        href: '/admin',
    },
    {
        title: 'Blog',
        href: '/admin/blog',
    },
    {
        title: 'Categories',
        href: '/admin/blog/categories',
    },
    {
        title: 'Create',
        href: '/admin/blog/categories/create',
    },
];

interface CategoryFormData {
    name: string;
    slug: string;
    description: string;
    color: string;
    meta_title: string;
    meta_description: string;
    sort_order: number;
    is_active: boolean;
}

const DEFAULT_COLORS = [
    '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3',
    '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a',
    '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'
];

export default function CreateBlogCategory() {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    
    const [formData, setFormData] = useState<CategoryFormData>({
        name: '',
        slug: '',
        description: '',
        color: '#e91e63',
        meta_title: '',
        meta_description: '',
        sort_order: 0,
        is_active: true,
    });

    const handleInputChange = (field: keyof CategoryFormData, value: any) => {
        setFormData(prev => ({
            ...prev,
            [field]: value
        }));

        // Auto-generate slug from name
        if (field === 'name' && !formData.slug) {
            const slug = value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .trim();
            setFormData(prev => ({
                ...prev,
                slug: slug
            }));
        }

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
            await router.post('/admin/blog/categories', formData);
        } catch (error: any) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
            console.error('Error creating category:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleSaveAndActivate = async () => {
        const activeData = { ...formData, is_active: true };
        setIsSubmitting(true);
        setErrors({});
        
        try {
            await router.post('/admin/blog/categories', activeData);
        } catch (error: any) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
            console.error('Error creating category:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleSaveDraft = async () => {
        const draftData = { ...formData, is_active: false };
        setIsSubmitting(true);
        setErrors({});
        
        try {
            await router.post('/admin/blog/categories', draftData);
        } catch (error: any) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
            console.error('Error creating category:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    const getCharacterCountColor = (current: number, max: number) => {
        const percentage = (current / max) * 100;
        if (percentage >= 90) return 'text-red-500';
        if (percentage >= 70) return 'text-yellow-500';
        return 'text-green-500';
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Blog Category" />

            <form onSubmit={handleSubmit} className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/admin/blog/categories">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Categories
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                Create Blog Category
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Create a new category to organize your blog posts
                            </p>
                        </div>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <Button 
                            type="button" 
                            variant="outline" 
                            onClick={handleSaveDraft}
                            disabled={isSubmitting || !formData.name}
                        >
                            <Save className="h-4 w-4 mr-2" />
                            Save Draft
                        </Button>
                        <Button 
                            type="button"
                            onClick={handleSaveAndActivate}
                            disabled={isSubmitting || !formData.name}
                        >
                            <Eye className="h-4 w-4 mr-2" />
                            Save & Activate
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Category Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="name">Name *</Label>
                                    <Input
                                        id="name"
                                        value={formData.name}
                                        onChange={(e) => handleInputChange('name', e.target.value)}
                                        placeholder="Enter category name..."
                                        className={errors.name ? 'border-red-500' : ''}
                                        maxLength={100}
                                    />
                                    {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                                    <p className="text-xs text-gray-500 mt-1">
                                        {formData.name.length}/100 characters
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={formData.slug}
                                        onChange={(e) => handleInputChange('slug', e.target.value)}
                                        placeholder="category-url-slug"
                                        className={errors.slug ? 'border-red-500' : ''}
                                        maxLength={255}
                                    />
                                    {errors.slug && <p className="text-sm text-red-500 mt-1">{errors.slug}</p>}
                                    <p className="text-xs text-gray-500 mt-1">
                                        Auto-generated from name. Used in URLs.
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        rows={4}
                                        value={formData.description}
                                        onChange={(e) => handleInputChange('description', e.target.value)}
                                        placeholder="Brief description of the category..."
                                        className={errors.description ? 'border-red-500' : ''}
                                    />
                                    {errors.description && <p className="text-sm text-red-500 mt-1">{errors.description}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="color">Category Color</Label>
                                    <div className="flex items-center space-x-3 mt-2">
                                        <div className="flex items-center space-x-2">
                                            <div 
                                                className="w-8 h-8 rounded-full border-2 border-gray-300"
                                                style={{ backgroundColor: formData.color }}
                                            />
                                            <Input
                                                id="color"
                                                type="color"
                                                value={formData.color}
                                                onChange={(e) => handleInputChange('color', e.target.value)}
                                                className="w-16 h-8 p-0 border-0 cursor-pointer"
                                            />
                                            <Input
                                                value={formData.color}
                                                onChange={(e) => handleInputChange('color', e.target.value)}
                                                placeholder="#e91e63"
                                                className="w-24 text-sm"
                                                pattern="^#[a-fA-F0-9]{6}$"
                                            />
                                        </div>
                                    </div>
                                    <div className="flex flex-wrap gap-2 mt-3">
                                        {DEFAULT_COLORS.map((color) => (
                                            <button
                                                key={color}
                                                type="button"
                                                className="w-6 h-6 rounded-full border-2 border-gray-300 hover:scale-110 transition-transform"
                                                style={{ backgroundColor: color }}
                                                onClick={() => handleInputChange('color', color)}
                                                title={color}
                                            />
                                        ))}
                                    </div>
                                    {errors.color && <p className="text-sm text-red-500 mt-1">{errors.color}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* SEO Settings */}
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
                                        placeholder="SEO-optimized title for search engines..."
                                        maxLength={60}
                                        className={errors.meta_title ? 'border-red-500' : ''}
                                    />
                                    {errors.meta_title && <p className="text-sm text-red-500 mt-1">{errors.meta_title}</p>}
                                    <p className={`text-xs mt-1 ${getCharacterCountColor(formData.meta_title.length, 60)}`}>
                                        {formData.meta_title.length}/60 characters - Optimal: 50-60
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="meta_description">Meta Description</Label>
                                    <Textarea
                                        id="meta_description"
                                        rows={3}
                                        value={formData.meta_description}
                                        onChange={(e) => handleInputChange('meta_description', e.target.value)}
                                        placeholder="Brief description for search engine results..."
                                        maxLength={160}
                                        className={errors.meta_description ? 'border-red-500' : ''}
                                    />
                                    {errors.meta_description && <p className="text-sm text-red-500 mt-1">{errors.meta_description}</p>}
                                    <p className={`text-xs mt-1 ${getCharacterCountColor(formData.meta_description.length, 160)}`}>
                                        {formData.meta_description.length}/160 characters - Optimal: 120-160
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Settings */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Settings</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        type="number"
                                        min="0"
                                        value={formData.sort_order}
                                        onChange={(e) => handleInputChange('sort_order', parseInt(e.target.value) || 0)}
                                        placeholder="0"
                                        className={errors.sort_order ? 'border-red-500' : ''}
                                    />
                                    {errors.sort_order && <p className="text-sm text-red-500 mt-1">{errors.sort_order}</p>}
                                    <p className="text-xs text-gray-500 mt-1">
                                        Lower numbers appear first in category lists
                                    </p>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        checked={formData.is_active}
                                        onCheckedChange={(checked) => handleInputChange('is_active', checked)}
                                    />
                                    <Label htmlFor="is_active">Active Category</Label>
                                </div>
                                <p className="text-xs text-gray-500">
                                    Only active categories are visible on the public blog
                                </p>
                            </CardContent>
                        </Card>

                        {/* Category Preview */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Preview</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <div className="flex items-center space-x-3">
                                        <div 
                                            className="w-4 h-4 rounded-full flex-shrink-0"
                                            style={{ backgroundColor: formData.color }}
                                        />
                                        <div>
                                            <div className="font-medium text-gray-900 dark:text-gray-100">
                                                {formData.name || 'Category Name'}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                /{formData.slug || 'category-slug'}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {formData.description && (
                                        <p className="text-sm text-gray-600 dark:text-gray-400">
                                            {formData.description}
                                        </p>
                                    )}
                                    
                                    <div className="flex items-center space-x-2">
                                        <Badge 
                                            variant={formData.is_active ? "default" : "secondary"}
                                            style={formData.is_active ? {
                                                backgroundColor: formData.color,
                                                borderColor: formData.color,
                                                color: 'white'
                                            } : {}}
                                        >
                                            {formData.is_active ? 'Active' : 'Draft'}
                                        </Badge>
                                        <span className="text-xs text-gray-500">
                                            Order: {formData.sort_order}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}