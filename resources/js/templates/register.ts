import { TemplateRegistry } from '@/core';
import { HomePage } from './public/HomePage';
import { AboutPage } from './public/AboutPage';

/**
 * Register Client Templates
 * 
 * This file registers all client-specific templates with the template system.
 * Add new templates here as you create them.
 */
export function registerClientTemplates() {
    // Register Home Page Template
    TemplateRegistry.register({
        id: 'client-home',
        name: 'Home Page Template',
        description: 'Custom home page template with hero, features, and testimonials',
        plugin: 'client',
        category: 'page',
        layouts: ['default'],
        blocks: [],
        themes: ['default'],
        config: {
            layouts: ['default'],
            blocks: [],
            defaultLayout: 'default',
            defaultTheme: 'default',
            settings: {
                showTestimonials: true,
                showFeatures: true,
                heroStyle: 'gradient'
            }
        },
        component: HomePage,
        isActive: true
    });

    // Register About Page Template
    TemplateRegistry.register({
        id: 'client-about',
        name: 'About Page Template',
        description: 'Custom about page template with story, values, and team sections',
        plugin: 'client',
        category: 'page',
        layouts: ['default'],
        blocks: [],
        themes: ['default'],
        config: {
            layouts: ['default'],
            blocks: [],
            defaultLayout: 'default',
            defaultTheme: 'default',
            settings: {
                showTeam: true,
                showValues: true,
                showStory: true
            }
        },
        component: AboutPage,
        isActive: true
    });

    // TODO: Add more client templates here
    // Example: ContactPage, ServicesPage, etc.
}

// Auto-register templates when this module is imported
registerClientTemplates();
