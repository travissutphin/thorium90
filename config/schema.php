<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Schema.org Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Schema.org structured data
    | types and their validation rules. This allows for extensible schema
    | types without hardcoding them in controllers.
    |
    */

    'types' => [
        'WebPage' => [
            'label' => 'Web Page (Default)',
            'description' => 'A basic web page with standard metadata',
            'fields' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'url' => 'nullable|url',
                'mainEntityOfPage' => 'nullable|url',
            ],
            'required_properties' => ['name', 'description'],
            'optional_properties' => ['url', 'mainEntityOfPage', 'breadcrumb', 'primaryImageOfPage'],
        ],

        'Article' => [
            'label' => 'Article',
            'description' => 'A news article, blog post, or similar editorial content',
            'fields' => [
                'headline' => 'required|string|max:110',
                'articleBody' => 'required|string',
                'wordCount' => 'nullable|integer|min:1',
                'articleSection' => 'nullable|string|max:100',
                'keywords' => 'nullable|string|max:500',
                'inLanguage' => 'nullable|string|size:2',
            ],
            'required_properties' => ['headline', 'articleBody'],
            'optional_properties' => ['wordCount', 'articleSection', 'keywords', 'inLanguage'],
        ],

        'BlogPosting' => [
            'label' => 'Blog Post',
            'description' => 'A blog post with additional blog-specific metadata',
            'extends' => 'Article',
            'fields' => [
                'headline' => 'required|string|max:110',
                'articleBody' => 'required|string',
                'wordCount' => 'nullable|integer|min:1',
                'articleSection' => 'nullable|string|max:100',
                'genre' => 'nullable|string|max:100',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
                'timeRequired' => 'nullable|string|regex:/^PT\d+M$/',
                'inLanguage' => 'nullable|string|size:2',
                'isAccessibleForFree' => 'nullable|boolean',
                'commentCount' => 'nullable|integer|min:0',
            ],
            'required_properties' => ['headline', 'articleBody', 'mainEntityOfPage'],
            'optional_properties' => [
                'wordCount', 'articleSection', 'genre', 'tags', 'timeRequired', 
                'inLanguage', 'isAccessibleForFree', 'commentCount', 'interactionStatistic',
                'comment', 'relatedLink'
            ],
        ],

        'BreadcrumbList' => [
            'label' => 'Breadcrumb Navigation',
            'description' => 'Navigation breadcrumbs for page hierarchy',
            'fields' => [
                'itemListElement' => 'required|array',
            ],
            'required_properties' => ['itemListElement'],
            'optional_properties' => [],
        ],

        'NewsArticle' => [
            'label' => 'News Article',
            'description' => 'A news article with journalistic standards',
            'extends' => 'Article',
            'fields' => [
                'headline' => 'required|string|max:110',
                'articleBody' => 'required|string',
                'wordCount' => 'nullable|integer|min:1',
                'dateline' => 'nullable|string|max:200',
                'printColumn' => 'nullable|string|max:50',
                'printEdition' => 'nullable|string|max:50',
                'printPage' => 'nullable|string|max:20',
                'printSection' => 'nullable|string|max:50',
            ],
            'required_properties' => ['headline', 'articleBody'],
            'optional_properties' => ['wordCount', 'dateline', 'printColumn', 'printEdition', 'printPage', 'printSection'],
        ],

        'FAQPage' => [
            'label' => 'FAQ Page',
            'description' => 'A page containing frequently asked questions and answers',
            'fields' => [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'mainEntity' => 'required|array|min:1',
                'mainEntity.*.@type' => 'required|in:Question',
                'mainEntity.*.name' => 'required|string|max:255',
                'mainEntity.*.acceptedAnswer' => 'required|array',
                'mainEntity.*.acceptedAnswer.@type' => 'required|in:Answer',
                'mainEntity.*.acceptedAnswer.text' => 'required|string',
            ],
            'required_properties' => ['name', 'mainEntity'],
            'optional_properties' => ['description', 'breadcrumb', 'keywords'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Schema Properties
    |--------------------------------------------------------------------------
    |
    | These properties are automatically included in all schema types
    | and don't need to be specified in individual type configurations.
    |
    */

    'default_properties' => [
        '@context' => 'https://schema.org',
        'datePublished',
        'dateModified',
        'author',
        'publisher',
        'breadcrumb',
        'keywords',
        'inLanguage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Global validation rules that apply to all schema types.
    |
    */

    'global_validation' => [
        'schema_type' => 'required|string',
        'schema_data' => 'nullable|array',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-generation Settings
    |--------------------------------------------------------------------------
    |
    | Configure which properties should be automatically generated
    | when not explicitly provided.
    |
    */

    'auto_generate' => [
        'name' => 'title',           // Use page title as schema name
        'description' => 'excerpt',  // Use page excerpt as schema description
        'url' => 'computed',         // Generate from route
        'wordCount' => 'computed',   // Calculate from content
        'inLanguage' => 'app.locale', // Use app locale
    ],
];