# Template System Documentation

## Overview

The Template System is a flexible, plugin-ready architecture that provides consistent UI/UX design capabilities across the Thorium90 CMS. It supports dynamic layouts, reusable blocks, and theming while maintaining simplicity and ease of management.

## Architecture

The system consists of four main components:

### 1. Template Registry
Central registry for managing templates across the application and plugins.

### 2. Layout System
Flexible layout components that define the structure of pages.

### 3. Block System
Reusable content blocks that can be composed into different layouts.

### 4. Theme System
Consistent theming across all templates and components.

## Core Components

### Template Registry (`TemplateRegistry`)

The Template Registry manages all available templates in the system.

```typescript
import { TemplateRegistry } from '@/core';

// Register a new template
TemplateRegistry.register({
    id: 'my-template',
    name: 'My Custom Template',
    description: 'A custom template for special pages',
    plugin: 'core',
    category: 'page',
    layouts: ['default', 'sidebar'],
    blocks: ['hero', 'content'],
    themes: ['default', 'modern'],
    config: {
        layouts: ['default', 'sidebar'],
        blocks: ['hero', 'content'],
        defaultLayout: 'default',
        defaultTheme: 'default'
    },
    component: MyTemplateComponent,
    isActive: true
});

// Get template options for a dropdown
const options = TemplateRegistry.getSelectOptions('page');
```

### Layout Registry (`LayoutRegistry`)

Manages layout components that define page structure.

```typescript
import { LayoutRegistry } from '@/core';

// Register a new layout
LayoutRegistry.register({
    id: 'my-layout',
    name: 'My Custom Layout',
    description: 'A custom layout with special sections',
    plugin: 'core',
    category: 'page',
    config: {
        name: 'My Custom Layout',
        sections: ['header', 'main', 'sidebar', 'footer'],
        defaultSections: {
            main: 'content',
            sidebar: 'related'
        }
    },
    component: MyLayoutComponent,
    isActive: true
});
```

### Block Registry (`BlockRegistry`)

Manages reusable content blocks.

```typescript
import { BlockRegistry } from '@/core';

// Register a new block
BlockRegistry.register({
    id: 'my-block',
    name: 'My Custom Block',
    description: 'A custom content block',
    plugin: 'core',
    category: 'content',
    component: MyBlockComponent,
    defaultConfig: {
        showTitle: true,
        alignment: 'center'
    },
    configSchema: {
        type: 'object',
        properties: {
            showTitle: { type: 'boolean', default: true },
            alignment: { 
                type: 'string', 
                enum: ['left', 'center', 'right'],
                default: 'center'
            }
        }
    },
    isActive: true
});
```

## Usage

### Rendering Templates

Use the `TemplateRenderer` component to render content with the template system:

```typescript
import { TemplateRenderer } from '@/core';

function MyPage({ page }) {
    return (
        <TemplateRenderer
            content={page}
            templateId="core-page"
            layout="sidebar"
            theme="modern"
            blocks={[
                {
                    type: 'hero',
                    position: 0,
                    config: {
                        height: 'lg',
                        showCTA: true
                    }
                },
                {
                    type: 'content',
                    position: 1,
                    config: {
                        showTitle: true,
                        showMeta: true
                    }
                }
            ]}
        />
    );
}
```

### Using Hooks

The system provides several React hooks for template management:

```typescript
import { useTemplate, useTemplateSelection } from '@/core';

function TemplateSelector({ contentType }) {
    const { templates, options } = useTemplateSelection(contentType);
    
    return (
        <select>
            {options.map(option => (
                <option key={option.value} value={option.value}>
                    {option.label}
                </option>
            ))}
        </select>
    );
}

function TemplateInfo({ templateId }) {
    const { template, exists, layouts, blocks } = useTemplate(templateId);
    
    if (!exists) {
        return <div>Template not found</div>;
    }
    
    return (
        <div>
            <h3>{template.name}</h3>
            <p>Available layouts: {layouts.join(', ')}</p>
            <p>Available blocks: {blocks.join(', ')}</p>
        </div>
    );
}
```

## Database Schema

The template system extends the `pages` table with additional fields:

```sql
-- Template system fields
template VARCHAR(255) DEFAULT 'core-page',
layout VARCHAR(255) NULL,
theme VARCHAR(255) NULL,
blocks JSON NULL,
template_config JSON NULL,

-- Indexes
INDEX idx_template (template),
INDEX idx_template_layout (template, layout)
```

### Page Model Updates

The `Page` model includes new fillable fields and casts:

```php
protected $fillable = [
    // ... existing fields
    'template',
    'layout',
    'theme',
    'blocks',
    'template_config',
];

protected $casts = [
    // ... existing casts
    'blocks' => 'array',
    'template_config' => 'array',
];
```

## Creating Custom Templates

### 1. Create Template Component

```typescript
// resources/js/templates/MyCustomTemplate.tsx
import React from 'react';
import { TemplateProps } from '@/core';

export const MyCustomTemplate: React.FC<TemplateProps> = ({
    content,
    layout,
    theme,
    blocks,
    config
}) => {
    return (
        <div className={`my-custom-template ${theme ? `theme-${theme}` : ''}`}>
            <header>
                <h1>{content.title}</h1>
            </header>
            <main>
                {/* Render blocks or content */}
                <div dangerouslySetInnerHTML={{ __html: content.content }} />
            </main>
        </div>
    );
};
```

### 2. Register Template

```typescript
// resources/js/templates/register.ts
import { TemplateRegistry } from '@/core';
import { MyCustomTemplate } from './MyCustomTemplate';

TemplateRegistry.register({
    id: 'my-custom-template',
    name: 'My Custom Template',
    description: 'A custom template for special use cases',
    plugin: 'core',
    category: 'page',
    layouts: ['default', 'full-width'],
    blocks: ['hero', 'content', 'cta'],
    themes: ['default', 'dark'],
    config: {
        layouts: ['default', 'full-width'],
        blocks: ['hero', 'content', 'cta'],
        defaultLayout: 'default',
        defaultTheme: 'default'
    },
    component: MyCustomTemplate,
    isActive: true
});
```

## Creating Custom Layouts

### 1. Create Layout Component

```typescript
// resources/js/layouts/MyCustomLayout.tsx
import React from 'react';
import { LayoutProps } from '@/core';

export const MyCustomLayout: React.FC<LayoutProps> = ({
    content,
    theme,
    children
}) => {
    return (
        <div className={`my-custom-layout ${theme ? `theme-${theme}` : ''}`}>
            <div className="layout-container">
                <aside className="layout-sidebar">
                    {/* Sidebar content */}
                </aside>
                <main className="layout-main">
                    {children}
                </main>
                <aside className="layout-secondary">
                    {/* Secondary sidebar */}
                </aside>
            </div>
        </div>
    );
};
```

### 2. Register Layout

```typescript
import { LayoutRegistry } from '@/core';
import { MyCustomLayout } from './MyCustomLayout';

LayoutRegistry.register({
    id: 'my-custom-layout',
    name: 'My Custom Layout',
    description: 'A three-column layout with dual sidebars',
    plugin: 'core',
    category: 'page',
    config: {
        name: 'My Custom Layout',
        sections: ['sidebar', 'main', 'secondary'],
        defaultSections: {
            main: 'content',
            sidebar: 'navigation',
            secondary: 'related'
        }
    },
    component: MyCustomLayout,
    isActive: true
});
```

## Creating Custom Blocks

### 1. Create Block Component

```typescript
// resources/js/blocks/MyCustomBlock.tsx
import React from 'react';
import { BlockProps } from '@/core';

export const MyCustomBlock: React.FC<BlockProps> = ({
    content,
    config,
    blockContent,
    position
}) => {
    const showBorder = config.showBorder as boolean;
    const backgroundColor = config.backgroundColor as string;
    
    return (
        <div 
            className={`my-custom-block ${showBorder ? 'border' : ''}`}
            style={{ backgroundColor }}
        >
            <h3>{blockContent?.title || 'Default Title'}</h3>
            <p>{blockContent?.description || 'Default description'}</p>
        </div>
    );
};
```

### 2. Register Block

```typescript
import { BlockRegistry } from '@/core';
import { MyCustomBlock } from './MyCustomBlock';

BlockRegistry.register({
    id: 'my-custom-block',
    name: 'My Custom Block',
    description: 'A custom block with configurable options',
    plugin: 'core',
    category: 'content',
    component: MyCustomBlock,
    defaultConfig: {
        showBorder: true,
        backgroundColor: '#f8f9fa'
    },
    configSchema: {
        type: 'object',
        properties: {
            showBorder: {
                type: 'boolean',
                default: true
            },
            backgroundColor: {
                type: 'string',
                format: 'color',
                default: '#f8f9fa'
            }
        }
    },
    isActive: true
});
```

## Plugin Integration

The template system is designed to work with plugins. Each plugin can register its own templates, layouts, and blocks:

### Plugin Structure

```
plugins/
├── blog/
│   ├── templates/
│   │   ├── BlogListTemplate.tsx
│   │   └── BlogPostTemplate.tsx
│   ├── blocks/
│   │   ├── RecentPostsBlock.tsx
│   │   └── CategoryListBlock.tsx
│   └── register.ts
└── ecommerce/
    ├── templates/
    │   ├── ProductTemplate.tsx
    │   └── CategoryTemplate.tsx
    ├── blocks/
    │   ├── ProductGridBlock.tsx
    │   └── AddToCartBlock.tsx
    └── register.ts
```

### Plugin Registration

```typescript
// plugins/blog/register.ts
import { TemplateRegistry, BlockRegistry } from '@/core';
import { BlogPostTemplate } from './templates/BlogPostTemplate';
import { RecentPostsBlock } from './blocks/RecentPostsBlock';

export function registerBlogPlugin() {
    // Register blog templates
    TemplateRegistry.register({
        id: 'blog-post',
        name: 'Blog Post Template',
        plugin: 'blog',
        category: 'post',
        // ... other config
        component: BlogPostTemplate,
        isActive: true
    });
    
    // Register blog blocks
    BlockRegistry.register({
        id: 'recent-posts',
        name: 'Recent Posts Block',
        plugin: 'blog',
        category: 'sidebar',
        component: RecentPostsBlock,
        isActive: true
    });
}
```

## Initialization

Initialize the core system in your application:

```typescript
// resources/js/app.tsx
import { initializeCoreSystem } from '@/core';

// Initialize the core template system
initializeCoreSystem();

// Initialize plugins
// registerBlogPlugin();
// registerEcommercePlugin();
```

## Best Practices

### 1. Template Design
- Keep templates focused on layout and structure
- Use blocks for reusable content components
- Provide sensible defaults for all configuration options

### 2. Layout Design
- Design layouts to be content-agnostic
- Use CSS Grid or Flexbox for responsive layouts
- Provide clear section definitions

### 3. Block Design
- Make blocks highly configurable
- Provide JSON schema for configuration validation
- Keep blocks focused on a single responsibility

### 4. Theme Design
- Use CSS custom properties for theme variables
- Provide both light and dark theme variants
- Ensure accessibility compliance

### 5. Performance
- Use React.lazy() for code splitting
- Implement proper error boundaries
- Cache template configurations

## Testing

The template system includes comprehensive tests:

```php
// tests/Feature/Core/TemplateSystemTest.php
class TemplateSystemTest extends TestCase
{
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
}
```

## Migration Guide

### From Existing Templates

1. **Identify Current Templates**: List all existing page templates
2. **Create Template Components**: Convert existing templates to the new system
3. **Register Templates**: Add templates to the registry
4. **Update Database**: Run migrations to add template fields
5. **Update Controllers**: Modify controllers to use the new system
6. **Test**: Ensure all pages render correctly

### Database Migration

```bash
# Run the migration to add template fields
php artisan migrate

# Update existing pages to use the new system
php artisan tinker
>>> Page::whereNull('template')->update(['template' => 'core-page']);
```

## Troubleshooting

### Common Issues

1. **Template Not Found**: Ensure the template is registered and active
2. **Layout Not Rendering**: Check that the layout is included in the template's available layouts
3. **Blocks Not Displaying**: Verify block registration and configuration
4. **Theme Not Applied**: Ensure theme is properly registered and CSS is loaded

### Debug Tools

```typescript
// Check registry status
console.log('Templates:', TemplateRegistry.getStats());
console.log('Layouts:', LayoutRegistry.getStats());
console.log('Blocks:', BlockRegistry.getStats());

// Validate template configuration
const errors = TemplateRegistry.validateTemplate(myTemplate);
if (errors.length > 0) {
    console.error('Template validation errors:', errors);
}
```

## Future Enhancements

### Planned Features
- Visual template builder
- Template marketplace
- Advanced block composition
- Real-time preview
- Template versioning
- A/B testing integration

### Plugin Ecosystem
- Blog plugin with post templates
- E-commerce plugin with product templates
- Portfolio plugin with gallery templates
- Event plugin with calendar templates

## Support

For questions and support:
- Check the troubleshooting section above
- Review the test files for usage examples
- Create an issue in the project repository
- Consult the developer documentation
