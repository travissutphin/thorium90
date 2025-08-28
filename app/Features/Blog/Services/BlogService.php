<?php

namespace App\Features\Blog\Services;

use App\Features\Blog\Models\BlogPost;
use App\Features\Blog\Models\BlogCategory;
use App\Features\Blog\Models\BlogTag;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BlogService
{
    /**
     * Get published blog posts with caching.
     */
    public function getPublishedPosts(int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('blog.settings.posts_per_page', 12);
        
        return BlogPost::published()
            ->with(['blogCategory', 'user', 'blogTags'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get featured blog posts.
     */
    public function getFeaturedPosts(int $limit = null): Collection
    {
        $limit = $limit ?? config('blog.settings.featured_posts_limit', 5);
        
        $cacheKey = "blog.featured_posts.{$limit}";
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() use ($limit) {
            return BlogPost::published()
                ->featured()
                ->with(['blogCategory', 'user'])
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get posts by category.
     */
    public function getPostsByCategory(BlogCategory $category, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('blog.settings.posts_per_page', 12);
        
        return $category->publishedPosts()
            ->with(['blogCategory', 'user', 'blogTags'])
            ->paginate($perPage);
    }

    /**
     * Get posts by tag.
     */
    public function getPostsByTag(BlogTag $tag, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('blog.settings.posts_per_page', 12);
        
        return $tag->publishedPosts()
            ->with(['blogCategory', 'user', 'blogTags'])
            ->paginate($perPage);
    }

    /**
     * Search blog posts.
     */
    public function searchPosts(string $query, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('blog.settings.posts_per_page', 12);
        
        return BlogPost::published()
            ->search($query)
            ->with(['blogCategory', 'user', 'blogTags'])
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get popular posts based on view count.
     */
    public function getPopularPosts(int $limit = 5): Collection
    {
        if (!config('blog.features.view_counts')) {
            return collect();
        }

        $cacheKey = "blog.popular_posts.{$limit}";
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() use ($limit) {
            return BlogPost::published()
                ->popular()
                ->with(['blogCategory', 'user'])
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get recent posts.
     */
    public function getRecentPosts(int $limit = 5): Collection
    {
        $cacheKey = "blog.recent_posts.{$limit}";
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() use ($limit) {
            return BlogPost::published()
                ->with(['blogCategory', 'user'])
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get all active categories with post counts.
     */
    public function getActiveCategories(): Collection
    {
        $cacheKey = 'blog.active_categories';
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() {
            return BlogCategory::active()
                ->ordered()
                ->where('posts_count', '>', 0)
                ->get();
        });
    }

    /**
     * Get popular tags.
     */
    public function getPopularTags(int $limit = 20): Collection
    {
        $cacheKey = "blog.popular_tags.{$limit}";
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() use ($limit) {
            return BlogTag::withPosts()
                ->popular()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get blog archive by year/month.
     */
    public function getArchiveData(): Collection
    {
        $cacheKey = 'blog.archive_data';
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() {
            return BlogPost::published()
                ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->groupBy('year');
        });
    }

    /**
     * Get blog statistics.
     */
    public function getBlogStats(): array
    {
        $cacheKey = 'blog.stats';
        
        return Cache::remember($cacheKey, config('blog.cache.ttl', 3600), function() {
            return [
                'total_posts' => BlogPost::published()->count(),
                'total_categories' => BlogCategory::active()->count(),
                'total_tags' => BlogTag::withPosts()->count(),
                'total_views' => config('blog.features.view_counts') ? BlogPost::sum('view_count') : 0,
                'total_comments' => config('blog.features.comments') ? BlogPost::sum('comment_count') : 0,
            ];
        });
    }

    /**
     * Clear blog-related caches.
     */
    public function clearCache(): void
    {
        $tags = config('blog.cache.tags', []);
        
        // Check if current cache driver supports tagging
        $supportsTags = method_exists(Cache::getStore(), 'tags');
        
        if (!$supportsTags || empty($tags)) {
            // Clear specific cache keys if tags not supported or configured
            $keys = [
                'blog.featured_posts.5',
                'blog.featured_posts.10',
                'blog.popular_posts.5',
                'blog.popular_posts.10',
                'blog.recent_posts.5',
                'blog.recent_posts.10',
                'blog.active_categories',
                'blog.popular_tags.10',
                'blog.popular_tags.20',
                'blog.archive_data',
                'blog.stats',
            ];
            
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            // Clear by cache tags if supported
            foreach ($tags as $tag) {
                Cache::tags($tag)->flush();
            }
        }
    }

    /**
     * Check if a feature is enabled.
     */
    public function isFeatureEnabled(string $feature): bool
    {
        return config("blog.features.{$feature}", false);
    }

    /**
     * Get blog configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return config("blog.{$key}", $default);
    }
}