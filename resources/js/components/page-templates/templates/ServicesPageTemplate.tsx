import { BasePageTemplate } from '../BasePageTemplate';
import { PageContent } from '../PageContent';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowRight, CheckCircle, Star, Clock, Users } from 'lucide-react';

interface Service {
    id: number;
    title: string;
    description: string;
    features: string[];
    price?: string;
    duration?: string;
    is_popular?: boolean;
}

interface ServicesPageTemplateProps {
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
    services?: Service[];
}

export function ServicesPageTemplate({ 
    page, 
    schemaData, 
    services = []
}: ServicesPageTemplateProps) {
    const defaultServices: Service[] = [
        {
            id: 1,
            title: 'Basic Service',
            description: 'Essential features for getting started',
            features: ['Feature 1', 'Feature 2', 'Feature 3'],
            price: '$99',
            duration: '1 month',
        },
        {
            id: 2,
            title: 'Professional Service',
            description: 'Advanced features for growing businesses',
            features: ['All Basic features', 'Feature 4', 'Feature 5', 'Priority Support'],
            price: '$199',
            duration: '3 months',
            is_popular: true,
        },
        {
            id: 3,
            title: 'Enterprise Service',
            description: 'Complete solution for large organizations',
            features: ['All Professional features', 'Feature 6', 'Feature 7', 'Custom Integration', '24/7 Support'],
            price: '$499',
            duration: '12 months',
        },
    ];

    const displayServices = services.length > 0 ? services : defaultServices;

    return (
        <BasePageTemplate
            page={page}
            schemaData={schemaData}
            template="hero"
            showSidebar={false}
            headerProps={{
                heroHeight: 'lg',
                titleSize: '3xl',
                showMeta: false,
                showActions: true,
            }}
            footerProps={{
                template: 'default',
                showFooterContent: true,
            }}
            navigationProps={{
                template: 'default',
                navigationItems: [
                    { label: 'Home', href: '/pages/home' },
                    { label: 'About', href: '/pages/about' },
                    { label: 'Contact', href: '/pages/contact' },
                ],
            }}
        >
            {/* Services Overview */}
            <div className="text-center mb-16">
                <h2 className="text-2xl font-semibold text-muted-foreground mb-4">
                    Our Comprehensive Services
                </h2>
                <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
                    We offer a range of services designed to meet your needs and help you achieve your goals.
                </p>
            </div>

            {/* Services Grid */}
            <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-3 mb-16">
                {displayServices.map((service) => (
                    <Card 
                        key={service.id} 
                        className={`relative hover:shadow-lg transition-all duration-300 ${
                            service.is_popular ? 'ring-2 ring-primary' : ''
                        }`}
                    >
                        {service.is_popular && (
                            <div className="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                <Badge className="bg-primary text-primary-foreground">
                                    <Star className="h-3 w-3 mr-1" />
                                    Most Popular
                                </Badge>
                            </div>
                        )}
                        
                        <CardHeader className="text-center">
                            <CardTitle className="text-xl">{service.title}</CardTitle>
                            <CardDescription className="text-base">
                                {service.description}
                            </CardDescription>
                        </CardHeader>
                        
                        <CardContent className="space-y-6">
                            {/* Pricing */}
                            {service.price && (
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-primary">
                                        {service.price}
                                    </div>
                                    {service.duration && (
                                        <div className="text-sm text-muted-foreground">
                                            per {service.duration}
                                        </div>
                                    )}
                                </div>
                            )}
                            
                            {/* Features */}
                            <div className="space-y-3">
                                <h4 className="font-semibold text-sm text-muted-foreground uppercase tracking-wide">
                                    What's Included
                                </h4>
                                <ul className="space-y-2">
                                    {service.features.map((feature, index) => (
                                        <li key={index} className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-green-500 flex-shrink-0" />
                                            <span>{feature}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            
                            {/* CTA Button */}
                            <Button className="w-full" variant={service.is_popular ? 'default' : 'outline'}>
                                Get Started
                                <ArrowRight className="h-4 w-4 ml-2" />
                            </Button>
                        </CardContent>
                    </Card>
                ))}
            </div>

            {/* Why Choose Us Section */}
            <div className="bg-muted/50 rounded-lg p-8 mb-16">
                <h3 className="text-2xl font-bold text-center mb-8">Why Choose Our Services?</h3>
                <div className="grid gap-6 md:grid-cols-3">
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Users className="h-12 w-12 text-blue-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Expert Team</h4>
                        <p className="text-sm text-muted-foreground">
                            Our experienced professionals are dedicated to delivering exceptional results.
                        </p>
                    </div>
                    
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Clock className="h-12 w-12 text-green-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Fast Delivery</h4>
                        <p className="text-sm text-muted-foreground">
                            We understand the importance of time and deliver projects on schedule.
                        </p>
                    </div>
                    
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Star className="h-12 w-12 text-yellow-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Quality Guaranteed</h4>
                        <p className="text-sm text-muted-foreground">
                            We maintain the highest standards of quality in everything we do.
                        </p>
                    </div>
                </div>
            </div>

            {/* Main Content */}
            <PageContent
                page={page}
                template="default"
                showMeta={false}
                showActions={false}
                contentClassName="mb-16"
            />

            {/* Call to Action */}
            <div className="text-center bg-primary text-primary-foreground rounded-lg p-8">
                <h3 className="text-2xl font-bold mb-4">Ready to Get Started?</h3>
                <p className="text-primary-foreground/80 mb-6 max-w-2xl mx-auto">
                    Contact us today to discuss your needs and get a personalized quote for our services.
                </p>
                <div className="flex items-center justify-center gap-4">
                    <Button size="lg" variant="secondary" asChild>
                        <a href="/pages/contact">
                            Contact Us
                            <ArrowRight className="h-4 w-4 ml-2" />
                        </a>
                    </Button>
                    <Button size="lg" variant="outline" className="border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary">
                        Request Quote
                    </Button>
                </div>
            </div>
        </BasePageTemplate>
    );
}
