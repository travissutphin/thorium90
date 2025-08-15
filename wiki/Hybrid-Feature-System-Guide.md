# Hybrid Feature System Guide

## 🚨 **IMPORTANT: Start Here First**

**BEFORE using the hybrid feature system, you MUST complete the [Development Workflow](Development-Workflow) consistency check.**

This ensures you understand how the feature system integrates with the overall system architecture and patterns.

## Overview

The Hybrid Feature System provides a flexible approach to managing functionality in Thorium90, combining the power of plugins for complex features with simple feature flags for basic on/off functionality. This system is designed for **rapid client development** where you need to quickly enable/disable features per project.

## 🎯 **Philosophy: Right Tool for the Job**

Instead of making everything a plugin, we use:
- **Plugins** for complex, reusable features that need their own models, migrations, and business logic
- **Feature Flags** for simple on/off functionality that doesn't require complex architecture
- **Client Code** for project-specific customizations

## Architecture Components

### 1. Feature Service (`FeatureService`)
Central service that manages both plugin and custom feature states.

### 2. Configuration System (`config/features.php`)
Centralized configuration file that defines all available features.

### 3. Helper Functions (`app/helpers.php`)
Convenient helper functions for checking feature states throughout the application.

### 4. Environment Variables
Feature states can be controlled via `.env` file for easy deployment configuration.

## Feature Types

### Plugin Features (Complex)
Use for features that require:
- Database migrations
- Multiple models
- Complex business logic
- Service providers
- Routes and controllers
- External API integrations

**Examples:**
- Blog system with posts, categories, tags
- E-commerce with products, orders, payments
- Event management with bookings
- Newsletter with campaigns and subscribers

### Custom Features (Simple)
Use for features that are:
- Simple on/off toggles
- Template sections
- UI components
- Client-specific functionality

**Examples:**
- Testimonials section
- Team member profiles
- FAQ section
- Pricing tables
- Gallery components

## Configuration

### Feature Configuration File

```php
// config/features.php
return [
    'plugins' => [
        'blog' => env('PLUGIN_BLOG', true),
        'shop' => env('PLUGIN_SHOP', false),
        'events' => env('PLUGIN_EVENTS', false),
        'portfolio' => env('PLUGIN_PORTFOLIO', false),
        'newsletter' => env('PLUGIN_NEWSLETTER', false),
        'forms' => env('PLUGIN_FORMS', true),
    ],

    'custom' => [
        'testimonials' => env('FEATURE_TESTIMONIALS', true),
        'team_page' => env('FEATURE_TEAM_PAGE', true),
        'case_studies' => env('FEATURE_CASE_STUDIES', false),
        'faq_section' => env('FEATURE_FAQ_SECTION', true),
        'pricing_tables' => env('FEATURE_PRICING_TABLES', false),
        'calculators' => env('FEATURE_CALCULATORS', false),
        'gallery' => env('FEATURE_GALLERY', true),
        'social_feed' => env('FEATURE_SOCIAL_FEED', false),
        'live_chat' => env('FEATURE_LIVE_CHAT', false),
        'booking_system' => env('FEATURE_BOOKING_SYSTEM', false),
    ],

    'descriptions' => [
        'plugins' => [
            'blog' => 'Full blog system with posts, categories, and tags',
            'shop' => 'E-commerce functionality with products and orders',
            // ... more descriptions
        ],
        'custom' => [
            'testimonials' => 'Customer testimonials section',
            'team_page' => 'Team member profiles and bios',
            // ... more descriptions
        ],
    ],

    'dependencies' => [
        'shop' => ['forms'], // Shop requires forms for checkout
        'events' => ['forms'], // Events require forms for registration
        'booking_system' => ['forms'], // Booking requires forms
    ],

    'categories' => [
        'content' => ['blog', 'portfolio', 'case_studies', 'gallery'],
        'commerce' => ['shop', 'booking_system', 'pricing_tables'],
        'engagement' => ['newsletter', 'testimonials', 'social_feed', 'live_chat'],
        'utility' => ['forms', 'calculators', 'faq_section'],
        'team' => ['team_page', 'events'],
    ],
];
```

### Environment Variables

```bash
# .env file
# Plugin Features (complex)
PLUGIN_BLOG=true
PLUGIN_SHOP=false
PLUGIN_EVENTS=false
PLUGIN_PORTFOLIO=true
PLUGIN_NEWSLETTER=false
PLUGIN_FORMS=true

# Custom Features (simple)
FEATURE_TESTIMONIALS=true
FEATURE_TEAM_PAGE=true
FEATURE_CASE_STUDIES=false
FEATURE_FAQ_SECTION=true
FEATURE_PRICING_TABLES=false
FEATURE_CALCULATORS=false
FEATURE_GALLERY=true
FEATURE_SOCIAL_FEED=false
FEATURE_LIVE_CHAT=false
FEATURE_BOOKING_SYSTEM=false
```

## Usage

### Helper Functions

The system provides convenient helper functions:

```php
// Check if a feature is enabled
if (feature('plugin.blog')) {
    // Blog plugin is enabled
}

if (feature('testimonials')) {
    // Testimonials feature is enabled
}

// Get all enabled plugins
$enabledPlugins = enabled_plugins();
// Returns: ['blog', 'forms', 'portfolio']

// Get all enabled custom features
$enabledFeatures = enabled_features();
// Returns: ['testimonials', 'team_page', 'faq_section', 'gallery']

// Get the FeatureService instance
$featureService = features();
$stats = $featureService->getStats();
```

### In Controllers

```php
// app/Http/Controllers/HomeController.php
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'showTestimonials' => feature('testimonials'),
            'showTeam' => feature('team_page'),
            'showBlog' => feature('plugin.blog'),
        ];

        return inertia('Home', $data);
    }
}
```

### In Blade Templates

```blade
{{-- resources/views/home.blade.php --}}
@if(feature('testimonials'))
    @include('sections.testimonials')
@endif

@if(feature('team_page'))
    @include('sections.team')
@endif

@if(feature('plugin.blog'))
    @include('plugins.blog.latest-posts')
@endif
```

### In React Components

```typescript
// You can pass feature flags from the controller
interface Props {
    showTestimonials: boolean;
    showTeam: boolean;
    showBlog: boolean;
}

export default function HomePage({ showTestimonials, showTeam, showBlog }: Props) {
    return (
        <div>
            {showTestimonials && <TestimonialsSection />}
            {showTeam && <TeamSection />}
            {showBlog && <BlogSection />}
        </div>
    );
}
```

### In Client Templates

```typescript
// resources/js/templates/public/HomePage.tsx
export const HomePage: React.FC<TemplateProps> = ({ content, config }) => {
    return (
        <>
            {/* ==================== TESTIMONIALS SECTION START ==================== */}
            {/* This section is controlled by the FEATURE_TESTIMONIALS flag */}
            {/* To enable/disable: Set FEATURE_TESTIMONIALS=true/false in .env */}
            <section className="py-16 bg-gray-50">
                <div className="container mx-auto px-4">
                    <div className="text-center mb-12">
                        <h2 className="text-3xl font-bold mb-4">What Our Clients Say</h2>
                    </div>
                    
                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {/* Add testimonials here */}
                    </div>
                </div>
            </section>
            {/* ==================== TESTIMONIALS SECTION END ==================== */}
        </>
    );
};
```

## Feature Service API

### Core Methods

```php
// Check if a feature is enabled
$featureService->isEnabled('plugin.blog'); // true/false
$featureService->isEnabled('testimonials'); // true/false

// Get enabled features
$featureService->enabledPlugins(); // ['blog', 'forms']
$featureService->enabledCustomFeatures(); // ['testimonials', 'team_page']

// Enable/disable features programmatically
$featureService->enable('testimonials');
$featureService->disable('testimonials');

// Get statistics
$stats = $featureService->getStats();
// Returns:
// [
//     'plugins' => ['total' => 6, 'enabled' => 3],
//     'custom' => ['total' => 10, 'enabled' => 4]
// ]

// Get all features with status
$allFeatures = $featureService->getAllFeatures();
```

## Client Project Setup Workflow

### 1. Clone Thorium90
```bash
git clone thorium90 new-client-project
cd new-client-project
```

### 2. Configure Features
Edit `.env` file to enable/disable features for this client:

```bash
# Enable features this client needs
PLUGIN_BLOG=true
PLUGIN_PORTFOLIO=true
FEATURE_TESTIMONIALS=true
FEATURE_TEAM_PAGE=true

# Disable features this client doesn't need
PLUGIN_SHOP=false
PLUGIN_EVENTS=false
FEATURE_CASE_STUDIES=false
FEATURE_PRICING_TABLES=false
```

### 3. Customize Templates
Edit templates in `resources/js/templates/public/` to match client design:

```typescript
// resources/js/templates/public/HomePage.tsx
// Customize the sections based on enabled features
```

### 4. Deploy
```bash
php artisan migrate
npm run build
# Deploy to client server
```

## Best Practices

### When to Use Plugins vs Features

| Use Plugin When | Use Feature Flag When |
|----------------|----------------------|
| Complex business logic | Simple on/off toggle |
| Multiple database tables | No database changes |
| External API integration | UI component visibility |
| Service providers needed | Template section control |
| Reusable across clients | Client-specific functionality |
| Independent functionality | Dependent on existing code |

### Examples

**✅ Good Plugin Candidates:**
- Blog system (posts, categories, comments, RSS)
- E-commerce (products, orders, payments, inventory)
- Event management (events, bookings, calendar)
- Newsletter (campaigns, subscribers, templates)

**✅ Good Feature Flag Candidates:**
- Testimonials section
- Team member profiles
- FAQ accordion
- Pricing comparison tables
- Image gallery
- Social media feed

**❌ Avoid:**
- Making simple UI toggles into full plugins
- Using feature flags for complex business logic
- Creating plugins for client-specific features

### Development Workflow

1. **Plan Features**: Decide which features are plugins vs flags
2. **Configure Environment**: Set up `.env` with required features
3. **Develop Templates**: Create client templates with feature checks
4. **Test Combinations**: Test different feature combinations
5. **Document**: Document which features are enabled for the client

## Testing

### Feature Testing

```php
// tests/Feature/FeatureSystemTest.php
class FeatureSystemTest extends TestCase
{
    /** @test */
    public function it_can_check_plugin_features()
    {
        config(['features.plugins.blog' => true]);
        
        $this->assertTrue(feature('plugin.blog'));
        $this->assertContains('blog', enabled_plugins());
    }

    /** @test */
    public function it_can_check_custom_features()
    {
        config(['features.custom.testimonials' => true]);
        
        $this->assertTrue(feature('testimonials'));
        $this->assertContains('testimonials', enabled_features());
    }

    /** @test */
    public function it_respects_feature_dependencies()
    {
        config([
            'features.plugins.shop' => true,
            'features.plugins.forms' => false,
        ]);
        
        // Shop depends on forms, so it should be disabled
        $this->assertFalse(feature('plugin.shop'));
    }
}
```

### Template Testing

```php
/** @test */
public function it_shows_testimonials_when_feature_enabled()
{
    config(['features.custom.testimonials' => true]);
    
    $response = $this->get('/');
    
    $response->assertSee('What Our Clients Say');
}

/** @test */
public function it_hides_testimonials_when_feature_disabled()
{
    config(['features.custom.testimonials' => false]);
    
    $response = $this->get('/');
    
    $response->assertDontSee('What Our Clients Say');
}
```

## Performance Considerations

### Caching
- Feature states are cached for 1 hour by default
- Cache is automatically cleared when features are changed
- Use `Cache::forget("feature.{$featureName}")` to manually clear

### Database Impact
- Feature flags don't require database queries
- Plugin features may require additional queries
- Consider eager loading for plugin data

### Frontend Impact
- Feature checks happen server-side
- No JavaScript overhead for feature detection
- Templates are conditionally rendered

## Migration Guide

### From Manual Feature Management

1. **Identify Current Features**: List all features currently managed manually
2. **Categorize**: Decide which are plugins vs feature flags
3. **Configure**: Add to `config/features.php`
4. **Update Code**: Replace manual checks with `feature()` helper
5. **Test**: Ensure all feature combinations work correctly

### Adding New Features

```php
// 1. Add to config/features.php
'custom' => [
    'new_feature' => env('FEATURE_NEW_FEATURE', false),
],

// 2. Add description
'descriptions' => [
    'custom' => [
        'new_feature' => 'Description of the new feature',
    ],
],

// 3. Use in code
if (feature('new_feature')) {
    // Feature code here
}
```

## Troubleshooting

### Common Issues

**Feature Not Working**
- Check `.env` file has correct variable name
- Verify `config/features.php` includes the feature
- Clear config cache: `php artisan config:clear`

**Plugin Not Loading**
- Ensure plugin is enabled in features config
- Check plugin service provider is registered
- Verify plugin dependencies are met

**Cache Issues**
- Clear feature cache: `Cache::forget("feature.{$name}")`
- Clear all cache: `php artisan cache:clear`

### Debug Commands

```php
// Check feature status
dd(feature('plugin.blog'));
dd(enabled_plugins());
dd(features()->getStats());

// Check configuration
dd(config('features.plugins'));
dd(config('features.custom'));
```

## Related Documentation

- **[Template System Guide](Template-System-Guide)** - Template system overview
- **[Plugin System Guide](Plugin-System-Guide)** - Plugin development
- **[Development Workflow](Development-Workflow)** - Required consistency process
- **[Developer Guide](Developer-Guide)** - Technical implementation details

## Support

For questions and support:
- Review this guide and related documentation
- Check the troubleshooting section above
- Review the test files for usage examples
- Create an issue in the project repository
- Consult the developer documentation

---

**Remember**: Always complete the [Development Workflow](Development-Workflow) consistency check before working with the feature system to ensure proper integration with the existing architecture.
