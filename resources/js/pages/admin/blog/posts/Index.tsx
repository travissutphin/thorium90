import { CanAccess } from '@/components/CanAccess';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { usePermissions } from '@/hooks/use-permissions';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Edit, Trash2, Eye, Calendar, User, Clock, BarChart3 } from 'lucide-react';
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
        title: 'Posts',
        href: '/admin/blog/posts',
    },
];

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

interface User {
    id: number;
    name: string;
    email: string;
}

interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    content: string;
    featured_image?: string;
    featured_image_alt?: string;
    status: 'draft' | 'published' | 'scheduled';
    published_at: string | null;
    reading_time?: number;
    view_count: number;
    user: User;
    blog_category?: BlogCategory;
    blog_tags: BlogTag[];
    created_at: string;
    updated_at: string;
}

interface Props {
    posts: {
        data: BlogPost[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters?: {
        search?: string;
        status?: string;
        category?: string;
        author?: string;
    };
}

export default function BlogPostsIndex({ posts, filters = {} }: Props) {
    const { hasAnyPermission } = usePermissions();
    const [isDeleting, setIsDeleting] = useState<number | null>(null);

    const handleDelete = async (postId: number) => {
        if (!confirm('Are you sure you want to delete this blog post?')) return;
        
        setIsDeleting(postId);
        try {
            await router.delete(`/admin/blog/posts/${postId}`);
        } catch (error) {
            console.error('Error deleting post:', error);
        } finally {
            setIsDeleting(null);
        }
    };

    const getStatusBadge = (status: string, publishedAt: string | null) => {
        switch (status) {
            case 'published':
                return <Badge variant="default" className="bg-green-100 text-green-800">Published</Badge>;
            case 'draft':
                return <Badge variant="secondary">Draft</Badge>;
            case 'scheduled':
                return <Badge variant="outline" className="border-blue-200 text-blue-800">Scheduled</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
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
            <Head title="My Blog Posts" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            My Blog Posts
                        </h1>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Manage your blog posts, drafts, and published content
                        </p>
                    </div>
                    
                    <CanAccess permissions={['blog.posts.create']}>
						<Link href="/admin/blog/posts/create">
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                New Post
                            </Button>
                        </Link>
                    </CanAccess>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-blue-100 rounded-lg">
                                    <Eye className="h-5 w-5 text-blue-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Published</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {posts.data.filter(p => p.status === 'published').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-yellow-100 rounded-lg">
                                    <Edit className="h-5 w-5 text-yellow-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Drafts</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {posts.data.filter(p => p.status === 'draft').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-purple-100 rounded-lg">
                                    <Calendar className="h-5 w-5 text-purple-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Scheduled</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {posts.data.filter(p => p.status === 'scheduled').length}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center">
                                <div className="p-2 bg-green-100 rounded-lg">
                                    <BarChart3 className="h-5 w-5 text-green-600" />
                                </div>
                                <div className="ml-4">
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Total Views</p>
                                    <p className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {posts.data.reduce((total, p) => total + p.view_count, 0).toLocaleString()}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Posts Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>All Posts</CardTitle>
                        <CardDescription>
                            Showing {posts.data.length} of {posts.total} posts
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {posts.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left p-4">Title</th>
                                            <th className="text-left p-4">Status</th>
                                            <th className="text-left p-4">Author</th>
                                            <th className="text-left p-4">Category</th>
                                            <th className="text-left p-4">Published</th>
                                            <th className="text-left p-4">Views</th>
                                            <th className="text-left p-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {posts.data.map((post) => (
                                            <tr key={post.id} className="border-b hover:bg-gray-50 dark:hover:bg-gray-900">
                                                <td className="p-4">
                                                    <div>
                                                        <p className="font-medium text-gray-900 dark:text-gray-100">
                                                            {post.title}
                                                        </p>
                                                        {post.excerpt && (
                                                            <p className="text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">
                                                                {post.excerpt}
                                                            </p>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    {getStatusBadge(post.status, post.published_at)}
                                                </td>
                                                <td className="p-4">
                                                    <div className="flex items-center">
                                                        <User className="h-4 w-4 mr-2 text-gray-400" />
                                                        <span className="text-sm text-gray-900 dark:text-gray-100">
                                                            {post.user.name}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    {post.blog_category ? (
                                                        <Badge 
                                                            variant="outline" 
                                                            style={{ 
                                                                borderColor: post.blog_category.color,
                                                                color: post.blog_category.color 
                                                            }}
                                                        >
                                                            {post.blog_category.name}
                                                        </Badge>
                                                    ) : (
                                                        <span className="text-sm text-gray-500">Uncategorized</span>
                                                    )}
                                                </td>
                                                <td className="p-4">
                                                    {post.published_at ? (
                                                        <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                            <Calendar className="h-4 w-4 mr-1" />
                                                            {formatDate(post.published_at)}
                                                        </div>
                                                    ) : (
                                                        <span className="text-sm text-gray-500">Not published</span>
                                                    )}
                                                </td>
                                                <td className="p-4">
                                                    <div className="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                        <Eye className="h-4 w-4 mr-1" />
                                                        {post.view_count.toLocaleString()}
                                                    </div>
                                                </td>
                                                <td className="p-4">
                                                    <div className="flex items-center space-x-2">
                                                        {post.status === 'published' && (
                                                            <Link href={`/blog/${post.slug}`} target="_blank">
                                                                <Button variant="ghost" size="sm">
                                                                    <Eye className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        )}
                                                        
                                                        <CanAccess permissions={['blog.posts.edit']}>
                                                            <Link href={`/admin/blog/posts/${post.id}/edit`}>
                                                                <Button variant="ghost" size="sm">
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </Link>
                                                        </CanAccess>
                                                        
                                                        <CanAccess permissions={['blog.posts.delete']}>
                                                            <Button 
                                                                variant="ghost" 
                                                                size="sm"
                                                                onClick={() => handleDelete(post.id)}
                                                                disabled={isDeleting === post.id}
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
                                    <Edit className="h-12 w-12 text-gray-400" />
                                </div>
                                <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    No posts yet
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-4">
                                    Get started by creating your first blog post.
                                </p>
                                <CanAccess permissions={['blog.posts.create']}>
                                    <Link href="/admin/blog/posts/create">
                                        <Button>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Create Post
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