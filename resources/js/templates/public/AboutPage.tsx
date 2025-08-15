import React from 'react';
import { TemplateProps } from '@/core';

/**
 * About Page Template
 * 
 * This is a client-customizable template for the about page.
 * Follow the comment sections to add your custom design code.
 */
export const AboutPage: React.FC<TemplateProps> = ({
    content,
    theme,
    config
}) => {
    return (
        <>
            {/* ==================== CUSTOM STYLES START ==================== */}
            {/* Add your custom CSS for the about page template here */}
            <style dangerouslySetInnerHTML={{
                __html: `
                .about-template {
                    /* Add your global about page styles */
                }
                
                .about-hero {
                    /* Hero section styles */
                    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                    color: white;
                    padding: 3rem 0;
                    text-align: center;
                }
                
                .about-story {
                    /* Story section styles */
                    padding: 4rem 0;
                    background: white;
                }
                
                .about-values {
                    /* Values section styles */
                    padding: 4rem 0;
                    background: #f8fafc;
                }
                
                .about-team {
                    /* Team section styles */
                    padding: 4rem 0;
                    background: white;
                }
                
                .about-cta {
                    /* Call-to-action section styles */
                    padding: 4rem 0;
                    background: #1e293b;
                    color: white;
                    text-align: center;
                }
                
                /* Add more custom styles as needed */
                `
            }} />
            {/* ==================== CUSTOM STYLES END ==================== */}

            <div className={`about-template ${config?.custom_class || ''}`} data-theme={theme}>
                
                {/* ==================== HEADER SECTION START ==================== */}
                {/* Customize the header for the about page */}
                <header className="about-header">
                    <nav className="container mx-auto px-4 py-4 flex justify-between items-center">
                        <div className="logo">
                            {/* Add your logo here */}
                            <h1 className="text-2xl font-bold">Your Brand</h1>
                        </div>
                        <div className="nav-menu">
                            {/* Add your navigation menu here */}
                            <ul className="flex space-x-6">
                                <li><a href="/" className="hover:text-blue-600">Home</a></li>
                                <li><a href="/about" className="hover:text-blue-600 font-semibold">About</a></li>
                                <li><a href="/services" className="hover:text-blue-600">Services</a></li>
                                <li><a href="/contact" className="hover:text-blue-600">Contact</a></li>
                            </ul>
                        </div>
                    </nav>
                </header>
                {/* ==================== HEADER SECTION END ==================== */}

                {/* ==================== HERO SECTION START ==================== */}
                {/* Design your about hero section here */}
                <section className="about-hero">
                    <div className="container mx-auto px-4">
                        <h1 className="text-4xl font-bold mb-4">
                            {content.title || 'About Our Company'}
                        </h1>
                        <p className="text-xl opacity-90 max-w-2xl mx-auto">
                            {(content as any).excerpt || 'Learn more about our story, values, and the team behind our success'}
                        </p>
                    </div>
                </section>
                {/* ==================== HERO SECTION END ==================== */}

                {/* ==================== STORY SECTION START ==================== */}
                {/* Add your company story section here */}
                <section className="about-story">
                    <div className="container mx-auto px-4">
                        <div className="grid lg:grid-cols-2 gap-12 items-center">
                            <div>
                                <h2 className="text-3xl font-bold mb-6">Our Story</h2>
                                <div className="prose prose-lg">
                                    {content.content ? (
                                        <div dangerouslySetInnerHTML={{ __html: content.content }} />
                                    ) : (
                                        <>
                                            <p className="text-gray-600 mb-4">
                                                Founded with a vision to transform the digital landscape, our company has grown 
                                                from a small startup to a trusted partner for businesses worldwide.
                                            </p>
                                            <p className="text-gray-600 mb-4">
                                                We believe in the power of innovation, collaboration, and delivering exceptional 
                                                results that exceed our clients' expectations.
                                            </p>
                                            <p className="text-gray-600">
                                                Today, we continue to push boundaries and create solutions that make a real 
                                                difference in the world.
                                            </p>
                                        </>
                                    )}
                                </div>
                            </div>
                            <div>
                                {/* Add your story image here */}
                                <div className="bg-gray-200 rounded-lg h-96 flex items-center justify-center">
                                    <span className="text-gray-500">Story Image Placeholder</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== STORY SECTION END ==================== */}

                {/* ==================== VALUES SECTION START ==================== */}
                {/* Add your company values section here */}
                <section className="about-values">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold mb-4">Our Values</h2>
                            <p className="text-gray-600 max-w-2xl mx-auto">
                                The principles that guide everything we do
                            </p>
                        </div>
                        
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Value 1 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🎯</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Excellence</h3>
                                <p className="text-gray-600">We strive for excellence in everything we do, never settling for mediocrity.</p>
                            </div>
                            
                            {/* Value 2 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🤝</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Integrity</h3>
                                <p className="text-gray-600">Honesty and transparency are at the core of all our relationships.</p>
                            </div>
                            
                            {/* Value 3 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">💡</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Innovation</h3>
                                <p className="text-gray-600">We embrace new ideas and technologies to stay ahead of the curve.</p>
                            </div>
                            
                            {/* Value 4 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🌟</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Quality</h3>
                                <p className="text-gray-600">Every project receives our full attention to detail and commitment to quality.</p>
                            </div>
                            
                            {/* Value 5 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🚀</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Growth</h3>
                                <p className="text-gray-600">We believe in continuous learning and helping our clients grow.</p>
                            </div>
                            
                            {/* Value 6 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">❤️</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Passion</h3>
                                <p className="text-gray-600">We love what we do and it shows in every project we deliver.</p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== VALUES SECTION END ==================== */}

                {/* ==================== TEAM SECTION START ==================== */}
                {/* Add your team section here - controlled by FEATURE_TEAM_PAGE */}
                <section className="about-team">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold mb-4">Meet Our Team</h2>
                            <p className="text-gray-600 max-w-2xl mx-auto">
                                The talented individuals who make it all happen
                            </p>
                        </div>
                        
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Team Member 1 */}
                            <div className="text-center">
                                <div className="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                                <h3 className="text-xl font-semibold mb-1">John Smith</h3>
                                <p className="text-blue-600 mb-2">CEO & Founder</p>
                                <p className="text-gray-600 text-sm">
                                    Visionary leader with 15+ years of industry experience.
                                </p>
                            </div>
                            
                            {/* Team Member 2 */}
                            <div className="text-center">
                                <div className="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                                <h3 className="text-xl font-semibold mb-1">Sarah Johnson</h3>
                                <p className="text-blue-600 mb-2">CTO</p>
                                <p className="text-gray-600 text-sm">
                                    Technology expert passionate about innovative solutions.
                                </p>
                            </div>
                            
                            {/* Team Member 3 */}
                            <div className="text-center">
                                <div className="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
                                <h3 className="text-xl font-semibold mb-1">Mike Davis</h3>
                                <p className="text-blue-600 mb-2">Lead Designer</p>
                                <p className="text-gray-600 text-sm">
                                    Creative designer with an eye for beautiful, functional design.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== TEAM SECTION END ==================== */}

                {/* ==================== CTA SECTION START ==================== */}
                {/* Add your call-to-action section here */}
                <section className="about-cta">
                    <div className="container mx-auto px-4">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold mb-4">Ready to Work Together?</h2>
                            <p className="text-xl mb-8 opacity-90">
                                Let's discuss how we can help bring your vision to life
                            </p>
                            
                            <div className="space-x-4">
                                <button className="bg-white text-gray-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                                    Get In Touch
                                </button>
                                <button className="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-gray-900 transition">
                                    View Our Work
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== CTA SECTION END ==================== */}

                {/* ==================== FOOTER SECTION START ==================== */}
                {/* Customize the footer for the about page */}
                <footer className="bg-gray-900 text-white py-12">
                    <div className="container mx-auto px-4">
                        <div className="grid md:grid-cols-4 gap-8">
                            <div>
                                <h3 className="text-xl font-bold mb-4">Your Brand</h3>
                                <p className="text-gray-300">
                                    Building exceptional digital experiences for businesses worldwide.
                                </p>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Services</h4>
                                <ul className="space-y-2 text-gray-300">
                                    <li><a href="#" className="hover:text-white">Web Development</a></li>
                                    <li><a href="#" className="hover:text-white">Digital Marketing</a></li>
                                    <li><a href="#" className="hover:text-white">Consulting</a></li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Company</h4>
                                <ul className="space-y-2 text-gray-300">
                                    <li><a href="/about" className="hover:text-white font-semibold">About Us</a></li>
                                    <li><a href="/contact" className="hover:text-white">Contact</a></li>
                                    <li><a href="#" className="hover:text-white">Careers</a></li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Connect</h4>
                                <div className="flex space-x-4">
                                    <a href="#" className="text-gray-300 hover:text-white">Facebook</a>
                                    <a href="#" className="text-gray-300 hover:text-white">Twitter</a>
                                    <a href="#" className="text-gray-300 hover:text-white">LinkedIn</a>
                                </div>
                            </div>
                        </div>
                        
                        <div className="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                            <p>&copy; 2025 Your Brand. All rights reserved.</p>
                        </div>
                    </div>
                </footer>
                {/* ==================== FOOTER SECTION END ==================== */}

            </div>

            {/* ==================== CUSTOM SCRIPTS START ==================== */}
            {/* Add any custom JavaScript for this template */}
            {config?.page_scripts && (
                <script dangerouslySetInnerHTML={{ __html: config.page_scripts }} />
            )}
            {/* ==================== CUSTOM SCRIPTS END ==================== */}
        </>
    );
};

export default AboutPage;
