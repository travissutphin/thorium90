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
        // Return a default URL if slug is not set (for new models)
        if (empty($this->slug)) {
            return config('app.url') . '/blog';
        }
        
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
        
        try {
            return route($routeName, $this->slug);
        } catch (\Exception $e) {
            // Fallback URL if route generation fails
            return config('app.url') . '/blog/' . $this->slug;
        }
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
        
        // Main entity of page (best practice for BlogPosting)
        $schema['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => $this->url,
        ];
        
        // Enhanced author information
        if (isset($this->user)) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $this->user->name,
                'url' => config('app.url'),
            ];
            
            // Add author bio if available
            if (isset($this->user->bio)) {
                $schema['author']['description'] = $this->user->bio;
            }
        }
        
        // Enhanced publisher information with logo
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
        ];
        
        // Add publisher logo if available
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $schema['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url' => asset('images/logo.png'),
                'width' => 600,
                'height' => 60,
            ];
        }

        // Content-based schema enhancements (applies to all content types)
        $contentBasedTypes = ['BlogPosting', 'Article', 'NewsArticle', 'Review', 'HowTo'];
        if (in_array($this->schema_type, $contentBasedTypes)) {
            if (!isset($schema['headline'])) {
                $schema['headline'] = $this->title;
            }
            if (!isset($schema['articleBody'])) {
                $schema['articleBody'] = strip_tags($this->content ?? '');
            }
            
            // Word count for content analysis
            $schema['wordCount'] = str_word_count(strip_tags($this->content ?? ''));
            
            // Blog category as articleSection
            if (isset($this->blogCategory)) {
                $schema['articleSection'] = $this->blogCategory->name;
                
                // Add genre based on category
                $schema['genre'] = $this->blogCategory->name;
            }
            
            // Blog tags as keywords and tags array
            if (isset($this->blogTags) && $this->blogTags->count() > 0) {
                $tagNames = $this->blogTags->pluck('name')->toArray();
                $schema['keywords'] = implode(', ', $tagNames);
                $schema['tags'] = $tagNames;
            }
            
            // Reading time in ISO duration format
            if ($this->reading_time) {
                $schema['timeRequired'] = "PT{$this->reading_time}M";
            }
            
            // Content language
            $schema['inLanguage'] = config('app.locale', 'en');
        }

        // Enhanced keywords and topics
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

        // Enhanced featured image with dimensions
        if (!empty($this->featured_image)) {
            $imagePath = public_path('storage/' . $this->featured_image);
            $imageInfo = file_exists($imagePath) ? getimagesize($imagePath) : null;
            
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => asset('storage/' . $this->featured_image),
                'description' => $this->featured_image_alt ?? $this->title,
            ];
            
            // Add image dimensions if available
            if ($imageInfo) {
                $schema['image']['width'] = $imageInfo[0];
                $schema['image']['height'] = $imageInfo[1];
            }
        }

        // Engagement metrics (if enabled)
        if (config('blog.features.view_counts') && $this->view_count) {
            $schema['interactionStatistic'] = [
                '@type' => 'InteractionCounter',
                'interactionType' => 'https://schema.org/ViewAction',
                'userInteractionCount' => $this->view_count,
            ];
        }
        
        // Content tier for premium content detection
        if (isset($this->is_premium) && $this->is_premium) {
            $schema['isAccessibleForFree'] = false;
        } else {
            $schema['isAccessibleForFree'] = true;
        }

        // Content rating if available
        if (config('blog.features.content_rating') && isset($this->content_rating)) {
            $schema['contentRating'] = $this->content_rating;
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