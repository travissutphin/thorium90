import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Home, Menu, X, ArrowLeft } from 'lucide-react';
import { useState } from 'react';

interface MainNavigationProps {
    page: {
        title: string;
        slug: string;
    };
    showLogo?: boolean;
    showBackButton?: boolean;
    navigationItems?: Array<{
        label: string;
        href: string;
        external?: boolean;
    }>;
    logoText?: string;
    logoSubtext?: string;
}

export function MainNavigation({
    page,
    showLogo = true,
    showBackButton = false,
    navigationItems = [],
    logoText,
    logoSubtext = 'Content Library',
}: MainNavigationProps) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <nav className="sticky top-0 z-50 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 border-b">
            <div className="container mx-auto px-4">
                <div className="flex items-center justify-between h-16">
                    {/* Logo and Title */}
                    {showLogo && (
                        <div className="flex items-center gap-3">
                            <Home className="h-6 w-6 text-primary" />
                            <div className="flex flex-col">
                                <span className="font-semibold text-lg">{logoText || page.title}</span>
                                <span className="text-xs text-muted-foreground">{logoSubtext}</span>
                            </div>
                        </div>
                    )}
                    
                    {/* Navigation Items */}
                    {navigationItems.length > 0 && (
                        <div className="hidden md:flex items-center gap-6">
                            {navigationItems.map((item, index) => (
                                <a
                                    key={index}
                                    href={item.href}
                                    target={item.external ? '_blank' : undefined}
                                    rel={item.external ? 'noopener noreferrer' : undefined}
                                    className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                >
                                    {item.label}
                                </a>
                            ))}
                        </div>
                    )}
                    
                    {/* Right Side Actions */}
                    <div className="flex items-center gap-4">
                        {showBackButton && (
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => window.history.back()}
                                className="hidden md:flex"
                            >
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Back
                            </Button>
                        )}
                        
                        {/* Page Status Badge */}
                        <Badge variant="outline" className="text-xs">
                            {page.slug}
                        </Badge>
                        
                        {/* Mobile Menu Button */}
                        {navigationItems.length > 0 && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="md:hidden"
                                onClick={() => setIsMenuOpen(!isMenuOpen)}
                            >
                                {isMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                            </Button>
                        )}
                    </div>
                </div>
                
                {/* Mobile Menu */}
                {isMenuOpen && navigationItems.length > 0 && (
                    <div className="md:hidden border-t py-4">
                        <div className="flex flex-col gap-4">
                            {navigationItems.map((item, index) => (
                                <a
                                    key={index}
                                    href={item.href}
                                    target={item.external ? '_blank' : undefined}
                                    rel={item.external ? 'noopener noreferrer' : undefined}
                                    className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                    onClick={() => setIsMenuOpen(false)}
                                >
                                    {item.label}
                                </a>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </nav>
    );
}
