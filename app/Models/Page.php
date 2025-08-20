<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Services\SchemaValidationService;

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
        // AEO Enhancement fields
        'topics',
        'keywords',
        'faq_data',
        'reading_time',
        'content_type',
        'content_score',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'schema_data' => 'array',
        'blocks' => 'array',
        'template_config' => 'array',
        'published_at' => 'datetime',
        // AEO Enhancement casts
        'topics' => 'array',
        'keywords' => 'array',
        'faq_data' => 'array',
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
            
            // Auto-calculate reading time if not manually set
            if (is_null($page->reading_time)) {
                $page->reading_time = $page->calculateReadingTime();
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->getOriginal('slug'))) {
                $page->slug = Str::slug($page->title);
            }
            
            // Recalculate reading time if content changed and reading_time wasn't manually set
            if ($page->isDirty('content') && is_null($page->reading_time)) {
                $page->reading_time = $page->calculateReadingTime();
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
        // If schema_data is already set, use it
        if (!empty($value)) {
            // If it's a JSON string, decode it
            if (is_string($value)) {
                $value = json_decode($value, true);
            }
            if (is_array($value) && !empty($value)) {
                return $this->enhanceSchemaData($value);
            }
        }

        // Always generate schema data if we have a schema_type
        if (empty($this->schema_type)) {
            return null;
        }

        // Generate default schema data directly (bypass service for now)
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->schema_type,
            'name' => $this->title,
            'description' => $this->meta_description ?? $this->excerpt,
        ];

        // Add Article-specific properties
        if (in_array($this->schema_type, ['Article', 'BlogPosting', 'NewsArticle'])) {
            $schema['headline'] = $this->title;
            $schema['articleBody'] = strip_tags($this->content ?? '');
        }
        
        return $this->enhanceSchemaData($schema);
    }

    /**
     * Enhance schema data with computed values.
     */
    protected function enhanceSchemaData(array $schema): array
    {
        // Always ensure these computed properties are up to date
        $schema['@context'] = 'https://schema.org';
        $schema['@type'] = $this->schema_type ?: 'WebPage';
        $schema['url'] = $this->url;
        $schema['datePublished'] = $this->published_at?->toISOString();
        $schema['dateModified'] = $this->updated_at->toISOString();
        
        // Author information
        $schema['author'] = [
            '@type' => 'Person',
            'name' => $this->user?->name ?? 'Unknown',
        ];
        
        // Publisher information
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => config('app.url'),
        ];

        // Add computed properties based on schema type (preserve user-provided values)
        switch ($this->schema_type) {
            case 'Article':
            case 'BlogPosting':
            case 'NewsArticle':
                if (!isset($schema['headline']) || empty($schema['headline'])) {
                    $schema['headline'] = $this->title;
                }
                if (!isset($schema['articleBody']) || empty($schema['articleBody'])) {
                    $schema['articleBody'] = strip_tags($this->content);
                }
                // Always compute wordCount from content (override user-provided value)
                $schema['wordCount'] = str_word_count(strip_tags($this->content ?? ''));
                break;
                
            case 'FAQPage':
                // Handle FAQ schema structure
                if ($this->faq_data && is_array($this->faq_data)) {
                    $schema['mainEntity'] = [];
                    foreach ($this->faq_data as $faq) {
                        $schema['mainEntity'][] = [
                            '@type' => 'Question',
                            'name' => $faq['question'] ?? '',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => $faq['answer'] ?? '',
                            ],
                        ];
                    }
                }
                break;
        }

        // Ensure name and description are set if missing (preserve user-provided values)
        if (!isset($schema['name']) || empty($schema['name'])) {
            $schema['name'] = $this->title;
        }
        if (!isset($schema['description']) || empty($schema['description'])) {
            $schema['description'] = $this->meta_description ?? $this->excerpt;
        }

        // AEO Enhancements - Add breadcrumb, keywords, and content categorization
        if (!isset($schema['breadcrumb'])) {
            $schema['breadcrumb'] = $this->generateBreadcrumbList();
        }
        
        if (!isset($schema['keywords']) && $this->keywords) {
            $schema['keywords'] = is_array($this->keywords) ? implode(', ', $this->keywords) : $this->keywords;
        }
        
        if (!isset($schema['inLanguage'])) {
            $schema['inLanguage'] = config('app.locale', 'en');
        }
        
        // Add content categorization for AEO
        if ($this->topics && is_array($this->topics)) {
            $schema['about'] = array_map(function($topic) {
                return [
                    '@type' => 'Thing',
                    'name' => $topic,
                ];
            }, $this->topics);
        }
        
        // Add reading time for content quality signals
        if ($this->reading_time) {
            $schema['timeRequired'] = "PT{$this->reading_time}M"; // ISO 8601 duration format
        }

        return $schema;
    }

    /**
     * Generate breadcrumb list schema for site navigation hierarchy.
     */
    protected function generateBreadcrumbList(): array
    {
        $breadcrumbs = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        // Home page breadcrumb
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => config('app.url')
        ];

        // If page has a topic/category, add it to breadcrumb
        if ($this->topics && is_array($this->topics) && count($this->topics) > 0) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $this->topics[0], // Use first topic as category
                'item' => config('app.url') . '/category/' . Str::slug($this->topics[0])
            ];
        }

        // Current page breadcrumb
        $position = count($breadcrumbs['itemListElement']) + 1;
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $this->title,
            'item' => $this->url
        ];

        return $breadcrumbs;
    }

    /**
     * Calculate reading time in minutes based on content length.
     */
    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $wordsPerMinute = 200; // Average reading speed
        return max(1, ceil($wordCount / $wordsPerMinute));
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

        // Validate schema data using the service
        $schemaService = app(SchemaValidationService::class);
        
        try {
            $validated = $schemaService->validateSchemaData($this->schema_type ?: 'WebPage', $value);
            $this->attributes['schema_data'] = json_encode($validated);
        } catch (\Exception $e) {
            // Log the validation error but don't break the model
            \Log::warning('Schema data validation failed for page: ' . $e->getMessage(), [
                'page_id' => $this->id,
                'schema_type' => $this->schema_type,
                'schema_data' => $value,
            ]);
            
            // Store the data anyway but mark it as unvalidated
            $this->attributes['schema_data'] = json_encode($value);
        }
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
