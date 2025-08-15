import { Button } from '@/components/ui/button';
import { Share2, ArrowUp, Heart } from 'lucide-react';
import { useState } from 'react';

interface DefaultFooterProps {
    page: {
        title: string;
        slug: string;
    };
    showShare?: boolean;
    showBackToTop?: boolean;
    showFooterContent?: boolean;
    footerDescription?: string;
    aboutText?: string;
    links?: Array<{
        label: string;
        href: string;
        external?: boolean;
    }>;
}

export function DefaultFooter({
    page,
    showShare = true,
    showBackToTop = true,
    showFooterContent = true,
    footerDescription = 'Thank you for reading this page. We hope you found it helpful and informative.',
    aboutText = 'This page is part of our comprehensive content library designed to provide valuable information and insights to our readers.',
    links = [
        { label: 'Privacy Policy', href: '#' },
        { label: 'Terms of Service', href: '#' },
        { label: 'Contact', href: '#' },
    ],
}: DefaultFooterProps) {
    const [isShared, setIsShared] = useState(false);

    const handleShare = async () => {
        const shareUrl = `${window.location.origin}/pages/${page.slug}`;
        
        if (navigator.share) {
            try {
                await navigator.share({
                    title: page.title,
                    text: `Check out this page: ${page.title}`,
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

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <footer className="mt-16 border-t bg-muted/50">
            <div className="container mx-auto px-4 py-8">
                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    {/* Page Info */}
                    <div>
                        <h3 className="font-semibold mb-3">{page.title}</h3>
                        <p className="text-sm text-muted-foreground">
                            {footerDescription}
                        </p>
                    </div>

                    {/* Quick Actions */}
                    <div>
                        <h3 className="font-semibold mb-3">Quick Actions</h3>
                        <div className="space-y-2">
                            {showShare && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={handleShare}
                                    className="w-full justify-start text-muted-foreground hover:text-foreground"
                                >
                                    <Share2 className="h-4 w-4 mr-2" />
                                    {isShared ? 'Copied!' : 'Share this page'}
                                </Button>
                            )}
                            
                            {showBackToTop && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={scrollToTop}
                                    className="w-full justify-start text-muted-foreground hover:text-foreground"
                                >
                                    <ArrowUp className="h-4 w-4 mr-2" />
                                    Back to Top
                                </Button>
                            )}
                        </div>
                    </div>

                    {/* Footer Content */}
                    {showFooterContent && (
                        <div>
                            <h3 className="font-semibold mb-3">About</h3>
                            <p className="text-sm text-muted-foreground">
                                {aboutText}
                            </p>
                        </div>
                    )}
                </div>

                {/* Bottom Bar */}
                <div className="mt-8 pt-6 border-t border-border/50">
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <div>
                            © {new Date().getFullYear()} {page.title}. Made with{' '}
                            <Heart className="inline h-3 w-3 text-red-500" /> for our readers.
                        </div>
                        
                        <div className="flex items-center gap-4">
                            {links.map((link, index) => (
                                <a
                                    key={index}
                                    href={link.href}
                                    target={link.external ? '_blank' : undefined}
                                    rel={link.external ? 'noopener noreferrer' : undefined}
                                    className="hover:text-foreground transition-colors"
                                >
                                    {link.label}
                                </a>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    );
}
