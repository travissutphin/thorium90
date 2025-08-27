<?php

namespace App\Features\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];


    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->getOriginal('slug'))) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get the posts that have this tag.
     */
    public function blogPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags');
    }

    /**
     * Get published posts that have this tag.
     */
    public function publishedPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    /**
     * Scope a query to order by popularity (usage count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('usage_count', 'desc');
    }

    /**
     * Scope a query to only include tags with posts.
     */
    public function scopeWithPosts($query)
    {
        return $query->where('usage_count', '>', 0);
    }

    /**
     * Update the usage count for this tag.
     */
    public function updateUsageCount()
    {
        $this->usage_count = $this->publishedPosts()->count();
        $this->save();
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement the usage count.
     */
    public function decrementUsage()
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    /**
     * Get the tag's color as a CSS class.
     */
    public function getCssColorAttribute()
    {
        return "color: {$this->color}; background-color: {$this->color}20;";
    }

    /**
     * Get the tag's URL.
     */
    public function getUrlAttribute(): string
    {
        return route('blog.tags.show', $this->slug);
    }

    /**
     * Find or create a tag by name.
     */
    public static function findOrCreateByName(string $name): self
    {
        $slug = Str::slug($name);
        
        return self::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'slug' => $slug,
            ]
        );
    }
}