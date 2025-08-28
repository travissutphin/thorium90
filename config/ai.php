<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | The default provider to use for AI content analysis. Options:
    | 'basic' - Free basic analysis (current MVP)
    | 'claude' - Claude API analysis
    | 'openai' - OpenAI GPT analysis
    | 'gemini' - Google Gemini analysis
    |
    */
    'default_provider' => env('AI_PROVIDER', 'basic'),

    /*
    |--------------------------------------------------------------------------
    | AI Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider including API keys, models,
    | and cost estimation.
    |
    */
    'providers' => [
        'basic' => [
            'enabled' => true,
            'cost_per_analysis' => 0.0,
            'quality_rating' => 2,
            'estimated_time' => 1,
        ],

        'claude' => [
            'api_key' => env('CLAUDE_API_KEY'),
            'model' => env('CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
            'cost_per_token' => env('CLAUDE_COST_PER_TOKEN', 0.000003),
            'enabled' => env('CLAUDE_ENABLED', false),
            'quality_rating' => 5,
            'estimated_time' => 4,
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'cost_per_token' => env('OPENAI_COST_PER_TOKEN', 0.00003),
            'enabled' => env('OPENAI_ENABLED', false),
            'quality_rating' => 5,
            'estimated_time' => 3,
        ],

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-pro'),
            'cost_per_token' => env('GEMINI_COST_PER_TOKEN', 0.000001),
            'enabled' => env('GEMINI_ENABLED', false),
            'quality_rating' => 4,
            'estimated_time' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Limits & Controls
    |--------------------------------------------------------------------------
    |
    | Controls to manage AI usage and costs.
    |
    */
    'limits' => [
        // Maximum AI analyses per user per month
        'per_user_monthly' => env('AI_LIMIT_PER_USER', 50),
        
        // Maximum cost per user per month (in dollars)
        'max_monthly_cost' => env('AI_MAX_MONTHLY_COST', 5.00),
        
        // Cache duration for AI results (in minutes)
        'cache_duration' => env('AI_CACHE_DURATION', 7 * 24 * 60), // 7 days
        
        // Minimum content length to allow AI analysis
        'min_content_length' => env('AI_MIN_CONTENT_LENGTH', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific AI features.
    |
    */
    'features' => [
        // Allow users to choose between basic and AI analysis
        'user_choice' => env('AI_ALLOW_USER_CHOICE', true),
        
        // Show cost estimates to users
        'show_costs' => env('AI_SHOW_COSTS', true),
        
        // Track usage analytics
        'usage_tracking' => env('AI_USAGE_TRACKING', true),
        
        // Enable fallback to basic analysis if AI fails
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
        
        // Cache AI results to reduce costs
        'caching_enabled' => env('AI_CACHING_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Prompts
    |--------------------------------------------------------------------------
    |
    | Templates for AI analysis prompts.
    |
    */
    'prompts' => [
        'content_analysis' => [
            'focus_areas' => [
                'SEO optimization',
                'Content structure',
                'Topic relevance',
                'Audience engagement',
                'Technical accuracy',
            ],
            'max_suggestions' => [
                'tags' => 8,
                'keywords' => 12,
                'topics' => 5,
                'faqs' => 5,
            ],
        ],
    ],
];