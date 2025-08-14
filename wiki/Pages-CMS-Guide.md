# Pages CMS Guide

## Overview

The Pages CMS system provides a comprehensive content management solution with built-in SEO, AEO (Answer Engine Optimization), and GEO (Generative Engine Optimization) capabilities. This system replaces the previous "Posts" functionality with a more robust "Pages" architecture.

## Features

### Core Functionality
- **Dynamic Page Creation**: Create and manage content pages with rich metadata
- **SEO Optimization**: Built-in meta tags, Open Graph, and Twitter Card support
- **Schema Markup**: Structured data for better search engine understanding
- **Sitemap Generation**: Automatic XML sitemap for search engines
- **Soft Deletes**: Safe content deletion with recovery options
- **Publishing Workflow**: Draft and published states for content

### SEO/AEO/GEO Features
- **Meta Tags Management**: Title, description, keywords
- **Open Graph Integration**: Facebook and social media optimization
- **Twitter Cards**: Enhanced Twitter sharing
- **Schema.org Markup**: Article, WebPage, and Organization schemas
- **Canonical URLs**: Proper URL canonicalization
- **Robots Meta**: Control search engine indexing

## Database Schema

### Pages Table
```sql
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    -- SEO Fields
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    canonical_url VARCHAR(255),
    robots_meta VARCHAR(100) DEFAULT 'index,follow',
    
    -- Open Graph Fields
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(255),
    og_type VARCHAR(50) DEFAULT 'article',
    
    -- Twitter Card Fields
    twitter_card VARCHAR(50) DEFAULT 'summary_large_image',
    twitter_title VARCHAR(255),
    twitter_description TEXT,
    twitter_image VARCHAR(255),
    
    -- Schema Markup
    schema_type VARCHAR(50) DEFAULT 'Article',
    schema_data JSON,
    
    -- Additional Fields
    template VARCHAR(100) DEFAULT 'default',
    parent_id BIGINT UNSIGNED NULL,
    order_column INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    view_count BIGINT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    INDEX idx_author (author_id),
    INDEX idx_parent (parent_id),
    INDEX idx_deleted (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
);
```

## Permissions

The Pages CMS uses the following permissions:

### Page Permissions
- `view pages` - View page listings and details
- `create pages` - Create new pages
- `edit pages` - Edit existing pages
- `delete pages` - Delete pages (soft delete)
- `publish pages` - Publish/unpublish pages
- `manage page seo` - Manage SEO settings
- `manage page schema` - Manage schema markup

## Routes

### Public Routes
```php
// Public page display
Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

// SEO Routes
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
```

### Admin Routes
```php
// Content Management Routes
Route::prefix('content')->name('content.')->group(function () {
    // Pages Management - Note: Specific routes MUST come before general routes
    Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}', [PageController::class, 'show'])->name('pages.show');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::patch('/pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
    Route::patch('/pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
});
```

## Controller Methods

### PageController

```php
class PageController extends Controller
{
    // Display listing of pages with statistics
    public function index()
    {
        $pages = Page::with('author')
            ->withCount('views')
            ->latest()
            ->paginate(20);
            
        $stats = [
            'total' => Page::count(),
            'published' => Page::published()->count(),
            'draft' => Page::draft()->count(),
            'featured' => Page::featured()->count(),
        ];
        
        return Inertia::render('content/pages/index', [
            'pages' => $pages,
            'stats' => $stats,
        ]);
    }
    
    // Show page creation form
    public function create()
    {
        return Inertia::render('content/pages/create', [
            'templates' => Page::getAvailableTemplates(),
            'schemaTypes' => Page::getSchemaTypes(),
        ]);
    }
    
    // Store new page with SEO data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'schema_type' => 'nullable|string',
            'schema_data' => 'nullable|json',
        ]);
        
        $page = Page::create([
            ...$validated,
            'author_id' => auth()->id(),
        ]);
        
        return redirect()->route('content.pages.index')
            ->with('success', 'Page created successfully');
    }
    
    // Generate XML sitemap
    public function sitemap()
    {
        $pages = Page::published()
            ->select(['slug', 'updated_at'])
            ->get();
            
        return response()->view('sitemap.pages', compact('pages'))
            ->header('Content-Type', 'text/xml');
    }
}
```

## Model Features

### Page Model

```php
class Page extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt',
        'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image',
        'twitter_title', 'twitter_description', 'twitter_image',
        'schema_type', 'schema_data',
        'status', 'published_at', 'author_id',
    ];
    
    protected $casts = [
        'schema_data' => 'array',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }
    
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id');
    }
    
    // SEO Methods
    public function getMetaTitle()
    {
        return $this->meta_title ?: $this->title;
    }
    
    public function getMetaDescription()
    {
        return $this->meta_description ?: Str::limit($this->excerpt ?: $this->content, 160);
    }
    
    // Schema Generation
    public function generateSchema()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->schema_type ?: 'Article',
            'headline' => $this->title,
            'description' => $this->getMetaDescription(),
            'author' => [
                '@type' => 'Person',
                'name' => $this->author->name,
            ],
            'datePublished' => $this->published_at?->toIso8601String(),
            'dateModified' => $this->updated_at->toIso8601String(),
        ];
        
        if ($this->featured_image) {
            $schema['image'] = asset($this->featured_image);
        }
        
        if ($this->schema_data) {
            $schema = array_merge($schema, $this->schema_data);
        }
        
        return $schema;
    }
}
```

## Frontend Components

### Page Index Component
```tsx
// resources/js/pages/content/pages/index.tsx
export default function PagesIndex({ pages, stats }) {
    return (
        <AppLayout>
            <Head title="Pages" />
            <div className="container">
                <PageStats stats={stats} />
                <PagesList pages={pages} />
            </div>
        </AppLayout>
    );
}
```

### Page Create Component
```tsx
// resources/js/pages/content/pages/create.tsx
export default function PageCreate({ templates, schemaTypes }) {
    const { data, setData, post, errors } = useForm({
        title: '',
        slug: '',
        content: '',
        meta_title: '',
        meta_description: '',
        schema_type: 'Article',
    });
    
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('content.pages.store'));
    };
    
    return (
        <AppLayout>
            <Head title="Create Page" />
            <form onSubmit={handleSubmit}>
                <SEOFieldset data={data} setData={setData} errors={errors} />
                <SchemaFieldset 
                    data={data} 
                    setData={setData} 
                    schemaTypes={schemaTypes} 
                />
                <Button type="submit">Create Page</Button>
            </form>
        </AppLayout>
    );
}
```

## SEO Implementation

### Meta Tags in Blade Template
```blade
{{-- resources/views/app.blade.php --}}
@if(isset($page))
    <!-- Primary Meta Tags -->
    <title>{{ $page->getMetaTitle() }}</title>
    <meta name="title" content="{{ $page->getMetaTitle() }}">
    <meta name="description" content="{{ $page->getMetaDescription() }}">
    <meta name="keywords" content="{{ $page->meta_keywords }}">
    <meta name="robots" content="{{ $page->robots_meta }}">
    <link rel="canonical" href="{{ $page->canonical_url ?: url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $page->og_type }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->og_title ?: $page->getMetaTitle() }}">
    <meta property="og:description" content="{{ $page->og_description ?: $page->getMetaDescription() }}">
    @if($page->og_image)
        <meta property="og:image" content="{{ asset($page->og_image) }}">
    @endif
    
    <!-- Twitter -->
    <meta property="twitter:card" content="{{ $page->twitter_card }}">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $page->twitter_title ?: $page->getMetaTitle() }}">
    <meta property="twitter:description" content="{{ $page->twitter_description ?: $page->getMetaDescription() }}">
    @if($page->twitter_image)
        <meta property="twitter:image" content="{{ asset($page->twitter_image) }}">
    @endif
    
    <!-- Schema.org Markup -->
    <script type="application/ld+json">
        {!! json_encode($page->generateSchema()) !!}
    </script>
@endif
```

### XML Sitemap Template
```blade
{{-- resources/views/sitemap/pages.blade.php --}}
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($pages as $page)
        <url>
            <loc>{{ url('/pages/' . $page->slug) }}</loc>
            <lastmod>{{ $page->updated_at->toW3cString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>
```

## Testing

### Feature Tests
```php
// tests/Feature/PageManagementTest.php
class PageManagementTest extends TestCase
{
    public function test_admin_can_create_page_with_seo_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Page content',
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'schema_type' => 'Article',
        ]);
        
        $response->assertRedirect('/content/pages');
        $this->assertDatabaseHas('pages', [
            'slug' => 'test-page',
            'meta_title' => 'SEO Title',
        ]);
    }
    
    public function test_sitemap_generates_correctly()
    {
        Page::factory()->published()->count(5)->create();
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee('</urlset>');
    }
}
```

## Best Practices

### SEO Optimization
1. **Always provide meta descriptions** - Keep them under 160 characters
2. **Use descriptive slugs** - Include target keywords in URLs
3. **Implement schema markup** - Use appropriate schema types for content
4. **Optimize images** - Use descriptive alt text and compress images
5. **Create XML sitemaps** - Submit to search engines regularly

### Content Management
1. **Use drafts** - Save work in progress as drafts
2. **Schedule publishing** - Set future publish dates for content
3. **Implement versioning** - Track content changes over time
4. **Use templates** - Create consistent page layouts
5. **Monitor analytics** - Track page performance and engagement

### Performance
1. **Cache rendered pages** - Use Laravel's caching for static content
2. **Lazy load images** - Improve initial page load times
3. **Minify assets** - Reduce CSS and JavaScript file sizes
4. **Use CDN** - Serve static assets from edge locations
5. **Optimize queries** - Use eager loading for relationships

## Troubleshooting

### Common Issues

#### Pages not appearing in sitemap
- Ensure pages are published (`status = 'published'`)
- Check that `published_at` is not in the future
- Verify the sitemap route is accessible

#### SEO meta tags not showing
- Check that the page is passed to the view
- Verify the blade template includes the meta tag section
- Ensure data is properly escaped in meta tags

#### Schema markup validation errors
- Use Google's Rich Results Test tool
- Validate JSON-LD syntax
- Ensure required schema properties are present

## Migration from Posts to Pages

If migrating from a Posts system:

1. **Run migration**: `php artisan migrate --path=database/migrations/2025_08_12_000000_update_posts_to_pages_permissions.php`
2. **Update permissions**: `php artisan db:seed --class=PermissionSeeder`
3. **Update routes**: Replace all `posts` references with `pages`
4. **Update components**: Rename Post components to Page components
5. **Update tests**: Update test files to use pages instead of posts

## Future Enhancements

### Planned Features
- **Multi-language support** - Internationalization for global content
- **A/B testing** - Test different page versions
- **Content blocks** - Reusable content components
- **Revision history** - Track all content changes
- **AI-powered SEO suggestions** - Automated optimization recommendations
- **Advanced analytics** - Detailed page performance metrics
