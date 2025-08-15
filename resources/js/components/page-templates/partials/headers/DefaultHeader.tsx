import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, User, Clock, Star, Share2 } from 'lucide-react';
import { useState } from 'react';

interface DefaultHeaderProps {
    page: {
        title: string;
        excerpt?: string;
        status: string;
        is_featured: boolean;
        published_at?: string;
        user: { name: string };
        reading_time: number;
        slug: string;
    };
    showMeta?: boolean;
    showActions?: boolean;
    titleSize?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
}

export function DefaultHeader({
    page,
    showMeta = true,
    showActions = true,
    titleSize = '3xl',
}: DefaultHeaderProps) {
    const [isShared, setIsShared] = useState(false);

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

    const getTitleSizeClass = () => {
        switch (titleSize) {
            case 'sm': return 'text-xl';
            case 'md': return 'text-2xl';
            case 'lg': return 'text-3xl';
            case 'xl': return 'text-4xl';
            case '2xl': return 'text-5xl';
            case '3xl': return 'text-6xl';
            case '4xl': return 'text-7xl';
            default: return 'text-3xl';
        }
    };

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

    return (
        <header className="border-b bg-background">
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-4xl">
                    {page.is_featured && (
                        <Badge variant="outline" className="mb-4 text-yellow-600 border-yellow-600">
                            <Star className="h-3 w-3 mr-1" />
                            Featured
                        </Badge>
                    )}
                    
                    <h1 className={`${getTitleSizeClass()} font-bold leading-tight mb-4`}>
                        {page.title}
                    </h1>
                    
                    {page.excerpt && (
                        <p className="text-xl text-muted-foreground mb-6">
                            {page.excerpt}
                        </p>
                    )}
                    
                    {showMeta && (
                        <div className="flex items-center gap-6 text-sm text-muted-foreground mb-6">
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
                            
                            <Badge className={getStatusColor(page.status)}>
                                {page.status.charAt(0).toUpperCase() + page.status.slice(1)}
                            </Badge>
                        </div>
                    )}
                    
                    {showActions && (
                        <div className="flex items-center gap-4">
                            <Button 
                                variant="outline" 
                                onClick={handleShare}
                            >
                                <Share2 className="h-4 w-4 mr-2" />
                                {isShared ? 'Copied!' : 'Share'}
                            </Button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
