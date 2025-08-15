import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, User, Clock, Star, Share2 } from 'lucide-react';
import { useState } from 'react';

interface HeroHeaderProps {
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
    heroImage?: string;
    heroHeight?: 'sm' | 'md' | 'lg' | 'xl';
    titleSize?: 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl' | '4xl';
    backgroundGradient?: string;
}

export function HeroHeader({
    page,
    showMeta = true,
    showActions = true,
    heroImage,
    heroHeight = 'lg',
    titleSize = '3xl',
    backgroundGradient = 'from-blue-600 to-purple-600',
}: HeroHeaderProps) {
    const [isShared, setIsShared] = useState(false);

    const getHeroHeightClass = () => {
        switch (heroHeight) {
            case 'sm': return 'py-8';
            case 'md': return 'py-12';
            case 'lg': return 'py-16';
            case 'xl': return 'py-20';
            default: return 'py-16';
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
        <header className={`relative ${getHeroHeightClass()} bg-gradient-to-r ${backgroundGradient} text-white`}>
            {heroImage && (
                <div className="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20" 
                     style={{ backgroundImage: `url(${heroImage})` }} />
            )}
            
            <div className="relative z-10 container mx-auto px-4 text-center">
                <div className="max-w-4xl mx-auto">
                    {page.is_featured && (
                        <Badge variant="secondary" className="mb-4 bg-white/20 text-white border-white/30">
                            <Star className="h-3 w-3 mr-1" />
                            Featured
                        </Badge>
                    )}
                    
                    <h1 className={`${getTitleSizeClass()} font-bold leading-tight mb-6`}>
                        {page.title}
                    </h1>
                    
                    {page.excerpt && (
                        <p className="text-xl text-white/90 mb-8 max-w-3xl mx-auto">
                            {page.excerpt}
                        </p>
                    )}
                    
                    {showMeta && (
                        <div className="flex items-center justify-center gap-6 text-white/80 text-sm mb-8">
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
                        </div>
                    )}
                    
                    {showActions && (
                        <div className="flex items-center justify-center gap-4">
                            <Button 
                                variant="outline" 
                                size="lg"
                                className="bg-white/10 text-white border-white/30 hover:bg-white/20"
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
