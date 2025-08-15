# Template System Guide

## 🚨 **IMPORTANT: Start Here First**

**BEFORE using the template system, you MUST complete the [Development Workflow](Development-Workflow) consistency check.**

This ensures you understand how the template system integrates with the overall system architecture and patterns.

## Overview

The Template System provides a flexible, developer-friendly architecture for creating custom UI/UX designs across the Thorium90 CMS. It's designed as a **rapid development framework** where you can quickly customize templates for different clients while keeping core functionality separate and updateable.

## 🎯 **Thorium90 as a Starter Framework**

Thorium90 is designed to be a **professional starter framework** that you can:
- Clone for each new client project
- Customize templates without touching core code
- Pull updates from the main Thorium90 repository
- Deploy quickly with client-specific designs

## Hybrid Feature System

The system uses a **hybrid approach** combining:
- **Plugins** for complex, reusable features (blog, shop, events)
- **Feature Flags** for simple on/off functionality (testimonials, team pages)
- **Client Templates** for custom page designs

## Key Features

- **Flexible Templates**: Create custom page templates with different layouts
- **Reusable Blocks**: Compose pages using reusable content blocks
- **Dynamic Layouts**: Switch between different layout structures
- **Theme Support**: Apply consistent theming across all templates
- **Plugin Ready**: Designed to support future plugins (blog, ecommerce, etc.)
- **SEO Optimized**: Built-in SEO and schema markup support
- **Type Safe**: Full TypeScript support with proper type definitions

## Architecture Components

### 1. Template Registry
Central registry that manages all available templates across the application and plugins.

### 2. Layout System
Flexible layout components that define the structure and positioning of content on pages.

### 3. Block System
Reusable content blocks that can be composed into different layouts and templates.

### 4. Theme System
Consistent theming system that applies across all templates and components.

## Core Templates

### Core Page Template (`core-page`)
The default template for standard pages with the following features:
- **Layouts**: `default`, `sidebar`, `full-width`
- **Blocks**: `hero`, `content`
- **Themes**: `default`
- **SEO**: Full meta tags and schema markup support

## Available Layouts

### Default Layout (`default`)
- Simple centered layout with container
- Maximum width of 4xl (896px)
- Responsive padding and margins

### Sidebar Layout (`sidebar`)
- Main content area with right sidebar
- 3:1 column ratio on large screens
- Stacked on mobile devices

### Full Width Layout (`full-width`)
- Full browser width without container constraints
- Ideal for hero sections and landing pages
- No maximum width restrictions

## Available Blocks

### Hero Block (`hero`)
Large banner section with title, subtitle, and call-to-action buttons.

**Configuration Options:**
- `height`: `sm`, `md`, `lg`, `xl`, `full`
- `alignment`: `left`, `center`, `right`
- `backgroundImage`: URL to background image
- `showCTA`: Boolean to show/hide call-to-action
- `ctaText`: Primary button text
- `secondaryCTA`: Boolean to show secondary button
- `secondaryCTAText`: Secondary button text

### Content Block (`content`)
Main content area with optional title and meta information.

**Configuration Options:**
- `showTitle`: Boolean to show/hide page title
- `showMeta`: Boolean to show/hide meta information (author, date, etc.)

## Database Schema

The template system extends the `pages` table with these fields:

```sql
template VARCHAR(255) DEFAULT 'core-page'  -- Template identifier
layout VARCHAR(255) NULL                   -- Layout identifier  
theme VARCHAR(255) NULL                    -- Theme identifier
blocks JSON NULL                           -- Block configuration
template_config JSON NULL                  -- Template-specific settings
```

## Usage in Pages

### Creating Pages with Templates

When creating or editing pages, you can specify:

```php
$page = Page::create([
    'title' => 'My Page',
    'slug' => 'my-page',
    'content' => '<p>Page content</p>',
    'template' => 'core-page',
    'layout' => 'sidebar',
    'theme' => 'default',
    'blocks' => [
        [
            'type' => 'hero',
            'position' => 0,
            'config' => [
                'height' => 'lg',
                'showCTA' => true,
                'ctaText' => 'Get Started'
            ]
        ],
        [
            'type' => 'content',
            'position' => 1,
            'config' => [
                'showTitle' => true,
                'showMeta' => true
            ]
        ]
    ]
]);
```

### Frontend Rendering

Pages are automatically rendered using the template system:

```typescript
// The TemplateRenderer automatically handles template selection
<TemplateRenderer
    content={page}
    templateId={page.template}
    layout={page.layout}
    theme={page.theme}
    blocks={page.blocks}
/>
```

## Page Controller Integration

The `PageController` has been updated to support template fields:

```php
// Store method validation includes template fields
$validated = $request->validate([
    // ... existing fields
    'template' => 'nullable|string',
    'layout' => 'nullable|string', 
    'theme' => 'nullable|string',
    'blocks' => 'nullable|array',
    'template_config' => 'nullable|array',
]);
```

## Frontend Integration

### Using Template Hooks

```typescript
import { useTemplate, useTemplateSelection } from '@/core';

// Get template information
const { template, exists, layouts, blocks } = useTemplate('core-page');

// Get template options for dropdowns
const { templates, options } = useTemplateSelection('page');
```

### Template Selection UI

```typescript
function TemplateSelector({ value, onChange }) {
    const { options } = useTemplateSelection('page');
    
    return (
        <select value={value} onChange={onChange}>
            {options.map(option => (
                <option key={option.value} value={option.value}>
                    {option.label}
                </option>
            ))}
        </select>
    );
}
```

## SEO and Schema Integration

The template system maintains full SEO support:

### Meta Tags
- All existing meta tag functionality is preserved
- Templates can override or extend meta tag generation
- Open Graph and Twitter Card support included

### Schema Markup
- Automatic schema.org structured data generation
- Template-specific schema types supported
- JSON-LD format for search engine optimization

## Testing

Comprehensive tests ensure template system reliability:

```php
/** @test */
public function it_can_render_pages_with_new_template_system()
{
    $page = Page::factory()->create([
        'template' => 'core-page',
        'layout' => 'default',
        'theme' => 'default'
    ]);

    $response = $this->get("/pages/{$page->slug}");
    
    $response->assertStatus(200);
    $response->assertSee($page->title);
}
```

## Migration from Existing System

### Database Migration

Run the migration to add template fields:

```bash
php artisan migrate
```

### Update Existing Pages

Update existing pages to use the new system:

```php
// Set default template for existing pages
Page::whereNull('template')->update(['template' => 'core-page']);
```

### Frontend Updates

The existing `BasePageTemplate` is automatically wrapped and integrated with the new system, ensuring backward compatibility.

## Performance Considerations

### Lazy Loading
- Templates are loaded on-demand using React.lazy()
- Code splitting reduces initial bundle size
- Suspense boundaries provide loading states

### Caching
- Template configurations are cached in memory
- Registry lookups are optimized for performance
- Database queries include proper indexing

### Error Handling
- Graceful fallbacks for missing templates
- Error boundaries prevent template failures from breaking pages
- Detailed error logging for debugging

## Future Plugin Support

The system is designed for future plugin integration:

### Blog Plugin (Planned)
- `blog-post` template for individual posts
- `blog-list` template for post listings
- `recent-posts` block for sidebars
- `category-list` block for navigation

### E-commerce Plugin (Planned)
- `product` template for product pages
- `category` template for product categories
- `product-grid` block for product listings
- `add-to-cart` block for purchase actions

### Classifieds Plugin (Planned)
- `classified-ad` template for individual ads
- `classified-list` template for ad listings
- `search-filters` block for filtering
- `contact-seller` block for inquiries

## Best Practices

### Template Design
1. Keep templates focused on layout and structure
2. Use blocks for reusable content components
3. Provide sensible defaults for all configuration options
4. Follow existing design patterns and conventions

### Layout Design
1. Design layouts to be content-agnostic
2. Use CSS Grid or Flexbox for responsive layouts
3. Provide clear section definitions
4. Ensure accessibility compliance

### Block Design
1. Make blocks highly configurable
2. Provide JSON schema for configuration validation
3. Keep blocks focused on a single responsibility
4. Include proper TypeScript types

### Performance
1. Use React.lazy() for code splitting
2. Implement proper error boundaries
3. Cache template configurations
4. Optimize database queries

## Troubleshooting

### Common Issues

**Template Not Found**
- Ensure the template is registered and active
- Check the template ID matches exactly
- Verify the template is initialized properly

**Layout Not Rendering**
- Check that the layout is included in the template's available layouts
- Verify the layout component is properly registered
- Ensure the layout ID is correct

**Blocks Not Displaying**
- Verify block registration and configuration
- Check block configuration schema
- Ensure block components are properly imported

**Theme Not Applied**
- Ensure theme is properly registered
- Check that CSS custom properties are defined
- Verify theme CSS is loaded

### Debug Tools

```typescript
// Check registry status
console.log('Templates:', TemplateRegistry.getStats());
console.log('Layouts:', LayoutRegistry.getStats());
console.log('Blocks:', BlockRegistry.getStats());

// Validate template configuration
const errors = TemplateRegistry.validateTemplate(template);
if (errors.length > 0) {
    console.error('Template validation errors:', errors);
}
```

## API Reference

### Template Registry Methods

```typescript
// Register a template
TemplateRegistry.register(template: UniversalTemplate): void

// Get a template
TemplateRegistry.get(templateId: string): UniversalTemplate | undefined

// Get all templates
TemplateRegistry.getAll(): UniversalTemplate[]

// Get templates by category
TemplateRegistry.getByCategory(category: string): UniversalTemplate[]

// Get select options
TemplateRegistry.getSelectOptions(contentType?: string): SelectOption[]
```

### Layout Registry Methods

```typescript
// Register a layout
LayoutRegistry.register(layout: UniversalLayout): void

// Get a layout
LayoutRegistry.get(layoutId: string): UniversalLayout | undefined

// Get layout sections
LayoutRegistry.getLayoutSections(layoutId: string): string[]
```

### Block Registry Methods

```typescript
// Register a block
BlockRegistry.register(block: UniversalBlock): void

// Get a block
BlockRegistry.get(blockId: string): UniversalBlock | undefined

// Get blocks by category
BlockRegistry.getByCategory(category: string): UniversalBlock[]

// Get grouped blocks
BlockRegistry.getGroupedByCategory(): Record<string, UniversalBlock[]>
```

## Related Documentation

- **[Development Workflow](Development-Workflow)** - Required consistency process
- **[Pages CMS Guide](Pages-CMS-Guide)** - Content management system
- **[Database Schema](Database-Schema)** - Database structure reference
- **[Developer Guide](Developer-Guide)** - Technical implementation details
- **[Testing Strategy](Testing-Strategy)** - Testing procedures and standards

## Support

For questions and support:
- Review this guide and related documentation
- Check the troubleshooting section above
- Review the test files for usage examples
- Create an issue in the project repository
- Consult the developer documentation

---

**Remember**: Always complete the [Development Workflow](Development-Workflow) consistency check before working with the template system to ensure proper integration with the existing architecture.
