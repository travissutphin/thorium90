<?php

namespace App\Features\Blog\Traits;

use Illuminate\Support\Str;

trait BlogSeoTrait
{
    /**
     * Boot the trait.
     */
    protected static function bootBlogSeoTrait()
    {
        static::creating(function ($model) {
            // Auto-generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
            
            // Auto-generate meta title if not provided
            if (empty($model->meta_title)) {
                $model->meta_title = $model->title;
            }
            
            // Auto-generate excerpt if not provided and content exists
            if (empty($model->excerpt) && !empty($model->content)) {
                $model->excerpt = Str::limit(strip_tags($model->content), config('blog.settings.excerpt_length', 160));
            }
            
            // Auto-calculate reading time if not manually set
            if (is_null($model->reading_time) && !empty($model->content)) {
                $model->reading_time = $model->calculateReadingTime();
            }
        });

        static::updating(function ($model) {
            // Regenerate slug if title changed and slug wasn't manually set
            if ($model->isDirty('title') && empty($model->getOriginal('slug'))) {
                $model->slug = Str::slug($model->title);
            }
            
            // Recalculate reading time if content changed and reading_time wasn't manually set
            if ($model->isDirty('content') && is_null($model->reading_time)) {
                $model->reading_time = $model->calculateReadingTime();
            }
            
            // Update excerpt if content changed and excerpt wasn't manually set
            if ($model->isDirty('content') && empty($model->getOriginal('excerpt'))) {
                $model->excerpt = Str::limit(strip_tags($model->content), config('blog.settings.excerpt_length', 160));
            }
        });
    }

    /**
     * Calculate reading time in minutes based on content length.
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $wordsPerMinute = config('blog.settings.reading_words_per_minute', 200);
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Get the model's URL.
     */
    public function getUrlAttribute(): string
    {
        if (isset($this->blog_post_id)) {
            // This is for comments or other related models
            return route('blog.posts.show', $this->blogPost->slug);
        }
        
        // For posts, categories, tags
        $routeName = match (class_basename($this)) {
            'BlogPost' => 'blog.posts.show',
            'BlogCategory' => 'blog.categories.show',
            'BlogTag' => 'blog.tags.show',
            default => 'blog.posts.show'
        };
        
        return route($routeName, $this->slug);
    }

    /**
     * Get the model's full meta title.
     */
    public function getFullMetaTitleAttribute(): string
    {
        $siteTitle = config('app.name', 'Laravel');
        $blogSuffix = config('blog.seo.meta_title_suffix', ' - Blog');
        
        return $this->meta_title 
            ? "{$this->meta_title}{$blogSuffix} | {$siteTitle}" 
            : "{$this->title}{$blogSuffix} | {$siteTitle}";
    }

    /**
     * Get or generate meta description.
     */
    public function getMetaDescriptionAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        if ($this->excerpt) {
            return Str::limit($this->excerpt, 160);
        }

        return Str::limit(strip_tags($this->content ?? ''), 160);
    }

    /**
     * Generate schema.org structured data for blog content.
     */
    public function getSchemaDataAttribute($value): ?array
    {
        // If schema_data is already set, use it
        if (!empty($value)) {
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            if (is_array($value) && !empty($value)) {
                return $this->enhanceBlogSchemaData($value);
            }
        }

        // Generate default schema data based on schema_type
        if (empty($this->schema_type)) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->schema_type,
            'name' => $this->title,
            'description' => $this->meta_description ?? $this->excerpt,
        ];

        return $this->enhanceBlogSchemaData($schema);
    }

    /**
     * Enhance schema data with blog-specific computed values.
     */
    protected function enhanceBlogSchemaData(array $schema): array
    {
        // Always ensure these computed properties are up to date
        $schema['@context'] = 'https://schema.org';
        $schema['@type'] = $this->schema_type ?: 'BlogPosting';
        $schema['url'] = $this->url;
        $schema['datePublished'] = $this->published_at?->toISOString();
        $schema['dateModified'] = $this->updated_at->toISOString();
        
        // Author information
        if (isset($this->user)) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $this->user->name,
            ];
        }
        
        // Publisher information
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
        ];

        // Blog-specific enhancements
        if ($this->schema_type === 'BlogPosting' || $this->schema_type === 'Article') {
            if (!isset($schema['headline'])) {
                $schema['headline'] = $this->title;
            }
            if (!isset($schema['articleBody'])) {
                $schema['articleBody'] = strip_tags($this->content ?? '');
            }
            
            // Word count for content analysis
            $schema['wordCount'] = str_word_count(strip_tags($this->content ?? ''));
            
            // Blog category
            if (isset($this->blogCategory)) {
                $schema['articleSection'] = $this->blogCategory->name;
            }
            
            // Reading time
            if ($this->reading_time) {
                $schema['timeRequired'] = "PT{$this->reading_time}M";
            }
        }

        // Keywords and topics
        if (!isset($schema['keywords']) && $this->keywords) {
            $schema['keywords'] = is_array($this->keywords) ? implode(', ', $this->keywords) : $this->keywords;
        }
        
        if ($this->topics && is_array($this->topics)) {
            $schema['about'] = array_map(function($topic) {
                return [
                    '@type' => 'Thing',
                    'name' => $topic,
                ];
            }, $this->topics);
        }

        // Featured image
        if (!empty($this->featured_image)) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => asset('storage/' . $this->featured_image),
                'description' => $this->featured_image_alt ?? $this->title,
            ];
        }

        // Engagement metrics (if enabled)
        if (config('blog.features.view_counts')) {
            $schema['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/ViewAction',
                'userInteractionCount' => $this->view_count ?? 0,
            ];
        }

        return $schema;
    }

    /**
     * Validate and set schema data.
     */
    public function setSchemaDataAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['schema_data'] = null;
            return;
        }

        // For blog models, we'll store the data directly for now
        // Later we can add validation similar to the Page model
        $this->attributes['schema_data'] = is_string($value) ? $value : json_encode($value);
    }
}