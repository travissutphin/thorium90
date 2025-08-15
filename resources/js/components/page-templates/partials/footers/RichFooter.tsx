import { Button } from '@/components/ui/button';
import { Share2, ArrowUp, Heart, Mail, Phone, MapPin } from 'lucide-react';
import { useState } from 'react';

interface FooterSection {
    title: string;
    links: Array<{
        label: string;
        href: string;
        external?: boolean;
    }>;
}

interface ContactInfo {
    email?: string;
    phone?: string;
    address?: string;
}

interface RichFooterProps {
    page: {
        title: string;
        slug: string;
    };
    showShare?: boolean;
    showBackToTop?: boolean;
    companyName?: string;
    description?: string;
    sections?: FooterSection[];
    contactInfo?: ContactInfo;
    socialLinks?: Array<{
        label: string;
        href: string;
        icon?: React.ReactNode;
    }>;
    bottomLinks?: Array<{
        label: string;
        href: string;
        external?: boolean;
    }>;
}

export function RichFooter({
    page,
    showShare = true,
    showBackToTop = true,
    companyName,
    description = 'We provide valuable content and insights to help you succeed.',
    sections = [],
    contactInfo = {},
    socialLinks = [],
    bottomLinks = [
        { label: 'Privacy Policy', href: '#' },
        { label: 'Terms of Service', href: '#' },
        { label: 'Cookie Policy', href: '#' },
        { label: 'Contact', href: '#' },
    ],
}: RichFooterProps) {
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
            <div className="container mx-auto px-4 py-12">
                <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {/* Company Info */}
                    <div className="lg:col-span-1">
                        <h3 className="font-semibold text-lg mb-4">{companyName || page.title}</h3>
                        <p className="text-sm text-muted-foreground mb-6">
                            {description}
                        </p>
                        
                        {/* Contact Info */}
                        {(contactInfo.email || contactInfo.phone || contactInfo.address) && (
                            <div className="space-y-2">
                                {contactInfo.email && (
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Mail className="h-4 w-4" />
                                        <a href={`mailto:${contactInfo.email}`} className="hover:text-foreground transition-colors">
                                            {contactInfo.email}
                                        </a>
                                    </div>
                                )}
                                {contactInfo.phone && (
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Phone className="h-4 w-4" />
                                        <a href={`tel:${contactInfo.phone}`} className="hover:text-foreground transition-colors">
                                            {contactInfo.phone}
                                        </a>
                                    </div>
                                )}
                                {contactInfo.address && (
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <MapPin className="h-4 w-4" />
                                        <span>{contactInfo.address}</span>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Footer Sections */}
                    {sections.map((section, index) => (
                        <div key={index}>
                            <h3 className="font-semibold mb-4">{section.title}</h3>
                            <ul className="space-y-2">
                                {section.links.map((link, linkIndex) => (
                                    <li key={linkIndex}>
                                        <a
                                            href={link.href}
                                            target={link.external ? '_blank' : undefined}
                                            rel={link.external ? 'noopener noreferrer' : undefined}
                                            className="text-sm text-muted-foreground hover:text-foreground transition-colors"
                                        >
                                            {link.label}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}

                    {/* Quick Actions */}
                    <div>
                        <h3 className="font-semibold mb-4">Quick Actions</h3>
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

                        {/* Social Links */}
                        {socialLinks.length > 0 && (
                            <div className="mt-6">
                                <h4 className="font-medium text-sm mb-3">Follow Us</h4>
                                <div className="flex gap-2">
                                    {socialLinks.map((social, index) => (
                                        <Button
                                            key={index}
                                            variant="outline"
                                            size="sm"
                                            asChild
                                        >
                                            <a
                                                href={social.href}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-muted-foreground hover:text-foreground"
                                            >
                                                {social.icon || social.label}
                                            </a>
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* Bottom Bar */}
                <div className="mt-12 pt-8 border-t border-border/50">
                    <div className="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-muted-foreground">
                        <div>
                            © {new Date().getFullYear()} {companyName || page.title}. Made with{' '}
                            <Heart className="inline h-3 w-3 text-red-500" /> for our community.
                        </div>
                        
                        <div className="flex items-center gap-4">
                            {bottomLinks.map((link, index) => (
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
