import { Button } from '@/components/ui/button';
import { Home, ArrowLeft } from 'lucide-react';

interface SimpleNavigationProps {
    page: {
        title: string;
        slug: string;
    };
    showLogo?: boolean;
    showBackButton?: boolean;
    logoText?: string;
}

export function SimpleNavigation({
    page,
    showLogo = true,
    showBackButton = false,
    logoText,
}: SimpleNavigationProps) {
    return (
        <nav className="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
            <div className="container mx-auto px-4">
                <div className="flex items-center justify-between h-16">
                    {/* Logo and Title */}
                    {showLogo && (
                        <div className="flex items-center gap-2">
                            <Home className="h-6 w-6 text-primary" />
                            <span className="font-semibold text-lg">{logoText || page.title}</span>
                        </div>
                    )}
                    
                    {/* Back Button */}
                    {showBackButton && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => window.history.back()}
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Back
                        </Button>
                    )}
                </div>
            </div>
        </nav>
    );
}
