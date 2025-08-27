import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { CanAccess } from '@/components/CanAccess';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft, Edit, Trash2, Tag as TagIcon, Hash, Calendar, TrendingUp, ExternalLink } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    status: 'published' | 'draft' | 'scheduled';
    published_at?: string;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    };
    blog_category?: {
        id: number;
        name: string;
        slug: string;
        color: string;
    };
}

interface BlogTag {
    id: number;
    name: string;
    slug: string;
    description?: string;
    color: string;
    usage_count: number;
    created_at: string;
    updated_at: string;
    blog_posts: BlogPost[];
}

interface Props {
    tag: BlogTag;
}

export default function ShowBlogTag({ tag }: Props) {
    const [isDeleting, setIsDeleting] = useState(false);

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
            title: tag.name,
            href: `/admin/blog/tags/${tag.id}`,
        },
    ];

    const handleDelete = async () => {
        if (!confirm(`Are you sure you want to delete the tag "${tag.name}"? This will remove it from all posts that use this tag. This action cannot be undone.`)) {
            return;
        }

        setIsDeleting(true);
        try {
            await router.delete(`/admin/blog/tags/${tag.id}`);
        } catch (error) {
            console.error('Error deleting tag:', error);
        } finally {
            setIsDeleting(false);
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatShortDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'published':
                return 'bg-green-100 text-green-800';
            case 'draft':
                return 'bg-gray-100 text-gray-800';
            case 'scheduled':
                return 'bg-blue-100 text-blue-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Tag: ${tag.name}`} />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href="/admin/blog/tags">
                            <Button variant="ghost" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back to Tags
                            </Button>
                        </Link>
                        <div className="flex items-center space-x-3">
                            <div 
                                className="w-8 h-8 rounded-full flex-shrink-0"
                                style={{ backgroundColor: tag.color }}
                            />
                            <div>
                                <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    #{tag.name}
                                </h1>
                                <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Tag details and associated content
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div className="flex items-center space-x-3">
                        <CanAccess permissions={['blog.tags.edit']}>
                            <Link href={`/admin/blog/tags/${tag.id}/edit`}>
                                <Button variant="outline">
                                    <Edit className="h-4 w-4 mr-2" />
                                    Edit Tag
                                </Button>
                            </Link>
                        </CanAccess>
                        
                        <CanAccess permissions={['blog.tags.delete']}>
                            <Button 
                                variant="destructive"
                                onClick={handleDelete}
                                disabled={isDeleting}
                            >
                                <Trash2 className="h-4 w-4 mr-2" />
                                {isDeleting ? 'Deleting...' : 'Delete Tag'}
                            </Button>
                        </CanAccess>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {/* Stats Cards */}
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-blue-100 rounded-lg">
                                    <TrendingUp className="h-5 w-5 text-blue-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Usage Count</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {tag.usage_count}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-green-100 rounded-lg">
                                    <Calendar className="h-5 w-5 text-green-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Created</p>
                                    <p className="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {formatShortDate(tag.created_at)}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-purple-100 rounded-lg">
                                    <TagIcon className="h-5 w-5 text-purple-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Slug</p>
                                    <p className="text-sm font-mono text-gray-900 dark:text-gray-100">
                                        {tag.slug}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div 
                                    className="w-8 h-8 rounded-full flex-shrink-0"
                                    style={{ backgroundColor: tag.color }}
                                />
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Color</p>
                                    <p className="text-sm font-mono text-gray-900 dark:text-gray-100">
                                        {tag.color}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Tag Information */}
                    <div className="lg:col-span-1">
                        <Card>
                            <CardHeader>
                                <CardTitle>Tag Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label className="text-sm font-medium text-gray-700 dark:text-gray-300">Name</Label>
                                    <p className="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">
                                        #{tag.name}
                                    </p>
                                </div>

                                <div>
                                    <Label className="text-sm font-medium text-gray-700 dark:text-gray-300">Slug</Label>
                                    <code className="block text-sm bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded mt-1">
                                        {tag.slug}
                                    </code>
                                </div>

                                {tag.description && (
                                    <div>
                                        <Label className="text-sm font-medium text-gray-700 dark:text-gray-300">Description</Label>
                                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {tag.description}
                                        </p>
                                    </div>
                                )}

                                <div>
                                    <Label className="text-sm font-medium text-gray-700 dark:text-gray-300">Preview</Label>
                                    <div className="mt-2">
                                        <Badge 
                                            variant="secondary"
                                            style={{ 
                                                backgroundColor: `${tag.color}20`, 
                                                borderColor: tag.color,
                                                color: tag.color 
                                            }}
                                            className="text-sm px-3 py-1"
                                        >
                                            <Hash className="h-3 w-3 mr-1" />
                                            {tag.name}
                                        </Badge>
                                    </div>
                                </div>

                                <div className="pt-4 border-t">
                                    <div className="space-y-2">
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600 dark:text-gray-400">Created:</span>
                                            <span>{formatDate(tag.created_at)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-gray-600 dark:text-gray-400">Updated:</span>
                                            <span>{formatDate(tag.updated_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Posts Using This Tag */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Posts Using This Tag ({tag.blog_posts.length})</CardTitle>
                            </CardHeader>
                            <CardContent>
                                {tag.blog_posts.length > 0 ? (
                                    <div className="space-y-4">
                                        {tag.blog_posts.map((post) => (
                                            <div key={post.id} className="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-900">
                                                <div className="flex items-start justify-between">
                                                    <div className="flex-1">
                                                        <div className="flex items-center space-x-2 mb-2">
                                                            <h3 className="font-semibold text-gray-900 dark:text-gray-100">
                                                                {post.title}
                                                            </h3>
                                                            <Badge className={`text-xs ${getStatusColor(post.status)}`}>
                                                                {post.status}
                                                            </Badge>
                                                        </div>
                                                        
                                                        {post.excerpt && (
                                                            <p className="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                                                {post.excerpt}
                                                            </p>
                                                        )}
                                                        
                                                        <div className="flex items-center space-x-4 text-xs text-gray-500">
                                                            <span>By {post.user.name}</span>
                                                            {post.blog_category && (
                                                                <div className="flex items-center space-x-1">
                                                                    <div 
                                                                        className="w-2 h-2 rounded-full"
                                                                        style={{ backgroundColor: post.blog_category.color }}
                                                                    />
                                                                    <span>{post.blog_category.name}</span>
                                                                </div>
                                                            )}
                                                            <span>
                                                                {post.published_at ? formatShortDate(post.published_at) : formatShortDate(post.created_at)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div className="flex items-center space-x-2 ml-4">
                                                        <CanAccess permissions={['blog.posts.edit']}>
                                                            <Link href={`/admin/blog/posts/${post.id}/edit`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </CanAccess>
                                                        
                                                        {post.status === 'published' && (
                                                            <Link href={`/blog/${post.slug}`} target="_blank">
                                                                <Button variant="ghost" size="sm">
                                                                    <ExternalLink className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-12">
                                        <div className="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <TagIcon className="h-12 w-12 text-gray-400" />
                                        </div>
                                        <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                            No posts yet
                                        </h3>
                                        <p className="text-gray-600 dark:text-gray-400 mb-4">
                                            This tag hasn't been used in any blog posts yet.
                                        </p>
                                        <CanAccess permissions={['blog.posts.create']}>
                                            <Link href="/admin/blog/posts/create">
                                                <Button>
                                                    Create First Post
                                                </Button>
                                            </Link>
                                        </CanAccess>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

// Label component for consistency
function Label({ children, className = '' }: { children: React.ReactNode; className?: string }) {
    return <label className={`block text-sm font-medium ${className}`}>{children}</label>;
}