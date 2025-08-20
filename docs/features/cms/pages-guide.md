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
- **Enhanced Schema.org Markup**: Comprehensive structured data with validation
- **Canonical URLs**: Proper URL canonicalization
- **Robots Meta**: Control search engine indexing
- **Schema Type Validation**: Type-safe schema data with extensible configuration
- **AEO Integration**: Answer Engine Optimization for AI-powered search
  - **Topic Categorization**: Content classification with breadcrumb generation
  - **FAQ Schema**: Question-answer pairs with structured data
  - **Reading Time Calculation**: Automatic content analysis and timing
  - **Keyword Management**: Primary, secondary, and long-tail keyword optimization
  - **Schema Preview**: Live JSON-LD preview with validation

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
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'private') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
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
    
    -- Enhanced Schema Markup
    schema_type VARCHAR(50) DEFAULT 'WebPage',
    schema_data JSON,
    
    -- AEO Enhancement Fields
    topics JSON NULL COMMENT 'Content categorization topics for AEO',
    keywords JSON NULL COMMENT 'SEO/AEO keywords array',
    faq_data JSON NULL COMMENT 'FAQ questions and answers for schema',
    reading_time INT NULL COMMENT 'Calculated reading time in minutes',
    content_type VARCHAR(50) DEFAULT 'general' COMMENT 'Content categorization',
    content_score DECIMAL(3,2) NULL COMMENT 'Content quality score',
    
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
    INDEX idx_user (user_id),
    INDEX idx_deleted (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
        $pages = Page::with('user')
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
            'user_id' => auth()->id(),
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
        'status', 'published_at', 'user_id',
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
    public function user()
    {
        return $this->belongsTo(User::class);
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
                'name' => $this->user->name,
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

## AEO (Answer Engine Optimization) Guide

### Overview

AEO components provide Answer Engine Optimization specifically designed for AI-powered search engines like ChatGPT, Perplexity, and Google's Bard. This system enhances traditional SEO with structured data optimized for answer generation.

### AEO Components

#### 1. Topic Selector (`TopicSelector.tsx`)
Manages content categorization with up to 5 topics per page:
- **Auto-suggestions** based on content analysis
- **Color-coded badges** for visual organization
- **Breadcrumb generation** for schema.org markup
- **Topic hierarchy** for content relationships

```tsx
<TopicSelector
    value={data.topics}
    onChange={(topics) => setData('topics', topics)}
    disabled={processing}
    error={errors.topics}
/>
```

#### 2. FAQ Editor (`AEOFaqEditor.tsx`)
Structured question-answer pairs for FAQ schema:
- **Dynamic FAQ management** with add/remove functionality
- **Character limits** (255 for questions, 2000 for answers)
- **Schema.org FAQPage** integration
- **Real-time validation** and preview

```tsx
<AEOFaqEditor
    value={data.faq_data}
    onChange={(faqData) => setData('faq_data', faqData)}
    disabled={processing}
    error={errors.faq_data}
/>
```

#### 3. Keyword Manager (`KeywordManager.tsx`)
Advanced keyword management with type classification:
- **Keyword types**: Primary, secondary, long-tail
- **Context-aware suggestions** based on content
- **Real-time analysis** and distribution tracking
- **AEO optimization** recommendations

```tsx
<KeywordManager
    value={data.keywords}
    onChange={(keywords) => setData('keywords', keywords)}
    disabled={processing}
    error={errors.keywords}
    contentPreview={data.content}
/>
```

#### 4. Reading Time Display (`ReadingTimeDisplay.tsx`)
Automatic content analysis and reading time calculation:
- **Word count analysis** with HTML tag removal
- **Reading time calculation** (200 words/minute average)
- **Content categorization** (short, medium, long, very long)
- **Readability scoring** based on sentence length
- **ISO 8601 duration** for schema markup

```tsx
<ReadingTimeDisplay
    content={data.content}
    showWordCount={true}
    showDetails={false}
/>
```

#### 5. Schema Preview (`SchemaPreview.tsx`)
Live preview of generated JSON-LD markup:
- **Real-time schema generation** from page data
- **Validation and error checking**
- **Copy to clipboard** functionality
- **Google Rich Results** testing integration
- **AEO property highlighting**

```tsx
<SchemaPreview
    schemaType={data.schema_type}
    title={data.title}
    content={data.content}
    topics={data.topics}
    keywords={data.keywords}
    faqData={data.faq_data}
    visible={showSchemaPreview}
    onToggle={() => setShowSchemaPreview(!showSchemaPreview)}
/>
```

### Backend Integration

#### Page Model AEO Fields
```php
protected $fillable = [
    // ... existing fields
    'topics',           // JSON array of content topics
    'keywords',         // JSON array of keywords
    'faq_data',         // JSON array of FAQ items
    'reading_time',     // Integer minutes
    'content_type',     // String categorization
    'content_score',    // Decimal quality score
];

protected $casts = [
    // ... existing casts
    'topics' => 'array',
    'keywords' => 'array',
    'faq_data' => 'array',
];
```

#### AEO Schema Enhancement
The Page model automatically enhances schema data with AEO properties:

```php
protected function enhanceSchemaData(array $schema): array
{
    // Add AEO enhancements
    if ($this->keywords) {
        $schema['keywords'] = is_array($this->keywords) 
            ? implode(', ', $this->keywords) 
            : $this->keywords;
    }
    
    if ($this->topics) {
        $schema['about'] = array_map(function($topic) {
            return ['@type' => 'Thing', 'name' => $topic];
        }, $this->topics);
        
        $schema['breadcrumb'] = $this->generateBreadcrumbList();
    }
    
    if ($this->reading_time) {
        $schema['timeRequired'] = "PT{$this->reading_time}M";
    }
    
    // FAQ Schema for FAQPage type
    if ($this->schema_type === 'FAQPage' && $this->faq_data) {
        $schema['mainEntity'] = array_map(function($faq) {
            return [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer']
                ]
            ];
        }, $this->faq_data);
    }
    
    return $schema;
}
```

#### Validation Rules
```php
// AEO Enhancement validation
'topics' => 'nullable|array|max:5',
'topics.*' => 'string|max:100',
'keywords' => 'nullable|array|max:20',
'keywords.*' => 'string|max:50',
'faq_data' => 'nullable|array|max:50',
'faq_data.*.question' => 'required_with:faq_data|string|max:255',
'faq_data.*.answer' => 'required_with:faq_data|string|max:2000',
'content_type' => 'nullable|string|max:50',
```

### AEO Best Practices

#### Topic Selection
- **Use 2-3 main topics** for optimal categorization
- **Choose specific over general** topics when possible
- **Consider user search intent** when selecting topics
- **Maintain topic consistency** across related content

#### FAQ Optimization
- **Answer common questions** your audience asks
- **Use natural language** that matches search queries
- **Keep answers comprehensive** but concise (under 2000 chars)
- **Structure for featured snippets** in search results

#### Keyword Strategy
- **Mix keyword types**: Primary (1-2), Secondary (3-5), Long-tail (5-10)
- **Focus on search intent** rather than keyword density
- **Use variations and synonyms** for natural language processing
- **Consider voice search** and conversational queries

#### Content Quality
- **Aim for 500+ words** for substantial content
- **Use shorter sentences** for better readability (avg 15-20 words)
- **Structure with headers** for content hierarchy
- **Include multimedia** when relevant

### Testing AEO Implementation

#### Feature Tests
```php
// tests/Feature/AEOIntegrationTest.php
public function test_faq_page_generates_proper_faq_schema()
{
    $page = Page::factory()->create([
        'schema_type' => 'FAQPage',
        'faq_data' => [
            [
                'question' => 'What is AEO?',
                'answer' => 'Answer Engine Optimization for AI search.'
            ]
        ]
    ]);
    
    $schema = $page->schema_data;
    
    $this->assertEquals('FAQPage', $schema['@type']);
    $this->assertArrayHasKey('mainEntity', $schema);
    $this->assertCount(1, $schema['mainEntity']);
}
```

#### Component Tests
```php
// tests/Unit/SchemaValidationServiceTest.php
public function test_aeo_schema_generation()
{
    $service = new SchemaValidationService();
    
    $pageData = [
        'topics' => ['Technology', 'AI'],
        'keywords' => ['artificial intelligence', 'machine learning'],
        'faq_data' => [
            ['question' => 'Test?', 'answer' => 'Answer']
        ]
    ];
    
    $schema = $service->generateSchemaWithAEO('Article', $pageData);
    
    $this->assertArrayHasKey('about', $schema);
    $this->assertArrayHasKey('keywords', $schema);
    $this->assertArrayHasKey('breadcrumb', $schema);
}
```

### AEO Performance Monitoring

#### Metrics to Track
- **Answer box appearances** in search results
- **Voice search optimization** effectiveness
- **AI citation rates** from content
- **Schema markup validation** scores
- **Content engagement** with AEO-optimized pages

#### Tools for AEO Analysis
- **Google Rich Results Test** for schema validation
- **Schema.org validator** for markup verification
- **Perplexity.ai** for answer engine testing
- **ChatGPT** for conversational query testing
- **Answer the Public** for question research

## Default Pages System

### Overview

Thorium90 comes with a comprehensive set of 20 professionally crafted default pages that cover all essential business needs. These pages are automatically installed during the initial setup and provide a solid foundation for any website.

### Installation

The default pages are installed via the migration and seeder system:

```bash
php artisan migrate
php artisan db:seed --class=Thorium90DefaultPagesSeeder
```

### Default Pages Included

#### Core Business Pages
1. **About Us** (`/about`) - Company overview and team information
2. **Features** (`/features`) - Product/service feature highlights
3. **Pricing** (`/pricing`) - Pricing plans and packages
4. **Contact** (`/contact`) - Contact information and inquiry form

#### Legal & Compliance Pages
5. **Privacy Policy** (`/privacy-policy`) - Data protection and privacy practices
6. **Terms of Service** (`/terms-of-service`) - Usage terms and conditions
7. **Cookie Policy** (`/cookie-policy`) - Cookie usage and consent information
8. **Refund Policy** (`/refund-policy`) - Refund terms and procedures

#### Support & Help Pages
9. **FAQ** (`/faq`) - Frequently asked questions with structured data
10. **Help Center** (`/help-center`) - Customer support hub
11. **Documentation** (`/documentation`) - Product documentation and guides
12. **Support** (`/support`) - Technical support and contact options

#### Content & Marketing Pages
13. **Blog** (`/blog`) - Blog landing page and article showcase
14. **Case Studies** (`/case-studies`) - Customer success stories
15. **Resources** (`/resources`) - Downloadable resources and tools
16. **News** (`/news`) - Company news and announcements

#### Utility Pages
17. **Sitemap** (`/sitemap`) - HTML sitemap for navigation
18. **404 Error** (`/404-error`) - Custom error page for missing content
19. **Coming Soon** (`/coming-soon`) - Maintenance and launch page (draft)
20. **Home** (`/`) - Main homepage with hero content

### Page Features

Each default page includes:

- **Professional Content**: Placeholder text that follows best practices
- **AEO Optimization**: Topics, keywords, and FAQ data where applicable
- **Schema Markup**: Appropriate schema types for each page type
- **SEO Optimization**: Meta titles, descriptions, and structured data
- **Reading Time**: Automatically calculated based on content length
- **Responsive Design**: Mobile-friendly content structure

### Content Structure

All pages use the container/prose pattern for consistent styling:

```html
<div class="container mx-auto px-4 py-8">
    <div class="prose max-w-none">
        <!-- Page content -->
    </div>
</div>
```

### Customization

The default pages serve as templates and can be fully customized:

1. **Edit Content**: Modify text, images, and structure as needed
2. **Update SEO**: Customize meta tags and schema data
3. **Change Schema Types**: Switch between Article, WebPage, FAQPage, etc.
4. **Add Components**: Include custom sections and interactive elements
5. **Modify URLs**: Update slugs to match your brand

### Migration Details

The migration `2025_08_17_202401_create_thorium90_default_pages.php`:

- Clears existing pages data only (preserves table structure)
- Resets auto-increment counters for clean installation
- Supports MySQL, SQLite, and PostgreSQL databases
- Can be safely rolled back if needed

### Seeder Configuration

The `Thorium90DefaultPagesSeeder`:

- Creates 19 published pages + 1 draft (Coming Soon)
- Automatically calculates reading times
- Assigns pages to the first available user
- Includes comprehensive AEO and schema data
- Uses professional placeholder content

### Testing Integration

The default pages are covered by comprehensive tests:

- **AEO Integration Tests**: Verify schema markup and optimization
- **Schema Validation Tests**: Ensure proper structured data
- **Route Tests**: Confirm accessibility and permissions
- **Content Tests**: Validate reading time and categorization

Run the test suite to verify installation:

```bash
php artisan test --filter=PageSchemaValidationTest
php artisan test --filter=AEOIntegrationTest
```

## Custom Page Sections System

### Overview

Thorium90 includes a powerful custom sections system that automatically enhances specific pages with additional content blocks. This system uses conditional includes based on page slugs to add professional layouts and functionality.

### How It Works

The system uses Blade template conditionals in `resources/views/public/layouts/page.blade.php` to automatically include custom sections based on the page slug:

```blade
{{-- Custom Page Sections - Conditional includes based on page slug --}}
@switch($page->slug)
    @case('about')
        @include('public.partials.sections.company-story-section')
        @include('public.partials.sections.team-values-section')
        @break
    
    @case('contact')
        @include('public.partials.sections.company-story-section')
        @include('public.partials.sections.office-locations-section')
        @break
    
    @case('features')
        @include('public.partials.sections.feature-highlights-section')
        @include('public.partials.sections.feature-comparison-section')
        @break
        
    {{-- Additional page cases... --}}
@endswitch
```

### Available Custom Sections

#### Core Business Pages
- **About** (`/about`):
  - Company story section with mission and problem/solution
  - Team and values section with team members and core values

- **Contact** (`/contact`):
  - Company story section (shared with About page)
  - Office locations section with global presence

- **Features** (`/features`):
  - Feature highlights with 6 key capabilities
  - Feature comparison table vs competitors

- **Pricing** (`/pricing`):
  - Interactive pricing cards with 3 tiers
  - Pricing FAQ section with common questions

#### Support Pages
- **FAQ** (`/faq`):
  - FAQ categories section for organized help

- **Help Center** (`/help-center`):
  - Help resources categorization
  - Popular articles showcase

#### Content Pages
- **Case Studies** (`/case-studies`):
  - Success stories section with customer testimonials

- **Resources** (`/resources`):
  - Resource library with downloadable content

- **Blog** (`/blog`):
  - Featured posts section highlighting latest content

#### Homepage
- **Home** (`/` or `/home`):
  - Hero section with main value proposition
  - Features overview with core capabilities

### Custom Section Files

All custom sections are stored in `resources/views/public/partials/sections/`:

```
sections/
├── company-story-section.blade.php      # Shared company story
├── team-values-section.blade.php        # Team and values
├── office-locations-section.blade.php   # Global office locations
├── feature-highlights-section.blade.php # Key feature showcase
├── feature-comparison-section.blade.php # Competitor comparison
├── pricing-cards-section.blade.php      # Pricing tier cards
├── pricing-faq-section.blade.php        # Pricing questions
├── faq-categories-section.blade.php     # FAQ organization
├── help-resources-section.blade.php     # Help categorization
├── popular-articles-section.blade.php   # Featured articles
├── success-stories-section.blade.php    # Customer case studies
├── resource-library-section.blade.php   # Downloadable resources
├── featured-posts-section.blade.php     # Blog highlights
├── hero-section.blade.php               # Homepage hero
└── features-overview-section.blade.php  # Homepage features
```

### Section Features

Each custom section includes:

- **Responsive Design**: Mobile-first layouts with Tailwind CSS
- **Professional Styling**: Consistent design system with gradients and shadows
- **Interactive Elements**: Hover effects and transitions
- **SVG Icons**: Scalable vector graphics for all icons
- **Call-to-Action Buttons**: Strategic CTAs for conversion
- **Semantic HTML**: Proper structure for accessibility and SEO

### Adding New Sections

To add a new custom section:

1. **Create the Section File**:
   ```bash
   touch resources/views/public/partials/sections/new-section.blade.php
   ```

2. **Add Conditional Logic**:
   Update `page.blade.php` to include your section:
   ```blade
   @case('your-page-slug')
       @include('public.partials.sections.new-section')
       @break
   ```

3. **Build the Section**:
   Use consistent styling and structure:
   ```blade
   {{-- ==================== YOUR SECTION START ==================== --}}
   <section class="py-16 md:py-24 bg-gray-50">
       <div class="container mx-auto px-4">
           <!-- Your content here -->
       </div>
   </section>
   {{-- ==================== YOUR SECTION END ==================== --}}
   ```

### Design Guidelines

#### Color Scheme
- **Primary Gradient**: `from-blue-500 to-purple-600`
- **Secondary**: `from-green-500 to-blue-600`
- **Background**: `bg-gray-50` and `bg-white` alternating
- **Text**: `text-gray-900` for headings, `text-gray-600` for body

#### Spacing
- **Section Padding**: `py-16 md:py-24`
- **Container**: `container mx-auto px-4`
- **Max Width**: `max-w-4xl` or `max-w-6xl` for content areas

#### Components
- **Cards**: `rounded-2xl p-8 shadow-lg`
- **Buttons**: Gradient backgrounds with hover effects
- **Icons**: 16x16 or 8x8 SVG icons with proper colors
- **Grid**: `grid md:grid-cols-2 lg:grid-cols-3` for responsive layouts

### Benefits

1. **Automatic Enhancement**: Pages are automatically enhanced without manual editing
2. **Consistent Design**: All sections follow the same design system
3. **Easy Maintenance**: Centralized section management
4. **Performance**: Sections only load on relevant pages
5. **Flexibility**: Easy to add, remove, or modify sections
6. **Reusability**: Sections can be shared across multiple pages (like company-story)

## Future Enhancements

### Planned Features
- **Multi-language support** - Internationalization for global content
- **A/B testing** - Test different page versions
- **Content blocks** - Reusable content components
- **Revision history** - Track all content changes
- **AI-powered SEO suggestions** - Automated optimization recommendations
- **Advanced analytics** - Detailed page performance metrics
- **Dynamic section management** - Admin interface for section control
