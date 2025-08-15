import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, Calendar, User, Tag } from 'lucide-react';

interface BlogSidebarProps {
    page: {
        title: string;
        status: string;
        is_featured: boolean;
        published_at?: string;
        user: { name: string };
        reading_time: number;
        meta_keywords?: string;
        slug: string;
    };
    showReadingTime?: boolean;
    showAuthor?: boolean;
    showTags?: boolean;
    showRelatedPosts?: boolean;
    categories?: Array<{
        name: string;
        href: string;
        count?: number;
    }>;
    recentPosts?: Array<{
        title: string;
        href: string;
        date: string;
    }>;
}

export function BlogSidebar({
    page,
    showReadingTime = true,
    showAuthor = true,
    showTags = true,
    showRelatedPosts = true,
    categories = [],
    recentPosts = [],
}: BlogSidebarProps) {
    return (
        <div className="space-y-6">
            {/* Quick Info */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Quick Info</CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                    {showReadingTime && (
                        <div className="flex items-center gap-2 text-sm">
                            <Clock className="h-4 w-4 text-muted-foreground" />
                            <span>{page.reading_time} min read</span>
                        </div>
                    )}
                    
                    {showAuthor && (
                        <div className="flex items-center gap-2 text-sm">
                            <User className="h-4 w-4 text-muted-foreground" />
                            <span>By {page.user.name}</span>
                        </div>
                    )}
                    
                    {page.published_at && (
                        <div className="flex items-center gap-2 text-sm">
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                            <span>{new Date(page.published_at).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric' 
                            })}</span>
                        </div>
                    )}
                    
                    {page.is_featured && (
                        <Badge variant="outline" className="text-yellow-600 border-yellow-600">
                            Featured Post
                        </Badge>
                    )}
                </CardContent>
            </Card>

            {/* Tags */}
            {showTags && page.meta_keywords && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Tags</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-wrap gap-2">
                            {page.meta_keywords.split(',').map((keyword, index) => (
                                <Badge key={index} variant="secondary" className="text-xs">
                                    <Tag className="h-3 w-3 mr-1" />
                                    {keyword.trim()}
                                </Badge>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Categories */}
            {categories.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Categories</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul className="space-y-2">
                            {categories.map((category, index) => (
                                <li key={index}>
                                    <a
                                        href={category.href}
                                        className="flex items-center justify-between text-sm text-muted-foreground hover:text-foreground transition-colors"
                                    >
                                        <span>{category.name}</span>
                                        {category.count && (
                                            <Badge variant="outline" className="text-xs">
                                                {category.count}
                                            </Badge>
                                        )}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>
            )}

            {/* Recent Posts */}
            {showRelatedPosts && recentPosts.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Recent Posts</CardTitle>
                        <CardDescription>Latest from our blog</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul className="space-y-3">
                            {recentPosts.map((post, index) => (
                                <li key={index}>
                                    <a
                                        href={post.href}
                                        className="block group"
                                    >
                                        <h4 className="text-sm font-medium group-hover:text-primary transition-colors line-clamp-2">
                                            {post.title}
                                        </h4>
                                        <p className="text-xs text-muted-foreground mt-1">
                                            {new Date(post.date).toLocaleDateString('en-US', { 
                                                month: 'short', 
                                                day: 'numeric' 
                                            })}
                                        </p>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>
            )}

            {/* Author Bio */}
            {showAuthor && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">About the Author</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center gap-3 mb-3">
                            <div className="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                {page.user.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h4 className="font-semibold">{page.user.name}</h4>
                                <p className="text-sm text-muted-foreground">Content Writer</p>
                            </div>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            Passionate about creating engaging content that informs and inspires our readers.
                        </p>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
