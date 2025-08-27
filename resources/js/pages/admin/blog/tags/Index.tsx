import { CanAccess } from '@/components/CanAccess';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Tag, Hash, TrendingUp } from 'lucide-react';
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
        title: 'Tags',
        href: '/admin/blog/tags',
    },
];

interface BlogTag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    color?: string;
    usage_count: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    tags: BlogTag[];
    permissions: {
        canCreate: boolean;
        canEdit: boolean;
        canDelete: boolean;
    };
}

export default function BlogTagsIndex({ tags, permissions }: Props) {
    const { hasAnyPermission } = usePermissions();
    const [isDeleting, setIsDeleting] = useState<number | null>(null);

    const handleDelete = async (tagId: number) => {
        if (!confirm('Are you sure you want to delete this tag? It will be removed from all posts.')) return;
        
        setIsDeleting(tagId);
        try {
            await router.delete(`/admin/blog/tags/${tagId}`);
        } catch (error) {
            console.error('Error deleting tag:', error);
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

    // Sort tags by usage count for better organization
    const sortedTags = [...tags].sort((a, b) => b.usage_count - a.usage_count);
    const popularTags = sortedTags.slice(0, 5);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Blog Tags" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Blog Tags
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage tags to help readers discover related content
                        </p>
                    </div>
                    
                    <CanAccess permissions={['blog.tags.create']}>
                        <Link href="/admin/blog/tags/create">
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                New Tag
                            </Button>
                        </Link>
                    </CanAccess>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-blue-100 rounded-lg">
                                    <Tag className="h-5 w-5 text-blue-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Total Tags</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {tags.length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-green-100 rounded-lg">
                                    <Hash className="h-5 w-5 text-green-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Total Usage</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {tags.reduce((total, tag) => total + tag.usage_count, 0)}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-purple-100 rounded-lg">
                                    <TrendingUp className="h-5 w-5 text-purple-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Most Popular</p>
                                    <p className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {popularTags.length > 0 ? popularTags[0].name : 'None'}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Popular Tags Section */}
                {popularTags.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Popular Tags</CardTitle>
                            <CardDescription>
                                Your most frequently used tags
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-2">
                                {popularTags.map((tag) => (
                                    <Badge
                                        key={tag.id}
                                        variant="secondary"
                                        className="text-sm px-3 py-1"
                                        style={tag.color ? { 
                                            backgroundColor: `${tag.color}20`, 
                                            borderColor: tag.color,
                                            color: tag.color 
                                        } : {}}
                                    >
                                        #{tag.name}
                                        <span className="ml-2 text-xs opacity-75">
                                            {tag.usage_count}
                                        </span>
                                    </Badge>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Tags List */}
                <Card>
                    <CardHeader>
                        <CardTitle>All Tags</CardTitle>
                        <CardDescription>
                            Manage all your blog tags
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {tags.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left p-4">Tag</th>
                                            <th className="text-left p-4">Slug</th>
                                            <th className="text-left p-4">Usage Count</th>
                                            <th className="text-left p-4">Created</th>
                                            <th className="text-left p-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {sortedTags.map((tag) => (
                                            <tr key={tag.id} className="border-b hover:bg-gray-50 dark:hover:bg-gray-900">
                                                <td className="p-4">
                                                    <div className="flex items-center space-x-2">
                                                        {tag.color && (
                                                            <div 
                                                                className="w-3 h-3 rounded-full flex-shrink-0"
                                                                style={{ backgroundColor: tag.color }}
                                                            />
                                                        )}
                                                        <div>
                                                            <p className="font-medium text-gray-900 dark:text-gray-100">
                                                                #{tag.name}
                                                            </p>
                                                            {tag.description && (
                                                                <p className="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">
                                                                    {tag.description}
                                                                </p>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    <code className="text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                                        {tag.slug}
                                                    </code>
                                                </td>
                                                <td className="p-4">
                                                    <Badge variant={tag.usage_count > 0 ? "default" : "secondary"}>
                                                        {tag.usage_count} posts
                                                    </Badge>
                                                </td>
                                                <td className="p-4">
                                                    <span className="text-sm text-gray-600 dark:text-gray-400">
                                                        {formatDate(tag.created_at)}
                                                    </span>
                                                </td>
                                                <td className="p-4">
                                                    <div className="flex items-center space-x-2">
                                                        <CanAccess permissions={['blog.tags.edit']}>
                                                            <Link href={`/admin/blog/tags/${tag.id}/edit`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </CanAccess>
                                                        
                                                        <CanAccess permissions={['blog.tags.delete']}>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="sm"
                                                                onClick={() => handleDelete(tag.id)}
                                                                disabled={isDeleting === tag.id}
                                                            >
                                                                <Trash2 className="h-4 w-4 text-red-500" />
                                                            </Button>
                                                        </CanAccess>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <div className="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <Tag className="h-12 w-12 text-gray-400" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    No tags yet
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-4">
                                    Create tags to help categorize and organize your blog content.
                                </p>
                                <CanAccess permissions={['blog.tags.create']}>
                                    <Link href="/admin/blog/tags/create">
                                        <Button>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Create Tag
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