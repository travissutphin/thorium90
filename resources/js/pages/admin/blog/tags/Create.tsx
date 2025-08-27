import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Save, Tag as TagIcon, Hash } from 'lucide-react';
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
        title: 'Tags',
        href: '/admin/blog/tags',
    },
    {
        title: 'Create',
        href: '/admin/blog/tags/create',
    },
];

interface TagFormData {
    name: string;
    slug: string;
    description: string;
    color: string;
}

const DEFAULT_COLORS = [
    '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3',
    '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a',
    '#cddc39', '#ffeb3b', '#ffc107', '#ff9800', '#ff5722'
];

export default function CreateBlogTag() {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    
    const [formData, setFormData] = useState<TagFormData>({
        name: '',
        slug: '',
        description: '',
        color: '#00bcd4',
    });

    const handleInputChange = (field: keyof TagFormData, value: any) => {
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
            await router.post('/admin/blog/tags', formData);
        } catch (error: any) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
            console.error('Error creating tag:', error);
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Blog Tag" />

            <form onSubmit={handleSubmit} className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/admin/blog/tags">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Tags
                            </Button>
                        </Link>
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                Create Blog Tag
                            </h1>
                            <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Create a new tag to help categorize your blog content
                            </p>
                        </div>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <Button 
                            type="submit"
                            disabled={isSubmitting || !formData.name}
                        >
                            <Save className="h-4 w-4 mr-2" />
                            {isSubmitting ? 'Creating...' : 'Create Tag'}
                        </Button>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Basic Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Tag Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label htmlFor="name">Name *</Label>
                                    <Input
                                        id="name"
                                        value={formData.name}
                                        onChange={(e) => handleInputChange('name', e.target.value)}
                                        placeholder="Enter tag name..."
                                        className={errors.name ? 'border-red-500' : ''}
                                        maxLength={50}
                                    />
                                    {errors.name && <p className="text-sm text-red-500 mt-1">{errors.name}</p>}
                                    <p className="text-xs text-gray-500 mt-1">
                                        {formData.name.length}/50 characters
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={formData.slug}
                                        onChange={(e) => handleInputChange('slug', e.target.value)}
                                        placeholder="tag-url-slug"
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
                                        rows={3}
                                        value={formData.description}
                                        onChange={(e) => handleInputChange('description', e.target.value)}
                                        placeholder="Brief description of the tag..."
                                        className={errors.description ? 'border-red-500' : ''}
                                    />
                                    {errors.description && <p className="text-sm text-red-500 mt-1">{errors.description}</p>}
                                    <p className="text-xs text-gray-500 mt-1">
                                        Optional description to help explain the tag's purpose
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="color">Tag Color</Label>
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
                                                placeholder="#00bcd4"
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
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Tag Preview */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <TagIcon className="h-4 w-4 mr-2" />
                                    Preview
                                </CardTitle>
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
                                                #{formData.name || 'Tag Name'}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                /{formData.slug || 'tag-slug'}
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
                                            variant="secondary"
                                            style={{ 
                                                backgroundColor: `${formData.color}20`, 
                                                borderColor: formData.color,
                                                color: formData.color 
                                            }}
                                        >
                                            <Hash className="h-3 w-3 mr-1" />
                                            {formData.name || 'tag'}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Usage Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm text-gray-600 dark:text-gray-400">
                                    Tag Guidelines
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm text-gray-600 dark:text-gray-400">
                                <ul className="space-y-2">
                                    <li>• Keep tag names short and descriptive</li>
                                    <li>• Use lowercase for consistency</li>
                                    <li>• Avoid special characters in names</li>
                                    <li>• Colors help users identify related content</li>
                                    <li>• Tags are automatically counted when used</li>
                                </ul>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}