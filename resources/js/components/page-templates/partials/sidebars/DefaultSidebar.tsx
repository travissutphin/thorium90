import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Eye } from 'lucide-react';

interface DefaultSidebarProps {
    page: {
        title: string;
        status: string;
        is_featured: boolean;
        published_at?: string;
        created_at: string;
        updated_at: string;
        user: { name: string };
        reading_time: number;
        meta_keywords?: string;
        schema_type?: string;
        slug: string;
    };
    showPageInfo?: boolean;
    showSeoInfo?: boolean;
    showRelatedPages?: boolean;
    showAuthorInfo?: boolean;
    showPublicUrl?: boolean;
}

export function DefaultSidebar({
    page,
    showPageInfo = true,
    showSeoInfo = true,
    showRelatedPages = false,
    showAuthorInfo = false,
    showPublicUrl = true,
}: DefaultSidebarProps) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'published':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'draft':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            case 'private':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    return (
        <div className="space-y-6">
            {/* Page Information */}
            {showPageInfo && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Page Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Status</label>
                            <div className="mt-1">
                                <Badge className={getStatusColor(page.status)}>
                                    {page.status.charAt(0).toUpperCase() + page.status.slice(1)}
                                </Badge>
                            </div>
                        </div>
                        
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Author</label>
                            <p className="mt-1 text-sm">{page.user.name}</p>
                        </div>
                        
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Created</label>
                            <p className="mt-1 text-sm">{new Date(page.created_at).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            })}</p>
                        </div>
                        
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Last Updated</label>
                            <p className="mt-1 text-sm">{new Date(page.updated_at).toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            })}</p>
                        </div>
                        
                        {page.published_at && (
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Published</label>
                                <p className="mt-1 text-sm">{new Date(page.published_at).toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                })}</p>
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* SEO Information */}
            {showSeoInfo && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">SEO Information</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div>
                            <label className="text-sm font-medium text-muted-foreground">Reading Time</label>
                            <p className="mt-1 text-sm">{page.reading_time} minutes</p>
                        </div>
                        
                        {page.schema_type && (
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Schema Type</label>
                                <p className="mt-1 text-sm">{page.schema_type}</p>
                            </div>
                        )}
                        
                        {page.meta_keywords && (
                            <div>
                                <label className="text-sm font-medium text-muted-foreground">Keywords</label>
                                <div className="mt-1 flex flex-wrap gap-1">
                                    {page.meta_keywords.split(',').map((keyword, index) => (
                                        <Badge key={index} variant="outline" className="text-xs">
                                            {keyword.trim()}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            )}

            {/* Author Information */}
            {showAuthorInfo && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">About the Author</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex items-center gap-3">
                            <div className="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                {page.user.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h4 className="font-semibold">{page.user.name}</h4>
                                <p className="text-sm text-muted-foreground">Content Creator</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Related Pages */}
            {showRelatedPages && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Related Pages</CardTitle>
                        <CardDescription>You might also be interested in</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="text-sm text-muted-foreground">
                            Related pages functionality can be implemented here.
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Public URL */}
            {showPublicUrl && page.status === 'published' && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Public URL</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center gap-2">
                            <Eye className="h-4 w-4 text-muted-foreground" />
                            <a 
                                href={`/pages/${page.slug}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 break-all"
                            >
                                /pages/{page.slug}
                            </a>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
