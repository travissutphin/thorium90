import { BasePageTemplate } from '../BasePageTemplate';
import { PageContent } from '../PageContent';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Mail, Phone, MapPin, Clock, Send, MessageSquare } from 'lucide-react';
import { useState } from 'react';

interface ContactPageTemplateProps {
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
    contactInfo?: {
        email: string;
        phone: string;
        address: string;
        hours: string;
    };
}

export function ContactPageTemplate({ 
    page, 
    schemaData, 
    contactInfo
}: ContactPageTemplateProps) {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        subject: '',
        message: '',
    });
    const [isSubmitting, setIsSubmitting] = useState(false);

    const defaultContactInfo = {
        email: 'contact@example.com',
        phone: '+1 (555) 123-4567',
        address: '123 Business Street, City, State 12345',
        hours: 'Monday - Friday: 9:00 AM - 6:00 PM',
    };

    const displayContactInfo = contactInfo || defaultContactInfo;

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSubmitting(true);
        
        // Simulate form submission
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        // Here you would typically send the form data to your backend
        console.log('Form submitted:', formData);
        
        // Reset form
        setFormData({
            name: '',
            email: '',
            subject: '',
            message: '',
        });
        
        setIsSubmitting(false);
        alert('Thank you for your message! We\'ll get back to you soon.');
    };

    return (
        <BasePageTemplate
            page={page}
            schemaData={schemaData}
            template="hero"
            showSidebar={false}
            headerProps={{
                heroHeight: 'md',
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
                    { label: 'Services', href: '/pages/services' },
                ],
            }}
        >
            {/* Contact Overview */}
            <div className="text-center mb-16">
                <h2 className="text-2xl font-semibold text-muted-foreground mb-4">
                    Get in Touch
                </h2>
                <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                    We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                </p>
            </div>

            <div className="grid gap-8 lg:grid-cols-2 mb-16">
                {/* Contact Form */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <MessageSquare className="h-5 w-5" />
                            Send us a Message
                        </CardTitle>
                        <CardDescription>
                            Fill out the form below and we'll get back to you within 24 hours.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Name *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        value={formData.name}
                                        onChange={handleInputChange}
                                        required
                                        placeholder="Your full name"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email *</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value={formData.email}
                                        onChange={handleInputChange}
                                        required
                                        placeholder="your.email@example.com"
                                    />
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <Label htmlFor="subject">Subject *</Label>
                                <Input
                                    id="subject"
                                    name="subject"
                                    value={formData.subject}
                                    onChange={handleInputChange}
                                    required
                                    placeholder="What is this about?"
                                />
                            </div>
                            
                            <div className="space-y-2">
                                <Label htmlFor="message">Message *</Label>
                                <Textarea
                                    id="message"
                                    name="message"
                                    value={formData.message}
                                    onChange={handleInputChange}
                                    required
                                    placeholder="Tell us more about your inquiry..."
                                    rows={5}
                                />
                            </div>
                            
                            <Button 
                                type="submit" 
                                className="w-full" 
                                disabled={isSubmitting}
                            >
                                {isSubmitting ? (
                                    <>
                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
                                        Sending...
                                    </>
                                ) : (
                                    <>
                                        <Send className="h-4 w-4 mr-2" />
                                        Send Message
                                    </>
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Contact Information */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Information</CardTitle>
                            <CardDescription>
                                Here's how you can reach us directly
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-start gap-3">
                                <Mail className="h-5 w-5 text-blue-500 mt-0.5" />
                                <div>
                                    <p className="font-medium">Email</p>
                                    <a 
                                        href={`mailto:${displayContactInfo.email}`}
                                        className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    >
                                        {displayContactInfo.email}
                                    </a>
                                </div>
                            </div>
                            
                            <div className="flex items-start gap-3">
                                <Phone className="h-5 w-5 text-green-500 mt-0.5" />
                                <div>
                                    <p className="font-medium">Phone</p>
                                    <a 
                                        href={`tel:${displayContactInfo.phone}`}
                                        className="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                                    >
                                        {displayContactInfo.phone}
                                    </a>
                                </div>
                            </div>
                            
                            <div className="flex items-start gap-3">
                                <MapPin className="h-5 w-5 text-red-500 mt-0.5" />
                                <div>
                                    <p className="font-medium">Address</p>
                                    <p className="text-sm text-muted-foreground">
                                        {displayContactInfo.address}
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex items-start gap-3">
                                <Clock className="h-5 w-5 text-purple-500 mt-0.5" />
                                <div>
                                    <p className="font-medium">Business Hours</p>
                                    <p className="text-sm text-muted-foreground">
                                        {displayContactInfo.hours}
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Response */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Response</CardTitle>
                            <CardDescription>
                                We typically respond to inquiries within 24 hours
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Response Time</span>
                                    <Badge variant="secondary">24 hours</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Support Hours</span>
                                    <Badge variant="outline">9 AM - 6 PM</Badge>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-sm">Priority Support</span>
                                    <Badge variant="default">Available</Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
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

            {/* FAQ Section */}
            <Card className="mb-16">
                <CardHeader>
                    <CardTitle>Frequently Asked Questions</CardTitle>
                    <CardDescription>
                        Find quick answers to common questions
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="border-b pb-4">
                        <h4 className="font-semibold mb-2">What are your business hours?</h4>
                        <p className="text-sm text-muted-foreground">
                            We're open Monday through Friday from 9:00 AM to 6:00 PM EST.
                        </p>
                    </div>
                    
                    <div className="border-b pb-4">
                        <h4 className="font-semibold mb-2">How quickly do you respond to inquiries?</h4>
                        <p className="text-sm text-muted-foreground">
                            We typically respond to all inquiries within 24 hours during business days.
                        </p>
                    </div>
                    
                    <div className="border-b pb-4">
                        <h4 className="font-semibold mb-2">Do you offer emergency support?</h4>
                        <p className="text-sm text-muted-foreground">
                            Yes, we offer priority support for urgent matters. Contact us directly by phone for immediate assistance.
                        </p>
                    </div>
                    
                    <div>
                        <h4 className="font-semibold mb-2">Can I schedule a consultation?</h4>
                        <p className="text-sm text-muted-foreground">
                            Absolutely! We offer free consultations. Use the contact form above or call us to schedule a time that works for you.
                        </p>
                    </div>
                </CardContent>
            </Card>

            {/* Call to Action */}
            <div className="text-center bg-muted/50 rounded-lg p-8">
                <h3 className="text-2xl font-bold mb-4">Still Have Questions?</h3>
                <p className="text-muted-foreground mb-6 max-w-2xl mx-auto">
                    If you couldn't find what you're looking for, don't hesitate to reach out. 
                    Our team is here to help you succeed.
                </p>
                <Button size="lg" asChild>
                    <a href={`mailto:${displayContactInfo.email}`}>
                        <Mail className="h-4 w-4 mr-2" />
                        Send us an Email
                    </a>
                </Button>
            </div>
        </BasePageTemplate>
    );
}
