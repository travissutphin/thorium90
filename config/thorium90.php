<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Thorium90 Boilerplate Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Thorium90 boilerplate
    | system including presets, modules, and deployment settings.
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Project Presets
    |--------------------------------------------------------------------------
    |
    | Define different project presets that can be selected during setup.
    | Each preset includes specific modules and configurations.
    |
    */

    'presets' => [
        'default' => [
            'name' => 'Default Website',
            'description' => 'Basic CMS with pages and user management',
            'modules' => ['pages', 'users', 'auth', 'api'],
            'features' => [
                'pages' => true,
                'blog' => false,
                'ecommerce' => false,
                'api' => true,
                'social_login' => false,
            ]
        ],
        
        'ecommerce' => [
            'name' => 'E-Commerce Platform',
            'description' => 'Full e-commerce with products, cart, and payments',
            'modules' => ['pages', 'users', 'auth', 'products', 'cart', 'orders', 'payments'],
            'features' => [
                'pages' => true,
                'blog' => true,
                'ecommerce' => true,
                'api' => true,
                'social_login' => true,
            ]
        ],
        
        'blog' => [
            'name' => 'Blog Platform',
            'description' => 'Content-focused blog with posts and comments',
            'modules' => ['pages', 'users', 'auth', 'posts', 'comments', 'categories', 'tags'],
            'features' => [
                'pages' => true,
                'blog' => true,
                'ecommerce' => false,
                'api' => true,
                'social_login' => true,
            ]
        ],
        
        'saas' => [
            'name' => 'SaaS Application',
            'description' => 'Multi-tenant SaaS with subscriptions and teams',
            'modules' => ['pages', 'users', 'auth', 'subscriptions', 'teams', 'billing', 'api'],
            'features' => [
                'pages' => true,
                'blog' => false,
                'ecommerce' => false,
                'api' => true,
                'social_login' => true,
                'subscriptions' => true,
                'teams' => true,
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Modules
    |--------------------------------------------------------------------------
    |
    | Control which features are enabled in the application.
    | These can be toggled per project.
    |
    */

    'modules' => [
        'pages' => env('MODULE_PAGES', true),
        'blog' => env('MODULE_BLOG', false),
        'ecommerce' => env('MODULE_ECOMMERCE', false),
        'api' => env('MODULE_API', true),
        '2fa' => env('MODULE_2FA', true),
        'social_login' => env('MODULE_SOCIAL_LOGIN', false),
        'subscriptions' => env('MODULE_SUBSCRIPTIONS', false),
        'teams' => env('MODULE_TEAMS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding Configuration
    |--------------------------------------------------------------------------
    |
    | Default branding settings that can be overridden per project.
    |
    */

    'branding' => [
        'app_name' => env('APP_NAME', 'Thorium90'),
        'tagline' => env('APP_TAGLINE', 'Built with Thorium90'),
        'logo' => env('APP_LOGO', '/images/logo.svg'),
        'logo_dark' => env('APP_LOGO_DARK', '/images/logo-dark.svg'),
        'favicon' => env('APP_FAVICON', '/favicon.ico'),
        'primary_color' => env('BRAND_PRIMARY', '#3B82F6'),
        'secondary_color' => env('BRAND_SECONDARY', '#10B981'),
        'footer_text' => env('FOOTER_TEXT', 'Powered by Thorium90'),
        'meta_description' => env('META_DESCRIPTION', 'Built with Thorium90 boilerplate'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Settings
    |--------------------------------------------------------------------------
    |
    | Settings specific to development and deployment processes.
    |
    */

    'development' => [
        'generate_docs' => env('GENERATE_DOCS', true),
        'include_demo_data' => env('INCLUDE_DEMO_DATA', false),
        'enable_debug_bar' => env('ENABLE_DEBUG_BAR', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for different deployment environments.
    |
    */

    'deployment' => [
        'environments' => ['development', 'staging', 'production'],
        'auto_migrate' => env('AUTO_MIGRATE', false),
        'cache_config' => env('CACHE_CONFIG_ON_DEPLOY', true),
        'optimize_autoloader' => env('OPTIMIZE_AUTOLOADER', true),
    ],
];