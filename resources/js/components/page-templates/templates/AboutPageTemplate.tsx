import { BasePageTemplate } from '../BasePageTemplate';
import { PageContent } from '../PageContent';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { ArrowRight, Users, Target, Award, TrendingUp, Heart, Lightbulb, Shield } from 'lucide-react';

interface TeamMember {
    id: number;
    name: string;
    role: string;
    bio: string;
    avatar?: string;
    email?: string;
    linkedin?: string;
}

interface AboutPageTemplateProps {
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
    teamMembers?: TeamMember[];
    companyStats?: {
        founded: string;
        employees: number;
        clients: number;
        projects: number;
    };
}

export function AboutPageTemplate({ 
    page, 
    schemaData, 
    teamMembers = [],
    companyStats
}: AboutPageTemplateProps) {
    const defaultTeamMembers: TeamMember[] = [
        {
            id: 1,
            name: 'John Doe',
            role: 'CEO & Founder',
            bio: 'Passionate leader with 15+ years of experience in the industry.',
            email: 'john@example.com',
            linkedin: 'https://linkedin.com/in/johndoe',
        },
        {
            id: 2,
            name: 'Jane Smith',
            role: 'CTO',
            bio: 'Technology expert driving innovation and digital transformation.',
            email: 'jane@example.com',
            linkedin: 'https://linkedin.com/in/janesmith',
        },
        {
            id: 3,
            name: 'Mike Johnson',
            role: 'Head of Operations',
            bio: 'Operations specialist ensuring smooth business processes.',
            email: 'mike@example.com',
            linkedin: 'https://linkedin.com/in/mikejohnson',
        },
    ];

    const defaultCompanyStats = {
        founded: '2010',
        employees: 25,
        clients: 150,
        projects: 500,
    };

    const displayTeamMembers = teamMembers.length > 0 ? teamMembers : defaultTeamMembers;
    const displayCompanyStats = companyStats || defaultCompanyStats;

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
                    { label: 'Services', href: '/pages/services' },
                    { label: 'Contact', href: '/pages/contact' },
                ],
            }}
        >
            {/* Company Overview */}
            <div className="text-center mb-16">
                <h2 className="text-2xl font-semibold text-muted-foreground mb-4">
                    Our Story
                </h2>
                <p className="text-lg text-muted-foreground max-w-3xl mx-auto">
                    We're a passionate team dedicated to delivering exceptional value and innovative solutions 
                    to our clients. Our journey began with a simple mission: to make a difference.
                </p>
            </div>

            {/* Company Stats */}
            <div className="grid gap-6 md:grid-cols-4 mb-16">
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <Award className="h-8 w-8 text-blue-500" />
                        </div>
                        <div className="text-2xl font-bold">{displayCompanyStats.founded}</div>
                        <p className="text-sm text-muted-foreground">Founded</p>
                    </CardContent>
                </Card>
                
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <Users className="h-8 w-8 text-green-500" />
                        </div>
                        <div className="text-2xl font-bold">{displayCompanyStats.employees}</div>
                        <p className="text-sm text-muted-foreground">Team Members</p>
                    </CardContent>
                </Card>
                
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <Target className="h-8 w-8 text-purple-500" />
                        </div>
                        <div className="text-2xl font-bold">{displayCompanyStats.clients}</div>
                        <p className="text-sm text-muted-foreground">Happy Clients</p>
                    </CardContent>
                </Card>
                
                <Card className="text-center">
                    <CardContent className="pt-6">
                        <div className="flex items-center justify-center mb-2">
                            <TrendingUp className="h-8 w-8 text-orange-500" />
                        </div>
                        <div className="text-2xl font-bold">{displayCompanyStats.projects}</div>
                        <p className="text-sm text-muted-foreground">Projects Completed</p>
                    </CardContent>
                </Card>
            </div>

            {/* Mission & Values */}
            <div className="grid gap-8 md:grid-cols-2 mb-16">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Target className="h-5 w-5 text-blue-500" />
                            Our Mission
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-muted-foreground mb-4">
                            To empower businesses with innovative solutions that drive growth, 
                            efficiency, and success in an ever-evolving digital landscape.
                        </p>
                        <p className="text-muted-foreground">
                            We believe in building lasting partnerships and delivering measurable 
                            results that exceed expectations.
                        </p>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Heart className="h-5 w-5 text-red-500" />
                            Our Values
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        <div className="flex items-center gap-2">
                            <Lightbulb className="h-4 w-4 text-yellow-500" />
                            <span className="text-sm">Innovation & Creativity</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <Shield className="h-4 w-4 text-green-500" />
                            <span className="text-sm">Integrity & Trust</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <Users className="h-4 w-4 text-blue-500" />
                            <span className="text-sm">Collaboration & Teamwork</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <Award className="h-4 w-4 text-purple-500" />
                            <span className="text-sm">Excellence & Quality</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Team Section */}
            <div className="mb-16">
                <h3 className="text-2xl font-bold text-center mb-8">Meet Our Team</h3>
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {displayTeamMembers.map((member) => (
                        <Card key={member.id} className="text-center hover:shadow-lg transition-shadow">
                            <CardContent className="pt-6">
                                <Avatar className="w-20 h-20 mx-auto mb-4">
                                    <AvatarImage src={member.avatar} alt={member.name} />
                                    <AvatarFallback className="text-lg">
                                        {member.name.split(' ').map(n => n[0]).join('')}
                                    </AvatarFallback>
                                </Avatar>
                                
                                <h4 className="font-semibold text-lg mb-1">{member.name}</h4>
                                <p className="text-sm text-muted-foreground mb-3">{member.role}</p>
                                
                                <p className="text-sm text-muted-foreground mb-4 line-clamp-3">
                                    {member.bio}
                                </p>
                                
                                <div className="flex items-center justify-center gap-2">
                                    {member.email && (
                                        <Button variant="ghost" size="sm" asChild>
                                            <a href={`mailto:${member.email}`}>
                                                <span className="sr-only">Email {member.name}</span>
                                                ✉️
                                            </a>
                                        </Button>
                                    )}
                                    {member.linkedin && (
                                        <Button variant="ghost" size="sm" asChild>
                                            <a href={member.linkedin} target="_blank" rel="noopener noreferrer">
                                                <span className="sr-only">LinkedIn {member.name}</span>
                                                💼
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
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

            {/* Why Choose Us */}
            <div className="bg-muted/50 rounded-lg p-8 mb-16">
                <h3 className="text-2xl font-bold text-center mb-8">Why Choose Us?</h3>
                <div className="grid gap-6 md:grid-cols-3">
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Lightbulb className="h-12 w-12 text-yellow-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Innovation</h4>
                        <p className="text-sm text-muted-foreground">
                            We stay ahead of the curve with cutting-edge solutions and creative approaches.
                        </p>
                    </div>
                    
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Shield className="h-12 w-12 text-green-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Reliability</h4>
                        <p className="text-sm text-muted-foreground">
                            You can count on us to deliver consistent, high-quality results every time.
                        </p>
                    </div>
                    
                    <div className="text-center">
                        <div className="flex items-center justify-center mb-4">
                            <Heart className="h-12 w-12 text-red-500" />
                        </div>
                        <h4 className="font-semibold mb-2">Passion</h4>
                        <p className="text-sm text-muted-foreground">
                            We're genuinely passionate about helping our clients succeed and grow.
                        </p>
                    </div>
                </div>
            </div>

            {/* Call to Action */}
            <div className="text-center bg-primary text-primary-foreground rounded-lg p-8">
                <h3 className="text-2xl font-bold mb-4">Ready to Work Together?</h3>
                <p className="text-primary-foreground/80 mb-6 max-w-2xl mx-auto">
                    Let's discuss how we can help you achieve your goals and take your business to the next level.
                </p>
                <div className="flex items-center justify-center gap-4">
                    <Button size="lg" variant="secondary" asChild>
                        <a href="/pages/contact">
                            Get in Touch
                            <ArrowRight className="h-4 w-4 ml-2" />
                        </a>
                    </Button>
                    <Button size="lg" variant="outline" className="border-primary-foreground text-primary-foreground hover:bg-primary-foreground hover:text-primary">
                        View Our Services
                    </Button>
                </div>
            </div>
        </BasePageTemplate>
    );
}
