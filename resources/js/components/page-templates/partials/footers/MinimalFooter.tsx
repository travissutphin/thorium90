import { Button } from '@/components/ui/button';
import { ArrowUp } from 'lucide-react';

interface MinimalFooterProps {
    page: {
        title: string;
        slug: string;
    };
    showBackToTop?: boolean;
    copyrightText?: string;
}

export function MinimalFooter({
    page,
    showBackToTop = true,
    copyrightText,
}: MinimalFooterProps) {
    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <footer className="mt-16 border-t">
            <div className="container mx-auto px-4 py-6">
                <div className="flex items-center justify-between">
                    <div className="text-sm text-muted-foreground">
                        {copyrightText || `© ${new Date().getFullYear()} ${page.title}. All rights reserved.`}
                    </div>
                    
                    {showBackToTop && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={scrollToTop}
                            className="text-muted-foreground hover:text-foreground"
                        >
                            <ArrowUp className="h-4 w-4 mr-2" />
                            Back to Top
                        </Button>
                    )}
                </div>
            </div>
        </footer>
    );
}
