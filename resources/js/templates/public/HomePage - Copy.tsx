import React from 'react';
import { TemplateProps } from '@/core';

/**
 * Home Page Template
 * 
 * This is a client-customizable template for the home page.
 * Follow the comment sections to add your custom design code.
 */
export const HomePage: React.FC<TemplateProps> = ({
    content,
    theme,
    config
}) => {
    return (
        <>
            {/* ==================== CUSTOM STYLES START ==================== */}
            {/* Add your custom CSS for the home page template here */}
            <style dangerouslySetInnerHTML={{
                __html: `
                .home-template {
                    /* Add your global home page styles */
                }
                
                .home-hero {
                    /* Hero section styles */
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 4rem 0;
                    text-align: center;
                }
                
                .home-features {
                    /* Features section styles */
                    padding: 3rem 0;
                    background: #f8f9fa;
                }
                
                .home-content {
                    /* Main content styles */
                    padding: 2rem 0;
                }
                
                .home-footer {
                    /* Footer styles */
                    background: #343a40;
                    color: white;
                    padding: 2rem 0;
                }
                
                /* Add more custom styles as needed */
                `
            }} />
            {/* ==================== CUSTOM STYLES END ==================== */}

            <div className={`home-template ${config?.custom_class || ''}`} data-theme={theme}>
                
                {/* ==================== HEADER SECTION START ==================== */}
                {/* Customize the header for the home page */}
                <header className="home-header">
                    <nav className="container mx-auto px-4 py-4 flex justify-between items-center">
                        <div className="logo">
                            {/* Add your logo here */}
                            <h1 className="text-2xl font-bold">Your Brand</h1>
                        </div>
                        <div className="nav-menu">
                            {/* Add your navigation menu here */}
                            <ul className="flex space-x-6">
                                <li><a href="/" className="hover:text-blue-600">Home</a></li>
                                <li><a href="/about" className="hover:text-blue-600">About</a></li>
                                <li><a href="/services" className="hover:text-blue-600">Services</a></li>
                                <li><a href="/contact" className="hover:text-blue-600">Contact</a></li>
                            </ul>
                        </div>
                    </nav>
                </header>
                {/* ==================== HEADER SECTION END ==================== */}

                {/* ==================== HERO SECTION START ==================== */}
                {/* Design your hero/banner section here */}
                <section className="home-hero">
                    <div className="container mx-auto px-4">
                        <h1 className="text-5xl font-bold mb-4">
                            {content.title || 'Welcome to Our Website'}
                        </h1>
                        <p className="text-xl mb-8 opacity-90">
                            {(content as any).excerpt || 'Your success is our mission'}
                        </p>
                        
                        {/* Call-to-action buttons */}
                        <div className="space-x-4">
                            <button className="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                                Get Started
                            </button>
                            <button className="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition">
                                Learn More
                            </button>
                        </div>
                    </div>
                </section>
                {/* ==================== HERO SECTION END ==================== */}

                {/* ==================== FEATURES SECTION START ==================== */}
                {/* Add your features/services section here */}
                <section className="home-features">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold mb-4">Why Choose Us</h2>
                            <p className="text-gray-600 max-w-2xl mx-auto">
                                We provide exceptional services that help your business grow
                            </p>
                        </div>
                        
                        <div className="grid md:grid-cols-3 gap-8">
                            {/* Feature 1 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    {/* Add icon here */}
                                    <span className="text-2xl">🚀</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Fast & Reliable</h3>
                                <p className="text-gray-600">Quick delivery and dependable service you can count on.</p>
                            </div>
                            
                            {/* Feature 2 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">💡</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Innovative Solutions</h3>
                                <p className="text-gray-600">Cutting-edge approaches to solve your business challenges.</p>
                            </div>
                            
                            {/* Feature 3 */}
                            <div className="text-center p-6 bg-white rounded-lg shadow-md">
                                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <span className="text-2xl">🎯</span>
                                </div>
                                <h3 className="text-xl font-semibold mb-2">Results Focused</h3>
                                <p className="text-gray-600">We measure success by the results we deliver for you.</p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== FEATURES SECTION END ==================== */}

                {/* ==================== MAIN CONTENT START ==================== */}
                {/* This renders the page content from the CMS */}
                <main className="home-content">
                    <div className="container mx-auto px-4">
                        {content.content && (
                            <div 
                                className="prose prose-lg max-w-none"
                                dangerouslySetInnerHTML={{ __html: content.content }} 
                            />
                        )}
                    </div>
                </main>
                {/* ==================== MAIN CONTENT END ==================== */}

                {/* ==================== TESTIMONIALS SECTION START ==================== */}
                {/* Add testimonials section if enabled */}
                {/* This will be controlled by the feature system we just built */}
                <section className="py-16 bg-gray-50">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl font-bold mb-4">What Our Clients Say</h2>
                        </div>
                        
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Testimonial 1 */}
                            <div className="bg-white p-6 rounded-lg shadow-md">
                                <div className="flex items-center mb-4">
                                    <div className="w-12 h-12 bg-gray-300 rounded-full mr-4"></div>
                                    <div>
                                        <h4 className="font-semibold">John Smith</h4>
                                        <p className="text-sm text-gray-600">CEO, Company Inc.</p>
                                    </div>
                                </div>
                                <p className="text-gray-700">"Exceptional service and outstanding results. Highly recommended!"</p>
                            </div>
                            
                            {/* Add more testimonials as needed */}
                        </div>
                    </div>
                </section>
                {/* ==================== TESTIMONIALS SECTION END ==================== */}

                {/* ==================== FOOTER SECTION START ==================== */}
                {/* Customize the footer for the home page */}
                <footer className="home-footer">
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
                                    <li><a href="/about" className="hover:text-white">About Us</a></li>
                                    <li><a href="/contact" className="hover:text-white">Contact</a></li>
                                    <li><a href="#" className="hover:text-white">Careers</a></li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Connect</h4>
                                <div className="flex space-x-4">
                                    {/* Add social media links */}
                                    <a href="#" className="text-gray-300 hover:text-white">Facebook</a>
                                    <a href="#" className="text-gray-300 hover:text-white">Twitter</a>
                                    <a href="#" className="text-gray-300 hover:text-white">LinkedIn</a>
                                </div>
                            </div>
                        </div>
                        
                        <div className="border-t border-gray-600 mt-8 pt-8 text-center text-gray-300">
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

export default HomePage;
