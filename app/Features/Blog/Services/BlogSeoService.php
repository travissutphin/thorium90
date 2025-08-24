<?php

namespace App\Features\Blog\Services;

use App\Features\Blog\Models\BlogPost;
use App\Features\Blog\Models\BlogCategory;
use App\Features\Blog\Models\BlogTag;
use Illuminate\Support\Collection;

class BlogSeoService
{
    /**
     * Generate sitemap data for blog content.
     */
    public function generateSitemapData(): array
    {
        if (!config('blog.seo.generate_sitemap', true)) {
            return [];
        }

        $data = [];
        $priority = config('blog.seo.sitemap_priority', 0.8);
        $changefreq = config('blog.seo.sitemap_change_frequency', 'weekly');

        // Add blog index page
        $data[] = [
            'url' => route('blog.index'),
            'lastmod' => BlogPost::published()->latest('published_at')->value('published_at'),
            'changefreq' => 'daily',
            'priority' => $priority,
        ];

        // Add blog posts
        BlogPost::published()
            ->select(['slug', 'published_at', 'updated_at'])
            ->chunk(100, function ($posts) use (&$data, $priority, $changefreq) {
                foreach ($posts as $post) {
                    $data[] = [
                        'url' => route('blog.posts.show', $post->slug),
                        'lastmod' => $post->updated_at,
                        'changefreq' => $changefreq,
                        'priority' => $priority,
                    ];
                }
            });

        // Add blog categories
        if (config('blog.features.categories')) {
            BlogCategory::active()
                ->where('posts_count', '>', 0)
                ->select(['slug', 'updated_at'])
                ->chunk(50, function ($categories) use (&$data, $priority, $changefreq) {
                    foreach ($categories as $category) {
                        $data[] = [
                            'url' => route('blog.categories.show', $category->slug),
                            'lastmod' => $category->updated_at,
                            'changefreq' => $changefreq,
                            'priority' => $priority - 0.1,
                        ];
                    }
                });
        }

        // Add blog tags
        if (config('blog.features.tags')) {
            BlogTag::withPosts()
                ->select(['slug', 'updated_at'])
                ->chunk(50, function ($tags) use (&$data, $priority, $changefreq) {
                    foreach ($tags as $tag) {
                        $data[] = [
                            'url' => route('blog.tags.show', $tag->slug),
                            'lastmod' => $tag->updated_at,
                            'changefreq' => $changefreq,
                            'priority' => $priority - 0.2,
                        ];
                    }
                });
        }

        return $data;
    }

    /**
     * Generate blog breadcrumbs for a given context.
     */
    public function generateBreadcrumbs($context, $item = null): array
    {
        $breadcrumbs = [
            [
                'title' => 'Home',
                'url' => route('home'),
            ],
            [
                'title' => 'Blog',
                'url' => route('blog.index'),
            ],
        ];

        switch ($context) {
            case 'post':
                if ($item instanceof BlogPost) {
                    if ($item->blogCategory) {
                        $breadcrumbs[] = [
                            'title' => $item->blogCategory->name,
                            'url' => route('blog.categories.show', $item->blogCategory->slug),
                        ];
                    }
                    $breadcrumbs[] = [
                        'title' => $item->title,
                        'url' => null, // Current page
                    ];
                }
                break;

            case 'category':
                if ($item instanceof BlogCategory) {
                    $breadcrumbs[] = [
                        'title' => $item->name,
                        'url' => null, // Current page
                    ];
                }
                break;

            case 'tag':
                if ($item instanceof BlogTag) {
                    $breadcrumbs[] = [
                        'title' => "Tagged: {$item->name}",
                        'url' => null, // Current page
                    ];
                }
                break;
        }

        return $breadcrumbs;
    }

    /**
     * Generate structured data for blog listing pages.
     */
    public function generateListingSchema($posts, $context = 'blog'): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $this->getListingTitle($context),
            'description' => $this->getListingDescription($context),
            'url' => url()->current(),
            'numberOfItems' => $posts->count(),
            'itemListElement' => [],
        ];

        foreach ($posts as $index => $post) {
            $schema['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => $post->url,
                'name' => $post->title,
            ];
        }

        return $schema;
    }

    /**
     * Generate Open Graph meta tags for blog content.
     */
    public function generateOpenGraphMeta($item, $context = 'post'): array
    {
        $meta = [
            'og:site_name' => config('app.name'),
            'og:type' => 'article',
            'twitter:card' => 'summary_large_image',
        ];

        if ($item instanceof BlogPost) {
            $meta = array_merge($meta, [
                'og:title' => $item->meta_title ?: $item->title,
                'og:description' => $item->meta_description ?: $item->excerpt,
                'og:url' => $item->url,
                'og:published_time' => $item->published_at?->toISOString(),
                'og:modified_time' => $item->updated_at->toISOString(),
                'article:author' => $item->user->name,
                'article:section' => $item->blogCategory?->name,
                'article:published_time' => $item->published_at?->toISOString(),
                'article:modified_time' => $item->updated_at->toISOString(),
            ]);

            // Add featured image if available
            if ($item->featured_image) {
                $imageUrl = asset('storage/' . $item->featured_image);
                $meta = array_merge($meta, [
                    'og:image' => $imageUrl,
                    'og:image:alt' => $item->featured_image_alt ?: $item->title,
                    'twitter:image' => $imageUrl,
                ]);
            }

            // Add tags as keywords
            if ($item->blogTags->count() > 0) {
                $meta['article:tag'] = $item->blogTags->pluck('name')->implode(', ');
            }

        } elseif ($item instanceof BlogCategory) {
            $meta = array_merge($meta, [
                'og:title' => $item->meta_title ?: $item->name,
                'og:description' => $item->meta_description ?: $item->description,
                'og:url' => $item->url,
                'og:type' => 'website',
            ]);

        } elseif ($item instanceof BlogTag) {
            $meta = array_merge($meta, [
                'og:title' => "Posts tagged with: {$item->name}",
                'og:description' => $item->description ?: "All posts tagged with {$item->name}",
                'og:url' => $item->url,
                'og:type' => 'website',
            ]);
        }

        return $meta;
    }

    /**
     * Get SEO-optimized title for listing pages.
     */
    protected function getListingTitle(string $context): string
    {
        return match ($context) {
            'blog' => 'Latest Blog Posts',
            'category' => 'Posts by Category',
            'tag' => 'Posts by Tag',
            'archive' => 'Blog Archive',
            'search' => 'Search Results',
            default => 'Blog Posts',
        };
    }

    /**
     * Get SEO-optimized description for listing pages.
     */
    protected function getListingDescription(string $context): string
    {
        $siteName = config('app.name');
        
        return match ($context) {
            'blog' => "Discover the latest blog posts from {$siteName}. Stay updated with our insights, tips, and stories.",
            'category' => "Browse blog posts organized by category to find content that interests you most.",
            'tag' => "Explore blog posts filtered by specific tags and topics.",
            'archive' => "Browse our complete blog archive organized by date.",
            'search' => "Search results for blog posts and articles.",
            default => "Blog posts and articles from {$siteName}.",
        };
    }

    /**
     * Optimize blog post content for SEO.
     */
    public function optimizePostContent(BlogPost $post): array
    {
        $suggestions = [];

        // Check title length
        $titleLength = strlen($post->title);
        if ($titleLength > 60) {
            $suggestions[] = "Title is {$titleLength} characters. Consider shortening to under 60 characters for better SEO.";
        } elseif ($titleLength < 30) {
            $suggestions[] = "Title is {$titleLength} characters. Consider expanding to 30-60 characters for better SEO.";
        }

        // Check meta description
        $metaDescLength = strlen($post->meta_description ?? '');
        if ($metaDescLength > 160) {
            $suggestions[] = "Meta description is {$metaDescLength} characters. Consider shortening to under 160 characters.";
        } elseif ($metaDescLength < 120) {
            $suggestions[] = "Meta description is {$metaDescLength} characters. Consider expanding to 120-160 characters.";
        }

        // Check content length
        $wordCount = str_word_count(strip_tags($post->content ?? ''));
        if ($wordCount < 300) {
            $suggestions[] = "Content has {$wordCount} words. Consider expanding to at least 300 words for better SEO.";
        }

        // Check for featured image
        if (!$post->featured_image) {
            $suggestions[] = "Consider adding a featured image to improve social media sharing and SEO.";
        }

        // Check for alt text on featured image
        if ($post->featured_image && !$post->featured_image_alt) {
            $suggestions[] = "Add alt text to the featured image for better accessibility and SEO.";
        }

        return $suggestions;
    }
}