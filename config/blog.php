<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Feature Status
    |--------------------------------------------------------------------------
    |
    | Controls whether the blog feature is enabled. When disabled, all blog
    | routes return 404 and blog functionality is completely disabled.
    |
    */
    'enabled' => env('BLOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Blog Features
    |--------------------------------------------------------------------------
    |
    | Individual blog features that can be enabled/disabled independently.
    | This allows for gradual rollout and customization per installation.
    |
    */
    'features' => [
        'comments' => env('BLOG_COMMENTS_ENABLED', true),
        'categories' => env('BLOG_CATEGORIES_ENABLED', true),
        'tags' => env('BLOG_TAGS_ENABLED', true),
        'seo' => env('BLOG_SEO_ENABLED', true),
        'featured_images' => env('BLOG_FEATURED_IMAGES_ENABLED', true),
        'reading_time' => env('BLOG_READING_TIME_ENABLED', true),
        'view_counts' => env('BLOG_VIEW_COUNTS_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Settings
    |--------------------------------------------------------------------------
    |
    | General blog configuration and defaults.
    |
    */
    'settings' => [
        'posts_per_page' => env('BLOG_POSTS_PER_PAGE', 12),
        'excerpt_length' => env('BLOG_EXCERPT_LENGTH', 160),
        'reading_words_per_minute' => env('BLOG_READING_WPM', 200),
        'comment_moderation' => env('BLOG_COMMENT_MODERATION', true),
        'auto_publish' => env('BLOG_AUTO_PUBLISH', false),
        'allow_guest_comments' => env('BLOG_GUEST_COMMENTS', false),
        'default_post_status' => env('BLOG_DEFAULT_STATUS', 'draft'),
        'featured_posts_limit' => env('BLOG_FEATURED_LIMIT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Configuration
    |--------------------------------------------------------------------------
    |
    | Configure available schema types for blog posts. This affects the
    | admin interface dropdown and validation rules.
    |
    */
    'schema' => [
        'available_types' => [
            'BlogPosting' => 'Blog Post (Default)',
            'Article' => 'Article', 
            'NewsArticle' => 'News Article',
            'Review' => 'Review',
            'HowTo' => 'How-To Guide',
            'FAQPage' => 'FAQ Page',
        ],
        'default_type' => 'BlogPosting',
        'descriptions' => [
            'BlogPosting' => 'Standard blog content with author, date, and engagement metrics',
            'Article' => 'Editorial content like journalism, research, or in-depth analysis',
            'NewsArticle' => 'Time-sensitive news content with journalistic standards',
            'Review' => 'Product, service, or media reviews with ratings and opinions',
            'HowTo' => 'Step-by-step tutorials and instructional content',
            'FAQPage' => 'Question and answer format content',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Mapping Configuration
    |--------------------------------------------------------------------------
    |
    | Maps schema types to content optimization settings. Used by the
    | ContentOptimizationService for AI-driven SEO and AEO optimization.
    |
    */
    'schema_mapping' => [
        'BlogPosting' => [
            'content_type' => 'blog_post',
            'keyword_focus' => 'broad',
            'requirements' => ['author', 'datePublished'],
            'optional_faqs' => false
        ],
        'Article' => [
            'content_type' => 'article',
            'keyword_focus' => 'authoritative',
            'requirements' => ['author', 'datePublished', 'publisher'],
            'optional_faqs' => false
        ],
        'NewsArticle' => [
            'content_type' => 'news',
            'keyword_focus' => 'timely',
            'requirements' => ['author', 'datePublished', 'publisher', 'location'],
            'optional_faqs' => false
        ],
        'Review' => [
            'content_type' => 'review',
            'keyword_focus' => 'product',
            'requirements' => ['author', 'reviewBody', 'reviewRating'],
            'optional_faqs' => true
        ],
        'HowTo' => [
            'content_type' => 'tutorial',
            'keyword_focus' => 'instructional',
            'requirements' => ['name', 'step', 'totalTime'],
            'optional_faqs' => true
        ],
        'FAQPage' => [
            'content_type' => 'faq',
            'keyword_focus' => 'question-based',
            'requirements' => ['mainEntity'],
            'optional_faqs' => true // Required for this schema
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Permissions
    |--------------------------------------------------------------------------
    |
    | Define which roles have access to specific blog functionality.
    | Integrates with existing Spatie Permission system.
    |
    */
    'permissions' => [
        'view_blog_admin' => ['Admin', 'Super Admin'],
        'create_posts' => ['Admin', 'Super Admin'],
        'edit_posts' => ['Admin', 'Super Admin'],
        'delete_posts' => ['Super Admin'],
        'publish_posts' => ['Admin', 'Super Admin'],
        'manage_categories' => ['Admin', 'Super Admin'],
        'manage_tags' => ['Admin', 'Super Admin'],
        'moderate_comments' => ['Admin', 'Super Admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog SEO Configuration
    |--------------------------------------------------------------------------
    |
    | SEO-related settings that integrate with the existing Page SEO system.
    |
    */
    'seo' => [
        'default_schema_type' => 'BlogPosting',
        'generate_sitemap' => env('BLOG_GENERATE_SITEMAP', true),
        'sitemap_priority' => env('BLOG_SITEMAP_PRIORITY', 0.8),
        'sitemap_change_frequency' => env('BLOG_SITEMAP_FREQ', 'weekly'),
        'meta_title_suffix' => env('BLOG_META_TITLE_SUFFIX', ' - Blog'),
        'social_sharing' => env('BLOG_SOCIAL_SHARING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog URL Structure
    |--------------------------------------------------------------------------
    |
    | Configure URL patterns for blog routes.
    |
    */
    'urls' => [
        'prefix' => env('BLOG_URL_PREFIX', 'blog'),
        'category_prefix' => env('BLOG_CATEGORY_PREFIX', 'category'),
        'tag_prefix' => env('BLOG_TAG_PREFIX', 'tag'),
        'author_prefix' => env('BLOG_AUTHOR_PREFIX', 'author'),
        'archive_prefix' => env('BLOG_ARCHIVE_PREFIX', 'archive'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Storage Configuration
    |--------------------------------------------------------------------------
    |
    | File storage settings for blog assets.
    |
    */
    'storage' => [
        'featured_images_disk' => env('BLOG_IMAGES_DISK', 'public'),
        'featured_images_path' => env('BLOG_IMAGES_PATH', 'blog/featured'),
        'max_image_size' => env('BLOG_MAX_IMAGE_SIZE', 2048), // KB
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Caching settings for blog performance optimization.
    |
    */
    'cache' => [
        'enabled' => env('BLOG_CACHE_ENABLED', true),
        'ttl' => env('BLOG_CACHE_TTL', 3600), // 1 hour
        'tags' => [
            'blog_posts' => 'blog.posts',
            'blog_categories' => 'blog.categories',
            'blog_tags' => 'blog.tags',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validation constraints for blog content.
    |
    */
    'validation' => [
        'title_max_length' => 255,
        'slug_max_length' => 255,
        'excerpt_max_length' => 500,
        'content_max_length' => 65535,
        'meta_title_max_length' => 60,
        'meta_description_max_length' => 160,
        'category_name_max_length' => 100,
        'tag_name_max_length' => 50,
    ],
];