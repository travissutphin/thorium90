<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\User;
use Carbon\Carbon;

class Thorium90DefaultPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $pages = [
            // Core Business Pages
            [
                'title' => 'About Thorium90',
                'slug' => 'about',
                'content' => $this->getAboutContent(),
                'excerpt' => 'Learn about Thorium90\'s mission to revolutionize content management with AI-driven solutions.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'About Thorium90 - AI-Driven Content Management',
                'meta_description' => 'Discover how Thorium90 combines artificial intelligence with human expertise to deliver powerful content management solutions.',
                'meta_keywords' => 'about thorium90, AI content management, company information',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Company', 'About'],
                'keywords' => ['AI', 'content management', 'innovation'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Features & Capabilities',
                'slug' => 'features',
                'content' => $this->getFeaturesContent(),
                'excerpt' => 'Explore Thorium90\'s comprehensive features designed to streamline your content management workflow.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'Features - Thorium90 Content Management Platform',
                'meta_description' => 'Discover powerful features including AEO optimization, schema validation, and AI-driven content tools.',
                'meta_keywords' => 'thorium90 features, CMS features, AEO optimization',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Features', 'Product'],
                'keywords' => ['features', 'AEO', 'schema validation', 'content tools'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Pricing Plans',
                'slug' => 'pricing',
                'content' => $this->getPricingContent(),
                'excerpt' => 'Choose the perfect Thorium90 plan for your content management needs with transparent pricing.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'Pricing Plans - Thorium90',
                'meta_description' => 'Explore Thorium90 pricing plans designed for businesses of all sizes. Start with our free trial.',
                'meta_keywords' => 'thorium90 pricing, CMS pricing, content management cost',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Pricing', 'Plans'],
                'keywords' => ['pricing', 'plans', 'subscription', 'free trial'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => $this->getContactContent(),
                'excerpt' => 'Get in touch with the Thorium90 team for support, sales inquiries, or partnership opportunities.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Contact Thorium90 - Get in Touch',
                'meta_description' => 'Contact Thorium90 for support, sales inquiries, or partnership opportunities. Multiple ways to reach us.',
                'meta_keywords' => 'contact thorium90, support, sales inquiry',
                'schema_type' => 'WebPage',
                'layout' => 'sidebar',
                'topics' => ['Contact', 'Support'],
                'keywords' => ['contact', 'support', 'sales'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],

            // Legal & Compliance Pages
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $this->getPrivacyPolicyContent(),
                'excerpt' => 'Learn how Thorium90 protects your privacy and handles your personal information.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Privacy Policy - Thorium90',
                'meta_description' => 'Read Thorium90\'s privacy policy to understand how we collect, use, and protect your personal information.',
                'meta_keywords' => 'privacy policy, data protection, personal information',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Privacy'],
                'keywords' => ['privacy', 'data protection', 'GDPR'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => $this->getTermsOfServiceContent(),
                'excerpt' => 'Review the terms and conditions for using Thorium90\'s content management platform.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Terms of Service - Thorium90',
                'meta_description' => 'Read Thorium90\'s terms of service and user agreement for our content management platform.',
                'meta_keywords' => 'terms of service, user agreement, terms and conditions',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Terms'],
                'keywords' => ['terms', 'agreement', 'conditions'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookie-policy',
                'content' => $this->getCookiePolicyContent(),
                'excerpt' => 'Learn about how Thorium90 uses cookies to improve your browsing experience.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Cookie Policy - Thorium90',
                'meta_description' => 'Understand how Thorium90 uses cookies and how you can manage your cookie preferences.',
                'meta_keywords' => 'cookie policy, cookies, tracking, preferences',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Cookies'],
                'keywords' => ['cookies', 'tracking', 'privacy'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Refund Policy',
                'slug' => 'refund-policy',
                'content' => $this->getRefundPolicyContent(),
                'excerpt' => 'Learn about Thorium90\'s refund policy and how to request a refund if needed.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Refund Policy - Thorium90',
                'meta_description' => 'Review Thorium90\'s refund policy and learn how to request a refund for our services.',
                'meta_keywords' => 'refund policy, money back guarantee, cancellation',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Refunds'],
                'keywords' => ['refund', 'cancellation', 'money back'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],

            // Support & Help Pages
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'content' => $this->getFAQContent(),
                'excerpt' => 'Find answers to common questions about Thorium90\'s content management platform.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'FAQ - Thorium90 Frequently Asked Questions',
                'meta_description' => 'Get answers to frequently asked questions about Thorium90\'s features, pricing, and support.',
                'meta_keywords' => 'FAQ, frequently asked questions, help, support',
                'schema_type' => 'FAQPage',
                'layout' => 'default',
                'topics' => ['Support', 'FAQ'],
                'keywords' => ['FAQ', 'questions', 'help', 'support'],
                'faq_data' => $this->getFAQData(),
                'content_type' => 'support',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Help Center',
                'slug' => 'help-center',
                'content' => $this->getHelpCenterContent(),
                'excerpt' => 'Access comprehensive guides and tutorials to get the most out of Thorium90.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Help Center - Thorium90 Support',
                'meta_description' => 'Access guides, tutorials, and documentation to help you use Thorium90 effectively.',
                'meta_keywords' => 'help center, guides, tutorials, documentation',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Support', 'Help'],
                'keywords' => ['help', 'guides', 'tutorials', 'documentation'],
                'content_type' => 'support',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Documentation',
                'slug' => 'documentation',
                'content' => $this->getDocumentationContent(),
                'excerpt' => 'Technical documentation and developer resources for Thorium90.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Documentation - Thorium90 Developer Resources',
                'meta_description' => 'Access technical documentation, API references, and developer resources for Thorium90.',
                'meta_keywords' => 'documentation, API, developer resources, technical docs',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Documentation', 'Development'],
                'keywords' => ['documentation', 'API', 'developers', 'technical'],
                'content_type' => 'documentation',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Customer Support',
                'slug' => 'support',
                'content' => $this->getSupportContent(),
                'excerpt' => 'Get help from our dedicated customer support team whenever you need assistance.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Customer Support - Thorium90',
                'meta_description' => 'Get help from Thorium90\'s customer support team. Multiple support channels available.',
                'meta_keywords' => 'customer support, help desk, technical support',
                'schema_type' => 'WebPage',
                'layout' => 'sidebar',
                'topics' => ['Support', 'Customer Service'],
                'keywords' => ['support', 'help', 'customer service'],
                'content_type' => 'support',
                'published_at' => Carbon::now(),
            ],

            // Content & Marketing Pages
            [
                'title' => 'Blog',
                'slug' => 'blog',
                'content' => $this->getBlogContent(),
                'excerpt' => 'Stay updated with the latest news, tips, and insights from the Thorium90 team.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'Blog - Thorium90 News and Insights',
                'meta_description' => 'Read the latest blog posts from Thorium90 covering content management, AI, and industry insights.',
                'meta_keywords' => 'thorium90 blog, content management blog, AI insights',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Blog', 'Content'],
                'keywords' => ['blog', 'news', 'insights', 'updates'],
                'content_type' => 'blog',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Case Studies',
                'slug' => 'case-studies',
                'content' => $this->getCaseStudiesContent(),
                'excerpt' => 'Discover how businesses have transformed their content management with Thorium90.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'Case Studies - Thorium90 Success Stories',
                'meta_description' => 'Read customer success stories and case studies showcasing Thorium90\'s impact on businesses.',
                'meta_keywords' => 'case studies, success stories, customer testimonials',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Case Studies', 'Success Stories'],
                'keywords' => ['case studies', 'success', 'testimonials', 'customers'],
                'content_type' => 'marketing',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Resources',
                'slug' => 'resources',
                'content' => $this->getResourcesContent(),
                'excerpt' => 'Access valuable resources including guides, templates, and tools for content management.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Resources - Thorium90 Tools and Guides',
                'meta_description' => 'Access free resources including guides, templates, and tools for effective content management.',
                'meta_keywords' => 'resources, guides, templates, tools, downloads',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Resources', 'Tools'],
                'keywords' => ['resources', 'guides', 'templates', 'tools'],
                'content_type' => 'resource',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'News & Announcements',
                'slug' => 'news',
                'content' => $this->getNewsContent(),
                'excerpt' => 'Stay informed about Thorium90 product updates, new features, and company news.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'News - Thorium90 Updates and Announcements',
                'meta_description' => 'Get the latest news and announcements from Thorium90 including product updates and features.',
                'meta_keywords' => 'thorium90 news, announcements, updates, features',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['News', 'Announcements'],
                'keywords' => ['news', 'announcements', 'updates', 'features'],
                'content_type' => 'news',
                'published_at' => Carbon::now(),
            ],

            // Technical Pages
            [
                'title' => 'Sitemap',
                'slug' => 'sitemap',
                'content' => $this->getSitemapContent(),
                'excerpt' => 'Navigate through all pages and sections of the Thorium90 website.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Sitemap - Thorium90 Website Navigation',
                'meta_description' => 'Browse the complete sitemap of Thorium90 website with links to all pages and sections.',
                'meta_keywords' => 'sitemap, website navigation, site structure',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Navigation', 'Sitemap'],
                'keywords' => ['sitemap', 'navigation', 'website structure'],
                'content_type' => 'utility',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Page Not Found',
                'slug' => '404-error',
                'content' => $this->get404Content(),
                'excerpt' => 'Sorry, the page you are looking for could not be found.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => '404 - Page Not Found | Thorium90',
                'meta_description' => 'The page you are looking for could not be found. Return to Thorium90 homepage or browse our content.',
                'meta_keywords' => '404, page not found, error',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Error', '404'],
                'keywords' => ['404', 'error', 'not found'],
                'content_type' => 'utility',
                'published_at' => Carbon::now(),
            ],
            [
                'title' => 'Coming Soon',
                'slug' => 'coming-soon',
                'content' => $this->getComingSoonContent(),
                'excerpt' => 'Exciting new features and capabilities are coming soon to Thorium90.',
                'status' => 'draft',
                'is_featured' => false,
                'meta_title' => 'Coming Soon - New Features | Thorium90',
                'meta_description' => 'Exciting new features and capabilities are coming soon to Thorium90. Stay tuned for updates.',
                'meta_keywords' => 'coming soon, new features, updates, roadmap',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Coming Soon', 'Features'],
                'keywords' => ['coming soon', 'new features', 'roadmap'],
                'content_type' => 'utility',
                'published_at' => null,
            ],
        ];

        foreach ($pages as $pageData) {
            $pageData['user_id'] = $user->id;
            $pageData['reading_time'] = $this->calculateReadingTime($pageData['content']);
            
            Page::create($pageData);
        }

        $this->command->info('Created ' . count($pages) . ' default pages successfully.');
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200)); // 200 words per minute average
    }

    // Content methods follow...
    private function getAboutContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>About Thorium90</h1>
                
                <div class="prose prose-lg">
                    <p class="lead">At Thorium90, we believe that content management should be both powerful and intuitive. Our mission is to revolutionize how businesses create, manage, and optimize their digital content through the perfect blend of artificial intelligence and human expertise.</p>
                    
                    <h2>Our Story</h2>
                    <p>Founded with the vision of making content management accessible to everyone, Thorium90 emerged from the recognition that traditional CMS platforms often fall short of modern needs. We saw an opportunity to create something better—a platform that not only manages content but actively helps optimize it for search engines and AI-powered discovery.</p>
                    
                    <h2>What Makes Us Different</h2>
                    <ul>
                        <li><strong>AI-Driven Optimization:</strong> Our platform automatically optimizes your content for both traditional search engines and modern AI answer engines.</li>
                        <li><strong>Human Verification:</strong> While AI powers our recommendations, human expertise ensures accuracy and quality.</li>
                        <li><strong>AEO Innovation:</strong> We\'re pioneers in Answer Engine Optimization (AEO), preparing your content for the future of search.</li>
                        <li><strong>Developer-Friendly:</strong> Built with modern technologies and clean APIs for seamless integration.</li>
                    </ul>
                    
                    <h2>Our Values</h2>
                    <p>We\'re committed to transparency, innovation, and putting our users first. Every feature we build is designed to save you time while improving your content\'s performance across all digital channels.</p>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Ready to Transform Your Content Management?</h3>
                        <p>Join thousands of businesses who trust Thorium90 to power their digital content strategy.</p>
                        <a href="/contact" class="btn btn-primary">Get Started Today</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getFeaturesContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-6xl mx-auto">
                <h1>Features & Capabilities</h1>
                
                <div class="prose prose-lg">
                    <p class="lead">Discover the comprehensive suite of features that make Thorium90 the most advanced content management platform available today.</p>
                    
                    <div class="grid md:grid-cols-2 gap-8 my-12">
                        <div class="feature-card">
                            <h3>🚀 AEO Optimization</h3>
                            <p>Pioneer the future of search with Answer Engine Optimization. Our platform automatically optimizes your content for AI-powered search engines like ChatGPT, Perplexity, and Google\'s Bard.</p>
                            <ul>
                                <li>Topic categorization for better content classification</li>
                                <li>FAQ schema generation for featured snippets</li>
                                <li>Keyword management with semantic analysis</li>
                                <li>Reading time calculation for user experience</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🎯 Schema Validation</h3>
                            <p>Ensure your content meets the highest SEO standards with our advanced schema validation and generation system.</p>
                            <ul>
                                <li>Real-time JSON-LD preview</li>
                                <li>Schema.org compliance checking</li>
                                <li>Multiple content types support</li>
                                <li>Google Rich Results optimization</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>📊 Content Analytics</h3>
                            <p>Make data-driven decisions with comprehensive analytics and performance insights.</p>
                            <ul>
                                <li>Content performance tracking</li>
                                <li>SEO score monitoring</li>
                                <li>User engagement metrics</li>
                                <li>Conversion rate analysis</li>
                            </ul>
                        </div>
                        
                        <div class="feature-card">
                            <h3>🔧 Developer Tools</h3>
                            <p>Built for developers, loved by content creators. Modern APIs and clean architecture for seamless integration.</p>
                            <ul>
                                <li>RESTful API with comprehensive documentation</li>
                                <li>Webhook support for real-time updates</li>
                                <li>Custom field types and templates</li>
                                <li>Multi-environment deployment</li>
                            </ul>
                        </div>
                    </div>
                    
                    <h2>Advanced Capabilities</h2>
                    
                    <h3>Content Management</h3>
                    <ul>
                        <li>Intuitive visual editor with live preview</li>
                        <li>Version control and content history</li>
                        <li>Collaborative editing and approval workflows</li>
                        <li>Media management with automatic optimization</li>
                        <li>Multi-language support</li>
                    </ul>
                    
                    <h3>SEO & Performance</h3>
                    <ul>
                        <li>Automatic sitemap generation</li>
                        <li>Meta tag optimization suggestions</li>
                        <li>Core Web Vitals monitoring</li>
                        <li>Lazy loading and image optimization</li>
                        <li>CDN integration for global performance</li>
                    </ul>
                    
                    <h3>Security & Compliance</h3>
                    <ul>
                        <li>Enterprise-grade security with SSL encryption</li>
                        <li>GDPR compliance tools</li>
                        <li>Role-based access control</li>
                        <li>Regular security audits and updates</li>
                        <li>Backup and disaster recovery</li>
                    </ul>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Experience These Features Today</h3>
                        <p>Start your free trial and discover how Thorium90 can transform your content management workflow.</p>
                        <a href="/pricing" class="btn btn-primary">Start Free Trial</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getPricingContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-6xl mx-auto">
                <h1>Pricing Plans</h1>
                
                <div class="prose prose-lg text-center">
                    <p class="lead">Choose the perfect plan for your content management needs. All plans include our core features with no hidden fees.</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8 my-12">
                    <div class="pricing-card">
                        <div class="card-header">
                            <h3>Starter</h3>
                            <div class="price">
                                <span class="currency">$</span>
                                <span class="amount">19</span>
                                <span class="period">/month</span>
                            </div>
                            <p>Perfect for small businesses and personal projects</p>
                        </div>
                        <div class="features">
                            <ul>
                                <li>✅ Up to 100 pages</li>
                                <li>✅ Basic AEO optimization</li>
                                <li>✅ Schema validation</li>
                                <li>✅ 5GB storage</li>
                                <li>✅ Email support</li>
                                <li>✅ SSL certificate</li>
                            </ul>
                        </div>
                        <a href="#" class="btn btn-outline">Start Free Trial</a>
                    </div>
                    
                    <div class="pricing-card featured">
                        <div class="card-header">
                            <div class="popular-badge">Most Popular</div>
                            <h3>Professional</h3>
                            <div class="price">
                                <span class="currency">$</span>
                                <span class="amount">49</span>
                                <span class="period">/month</span>
                            </div>
                            <p>Ideal for growing businesses and marketing teams</p>
                        </div>
                        <div class="features">
                            <ul>
                                <li>✅ Up to 1,000 pages</li>
                                <li>✅ Advanced AEO optimization</li>
                                <li>✅ Full schema suite</li>
                                <li>✅ 50GB storage</li>
                                <li>✅ Priority support</li>
                                <li>✅ Custom domains</li>
                                <li>✅ Analytics dashboard</li>
                                <li>✅ API access</li>
                            </ul>
                        </div>
                        <a href="#" class="btn btn-primary">Start Free Trial</a>
                    </div>
                    
                    <div class="pricing-card">
                        <div class="card-header">
                            <h3>Enterprise</h3>
                            <div class="price">
                                <span class="currency">$</span>
                                <span class="amount">149</span>
                                <span class="period">/month</span>
                            </div>
                            <p>For large organizations with advanced needs</p>
                        </div>
                        <div class="features">
                            <ul>
                                <li>✅ Unlimited pages</li>
                                <li>✅ Enterprise AEO suite</li>
                                <li>✅ Custom schema types</li>
                                <li>✅ 500GB storage</li>
                                <li>✅ 24/7 phone support</li>
                                <li>✅ White-label options</li>
                                <li>✅ Advanced analytics</li>
                                <li>✅ SLA guarantee</li>
                                <li>✅ Custom integrations</li>
                            </ul>
                        </div>
                        <a href="#" class="btn btn-outline">Contact Sales</a>
                    </div>
                </div>
                
                <div class="faq-section mt-16">
                    <h2>Pricing FAQ</h2>
                    
                    <div class="faq-item">
                        <h4>Is there a free trial?</h4>
                        <p>Yes! All plans come with a 14-day free trial. No credit card required to get started.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Can I change plans anytime?</h4>
                        <p>Absolutely. You can upgrade or downgrade your plan at any time. Changes take effect immediately.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>What payment methods do you accept?</h4>
                        <p>We accept all major credit cards, PayPal, and bank transfers for annual plans.</p>
                    </div>
                    
                    <div class="faq-item">
                        <h4>Is there a setup fee?</h4>
                        <p>No setup fees, ever. What you see is what you pay—simple and transparent pricing.</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8 text-center">
                    <h3>Not Sure Which Plan Is Right for You?</h3>
                    <p>Our team is here to help you choose the perfect plan for your needs.</p>
                    <a href="/contact" class="btn btn-primary">Talk to Sales</a>
                </div>
            </div>
        </div>';
    }

    private function getContactContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Contact Us</h1>
                
                <div class="prose prose-lg">
                    <p class="lead">We\'d love to hear from you. Get in touch with our team for support, sales inquiries, or partnership opportunities.</p>
                    
                    <div class="grid md:grid-cols-2 gap-8 my-12">
                        <div>
                            <h3>Get in Touch</h3>
                            <p>Whether you have questions about our platform, need technical support, or want to discuss a custom solution, our team is here to help.</p>
                            
                            <div class="contact-info">
                                <div class="contact-item">
                                    <strong>Sales Inquiries</strong><br>
                                    Email: sales@thorium90.com<br>
                                    Phone: +1 (555) 123-4567
                                </div>
                                
                                <div class="contact-item">
                                    <strong>Technical Support</strong><br>
                                    Email: support@thorium90.com<br>
                                    Available: 24/7 for Enterprise customers
                                </div>
                                
                                <div class="contact-item">
                                    <strong>Partnerships</strong><br>
                                    Email: partners@thorium90.com<br>
                                    Response time: Within 24 hours
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3>Send us a Message</h3>
                            <form class="contact-form">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <input type="text" id="company" name="company">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <select id="subject" name="subject" required>
                                        <option value="">Choose a topic</option>
                                        <option value="sales">Sales Inquiry</option>
                                        <option value="support">Technical Support</option>
                                        <option value="partnership">Partnership</option>
                                        <option value="general">General Question</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="message">Message *</label>
                                    <textarea id="message" name="message" rows="5" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="office-locations mt-16">
                        <h2>Our Offices</h2>
                        
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="office">
                                <h4>San Francisco (HQ)</h4>
                                <p>
                                    123 Technology Street<br>
                                    San Francisco, CA 94105<br>
                                    United States
                                </p>
                            </div>
                            
                            <div class="office">
                                <h4>London</h4>
                                <p>
                                    456 Innovation Lane<br>
                                    London EC2A 3DY<br>
                                    United Kingdom
                                </p>
                            </div>
                            
                            <div class="office">
                                <h4>Tokyo</h4>
                                <p>
                                    789 Digital Avenue<br>
                                    Shibuya, Tokyo 150-0002<br>
                                    Japan
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Looking for Immediate Help?</h3>
                        <p>Check out our comprehensive help center with guides, tutorials, and troubleshooting tips.</p>
                        <a href="/help-center" class="btn btn-primary">Visit Help Center</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getPrivacyPolicyContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Privacy Policy</h1>
                
                <div class="prose prose-lg">
                    <p><em>Last updated: ' . date('F j, Y') . '</em></p>
                    
                    <p class="lead">At Thorium90, we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your data.</p>
                    
                    <h2>Information We Collect</h2>
                    
                    <h3>Personal Information</h3>
                    <p>We collect information you provide directly to us, such as:</p>
                    <ul>
                        <li>Name, email address, and contact information when you create an account</li>
                        <li>Payment information when you subscribe to our services</li>
                        <li>Content you create, upload, or store using our platform</li>
                        <li>Communications you send to us for support or feedback</li>
                    </ul>
                    
                    <h3>Automatically Collected Information</h3>
                    <p>We automatically collect certain information when you use our services:</p>
                    <ul>
                        <li>Log data including IP address, browser type, and usage patterns</li>
                        <li>Device information such as operating system and device identifiers</li>
                        <li>Cookies and similar tracking technologies (see our Cookie Policy)</li>
                        <li>Analytics data about how you interact with our platform</li>
                    </ul>
                    
                    <h2>How We Use Your Information</h2>
                    
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services</li>
                        <li>Process transactions and send you related information</li>
                        <li>Send you technical notices, updates, and support messages</li>
                        <li>Respond to your comments, questions, and customer service requests</li>
                        <li>Monitor and analyze trends, usage, and activities in connection with our services</li>
                        <li>Detect, investigate, and prevent fraudulent transactions and other illegal activities</li>
                    </ul>
                    
                    <h2>Information Sharing and Disclosure</h2>
                    
                    <p>We do not sell, trade, or otherwise transfer your personal information to third parties except in the following circumstances:</p>
                    
                    <h3>Service Providers</h3>
                    <p>We may share your information with third-party service providers who perform services on our behalf, such as payment processing, data analysis, email delivery, and customer service.</p>
                    
                    <h3>Legal Requirements</h3>
                    <p>We may disclose your information if required to do so by law or in response to valid requests by public authorities.</p>
                    
                    <h3>Business Transfers</h3>
                    <p>If Thorium90 is involved in a merger, acquisition, or asset sale, your personal information may be transferred as part of that transaction.</p>
                    
                    <h2>Data Security</h2>
                    
                    <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                    <ul>
                        <li>Encryption of data in transit and at rest</li>
                        <li>Regular security assessments and penetration testing</li>
                        <li>Access controls and authentication mechanisms</li>
                        <li>Employee training on data protection practices</li>
                    </ul>
                    
                    <h2>Your Rights and Choices</h2>
                    
                    <p>You have the following rights regarding your personal information:</p>
                    <ul>
                        <li><strong>Access:</strong> Request access to your personal information</li>
                        <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                        <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                        <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
                        <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
                    </ul>
                    
                    <h2>Data Retention</h2>
                    
                    <p>We retain your personal information for as long as necessary to provide our services and fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required by law.</p>
                    
                    <h2>International Data Transfers</h2>
                    
                    <p>Your information may be transferred to and processed in countries other than your own. We ensure that such transfers are made in accordance with applicable data protection laws and regulations.</p>
                    
                    <h2>Children\'s Privacy</h2>
                    
                    <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13.</p>
                    
                    <h2>Changes to This Privacy Policy</h2>
                    
                    <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "last updated" date.</p>
                    
                    <h2>Contact Us</h2>
                    
                    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
                    <ul>
                        <li>Email: privacy@thorium90.com</li>
                        <li>Address: 123 Technology Street, San Francisco, CA 94105</li>
                    </ul>
                </div>
            </div>
        </div>';
    }

    private function getTermsOfServiceContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Terms of Service</h1>
                
                <div class="prose prose-lg">
                    <p><em>Last updated: ' . date('F j, Y') . '</em></p>
                    
                    <p class="lead">These Terms of Service ("Terms") govern your use of the Thorium90 content management platform and services. By using our services, you agree to be bound by these Terms.</p>
                    
                    <h2>Acceptance of Terms</h2>
                    
                    <p>By accessing or using Thorium90\'s services, you agree to be bound by these Terms and our Privacy Policy. If you do not agree to these Terms, you may not use our services.</p>
                    
                    <h2>Description of Service</h2>
                    
                    <p>Thorium90 provides a cloud-based content management platform that enables users to create, manage, and optimize digital content with AI-driven tools and features.</p>
                    
                    <h2>User Accounts</h2>
                    
                    <h3>Account Creation</h3>
                    <p>To use our services, you must create an account and provide accurate and complete information. You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Notifying us immediately of any unauthorized use</li>
                    </ul>
                    
                    <h3>Account Eligibility</h3>
                    <p>You must be at least 13 years old to use our services. If you are under 18, you must have parental consent.</p>
                    
                    <h2>Acceptable Use</h2>
                    
                    <p>You agree to use our services only for lawful purposes and in accordance with these Terms. You must not:</p>
                    <ul>
                        <li>Violate any applicable laws or regulations</li>
                        <li>Infringe upon the rights of others</li>
                        <li>Upload or distribute malicious code or content</li>
                        <li>Attempt to gain unauthorized access to our systems</li>
                        <li>Use our services for spam or unsolicited communications</li>
                        <li>Reverse engineer or attempt to extract source code</li>
                    </ul>
                    
                    <h2>Content and Intellectual Property</h2>
                    
                    <h3>Your Content</h3>
                    <p>You retain ownership of all content you create or upload to our platform. By using our services, you grant us a limited license to:</p>
                    <ul>
                        <li>Store, process, and display your content as necessary to provide our services</li>
                        <li>Make backup copies for data protection purposes</li>
                        <li>Analyze content for optimization and improvement suggestions</li>
                    </ul>
                    
                    <h3>Our Intellectual Property</h3>
                    <p>Thorium90 and our licensors own all rights to our platform, including software, design, trademarks, and proprietary algorithms. You may not copy, modify, or redistribute our intellectual property without permission.</p>
                    
                    <h2>Subscription and Payment</h2>
                    
                    <h3>Subscription Plans</h3>
                    <p>Our services are offered on a subscription basis with different plan options. Subscription fees are charged in advance and are non-refundable except as specified in our Refund Policy.</p>
                    
                    <h3>Payment Terms</h3>
                    <ul>
                        <li>Subscription fees are billed monthly or annually</li>
                        <li>Payment is due immediately upon subscription</li>
                        <li>We may change pricing with 30 days\' notice</li>
                        <li>Taxes may apply based on your location</li>
                    </ul>
                    
                    <h3>Free Trial</h3>
                    <p>We offer a 14-day free trial for new users. No payment information is required to start your trial. At the end of the trial period, your account will be suspended unless you subscribe to a paid plan.</p>
                    
                    <h2>Service Availability</h2>
                    
                    <p>We strive to maintain high service availability but cannot guarantee uninterrupted access. We may:</p>
                    <ul>
                        <li>Perform scheduled maintenance with advance notice</li>
                        <li>Experience temporary outages due to technical issues</li>
                        <li>Suspend service for security or legal reasons</li>
                    </ul>
                    
                    <h2>Termination</h2>
                    
                    <h3>Termination by You</h3>
                    <p>You may terminate your account at any time through your account settings or by contacting our support team.</p>
                    
                    <h3>Termination by Us</h3>
                    <p>We may terminate or suspend your account if you:</p>
                    <ul>
                        <li>Violate these Terms or our policies</li>
                        <li>Fail to pay subscription fees</li>
                        <li>Engage in fraudulent or illegal activities</li>
                    </ul>
                    
                    <h3>Effect of Termination</h3>
                    <p>Upon termination, your access to our services will cease, and we may delete your content after a reasonable grace period.</p>
                    
                    <h2>Disclaimers and Limitation of Liability</h2>
                    
                    <h3>Service Warranty</h3>
                    <p>Our services are provided "as is" without warranties of any kind. We disclaim all warranties, express or implied, including merchantability and fitness for a particular purpose.</p>
                    
                    <h3>Limitation of Liability</h3>
                    <p>To the fullest extent permitted by law, Thorium90 shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of our services.</p>
                    
                    <h2>Indemnification</h2>
                    
                    <p>You agree to indemnify and hold harmless Thorium90 from any claims, damages, or expenses arising from your use of our services or violation of these Terms.</p>
                    
                    <h2>Governing Law</h2>
                    
                    <p>These Terms are governed by the laws of the State of California, United States, without regard to conflict of law principles.</p>
                    
                    <h2>Changes to Terms</h2>
                    
                    <p>We may modify these Terms at any time. We will notify you of material changes via email or through our platform. Continued use of our services after changes constitutes acceptance of the new Terms.</p>
                    
                    <h2>Contact Information</h2>
                    
                    <p>For questions about these Terms, contact us at:</p>
                    <ul>
                        <li>Email: legal@thorium90.com</li>
                        <li>Address: 123 Technology Street, San Francisco, CA 94105</li>
                    </ul>
                </div>
            </div>
        </div>';
    }

    private function getCookiePolicyContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Cookie Policy</h1>
                
                <div class="prose prose-lg">
                    <p><em>Last updated: ' . date('F j, Y') . '</em></p>
                    
                    <p class="lead">This Cookie Policy explains how Thorium90 uses cookies and similar tracking technologies on our website and platform.</p>
                    
                    <h2>What Are Cookies?</h2>
                    
                    <p>Cookies are small text files that are placed on your device when you visit a website. They are widely used to make websites work more efficiently and to provide information to website owners.</p>
                    
                    <h2>How We Use Cookies</h2>
                    
                    <p>We use cookies for several purposes:</p>
                    
                    <h3>Essential Cookies</h3>
                    <p>These cookies are necessary for our website to function properly. They enable core functionality such as:</p>
                    <ul>
                        <li>User authentication and login sessions</li>
                        <li>Shopping cart functionality</li>
                        <li>Security and fraud prevention</li>
                        <li>Load balancing and performance optimization</li>
                    </ul>
                    
                    <h3>Performance Cookies</h3>
                    <p>These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously:</p>
                    <ul>
                        <li>Page views and navigation patterns</li>
                        <li>Time spent on pages</li>
                        <li>Error messages and technical issues</li>
                        <li>Browser and device information</li>
                    </ul>
                    
                    <h3>Functionality Cookies</h3>
                    <p>These cookies enable enhanced functionality and personalization:</p>
                    <ul>
                        <li>Language and region preferences</li>
                        <li>Theme and display settings</li>
                        <li>Content customization</li>
                        <li>User interface preferences</li>
                    </ul>
                    
                    <h3>Marketing Cookies</h3>
                    <p>These cookies are used to deliver relevant advertisements and track marketing campaign effectiveness:</p>
                    <ul>
                        <li>Targeted advertising based on interests</li>
                        <li>Conversion tracking and attribution</li>
                        <li>Social media integration</li>
                        <li>Remarketing and retargeting</li>
                    </ul>
                    
                    <h2>Types of Cookies We Use</h2>
                    
                    <h3>First-Party Cookies</h3>
                    <p>These are cookies set directly by our website and can only be read by us. We use first-party cookies for:</p>
                    <ul>
                        <li>User authentication and session management</li>
                        <li>Website functionality and user preferences</li>
                        <li>Analytics and performance monitoring</li>
                    </ul>
                    
                    <h3>Third-Party Cookies</h3>
                    <p>These are cookies set by external services we use on our website:</p>
                    <ul>
                        <li><strong>Google Analytics:</strong> Website traffic and user behavior analysis</li>
                        <li><strong>Stripe:</strong> Payment processing and fraud prevention</li>
                        <li><strong>Intercom:</strong> Customer support and communication</li>
                        <li><strong>Social Media:</strong> Social sharing and login functionality</li>
                    </ul>
                    
                    <h2>Cookie Duration</h2>
                    
                    <h3>Session Cookies</h3>
                    <p>These cookies are temporary and are deleted when you close your browser. They are used for essential website functionality.</p>
                    
                    <h3>Persistent Cookies</h3>
                    <p>These cookies remain on your device for a set period or until you delete them. They are used for:</p>
                    <ul>
                        <li>Remembering login status</li>
                        <li>Storing user preferences</li>
                        <li>Analytics and performance tracking</li>
                    </ul>
                    
                    <h2>Managing Your Cookie Preferences</h2>
                    
                    <h3>Browser Settings</h3>
                    <p>You can control cookies through your browser settings:</p>
                    <ul>
                        <li>Block all cookies</li>
                        <li>Block third-party cookies only</li>
                        <li>Delete existing cookies</li>
                        <li>Receive notifications when cookies are set</li>
                    </ul>
                    
                    <h3>Cookie Consent Tool</h3>
                    <p>We provide a cookie consent tool that allows you to:</p>
                    <ul>
                        <li>Accept or reject different types of cookies</li>
                        <li>Change your preferences at any time</li>
                        <li>Access detailed information about each cookie</li>
                    </ul>
                    
                    <h3>Opt-Out Links</h3>
                    <p>You can opt out of specific tracking services:</p>
                    <ul>
                        <li><a href="https://tools.google.com/dlpage/gaoptout" target="_blank">Google Analytics Opt-out</a></li>
                        <li><a href="http://www.aboutads.info/choices/" target="_blank">Digital Advertising Alliance Opt-out</a></li>
                        <li><a href="http://www.youronlinechoices.eu/" target="_blank">European Interactive Digital Advertising Alliance</a></li>
                    </ul>
                    
                    <h2>Impact of Disabling Cookies</h2>
                    
                    <p>If you disable cookies, some website functionality may be affected:</p>
                    <ul>
                        <li>You may need to log in repeatedly</li>
                        <li>Your preferences may not be saved</li>
                        <li>Some features may not work properly</li>
                        <li>Personalized content may not be available</li>
                    </ul>
                    
                    <h2>Updates to This Policy</h2>
                    
                    <p>We may update this Cookie Policy to reflect changes in our practices or for legal and regulatory reasons. We will notify you of any material changes.</p>
                    
                    <h2>Contact Us</h2>
                    
                    <p>If you have questions about our use of cookies, please contact us at:</p>
                    <ul>
                        <li>Email: privacy@thorium90.com</li>
                        <li>Address: 123 Technology Street, San Francisco, CA 94105</li>
                    </ul>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Manage Your Cookie Preferences</h3>
                        <p>You can update your cookie preferences at any time using our preference center.</p>
                        <button class="btn btn-primary" onclick="openCookiePreferences()">Cookie Preferences</button>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getRefundPolicyContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Refund Policy</h1>
                
                <div class="prose prose-lg">
                    <p><em>Last updated: ' . date('F j, Y') . '</em></p>
                    
                    <p class="lead">At Thorium90, we want you to be completely satisfied with our service. This Refund Policy outlines our approach to refunds and cancellations.</p>
                    
                    <h2>Free Trial Period</h2>
                    
                    <p>We offer a 14-day free trial for all new users to evaluate our platform:</p>
                    <ul>
                        <li>No credit card required to start your trial</li>
                        <li>Full access to all plan features during trial</li>
                        <li>Cancel anytime during trial with no charges</li>
                        <li>Automatic account suspension if no plan is selected after trial</li>
                    </ul>
                    
                    <h2>Subscription Cancellation</h2>
                    
                    <h3>Monthly Subscriptions</h3>
                    <p>For monthly subscriptions:</p>
                    <ul>
                        <li>Cancel anytime through your account settings</li>
                        <li>Service continues until the end of your current billing period</li>
                        <li>No partial refunds for unused portions of the month</li>
                        <li>Access to data export tools until service ends</li>
                    </ul>
                    
                    <h3>Annual Subscriptions</h3>
                    <p>For annual subscriptions:</p>
                    <ul>
                        <li>30-day refund window from initial purchase date</li>
                        <li>Pro-rated refunds may be available in exceptional circumstances</li>
                        <li>Contact support for refund requests beyond 30 days</li>
                        <li>Service continues until refund is processed</li>
                    </ul>
                    
                    <h2>Refund Eligibility</h2>
                    
                    <h3>Eligible for Full Refund</h3>
                    <p>You may be eligible for a full refund if:</p>
                    <ul>
                        <li>You request a refund within 30 days of your initial annual subscription</li>
                        <li>You experience significant service outages that prevent platform use</li>
                        <li>You encounter billing errors or unauthorized charges</li>
                        <li>Technical issues prevent access to core platform features</li>
                    </ul>
                    
                    <h3>Not Eligible for Refund</h3>
                    <p>Refunds are generally not available for:</p>
                    <ul>
                        <li>Monthly subscription fees (except in exceptional circumstances)</li>
                        <li>Add-on services or additional storage</li>
                        <li>Account termination due to Terms of Service violations</li>
                        <li>Refund requests made more than 30 days after annual billing</li>
                        <li>Changes in business needs or platform requirements</li>
                    </ul>
                    
                    <h2>How to Request a Refund</h2>
                    
                    <h3>Contact Support</h3>
                    <p>To request a refund:</p>
                    <ol>
                        <li>Email our support team at billing@thorium90.com</li>
                        <li>Include your account email and reason for refund request</li>
                        <li>Provide any relevant details about issues experienced</li>
                        <li>Our team will review your request within 2 business days</li>
                    </ol>
                    
                    <h3>Required Information</h3>
                    <p>Please include the following in your refund request:</p>
                    <ul>
                        <li>Account email address</li>
                        <li>Subscription plan and billing date</li>
                        <li>Detailed reason for refund request</li>
                        <li>Any supporting documentation</li>
                    </ul>
                    
                    <h2>Refund Processing</h2>
                    
                    <h3>Processing Time</h3>
                    <p>Once approved, refunds are processed as follows:</p>
                    <ul>
                        <li><strong>Credit Cards:</strong> 5-10 business days</li>
                        <li><strong>PayPal:</strong> 3-5 business days</li>
                        <li><strong>Bank Transfers:</strong> 7-14 business days</li>
                    </ul>
                    
                    <h3>Refund Method</h3>
                    <p>Refunds are issued to the original payment method used for the subscription. We cannot process refunds to different payment methods or accounts.</p>
                    
                    <h2>Exceptional Circumstances</h2>
                    
                    <p>We may consider refunds outside our standard policy for:</p>
                    <ul>
                        <li>Extended service outages affecting your business operations</li>
                        <li>Significant platform changes that impact your use case</li>
                        <li>Medical emergencies or other hardship situations</li>
                        <li>Technical issues that cannot be resolved by our support team</li>
                    </ul>
                    
                    <p>Each request is reviewed individually, and approval is at our discretion.</p>
                    
                    <h2>Data and Account Access</h2>
                    
                    <h3>After Cancellation</h3>
                    <p>Following subscription cancellation:</p>
                    <ul>
                        <li>Your account remains active until the end of the billing period</li>
                        <li>Data export tools are available for 30 days after service ends</li>
                        <li>Account data is permanently deleted after 90 days</li>
                        <li>We recommend exporting your data before cancellation</li>
                    </ul>
                    
                    <h3>After Refund</h3>
                    <p>If a refund is processed:</p>
                    <ul>
                        <li>Account access is immediately terminated</li>
                        <li>Data export opportunity is provided before termination</li>
                        <li>Re-subscription is allowed after refund processing</li>
                    </ul>
                    
                    <h2>Billing Disputes</h2>
                    
                    <p>If you notice any billing discrepancies:</p>
                    <ul>
                        <li>Contact our billing team immediately</li>
                        <li>Provide transaction details and account information</li>
                        <li>We will investigate and resolve within 3 business days</li>
                        <li>Credits or refunds will be issued for confirmed errors</li>
                    </ul>
                    
                    <h2>Policy Changes</h2>
                    
                    <p>We may update this Refund Policy to reflect changes in our business practices or legal requirements. Material changes will be communicated via email and through our platform.</p>
                    
                    <h2>Contact Information</h2>
                    
                    <p>For questions about refunds or billing:</p>
                    <ul>
                        <li>Email: billing@thorium90.com</li>
                        <li>Phone: +1 (555) 123-4567</li>
                        <li>Support hours: Monday-Friday, 9 AM - 6 PM PST</li>
                    </ul>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Need Help?</h3>
                        <p>Our support team is here to help resolve any issues before considering cancellation.</p>
                        <a href="/support" class="btn btn-primary">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getFAQContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-4xl mx-auto">
                <h1>Frequently Asked Questions</h1>
                
                <div class="prose prose-lg">
                    <p class="lead">Find answers to common questions about Thorium90\'s content management platform, features, and services.</p>
                    
                    <div class="faq-categories">
                        <h2>Getting Started</h2>
                        
                        <div class="faq-item">
                            <h3>What is Thorium90?</h3>
                            <p>Thorium90 is an AI-driven content management platform that helps businesses create, manage, and optimize their digital content. Our platform combines advanced AEO (Answer Engine Optimization) features with traditional CMS functionality to prepare your content for both current and future search technologies.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>How do I get started with Thorium90?</h3>
                            <p>Getting started is easy! Sign up for our 14-day free trial—no credit card required. You\'ll have access to all features and can explore the platform with sample content. Our onboarding wizard will guide you through the setup process.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Do I need technical knowledge to use Thorium90?</h3>
                            <p>Not at all! Thorium90 is designed for users of all technical levels. Our intuitive interface makes content creation and management simple, while our AI-powered tools handle the complex optimization automatically. We also provide comprehensive documentation and support.</p>
                        </div>
                    </div>
                    
                    <div class="faq-categories">
                        <h2>Features & Functionality</h2>
                        
                        <div class="faq-item">
                            <h3>What is AEO (Answer Engine Optimization)?</h3>
                            <p>AEO is optimization for AI-powered answer engines like ChatGPT, Perplexity, and Google\'s Bard. Unlike traditional SEO that focuses on ranking web pages, AEO optimizes content to be selected and cited by AI systems when answering user questions.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>How does schema validation work?</h3>
                            <p>Our platform automatically generates and validates Schema.org markup for your content. You can preview the JSON-LD output in real-time, test it with Google\'s Rich Results tools, and ensure compliance with structured data standards.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Can I import content from my existing CMS?</h3>
                            <p>Yes! We support content migration from popular platforms including WordPress, Drupal, and Contentful. Our migration tools preserve your content structure, metadata, and media files. Contact our support team for assistance with large migrations.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Is there an API available?</h3>
                            <p>Absolutely! Our RESTful API provides full access to all platform features. You can manage content, retrieve analytics, and integrate with external systems. API access is available on Professional and Enterprise plans.</p>
                        </div>
                    </div>
                    
                    <div class="faq-categories">
                        <h2>Pricing & Plans</h2>
                        
                        <div class="faq-item">
                            <h3>What\'s included in the free trial?</h3>
                            <p>The 14-day free trial includes full access to all Thorium90 features: unlimited pages, AEO optimization tools, schema validation, analytics dashboard, and email support. No credit card is required to start.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Can I change my plan anytime?</h3>
                            <p>Yes, you can upgrade or downgrade your plan at any time. Upgrades take effect immediately, while downgrades take effect at your next billing cycle. We\'ll help you transition smoothly between plans.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Are there any setup fees or hidden costs?</h3>
                            <p>No setup fees, ever! Our pricing is transparent with no hidden costs. What you see on our pricing page is exactly what you pay. Additional storage or premium integrations may have separate fees, which are clearly disclosed.</p>
                        </div>
                    </div>
                    
                    <div class="faq-categories">
                        <h2>Security & Privacy</h2>
                        
                        <div class="faq-item">
                            <h3>How secure is my data?</h3>
                            <p>Security is our top priority. We use enterprise-grade encryption for data in transit and at rest, perform regular security audits, and maintain SOC 2 compliance. Your data is backed up daily across multiple geographic locations.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Do you sell my data to third parties?</h3>
                            <p>Never. We do not sell, rent, or share your personal data or content with third parties for marketing purposes. Your content belongs to you, and we only use it to provide and improve our services as outlined in our Privacy Policy.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Can I export my data if I cancel?</h3>
                            <p>Yes! You can export all your content, media files, and metadata at any time through our data export tools. After cancellation, you have 30 days to download your data before it\'s permanently deleted.</p>
                        </div>
                    </div>
                    
                    <div class="faq-categories">
                        <h2>Support & Training</h2>
                        
                        <div class="faq-item">
                            <h3>What support options are available?</h3>
                            <p>We offer multiple support channels: email support for all users, priority support for Professional plans, and 24/7 phone support for Enterprise customers. Our help center includes extensive documentation, tutorials, and troubleshooting guides.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>Do you provide training for my team?</h3>
                            <p>Yes! Enterprise customers receive complimentary onboarding sessions and team training. We also offer webinars, video tutorials, and best practice guides to help your team get the most out of Thorium90.</p>
                        </div>
                        
                        <div class="faq-item">
                            <h3>How quickly do you respond to support requests?</h3>
                            <p>We respond to support requests within 24 hours for standard plans, 4 hours for Professional plans, and 1 hour for Enterprise plans during business hours. Critical issues receive priority attention regardless of plan level.</p>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Still Have Questions?</h3>
                        <p>Can\'t find the answer you\'re looking for? Our support team is here to help.</p>
                        <a href="/contact" class="btn btn-primary">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    private function getFAQData(): array
    {
        return [
            [
                'id' => '1',
                'question' => 'What is Thorium90?',
                'answer' => 'Thorium90 is an AI-driven content management platform that helps businesses create, manage, and optimize their digital content. Our platform combines advanced AEO (Answer Engine Optimization) features with traditional CMS functionality to prepare your content for both current and future search technologies.'
            ],
            [
                'id' => '2',
                'question' => 'How do I get started with Thorium90?',
                'answer' => 'Getting started is easy! Sign up for our 14-day free trial—no credit card required. You\'ll have access to all features and can explore the platform with sample content. Our onboarding wizard will guide you through the setup process.'
            ],
            [
                'id' => '3',
                'question' => 'What is AEO (Answer Engine Optimization)?',
                'answer' => 'AEO is optimization for AI-powered answer engines like ChatGPT, Perplexity, and Google\'s Bard. Unlike traditional SEO that focuses on ranking web pages, AEO optimizes content to be selected and cited by AI systems when answering user questions.'
            ],
            [
                'id' => '4',
                'question' => 'What\'s included in the free trial?',
                'answer' => 'The 14-day free trial includes full access to all Thorium90 features: unlimited pages, AEO optimization tools, schema validation, analytics dashboard, and email support. No credit card is required to start.'
            ],
            [
                'id' => '5',
                'question' => 'How secure is my data?',
                'answer' => 'Security is our top priority. We use enterprise-grade encryption for data in transit and at rest, perform regular security audits, and maintain SOC 2 compliance. Your data is backed up daily across multiple geographic locations.'
            ]
        ];
    }

    // Additional content methods would continue here...
    // For brevity, I\'ll include a few more key ones and indicate where others would go

    private function getHelpCenterContent(): string
    {
        return '
        <div class="container">
            <div class="max-w-6xl mx-auto">
                <h1>Help Center</h1>
                
                <div class="prose prose-lg">
                    <p class="lead">Welcome to the Thorium90 Help Center. Find guides, tutorials, and resources to help you get the most out of our platform.</p>
                    
                    <div class="grid md:grid-cols-3 gap-8 my-12">
                        <div class="help-category">
                            <h3>🚀 Getting Started</h3>
                            <ul>
                                <li><a href="#quick-start">Quick Start Guide</a></li>
                                <li><a href="#account-setup">Account Setup</a></li>
                                <li><a href="#first-page">Creating Your First Page</a></li>
                                <li><a href="#navigation">Platform Navigation</a></li>
                            </ul>
                        </div>
                        
                        <div class="help-category">
                            <h3>📝 Content Management</h3>
                            <ul>
                                <li><a href="#content-creation">Content Creation Guide</a></li>
                                <li><a href="#media-management">Media Management</a></li>
                                <li><a href="#content-organization">Organizing Content</a></li>
                                <li><a href="#publishing">Publishing Workflow</a></li>
                            </ul>
                        </div>
                        
                        <div class="help-category">
                            <h3>🎯 AEO Optimization</h3>
                            <ul>
                                <li><a href="#aeo-basics">AEO Basics</a></li>
                                <li><a href="#topic-selection">Topic Selection</a></li>
                                <li><a href="#keyword-strategy">Keyword Strategy</a></li>
                                <li><a href="#faq-optimization">FAQ Optimization</a></li>
                            </ul>
                        </div>
                        
                        <div class="help-category">
                            <h3>🔧 Technical Guides</h3>
                            <ul>
                                <li><a href="#api-documentation">API Documentation</a></li>
                                <li><a href="#integrations">Third-party Integrations</a></li>
                                <li><a href="#custom-domains">Custom Domains</a></li>
                                <li><a href="#advanced-settings">Advanced Settings</a></li>
                            </ul>
                        </div>
                        
                        <div class="help-category">
                            <h3>📊 Analytics & SEO</h3>
                            <ul>
                                <li><a href="#analytics-dashboard">Analytics Dashboard</a></li>
                                <li><a href="#seo-optimization">SEO Best Practices</a></li>
                                <li><a href="#performance-monitoring">Performance Monitoring</a></li>
                                <li><a href="#schema-markup">Schema Markup</a></li>
                            </ul>
                        </div>
                        
                        <div class="help-category">
                            <h3>💼 Account & Billing</h3>
                            <ul>
                                <li><a href="#account-settings">Account Settings</a></li>
                                <li><a href="#billing-management">Billing Management</a></li>
                                <li><a href="#team-collaboration">Team Collaboration</a></li>
                                <li><a href="#security-settings">Security Settings</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="popular-articles mt-16">
                        <h2>Popular Articles</h2>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="article-card">
                                <h4>How to Optimize Content for AI Search Engines</h4>
                                <p>Learn the fundamentals of AEO and how to prepare your content for AI-powered search engines.</p>
                                <a href="#aeo-guide" class="text-blue-600 hover:text-blue-800">Read Article →</a>
                            </div>
                            
                            <div class="article-card">
                                <h4>Setting Up Your First Content Strategy</h4>
                                <p>A step-by-step guide to planning and implementing an effective content strategy with Thorium90.</p>
                                <a href="#content-strategy" class="text-blue-600 hover:text-blue-800">Read Article →</a>
                            </div>
                            
                            <div class="article-card">
                                <h4>Understanding Schema Markup and SEO</h4>
                                <p>Master schema markup to improve your search engine visibility and rich snippet eligibility.</p>
                                <a href="#schema-guide" class="text-blue-600 hover:text-blue-800">Read Article →</a>
                            </div>
                            
                            <div class="article-card">
                                <h4>API Integration Best Practices</h4>
                                <p>Learn how to integrate Thorium90 with your existing systems using our comprehensive API.</p>
                                <a href="#api-guide" class="text-blue-600 hover:text-blue-800">Read Article →</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="video-tutorials mt-16">
                        <h2>Video Tutorials</h2>
                        
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="video-card">
                                <div class="video-thumbnail">
                                    <div class="play-button">▶</div>
                                </div>
                                <h4>Platform Overview (5 min)</h4>
                                <p>Get a quick tour of the Thorium90 platform and its key features.</p>
                            </div>
                            
                            <div class="video-card">
                                <div class="video-thumbnail">
                                    <div class="play-button">▶</div>
                                </div>
                                <h4>AEO Optimization Walkthrough (12 min)</h4>
                                <p>Learn how to use our AEO tools to optimize your content for AI search engines.</p>
                            </div>
                            
                            <div class="video-card">
                                <div class="video-thumbnail">
                                    <div class="play-button">▶</div>
                                </div>
                                <h4>Team Collaboration Features (8 min)</h4>
                                <p>Discover how teams can collaborate effectively using Thorium90\'s workflow tools.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-6 rounded-lg mt-8">
                        <h3>Need Personal Assistance?</h3>
                        <p>Can\'t find what you\'re looking for? Our support team is ready to help you succeed.</p>
                        <a href="/support" class="btn btn-primary">Contact Support</a>
                        <a href="/documentation" class="btn btn-outline ml-4">View Documentation</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    // Placeholder methods for remaining content (these would be fully implemented)
    private function getDocumentationContent(): string { return '<div class="container"><h1>Documentation</h1><p>Technical documentation and developer resources...</p></div>'; }
    private function getSupportContent(): string { return '<div class="container"><h1>Customer Support</h1><p>Get help from our support team...</p></div>'; }
    private function getBlogContent(): string { return '<div class="container"><h1>Blog</h1><p>Latest news and insights...</p></div>'; }
    private function getCaseStudiesContent(): string { return '<div class="container"><h1>Case Studies</h1><p>Customer success stories...</p></div>'; }
    private function getResourcesContent(): string { return '<div class="container"><h1>Resources</h1><p>Valuable resources and tools...</p></div>'; }
    private function getNewsContent(): string { return '<div class="container"><h1>News</h1><p>Latest news and announcements...</p></div>'; }
    private function getSitemapContent(): string { return '<div class="container"><h1>Sitemap</h1><p>Site navigation and structure...</p></div>'; }
    private function get404Content(): string { return '<div class="container"><h1>Page Not Found</h1><p>Sorry, the page you are looking for could not be found...</p></div>'; }
    private function getComingSoonContent(): string { return '<div class="container"><h1>Coming Soon</h1><p>Exciting new features are coming soon...</p></div>'; }
}