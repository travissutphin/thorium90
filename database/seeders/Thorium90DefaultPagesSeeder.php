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

        // Only create the 9 required pages for a clean installation
        $pages = [
            // 1. Home Page (handled separately by HomePageSeeder)
            
            // 2. About Page
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => $this->getAboutContent(),
                'excerpt' => 'Learn about our company, mission, and values.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'About Us',
                'meta_description' => 'Learn about our company, our mission, and the team behind our success.',
                'meta_keywords' => 'about us, company, mission, values, team',
                'schema_type' => 'AboutPage',
                'layout' => 'default',
                'topics' => ['Company', 'About'],
                'keywords' => ['about', 'company', 'mission', 'values'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            
            // 3. Contact Page
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => $this->getContactContent(),
                'excerpt' => 'Get in touch with our team. We\'d love to hear from you.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Contact Us',
                'meta_description' => 'Get in touch with our team. Contact information, office locations, and contact form.',
                'meta_keywords' => 'contact, contact us, get in touch, support',
                'schema_type' => 'ContactPage',
                'layout' => 'default',
                'topics' => ['Contact', 'Support'],
                'keywords' => ['contact', 'support', 'get in touch'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            
            // 4. Coming Soon Page
            [
                'title' => 'Coming Soon',
                'slug' => 'coming-soon',
                'content' => $this->getComingSoonContent(),
                'excerpt' => 'Exciting new features and updates are coming soon.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Coming Soon',
                'meta_description' => 'Exciting new features and updates are coming soon. Stay tuned for the latest developments.',
                'meta_keywords' => 'coming soon, new features, updates, announcements',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Coming Soon', 'Updates'],
                'keywords' => ['coming soon', 'updates', 'announcements'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            
            // 5. Privacy Policy
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => $this->getPrivacyPolicyContent(),
                'excerpt' => 'Our privacy policy explains how we collect, use, and protect your information.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Learn how we collect, use, and protect your personal information.',
                'meta_keywords' => 'privacy policy, data protection, privacy, GDPR',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Privacy'],
                'keywords' => ['privacy', 'data protection', 'GDPR'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],
            
            // 6. Terms and Conditions
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'content' => $this->getTermsAndConditionsContent(),
                'excerpt' => 'Terms and conditions for using our website and services.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Terms and Conditions',
                'meta_description' => 'Read our terms and conditions for using our website and services.',
                'meta_keywords' => 'terms, conditions, terms of service, legal',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Legal', 'Terms'],
                'keywords' => ['terms', 'conditions', 'legal'],
                'content_type' => 'legal',
                'published_at' => Carbon::now(),
            ],
            
            // 7. FAQ
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'content' => $this->getFAQContent(),
                'excerpt' => 'Find answers to commonly asked questions.',
                'status' => 'published',
                'is_featured' => true,
                'meta_title' => 'FAQ - Frequently Asked Questions',
                'meta_description' => 'Find answers to frequently asked questions about our products and services.',
                'meta_keywords' => 'FAQ, frequently asked questions, help, support',
                'schema_type' => 'FAQPage',
                'layout' => 'default',
                'topics' => ['Support', 'FAQ'],
                'keywords' => ['FAQ', 'questions', 'help', 'support'],
                'faq_data' => $this->getFAQData(),
                'content_type' => 'support',
                'published_at' => Carbon::now(),
            ],
            
            // 8. Our Team
            [
                'title' => 'Our Team',
                'slug' => 'our-team',
                'content' => $this->getOurTeamContent(),
                'excerpt' => 'Meet the talented individuals who make our company successful.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => 'Our Team',
                'meta_description' => 'Meet our team of dedicated professionals who are passionate about what they do.',
                'meta_keywords' => 'team, staff, employees, about team, company team',
                'schema_type' => 'AboutPage',
                'layout' => 'default',
                'topics' => ['Team', 'Company'],
                'keywords' => ['team', 'staff', 'employees', 'people'],
                'content_type' => 'page',
                'published_at' => Carbon::now(),
            ],
            
            // 9. 404 Error Page
            [
                'title' => 'Page Not Found',
                'slug' => '404',
                'content' => $this->get404Content(),
                'excerpt' => 'Sorry, the page you are looking for could not be found.',
                'status' => 'published',
                'is_featured' => false,
                'meta_title' => '404 - Page Not Found',
                'meta_description' => 'The page you are looking for could not be found. Return to our homepage or browse our content.',
                'meta_keywords' => '404, page not found, error',
                'schema_type' => 'WebPage',
                'layout' => 'default',
                'topics' => ['Error', '404'],
                'keywords' => ['404', 'error', 'not found'],
                'content_type' => 'utility',
                'published_at' => Carbon::now(),
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
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg mx-auto">
                    <p class="text-xl text-gray-600 mb-8">We are passionate about creating innovative solutions that make a difference in people\'s lives and businesses.</p>
                    
                    <h2>Our Story</h2>
                    <p>Founded with a vision to provide exceptional service and innovative solutions, our company has grown from a small startup to a trusted partner for businesses worldwide. We believe in the power of technology to transform industries and improve lives.</p>
                    
                    <h2>Our Mission</h2>
                    <p>To deliver cutting-edge solutions that empower businesses to achieve their goals while maintaining the highest standards of quality, integrity, and customer service.</p>
                    
                    <h2>What We Do</h2>
                    <ul>
                        <li><strong>Innovation:</strong> We stay at the forefront of technology to provide the most advanced solutions.</li>
                        <li><strong>Quality:</strong> Every product and service we deliver meets our rigorous quality standards.</li>
                        <li><strong>Customer Focus:</strong> Our customers are at the center of everything we do.</li>
                        <li><strong>Integrity:</strong> We conduct business with honesty, transparency, and ethical practices.</li>
                    </ul>
                    
                    <h2>Our Values</h2>
                    <p>We are guided by core values that shape our culture, drive our decisions, and define our relationships with customers, partners, and each other.</p>
                    
                    <div class="bg-blue-50 p-6 rounded-lg mt-8">
                        <h3 class="text-xl font-semibold mb-4">Ready to Learn More?</h3>
                        <p class="mb-4">Discover how we can help your business succeed and grow.</p>
                        <a href="/contact" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Get in Touch</a>
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
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg mx-auto">
                    <p class="text-xl text-gray-600 mb-8">Find answers to commonly asked questions about our products and services.</p>
                    
                    <div class="space-y-8">
                        <div class="faq-section">
                            <h2 class="text-2xl font-semibold mb-6">General Questions</h2>
                            
                            <div class="space-y-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-3">What services do you offer?</h3>
                                    <p class="text-gray-600">We provide comprehensive solutions tailored to meet your business needs, including consulting, implementation, and ongoing support services.</p>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-3">How do I get started?</h3>
                                    <p class="text-gray-600">Getting started is easy! Simply contact us through our contact form or give us a call. We\'ll schedule a consultation to discuss your needs and provide a customized solution.</p>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-3">Do you provide support?</h3>
                                    <p class="text-gray-600">Yes, we offer comprehensive support to all our clients. Our support team is available during business hours and we provide various support channels to assist you.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="faq-section">
                            <h2 class="text-2xl font-semibold mb-6">Pricing & Billing</h2>
                            
                            <div class="space-y-4">
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-3">How is pricing determined?</h3>
                                    <p class="text-gray-600">Our pricing is based on the scope and complexity of your project. We provide transparent, competitive pricing with no hidden fees. Contact us for a detailed quote.</p>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-3">What payment methods do you accept?</h3>
                                    <p class="text-gray-600">We accept various payment methods including credit cards, bank transfers, and other secure payment options. Payment terms will be discussed during the proposal phase.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-6 rounded-lg mt-8 text-center">
                        <h3 class="text-xl font-semibold mb-4">Still Have Questions?</h3>
                        <p class="text-gray-600 mb-4">Can\'t find the answer you\'re looking for? We\'re here to help.</p>
                        <a href="/contact" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Contact Us</a>
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
                'question' => 'What services do you offer?',
                'answer' => 'We provide comprehensive solutions tailored to meet your business needs, including consulting, implementation, and ongoing support services.'
            ],
            [
                'id' => '2', 
                'question' => 'How do I get started?',
                'answer' => 'Getting started is easy! Simply contact us through our contact form or give us a call. We\'ll schedule a consultation to discuss your needs and provide a customized solution.'
            ],
            [
                'id' => '3',
                'question' => 'Do you provide support?',
                'answer' => 'Yes, we offer comprehensive support to all our clients. Our support team is available during business hours and we provide various support channels to assist you.'
            ],
            [
                'id' => '4',
                'question' => 'How is pricing determined?',
                'answer' => 'Our pricing is based on the scope and complexity of your project. We provide transparent, competitive pricing with no hidden fees. Contact us for a detailed quote.'
            ],
            [
                'id' => '5',
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept various payment methods including credit cards, bank transfers, and other secure payment options. Payment terms will be discussed during the proposal phase.'
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

    private function getTermsAndConditionsContent(): string
    {
        return '
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div class="prose prose-lg mx-auto">
                    <p><em>Last updated: ' . date('F j, Y') . '</em></p>
                    
                    <p class="text-xl text-gray-600 mb-8">These terms and conditions outline the rules and regulations for the use of our website and services.</p>
                    
                    <h2>1. Terms</h2>
                    <p>By accessing this website, we assume you accept these terms and conditions in full. Do not continue to use this website if you do not accept all of the terms and conditions stated on this page.</p>
                    
                    <h2>2. Use License</h2>
                    <p>Permission is granted to temporarily use our website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul>
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose or for any public display</li>
                        <li>Attempt to reverse engineer any software contained on our website</li>
                        <li>Remove any copyright or other proprietary notations from the materials</li>
                    </ul>
                    
                    <h2>3. Disclaimer</h2>
                    <p>The materials on our website are provided on an \'as is\' basis. To the fullest extent permitted by law, we exclude all representations, warranties and conditions relating to our website and the use of this website.</p>
                    
                    <h2>4. Limitations</h2>
                    <p>In no event shall our company or its suppliers be liable for any damages arising out of the use or inability to use the materials on our website, even if we have been notified orally or in writing of the possibility of such damage.</p>
                    
                    <h2>5. Contact Information</h2>
                    <p>If you have any questions about these Terms and Conditions, please contact us through our contact page.</p>
                </div>
            </div>
        </div>';
    }

    private function getOurTeamContent(): string
    {
        return '
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <p class="text-xl text-gray-600">Meet the passionate individuals who drive our success and make our vision a reality.</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <div class="text-center">
                        <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-3xl text-gray-500">👤</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">John Smith</h3>
                        <p class="text-blue-600 mb-3">Chief Executive Officer</p>
                        <p class="text-gray-600 text-sm">John brings over 15 years of industry experience and leads our strategic vision with passion and expertise.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-3xl text-gray-500">👤</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Sarah Johnson</h3>
                        <p class="text-blue-600 mb-3">Chief Technology Officer</p>
                        <p class="text-gray-600 text-sm">Sarah oversees our technical direction and ensures we stay at the cutting edge of innovation.</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-3xl text-gray-500">👤</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Michael Davis</h3>
                        <p class="text-blue-600 mb-3">Head of Operations</p>
                        <p class="text-gray-600 text-sm">Michael ensures smooth operations and exceptional service delivery across all our projects.</p>
                    </div>
                </div>
                
                <div class="bg-blue-50 p-8 rounded-lg text-center">
                    <h2 class="text-2xl font-semibold mb-4">Join Our Team</h2>
                    <p class="text-gray-600 mb-6">We\'re always looking for talented individuals who share our passion for innovation and excellence.</p>
                    <a href="/contact" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">View Open Positions</a>
                </div>
            </div>
        </div>';
    }

    private function get404Content(): string
    {
        return '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-2xl mx-auto text-center">
                <div class="mb-8">
                    <h1 class="text-9xl font-bold text-gray-200 mb-4">404</h1>
                    <h2 class="text-3xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
                    <p class="text-xl text-gray-600 mb-8">Sorry, the page you are looking for could not be found. It might have been moved, deleted, or you entered the wrong URL.</p>
                </div>
                
                <div class="space-y-4 mb-8">
                    <a href="/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors mr-4">Go Home</a>
                    <a href="/contact" class="inline-block border border-blue-600 text-blue-600 px-6 py-3 rounded-lg hover:bg-blue-50 transition-colors">Contact Us</a>
                </div>
                
                <div class="text-left max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Popular Pages:</h3>
                    <ul class="space-y-2 text-blue-600">
                        <li><a href="/about" class="hover:underline">About Us</a></li>
                        <li><a href="/contact" class="hover:underline">Contact Us</a></li>
                        <li><a href="/faq" class="hover:underline">FAQ</a></li>
                        <li><a href="/our-team" class="hover:underline">Our Team</a></li>
                    </ul>
                </div>
            </div>
        </div>';
    }

    private function getComingSoonContent(): string
    {
        return '
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-3xl mx-auto text-center">
                <div class="mb-12">
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">Something Exciting is Coming Soon!</h2>
                    <p class="text-xl text-gray-600 mb-8">We\'re working hard to bring you something amazing. Stay tuned for updates and announcements.</p>
                </div>
                
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-8 rounded-lg mb-12">
                    <h3 class="text-2xl font-semibold mb-4">Get Notified</h3>
                    <p class="text-gray-600 mb-6">Be the first to know when we launch. Enter your email to stay updated.</p>
                    
                    <form class="max-w-md mx-auto flex gap-2">
                        <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">Notify Me</button>
                    </form>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6 text-left">
                    <div class="text-center">
                        <div class="text-3xl mb-3">🚀</div>
                        <h4 class="font-semibold mb-2">Innovation</h4>
                        <p class="text-sm text-gray-600">Cutting-edge features designed to enhance your experience.</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-3">⚡</div>
                        <h4 class="font-semibold mb-2">Performance</h4>
                        <p class="text-sm text-gray-600">Lightning-fast performance that exceeds expectations.</p>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl mb-3">🎯</div>
                        <h4 class="font-semibold mb-2">Precision</h4>
                        <p class="text-sm text-gray-600">Carefully crafted solutions tailored to your needs.</p>
                    </div>
                </div>
            </div>
        </div>';
    }
}