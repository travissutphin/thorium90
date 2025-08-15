import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Home, Menu, X, ChevronDown } from 'lucide-react';
import { useState } from 'react';

interface MegaMenuItem {
    label: string;
    href: string;
    external?: boolean;
    children?: Array<{
        label: string;
        href: string;
        external?: boolean;
        description?: string;
    }>;
}

interface MegaMenuNavigationProps {
    page: {
        title: string;
        slug: string;
    };
    showLogo?: boolean;
    navigationItems?: MegaMenuItem[];
    logoText?: string;
    logoSubtext?: string;
}

export function MegaMenuNavigation({
    page,
    showLogo = true,
    navigationItems = [],
    logoText,
    logoSubtext = 'Content Library',
}: MegaMenuNavigationProps) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [activeDropdown, setActiveDropdown] = useState<string | null>(null);

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
                    
                    {/* Desktop Navigation */}
                    {navigationItems.length > 0 && (
                        <div className="hidden lg:flex items-center gap-6">
                            {navigationItems.map((item, index) => (
                                <div
                                    key={index}
                                    className="relative"
                                    onMouseEnter={() => item.children && setActiveDropdown(item.label)}
                                    onMouseLeave={() => setActiveDropdown(null)}
                                >
                                    {item.children ? (
                                        <button className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground transition-colors">
                                            {item.label}
                                            <ChevronDown className="h-3 w-3" />
                                        </button>
                                    ) : (
                                        <a
                                            href={item.href}
                                            target={item.external ? '_blank' : undefined}
                                            rel={item.external ? 'noopener noreferrer' : undefined}
                                            className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            {item.label}
                                        </a>
                                    )}
                                    
                                    {/* Mega Menu Dropdown */}
                                    {item.children && activeDropdown === item.label && (
                                        <div className="absolute top-full left-0 mt-2 w-96 bg-background border rounded-lg shadow-lg p-6">
                                            <div className="grid grid-cols-2 gap-4">
                                                {item.children.map((child, childIndex) => (
                                                    <a
                                                        key={childIndex}
                                                        href={child.href}
                                                        target={child.external ? '_blank' : undefined}
                                                        rel={child.external ? 'noopener noreferrer' : undefined}
                                                        className="block p-3 rounded-md hover:bg-muted transition-colors"
                                                    >
                                                        <div className="font-medium text-sm">{child.label}</div>
                                                        {child.description && (
                                                            <div className="text-xs text-muted-foreground mt-1">
                                                                {child.description}
                                                            </div>
                                                        )}
                                                    </a>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                    
                    {/* Right Side Actions */}
                    <div className="flex items-center gap-4">
                        {/* Page Status Badge */}
                        <Badge variant="outline" className="text-xs hidden md:block">
                            {page.slug}
                        </Badge>
                        
                        {/* Mobile Menu Button */}
                        {navigationItems.length > 0 && (
                            <Button
                                variant="ghost"
                                size="sm"
                                className="lg:hidden"
                                onClick={() => setIsMenuOpen(!isMenuOpen)}
                            >
                                {isMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                            </Button>
                        )}
                    </div>
                </div>
                
                {/* Mobile Menu */}
                {isMenuOpen && navigationItems.length > 0 && (
                    <div className="lg:hidden border-t py-4">
                        <div className="flex flex-col gap-4">
                            {navigationItems.map((item, index) => (
                                <div key={index}>
                                    {item.children ? (
                                        <div>
                                            <div className="font-medium text-sm mb-2">{item.label}</div>
                                            <div className="ml-4 flex flex-col gap-2">
                                                {item.children.map((child, childIndex) => (
                                                    <a
                                                        key={childIndex}
                                                        href={child.href}
                                                        target={child.external ? '_blank' : undefined}
                                                        rel={child.external ? 'noopener noreferrer' : undefined}
                                                        className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                                        onClick={() => setIsMenuOpen(false)}
                                                    >
                                                        {child.label}
                                                    </a>
                                                ))}
                                            </div>
                                        </div>
                                    ) : (
                                        <a
                                            href={item.href}
                                            target={item.external ? '_blank' : undefined}
                                            rel={item.external ? 'noopener noreferrer' : undefined}
                                            className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                            onClick={() => setIsMenuOpen(false)}
                                        >
                                            {item.label}
                                        </a>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </nav>
    );
}
