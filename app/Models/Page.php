<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory, SoftDeletes;

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
        'template',
        'layout',
        'theme',
        'blocks',
        'template_config',
        'user_id',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'schema_data' => 'array',
        'blocks' => 'array',
        'template_config' => 'array',
        'published_at' => 'datetime',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
            
            if (empty($page->meta_title)) {
                $page->meta_title = $page->title;
            }
            
            if (empty($page->excerpt) && !empty($page->content)) {
                $page->excerpt = Str::limit(strip_tags($page->content), 160);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->getOriginal('slug'))) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Get the user that owns the page.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured pages.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the page's URL.
     */
    public function getUrlAttribute()
    {
        return route('pages.show', $this->slug);
    }

    /**
     * Get the page's full meta title.
     */
    public function getFullMetaTitleAttribute()
    {
        $siteTitle = config('app.name', 'Laravel');
        return $this->meta_title ? "{$this->meta_title} | {$siteTitle}" : "{$this->title} | {$siteTitle}";
    }

    /**
     * Get the page's meta description or generate one.
     */
    public function getMetaDescriptionAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->excerpt) {
            return Str::limit($this->excerpt, 160);
        }

        return Str::limit(strip_tags($this->content), 160);
    }

    /**
     * Generate schema.org structured data.
     */
    public function getSchemaDataAttribute($value)
    {
        if ($value) {
            return $value;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->schema_type ?: 'WebPage',
            'name' => $this->title,
            'description' => $this->meta_description,
            'url' => $this->url,
            'datePublished' => $this->published_at?->toISOString(),
            'dateModified' => $this->updated_at->toISOString(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->user?->name,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => config('app.url'),
            ],
        ];

        if ($this->schema_type === 'Article') {
            $schema['@type'] = 'Article';
            $schema['headline'] = $this->title;
            $schema['articleBody'] = strip_tags($this->content);
            $schema['wordCount'] = str_word_count(strip_tags($this->content));
        }

        return $schema;
    }

    /**
     * Get the page's reading time in minutes.
     */
    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, round($wordCount / 200)); // Average reading speed: 200 words per minute
    }

    /**
     * Check if the page is published.
     */
    public function isPublished()
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Publish the page.
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the page.
     */
    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
