<?php

namespace App\Features\Blog\Models;

use App\Features\Blog\Traits\BlogSeoTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes, BlogSeoTrait;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'is_featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'schema_type',
        'schema_data',
        'blog_category_id',
        'user_id',
        'featured_image',
        'featured_image_alt',
        'topics',
        'keywords',
        'faq_data',
        'reading_time',
        'content_type',
        'content_score',
        'view_count',
        'like_count',
        'comment_count',
        'share_count',
        'published_at',
        // Unified SEO fields
        'seo_keywords',
        'enhanced_tags',
        'optimization_data',
        'ai_optimized_at',
        'ai_model_used',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'schema_data' => 'array',
        'topics' => 'array',
        'keywords' => 'array',
        'faq_data' => 'array',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'share_count' => 'integer',
        'published_at' => 'datetime',
        // Unified SEO field casts
        'seo_keywords' => 'array',
        'enhanced_tags' => 'array',
        'optimization_data' => 'array',
        'ai_optimized_at' => 'datetime',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    protected $attributes = [
        'schema_type' => 'BlogPosting',
        'content_type' => 'blog_post',
    ];

    /**
     * Get the user that authored the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that the post belongs to.
     */
    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class);
    }

    /**
     * Get the tags associated with the post.
     */
    public function blogTags()
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags');
    }

    /**
     * Get the comments for the post.
     */
    public function blogComments()
    {
        if (!config('blog.features.comments')) {
            return collect();
        }

        return $this->hasMany(BlogComment::class)->whereNull('parent_id');
    }

    /**
     * Get all comments (including replies) for the post.
     */
    public function allBlogComments()
    {
        if (!config('blog.features.comments')) {
            return collect();
        }

        return $this->hasMany(BlogComment::class);
    }

    /**
     * Get approved comments for the post.
     */
    public function approvedComments()
    {
        if (!config('blog.features.comments')) {
            return collect();
        }

        return $this->hasMany(BlogComment::class)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include posts in a specific category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('blog_category_id', $categoryId);
    }

    /**
     * Scope a query to only include posts with specific tags.
     */
    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('blogTags', function ($q) use ($tagId) {
            $q->where('blog_tag_id', $tagId);
        });
    }

    /**
     * Scope a query to order by most popular (view count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Scope a query to search posts by title or content.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Publish the post.
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Update category posts count
        if ($this->blogCategory) {
            $this->blogCategory->updatePostsCount();
        }

        // Update tags usage count
        foreach ($this->blogTags as $tag) {
            $tag->incrementUsage();
        }
    }

    /**
     * Unpublish the post.
     */
    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Update category posts count
        if ($this->blogCategory) {
            $this->blogCategory->updatePostsCount();
        }

        // Update tags usage count
        foreach ($this->blogTags as $tag) {
            $tag->decrementUsage();
        }
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        if (config('blog.features.view_counts')) {
            $this->increment('view_count');
        }
    }

    /**
     * Get the next published post.
     */
    public function nextPost()
    {
        return self::published()
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }

    /**
     * Get the previous published post.
     */
    public function previousPost()
    {
        return self::published()
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    /**
     * Get related posts based on category and tags.
     */
    public function relatedPosts($limit = 3)
    {
        return self::published()
            ->where('id', '!=', $this->id)
            ->where(function ($query) {
                // Posts in same category
                $query->where('blog_category_id', $this->blog_category_id);
                
                // Or posts with similar tags
                if ($this->blogTags->count() > 0) {
                    $query->orWhereHas('blogTags', function ($q) {
                        $q->whereIn('blog_tag_id', $this->blogTags->pluck('id'));
                    });
                }
            })
            ->with(['blogCategory', 'user', 'blogTags']) // Eager load to prevent N+1 queries
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Update comment count cache.
     */
    public function updateCommentCount()
    {
        if (config('blog.features.comments')) {
            $this->comment_count = $this->approvedComments()->count();
            $this->save();
        }
    }

    /**
     * Sync tags and update usage counts.
     */
    public function syncTagsWithUsageCount(array $tagIds)
    {
        // Get current tags to decrement usage
        $currentTags = $this->blogTags;
        
        // Sync new tags
        $this->blogTags()->sync($tagIds);
        
        // Update usage counts
        foreach ($currentTags as $tag) {
            $tag->updateUsageCount();
        }
        
        foreach (BlogTag::whereIn('id', $tagIds)->get() as $tag) {
            $tag->updateUsageCount();
        }
    }
}