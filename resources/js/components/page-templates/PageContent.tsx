import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Share2, Calendar, User, Clock, Star, Eye } from 'lucide-react';
import { useState } from 'react';

interface PageContentProps {
    page: {
        title: string;
        content: string;
        excerpt?: string;
        status: string;
        is_featured: boolean;
        published_at?: string;
        user: { name: string };
        reading_time: number;
        slug: string;
    };
    template?: string;
    showMeta?: boolean;
    showActions?: boolean;
    showReadingProgress?: boolean;
    contentClassName?: string;
    customContent?: React.ReactNode;
}

export function PageContent({
    page,
    template = 'default',
    showMeta = true,
    showActions = true,
    showReadingProgress = false,
    contentClassName = '',
    customContent,
}: PageContentProps) {
    const [isShared, setIsShared] = useState(false);

    const handleShare = async () => {
        const shareUrl = `${window.location.origin}/pages/${page.slug}`;
        
        if (navigator.share) {
            try {
                await navigator.share({
                    title: page.title,
                    text: page.excerpt,
                    url: shareUrl,
                });
            } catch (err) {
                console.log('Error sharing:', err);
            }
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(shareUrl);
            setIsShared(true);
            setTimeout(() => setIsShared(false), 2000);
        }
    };

    // Custom content takes precedence
    if (customContent) {
        return <div className={contentClassName}>{customContent}</div>;
    }

    // Landing page template - minimal content
    if (template === 'landing') {
        return (
            <div className={contentClassName}>
                <div className="prose prose-gray dark:prose-invert max-w-none">
                    <div dangerouslySetInnerHTML={{ __html: page.content }} />
                </div>
                
                {showActions && (
                    <div className="mt-8 flex items-center justify-center">
                        <Button 
                            variant="outline" 
                            onClick={handleShare}
                            className="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Share2 className="h-4 w-4 mr-2" />
                            {isShared ? 'Copied!' : 'Share This Page'}
                        </Button>
                    </div>
                )}
            </div>
        );
    }

    // Full-width template - content only
    if (template === 'full-width') {
        return (
            <div className={contentClassName}>
                <div className="prose prose-gray dark:prose-invert max-w-none">
                    <div dangerouslySetInnerHTML={{ __html: page.content }} />
                </div>
            </div>
        );
    }

    // Default content layout
    return (
        <div className={contentClassName}>
            {/* Reading Progress Bar */}
            {showReadingProgress && (
                <div className="sticky top-16 z-40 bg-background border-b">
                    <div className="h-1 bg-muted">
                        <div 
                            className="h-full bg-primary transition-all duration-300"
                            style={{ width: '0%' }}
                            id="reading-progress"
                        />
                    </div>
                </div>
            )}

            {/* Content Card */}
            <Card>
                <CardContent className="pt-6">
                    {/* Meta Information */}
                    {showMeta && (
                        <div className="flex items-center gap-4 text-sm text-muted-foreground mb-6 pb-4 border-b">
                            <div className="flex items-center gap-2">
                                <User className="h-4 w-4" />
                                <span>By {page.user.name}</span>
                            </div>
                            
                            {page.published_at && (
                                <div className="flex items-center gap-2">
                                    <Calendar className="h-4 w-4" />
                                    <span>{new Date(page.published_at).toLocaleDateString('en-US', { 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric' 
                                    })}</span>
                                </div>
                            )}
                            
                            <div className="flex items-center gap-2">
                                <Clock className="h-4 w-4" />
                                <span>{page.reading_time} min read</span>
                            </div>
                            
                            {page.is_featured && (
                                <div className="flex items-center gap-2">
                                    <Star className="h-4 w-4 text-yellow-500" />
                                    <span>Featured</span>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Main Content */}
                    <div className="prose prose-gray dark:prose-invert max-w-none">
                        <div dangerouslySetInnerHTML={{ __html: page.content }} />
                    </div>

                    {/* Actions */}
                    {showActions && (
                        <div className="mt-8 pt-6 border-t flex items-center justify-between">
                            <div className="flex items-center gap-4">
                                <Button 
                                    variant="outline" 
                                    onClick={handleShare}
                                >
                                    <Share2 className="h-4 w-4 mr-2" />
                                    {isShared ? 'Copied!' : 'Share'}
                                </Button>
                            </div>
                            
                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                <Eye className="h-4 w-4" />
                                <span>Page: {page.slug}</span>
                            </div>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
