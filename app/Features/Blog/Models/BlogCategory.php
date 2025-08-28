<?php

namespace App\Features\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'meta_title',
        'meta_description',
        'schema_data',
        'sort_order',
        'is_active',
        'posts_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'schema_data' => 'array',
        'sort_order' => 'integer',
        'posts_count' => 'integer',
    ];


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->getOriginal('slug'))) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the posts for the category.
     */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    /**
     * Get published posts for the category.
     */
    public function publishedPosts()
    {
        return $this->hasMany(BlogPost::class)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Update the posts count for this category.
     */
    public function updatePostsCount()
    {
        $this->posts_count = $this->publishedPosts()->count();
        $this->save();
    }

    /**
     * Get the category's color as a CSS variable.
     */
    public function getCssColorAttribute()
    {
        return "color: {$this->color}; background-color: {$this->color}20;";
    }

    /**
     * Get the category's URL.
     */
    public function getUrlAttribute(): string
    {
        return route('blog.categories.show', $this->slug);
    }

    /**
     * Check if the category has any published posts.
     */
    public function hasPublishedPosts(): bool
    {
        return $this->publishedPosts()->exists();
    }
}