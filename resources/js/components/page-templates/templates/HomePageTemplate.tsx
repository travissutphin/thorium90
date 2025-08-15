import { BasePageTemplate } from '../BasePageTemplate';
import { PageContent } from '../PageContent';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowRight, Star, TrendingUp, Users, Clock } from 'lucide-react';

interface HomePageTemplateProps {
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
        meta_title?: string;
        meta_description?: string;
        meta_keywords?: string;
        schema_type?: string;
    };
    schemaData?: Record<string, unknown>;
    featuredPages?: Array<{
        id: number;
        title: string;
        excerpt?: string;
        slug: string;
        reading_time: number;
        is_featured: boolean;
    }>;
    stats?: {
        totalPages: number;
        totalUsers: number;
        totalViews: number;
    };
}

export function HomePageTemplate({ 
    page, 
    schemaData, 
    featuredPages = [],
    stats = { totalPages: 0, totalUsers: 0, totalViews: 0 }
}: HomePageTemplateProps) {
    return (
        <BasePageTemplate
            page={page}
            schemaData={schemaData}
            template="hero"
            showSidebar={false}
            headerProps={{
                heroHeight: 'xl',
                titleSize: '4xl',
                showMeta: false,
                showActions: true,
            }}
            footerProps={{
                template: 'landing',
                showFooterContent: false,
            }}
            navigationProps={{
                template: 'landing',
                navigationItems: [
                    { label: 'About', href: '/pages/about' },
                    { label: 'Services', href: '/pages/services' },
                    { label: 'Contact', href: '/pages/contact' },
                ],
            }}
        >
            {/* Hero Section Content */}
            <div className="text-center mb-16">
                <h2 className="text-2xl font-semibold text-muted-foreground mb-4">
                    Welcome to Our Platform
                </h2>
                <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                    Discover amazing content, insights, and resources designed to help you succeed.
                </p>
            </div>

            {/* Stats Section */}
            <div className="grid gap-6 md:grid-cols-3 mb-16">
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <TrendingUp className="h-8 w-8 text-blue-500" />
                        </div>
                        <div className="text-2xl font-bold">{stats.totalPages}</div>
                        <p className="text-sm text-muted-foreground">Total Pages</p>
                    </CardContent>
                </Card>
                
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <Users className="h-8 w-8 text-green-500" />
                        </div>
                        <div className="text-2xl font-bold">{stats.totalUsers}</div>
                        <p className="text-sm text-muted-foreground">Active Users</p>
                    </CardContent>
                </Card>
                
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <Clock className="h-8 w-8 text-purple-500" />
                        </div>
                        <div className="text-2xl font-bold">{stats.totalViews}</div>
                        <p className="text-sm text-muted-foreground">Total Views</p>
                    </CardContent>
                </Card>
            </div>

            {/* Featured Content */}
            {featuredPages.length > 0 && (
                <div className="mb-16">
                    <h3 className="text-2xl font-bold mb-6">Featured Content</h3>
                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {featuredPages.map((featuredPage) => (
                            <Card key={featuredPage.id} className="hover:shadow-lg transition-shadow">
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-lg line-clamp-2">
                                            {featuredPage.title}
                                        </CardTitle>
                                        {featuredPage.is_featured && (
                                            <Star className="h-5 w-5 text-yellow-500" />
                                        )}
                                    </div>
                                    {featuredPage.excerpt && (
                                        <CardDescription className="line-clamp-3">
                                            {featuredPage.excerpt}
                                        </CardDescription>
                                    )}
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Clock className="h-4 w-4" />
                                            <span>{featuredPage.reading_time} min read</span>
                                        </div>
                                        <Button variant="ghost" size="sm" asChild>
                                            <a href={`/pages/${featuredPage.slug}`}>
                                                Read More
                                                <ArrowRight className="h-4 w-4 ml-2" />
                                            </a>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            )}

            {/* Main Content */}
            <PageContent
                page={page}
                template="landing"
                showMeta={false}
                showActions={false}
                contentClassName="mb-16"
            />

            {/* Call to Action */}
            <div className="text-center bg-muted/50 rounded-lg p-8">
                <h3 className="text-2xl font-bold mb-4">Ready to Get Started?</h3>
                <p className="text-muted-foreground mb-6 max-w-2xl mx-auto">
                    Explore our content library and discover valuable insights that can help you achieve your goals.
                </p>
                <div className="flex items-center justify-center gap-4">
                    <Button size="lg" asChild>
                        <a href="/pages/services">
                            Explore Services
                            <ArrowRight className="h-4 w-4 ml-2" />
                        </a>
                    </Button>
                    <Button variant="outline" size="lg" asChild>
                        <a href="/pages/contact">Contact Us</a>
                    </Button>
                </div>
            </div>
        </BasePageTemplate>
    );
}
