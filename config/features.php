<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Modules
    |--------------------------------------------------------------------------
    |
    | Complex, reusable features that are implemented as full feature modules.
    | These have their own service providers, migrations, routes, etc.
    |
    */
    'modules' => [
        'blog' => env('BLOG_ENABLED', true),
        'shop' => env('SHOP_ENABLED', false),
        'events' => env('EVENTS_ENABLED', false),
        'portfolio' => env('PORTFOLIO_ENABLED', false),
        'newsletter' => env('NEWSLETTER_ENABLED', false),
        'forms' => env('FORMS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Features
    |--------------------------------------------------------------------------
    |
    | Simple on/off features that don't require full plugin architecture.
    | These are typically client-specific sections or simple functionality.
    |
    */
    'custom' => [
        'testimonials' => env('FEATURE_TESTIMONIALS', true),
        'team_page' => env('FEATURE_TEAM_PAGE', true),
        'case_studies' => env('FEATURE_CASE_STUDIES', false),
        'faq_section' => env('FEATURE_FAQ_SECTION', true),
        'pricing_tables' => env('FEATURE_PRICING_TABLES', false),
        'calculators' => env('FEATURE_CALCULATORS', false),
        'gallery' => env('FEATURE_GALLERY', true),
        'social_feed' => env('FEATURE_SOCIAL_FEED', false),
        'live_chat' => env('FEATURE_LIVE_CHAT', false),
        'booking_system' => env('FEATURE_BOOKING_SYSTEM', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Descriptions
    |--------------------------------------------------------------------------
    |
    | Human-readable descriptions for features, used in admin interfaces
    | and documentation.
    |
    */
    'descriptions' => [
        // Feature module descriptions
        'modules' => [
            'blog' => 'Full blog system with posts, categories, and tags',
            'shop' => 'E-commerce functionality with products and orders',
            'events' => 'Event management with calendar and bookings',
            'portfolio' => 'Portfolio galleries and project showcases',
            'newsletter' => 'Email newsletter management and campaigns',
            'forms' => 'Dynamic form builder with submissions',
        ],
        
        // Custom feature descriptions
        'custom' => [
            'testimonials' => 'Customer testimonials section',
            'team_page' => 'Team member profiles and bios',
            'case_studies' => 'Detailed case study pages',
            'faq_section' => 'Frequently asked questions section',
            'pricing_tables' => 'Service pricing comparison tables',
            'calculators' => 'Custom calculation tools',
            'gallery' => 'Image and media galleries',
            'social_feed' => 'Social media feed integration',
            'live_chat' => 'Live chat widget integration',
            'booking_system' => 'Appointment booking system',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Dependencies
    |--------------------------------------------------------------------------
    |
    | Define which features depend on others. If a dependency is disabled,
    | the dependent feature will also be disabled.
    |
    */
    'dependencies' => [
        'shop' => ['forms'], // Shop requires forms for checkout
        'events' => ['forms'], // Events require forms for registration
        'booking_system' => ['forms'], // Booking requires forms
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Categories
    |--------------------------------------------------------------------------
    |
    | Group features by category for better organization in admin interfaces.
    |
    */
    'categories' => [
        'content' => ['blog', 'portfolio', 'case_studies', 'gallery'],
        'commerce' => ['shop', 'booking_system', 'pricing_tables'],
        'engagement' => ['newsletter', 'testimonials', 'social_feed', 'live_chat'],
        'utility' => ['forms', 'calculators', 'faq_section'],
        'team' => ['team_page', 'events'],
    ],
];
