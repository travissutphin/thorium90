import { CanAccess } from '@/components/CanAccess';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Folder, FileText } from 'lucide-react';
import { useState } from 'react';

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
];

interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    description?: string;
    color: string;
    posts_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    categories: BlogCategory[];
}

export default function BlogCategoriesIndex({ categories }: Props) {
    const { hasAnyPermission } = usePermissions();
    const [isDeleting, setIsDeleting] = useState<number | null>(null);

    const handleDelete = async (categoryId: number) => {
        if (!confirm('Are you sure you want to delete this category? All posts in this category will become uncategorized.')) return;
        
        setIsDeleting(categoryId);
        try {
            await router.delete(`/admin/blog/categories/${categoryId}`);
        } catch (error) {
            console.error('Error deleting category:', error);
        } finally {
            setIsDeleting(null);
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
            <Head title="Blog Categories" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Blog Categories
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Organize your blog posts with categories
                        </p>
                    </div>
                    
                    <CanAccess permissions={['blog.categories.create']}>
                        <Link href="/admin/blog/categories/create">
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                New Category
                            </Button>
                        </Link>
                    </CanAccess>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-purple-100 rounded-lg">
                                    <Folder className="h-5 w-5 text-purple-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Total Categories</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {categories.length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-green-100 rounded-lg">
                                    <FileText className="h-5 w-5 text-green-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Total Posts</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {categories.reduce((total, cat) => total + cat.posts_count, 0)}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-blue-100 rounded-lg">
                                    <Folder className="h-5 w-5 text-blue-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Most Popular</p>
                                    <p className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {categories.length > 0 
                                            ? categories.sort((a, b) => b.posts_count - a.posts_count)[0]?.name || 'None'
                                            : 'None'
                                        }
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Categories List */}
                <Card>
                    <CardHeader>
                        <CardTitle>All Categories</CardTitle>
                        <CardDescription>
                            Manage your blog categories and their associated posts
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {categories.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {categories.map((category) => (
                                    <div key={category.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div className="flex items-start justify-between mb-3">
                                            <div className="flex items-center space-x-3">
                                                <div 
                                                    className="w-4 h-4 rounded-full flex-shrink-0"
                                                    style={{ backgroundColor: category.color }}
                                                />
                                                <div>
                                                    <h3 className="font-medium text-gray-900 dark:text-gray-100">
                                                        {category.name}
                                                    </h3>
                                                    <p className="text-sm text-gray-500">
                                                        /{category.slug}
                                                    </p>
                                                </div>
                                            </div>
                                            <Badge variant="secondary">
                                                {category.posts_count} posts
                                            </Badge>
                                        </div>

                                        {category.description && (
                                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                                {category.description}
                                            </p>
                                        )}

                                        <div className="flex items-center justify-between">
                                            <p className="text-xs text-gray-500">
                                                Created {formatDate(category.created_at)}
                                            </p>
                                            
                                            <div className="flex items-center space-x-2">
                                                <CanAccess permissions={['blog.categories.edit']}>
                                                    <Link href={`/admin/blog/categories/${category.id}/edit`}>
                                                        <Button variant="ghost" size="sm">
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                </CanAccess>
                                                
                                                <CanAccess permissions={['blog.categories.delete']}>
                                                    <Button 
                                                        variant="ghost" 
                                                        size="sm"
                                                        onClick={() => handleDelete(category.id)}
                                                        disabled={isDeleting === category.id}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-500" />
                                                    </Button>
                                                </CanAccess>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <div className="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <Folder className="h-12 w-12 text-gray-400" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    No categories yet
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-4">
                                    Create categories to help organize your blog posts.
                                </p>
                                <CanAccess permissions={['blog.categories.create']}>
                                    <Link href="/admin/blog/categories/create">
                                        <Button>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Create Category
                                        </Button>
                                    </Link>
                                </CanAccess>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}