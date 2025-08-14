# Developer Guide

## 🚨 **IMPORTANT: Start Here First**

**BEFORE reading this guide, you MUST complete the [Development Workflow](Development-Workflow) consistency check.**

This ensures you understand the system architecture and patterns before diving into technical details.

## Overview

This guide provides technical implementation details for developers working with the Thorium90 CMS. It covers architecture, code organization, development patterns, and extension points.

## Architecture Overview

### Technology Stack

**Backend:**
- **Laravel 11**: PHP framework with Eloquent ORM
- **Spatie Laravel Permission**: Role and permission management
- **Laravel Sanctum**: API authentication
- **Laravel Fortify**: Authentication scaffolding
- **Inertia.js**: Server-side rendering adapter

**Frontend:**
- **React 18**: UI library with hooks
- **TypeScript**: Type-safe JavaScript
- **Inertia.js**: SPA-like experience without API
- **Tailwind CSS**: Utility-first CSS framework
- **Vite**: Build tool and dev server

**Database:**
- **MySQL/PostgreSQL**: Primary database
- **SQLite**: Development and testing
- **Redis**: Caching and sessions (production)

### Directory Structure

```
thorium90/
├── app/
│   ├── Http/Controllers/          # Request handlers
│   │   ├── Admin/                 # Admin-specific controllers
│   │   ├── Auth/                  # Authentication controllers
│   │   └── PageController.php     # Content management
│   ├── Models/                    # Eloquent models
│   │   ├── User.php              # User model with roles
│   │   ├── Page.php              # Content pages
│   │   └── Setting.php           # System settings
│   ├── Middleware/               # HTTP middleware
│   ├── Providers/                # Service providers
│   └── Actions/                  # Business logic actions
├── database/
│   ├── migrations/               # Database schema
│   ├── seeders/                  # Data seeders
│   └── factories/                # Model factories
├── resources/
│   ├── js/                       # React frontend
│   │   ├── components/           # Reusable components
│   │   ├── pages/                # Inertia pages
│   │   ├── layouts/              # Page layouts
│   │   └── types/                # TypeScript definitions
│   └── views/                    # Blade templates
├── routes/
│   ├── web.php                   # Web routes
│   ├── api.php                   # API routes
│   ├── admin.php                 # Admin routes
│   └── auth.php                  # Authentication routes
├── tests/
│   ├── Feature/                  # Integration tests
│   └── Unit/                     # Unit tests
└── wiki/                         # Documentation
```

## Core Components

### Authentication System

#### User Model

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;
    
    protected $fillable = [
        'name', 'email', 'password', 'provider', 'provider_id', 'avatar'
    ];
    
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relationships
    public function pages()
    {
        return $this->hasMany(Page::class);
    }
}
```

#### Permission System

The system uses Spatie Laravel Permission with these key concepts:

**Roles:**
- Super Admin (all permissions)
- Admin (most permissions except system settings)
- Editor (content management)
- Author (own content only)
- Subscriber (basic access)

**Permission Categories:**
- User Management: `view users`, `create users`, `edit users`, `delete users`
- Content Management: `view pages`, `create pages`, `edit pages`, `delete pages`, `publish pages`
- Settings: `manage settings`, `view system stats`
- Media: `upload media`, `manage media`

#### Middleware Integration

```php
// routes/admin.php
Route::middleware(['auth', 'verified', 'role.any:Super Admin,Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Admin routes
    });

Route::middleware(['auth', 'verified', 'permission:create pages'])
    ->group(function () {
        // Protected routes
    });
```

### Content Management System

#### Page Model

```php
// app/Models/Page.php
class Page extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'status', 'is_featured',
        'meta_title', 'meta_description', 'meta_keywords',
        'schema_type', 'schema_data', 'user_id', 'published_at'
    ];
    
    protected $casts = [
        'is_featured' => 'boolean',
        'schema_data' => 'array',
        'published_at' => 'datetime',
    ];
    
    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }
    
    // SEO Methods
    public function getMetaTitle()
    {
        return $this->meta_title ?: $this->title;
    }
    
    // Schema.org generation
    public function generateSchema()
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => $this->schema_type ?: 'WebPage',
            'name' => $this->title,
            'description' => $this->meta_description,
            'author' => ['@type' => 'Person', 'name' => $this->user->name],
            'datePublished' => $this->published_at?->toISOString(),
            'dateModified' => $this->updated_at->toISOString(),
        ];
    }
}
```

#### Page Controller Pattern

```php
// app/Http/Controllers/PageController.php
class PageController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create pages');
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published,private',
            // ... other fields
        ]);
        
        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        
        // Ensure unique slug
        $validated['slug'] = $this->ensureUniqueSlug($validated['slug']);
        
        // Set published timestamp
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        
        $validated['user_id'] = Auth::id();
        
        $page = Page::create($validated);
        
        return redirect()->route('content.pages.index')
                        ->with('success', 'Page created successfully.');
    }
    
    private function ensureUniqueSlug($slug, $excludeId = null)
    {
        $originalSlug = $slug;
        $counter = 1;
        
        while (Page::where('slug', $slug)
                  ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                  ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
```

### Frontend Architecture

#### Inertia.js Integration

```typescript
// resources/js/app.tsx
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'

createInertiaApp({
    title: (title) => `${title} - Thorium90 CMS`,
    resolve: (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx', { eager: true })
        return pages[`./pages/${name}.tsx`]
    },
    setup({ el, App, props }) {
        const root = createRoot(el)
        root.render(<App {...props} />)
    },
})
```

#### TypeScript Definitions

```typescript
// resources/js/types/index.ts
export interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
    created_at: string;
    updated_at: string;
}

export interface Page {
    id: number;
    title: string;
    slug: string;
    content: string;
    excerpt?: string;
    status: 'draft' | 'published' | 'private';
    is_featured: boolean;
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
    schema_type: string;
    user: User;
    published_at?: string;
    created_at: string;
    updated_at: string;
}

export interface PageData<T = {}> {
    component: string;
    props: T & {
        auth: {
            user: User;
        };
        flash: {
            success?: string;
            error?: string;
        };
    };
    url: string;
    version: string;
}
```

#### Component Patterns

```tsx
// resources/js/pages/content/pages/create.tsx
import { useForm } from '@inertiajs/react'
import { FormEventHandler } from 'react'
import AppLayout from '@/layouts/AppLayout'

interface Props {
    schemaTypes: Record<string, string>;
}

export default function CreatePage({ schemaTypes }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        slug: '',
        content: '',
        status: 'draft' as const,
        is_featured: false,
        meta_title: '',
        meta_description: '',
        schema_type: 'WebPage',
    })

    const submit: FormEventHandler = (e) => {
        e.preventDefault()
        post(route('content.pages.store'))
    }

    return (
        <AppLayout title="Create Page">
            <form onSubmit={submit} className="space-y-6">
                <div>
                    <label htmlFor="title">Title</label>
                    <input
                        id="title"
                        type="text"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        className="mt-1 block w-full"
                        required
                    />
                    {errors.title && <div className="text-red-600">{errors.title}</div>}
                </div>
                
                {/* More form fields... */}
                
                <button
                    type="submit"
                    disabled={processing}
                    className="btn btn-primary"
                >
                    {processing ? 'Creating...' : 'Create Page'}
                </button>
            </form>
        </AppLayout>
    )
}
```

## Development Patterns

### Service Layer Pattern

```php
// app/Services/PageService.php
class PageService
{
    public function createPage(array $data, User $user): Page
    {
        $data['user_id'] = $user->id;
        $data['slug'] = $this->generateUniqueSlug($data['title'], $data['slug'] ?? null);
        
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }
        
        return Page::create($data);
    }
    
    public function generateSitemap(): Collection
    {
        return Page::published()
                  ->select(['slug', 'updated_at', 'created_at'])
                  ->orderBy('updated_at', 'desc')
                  ->get();
    }
    
    private function generateUniqueSlug(string $title, ?string $slug = null): string
    {
        $baseSlug = $slug ?: Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
```

### Repository Pattern (Optional)

```php
// app/Repositories/PageRepository.php
interface PageRepositoryInterface
{
    public function findPublished(): Collection;
    public function findBySlug(string $slug): ?Page;
    public function create(array $data): Page;
    public function update(Page $page, array $data): bool;
}

class PageRepository implements PageRepositoryInterface
{
    public function findPublished(): Collection
    {
        return Page::published()->with('user')->get();
    }
    
    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }
    
    // ... other methods
}
```

### Event-Driven Architecture

```php
// app/Events/PagePublished.php
class PagePublished
{
    public function __construct(public Page $page) {}
}

// app/Listeners/UpdateSitemap.php
class UpdateSitemap
{
    public function handle(PagePublished $event): void
    {
        // Regenerate sitemap
        Cache::forget('sitemap');
    }
}

// app/Providers/EventServiceProvider.php
protected $listen = [
    PagePublished::class => [
        UpdateSitemap::class,
    ],
];
```

## Testing Strategy

### Feature Tests

```php
// tests/Feature/Content/PageManagementTest.php
class PageManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
    }

    /** @test */
    public function admin_can_create_page_with_seo_data()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'status' => 'draft',
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
        ];
        
        $response = $this->actingAs($admin)
                         ->post('/content/pages', $pageData);
        
        $response->assertRedirect('/content/pages');
        $this->assertDatabaseHas('pages', [
            'slug' => 'test-page',
            'user_id' => $admin->id,
        ]);
    }
}
```

### Unit Tests

```php
// tests/Unit/Models/PageTest.php
class PageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_schema_markup_correctly()
    {
        $user = User::factory()->create();
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'schema_type' => 'Article',
            'user_id' => $user->id,
        ]);

        $schema = $page->generateSchema();

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('Article', $schema['@type']);
        $this->assertEquals('Test Page', $schema['name']);
    }
}
```

## API Development

### API Controllers

```php
// app/Http/Controllers/Api/PageController.php
class PageController extends Controller
{
    public function index(Request $request)
    {
        $pages = Page::query()
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->search, fn($q, $search) => 
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
            )
            ->with('user:id,name')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $pages->items(),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }
}
```

### API Resources

```php
// app/Http/Resources/PageResource.php
class PageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->when($request->routeIs('api.pages.show'), $this->content),
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'author' => new UserResource($this->whenLoaded('user')),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```

## Extension Points

### Custom Permissions

```php
// database/seeders/CustomPermissionSeeder.php
class CustomPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'manage custom feature',
            'view analytics',
            'export data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
```

### Custom Middleware

```php
// app/Http/Middleware/CheckCustomPermission.php
class CheckCustomPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user()?->can($permission)) {
            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}
```

### Custom Models

```php
// app/Models/CustomContent.php
class CustomContent extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['title', 'content', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
```

## Performance Optimization

### Database Optimization

```php
// Eager loading relationships
$pages = Page::with(['user:id,name'])
            ->select(['id', 'title', 'slug', 'status', 'user_id', 'created_at'])
            ->paginate(15);

// Query optimization with indexes
Schema::table('pages', function (Blueprint $table) {
    $table->index(['status', 'published_at']);
    $table->index(['user_id', 'status']);
    $table->index('is_featured');
});
```

### Caching Strategies

```php
// Cache frequently accessed data
public function getPublishedPages()
{
    return Cache::remember('pages.published', 3600, function () {
        return Page::published()->with('user')->get();
    });
}

// Cache invalidation
public function updatePage(Page $page, array $data)
{
    $page->update($data);
    
    Cache::forget('pages.published');
    Cache::forget("page.{$page->slug}");
}
```

### Frontend Optimization

```typescript
// Lazy loading components
const PageEditor = lazy(() => import('./components/PageEditor'))

// Memoization for expensive calculations
const MemoizedPageList = memo(({ pages }: { pages: Page[] }) => {
    const sortedPages = useMemo(
        () => pages.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()),
        [pages]
    )
    
    return <div>{/* Render pages */}</div>
})
```

## Security Best Practices

### Input Validation

```php
// Custom validation rules
class UniqueSlugRule implements Rule
{
    public function __construct(private ?int $excludeId = null) {}
    
    public function passes($attribute, $value)
    {
        return !Page::where('slug', $value)
                   ->when($this->excludeId, fn($q) => $q->where('id', '!=', $this->excludeId))
                   ->exists();
    }
    
    public function message()
    {
        return 'The slug has already been taken.';
    }
}
```

### Authorization Policies

```php
// app/Policies/PagePolicy.php
class PagePolicy
{
    public function view(User $user, Page $page): bool
    {
        if ($page->status === 'published') {
            return true;
        }
        
        return $user->id === $page->user_id || $user->can('edit pages');
    }
    
    public function update(User $user, Page $page): bool
    {
        return $user->id === $page->user_id || $user->can('edit pages');
    }
    
    public function delete(User $user, Page $page): bool
    {
        return $user->id === $page->user_id || $user->can('delete pages');
    }
}
```

### CSRF Protection

```typescript
// Frontend CSRF handling
import { router } from '@inertiajs/react'

// Inertia automatically handles CSRF tokens
router.post('/content/pages', formData)

// For manual requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
```

## Deployment Considerations

### Environment Configuration

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build
```

### Database Migrations

```php
// Safe migration patterns
public function up()
{
    Schema::table('pages', function (Blueprint $table) {
        $table->string('new_field')->nullable()->after('existing_field');
    });
}

public function down()
{
    Schema::table('pages', function (Blueprint $table) {
        $table->dropColumn('new_field');
    });
}
```

### Queue Configuration

```php
// app/Jobs/ProcessPageContent.php
class ProcessPageContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(private Page $page) {}
    
    public function handle(): void
    {
        // Process page content (SEO analysis, etc.)
    }
}
```

## Troubleshooting

### Common Issues

1. **Permission Denied Errors**: Check role assignments and permission seeding
2. **Route Not Found**: Verify route definitions and middleware
3. **Database Connection**: Check environment configuration
4. **Frontend Build Issues**: Clear node_modules and reinstall

### Debug Tools

```php
// Enable query logging
DB::enableQueryLog();
// ... run queries
dd(DB::getQueryLog());

// Debug permissions
dd(auth()->user()->getAllPermissions()->pluck('name'));

// Debug Inertia data
// Add to HandleInertiaRequests middleware
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'debug' => app()->environment('local') ? [
            'user_permissions' => $request->user()?->getAllPermissions()->pluck('name'),
        ] : [],
    ]);
}
```

## Contributing

### Code Standards

- Follow PSR-12 coding standards
- Use TypeScript for frontend code
- Write tests for new features
- Document public methods
- Use meaningful commit messages

### Development Workflow

1. Create feature branch from `main`
2. Write tests first (TDD)
3. Implement feature
4. Run test suite
5. Create pull request
6. Code review
7. Merge to main

For more information, see the [Contributing Guide](Contributing-Guide).

---

This developer guide provides the foundation for working with Thorium90 CMS. For specific implementation details, refer to the source code and additional documentation.
