# Schema.org Validation System

## Overview

The enhanced schema.org validation system provides comprehensive type safety and validation for structured data in the Pages CMS. This system ensures that schema data adheres to Schema.org standards while maintaining flexibility for different content types.

## Available Schema Types

| Type | Use Case | Required Fields | Auto-Generated |
|------|----------|----------------|----------------|
| `WebPage` | General pages, about, contact | name, description | ✓ |
| `Article` | Editorial content, articles | headline, articleBody | ✓ |
| `BlogPosting` | Blog posts | headline, articleBody | ✓ |
| `NewsArticle` | News content | headline, articleBody | ✓ |
| `FAQPage` | FAQ pages with Q&A content | name, mainEntity | ✓ |

## Configuration

Schema types are configured in `config/schema.php`:

```php
'types' => [
    'WebPage' => [
        'label' => 'Web Page (Default)',
        'fields' => [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ],
        'required_properties' => ['name', 'description'],
    ],
]
```

## Service Layer

### SchemaValidationService

Central service for all schema operations:

```php
use App\Services\SchemaValidationService;

$service = new SchemaValidationService();

// Get available types
$types = $service->getAvailableTypes();

// Validate schema data
$validated = $service->validateSchemaData('Article', $data);

// Generate defaults
$defaults = $service->generateDefaultSchemaData('WebPage', $pageData);
```

## Model Integration

### Automatic Validation

The Page model automatically validates schema data:

```php
$page = new Page();
$page->schema_type = 'Article';
$page->schema_data = [
    'headline' => 'My Article',
    'articleBody' => 'Content...'
]; // Automatically validated
```

### Enhanced Getter

Schema data is enhanced with computed properties:

```php
$schemaData = $page->schema_data;
// Returns enhanced data with:
// - Auto-generated @context and @type
// - Computed URLs and timestamps
// - Author and publisher information
```

## AEO (Answer Engine Optimization) Features

### Content Categorization
```php
// Enhanced content classification for AI engines
$page->topics = ['Technology', 'Machine Learning'];
$page->keywords = ['AI', 'algorithms', 'automation'];
$page->content_type = 'educational'; // general, educational, commercial, etc.
```

### FAQ Schema Integration
```php
// Structured Q&A for direct answer extraction
$page->schema_type = 'FAQPage';
$page->faq_data = [
    ['question' => 'What is AEO?', 'answer' => 'Answer Engine Optimization...'],
    ['question' => 'How to implement?', 'answer' => 'Follow structured data guidelines...']
];
```

### Breadcrumb Navigation
Automatic breadcrumb generation based on content hierarchy:
- Home > Category > Current Page
- Uses first topic as category classification
- Provides clear navigation context for AI engines

### Content Quality Signals
- **Reading Time**: Auto-calculated based on word count (200 WPM)
- **Content Depth**: Word count and complexity analysis  
- **Topic Authority**: Structured entity relationships via `about` properties
- **Language Detection**: Automatic `inLanguage` property

### Semantic HTML5 Structure
Enhanced templates with proper semantic markup:
```html
<article itemscope itemtype="https://schema.org/Article">
  <header>
    <h1 itemprop="headline">Page Title</h1>
    <time itemprop="datePublished">Publication Date</time>
    <span itemprop="author">Author Name</span>
  </header>
  <section itemprop="articleBody">Content</section>
  <footer>
    <span itemprop="about">Topic Tags</span>
  </footer>
</article>
```

## Frontend Integration

### TypeScript Types

Strong typing for frontend components:

```typescript
import { SchemaType, SchemaData } from '@/types';

interface Props {
    schemaTypes: SchemaTypeConfig[];
}

const schemaType: SchemaType = 'Article';
const schemaData: SchemaData = {
    '@type': 'Article',
    headline: 'Title',
    articleBody: 'Content'
};
```

### Form Components

Updated components use new schema system:

```tsx
// Schema type selection
<Select value={data.schema_type} onValueChange={(value) => setData('schema_type', value as SchemaType)}>
    {schemaTypes.map((type) => (
        <SelectItem key={type.value} value={type.value}>
            {type.label}
        </SelectItem>
    ))}
</Select>
```

## Validation Rules

### Base Validation

All schema types include base validation:

```php
'schema_type' => 'required|string',
'schema_data' => 'nullable|array',
```

### Type-Specific Rules

Each schema type has specific field validation:

```php
// Article validation
'schema_data.headline' => 'required|string|max:110',
'schema_data.articleBody' => 'required|string',
'schema_data.wordCount' => 'nullable|integer|min:1',
```

### Inheritance

Types can extend others:

```php
'BlogPosting' => [
    'extends' => 'Article',  // Inherits Article validation
    'fields' => [
        'blogCategory' => 'nullable|string|max:100',
    ],
]
```

## Auto-Generation

### Default Properties

Common properties are auto-generated:

```php
'auto_generate' => [
    'name' => 'title',           // Use page title
    'description' => 'excerpt',  // Use page excerpt
    'url' => 'computed',         // Generate from route
    'wordCount' => 'computed',   // Calculate from content
]
```

### Computed Values

Some values are computed dynamically:
- `datePublished` - From published_at timestamp
- `dateModified` - From updated_at timestamp
- `author` - From page user relationship
- `publisher` - From app configuration

## Error Handling

### Validation Errors

Detailed validation feedback:

```php
try {
    $service->validateSchemaData($type, $data);
} catch (ValidationException $e) {
    foreach ($e->validator->errors()->all() as $error) {
        echo $error;
    }
}
```

### Graceful Degradation

Invalid data is handled gracefully:
- Validation errors are logged
- Invalid data is still stored
- Application continues functioning
- Warnings are displayed in logs

## Best Practices

### 1. Choose Appropriate Types

Select the most specific schema type:
- `WebPage` for general informational pages
- `Article` for editorial content
- `BlogPosting` for blog entries
- `NewsArticle` for news content

### 2. Provide Required Fields

Always include required fields for your schema type:

```php
// For Article type
$schema_data = [
    'headline' => 'Required article headline',
    'articleBody' => 'Required article content'
];
```

### 3. Leverage Auto-Generation

Let the system generate common properties:

```php
// These are automatically populated
$schema = $service->generateDefaultSchemaData('Article', [
    'title' => 'Page Title',
    'content' => 'Page content...',
    'excerpt' => 'Page excerpt'
]);
```

### 4. Validate Early

Validate schema data before saving:

```php
// In controller
$schemaRules = $service->getValidationRulesForRequest($type);
$validated = $request->validate($schemaRules);
```

## Migration from Legacy System

### Existing Data

Legacy pages continue working:
- Old schema_type values remain valid
- Existing schema_data is preserved
- New validation applies to new/updated pages only

### Upgrading

To upgrade existing pages:

1. Review current schema_type values
2. Update schema_data to match new structure
3. Run validation to identify issues
4. Update frontend components for new format

## Troubleshooting

### Common Issues

**Unknown schema type error:**
- Check `config/schema.php` for available types
- Verify type name spelling and case

**Validation failures:**
- Review required fields for your schema type
- Check field length and format requirements
- Ensure all required properties are provided

**Frontend TypeScript errors:**
- Update schema interfaces in `types/index.d.ts`
- Ensure prop types match new schema format
- Check component imports for schema types

**Empty dropdown in forms:**
- Verify SchemaValidationService injection in controller
- Check that schemaTypes prop is passed correctly
- Ensure service returns array of type configs

### Debug Information

Enable schema validation logging:

```php
// Log validation attempts
\Log::debug('Schema validation', [
    'type' => $type,
    'data' => $data,
    'result' => $result
]);
```

## Performance Optimization

### Caching

- Schema configurations are cached automatically
- Consider caching generated defaults for high-traffic pages
- Validation rules are memoized per request

### Database

- Use indexes on schema_type for filtering
- Consider separate tables for complex schema requirements
- JSON fields are indexed for common queries

## Advanced Usage

### Custom Validation Rules

Add custom validation for specific needs:

```php
'fields' => [
    'customField' => [
        'required',
        'string',
        Rule::in(['value1', 'value2'])
    ]
]
```

### Dynamic Schema Generation

Generate schema based on page content:

```php
public function generateCustomSchema(Page $page): array
{
    $schema = $this->generateDefaultSchemaData($page->schema_type, $page->toArray());
    
    // Add custom logic
    if ($page->hasImages()) {
        $schema['image'] = $page->getFeaturedImageUrl();
    }
    
    return $schema;
}
```

### Extending the System

Add new schema types by:

1. Adding configuration to `config/schema.php`
2. Creating TypeScript interfaces
3. Adding validation rules
4. Updating frontend components

## Testing

Test schema validation in your application:

```php
public function test_article_schema_validation()
{
    $service = app(SchemaValidationService::class);
    
    $data = [
        'headline' => 'Test Article',
        'articleBody' => 'Test content'
    ];
    
    $validated = $service->validateSchemaData('Article', $data);
    
    $this->assertArrayHasKey('headline', $validated);
    $this->assertEquals('Test Article', $validated['headline']);
}
```

## Related Documentation

- [Pages CMS Guide](Pages-CMS-Guide.md) - Complete CMS documentation
- [API Reference](API-Reference.md) - API endpoints
- [Testing Strategy](Testing-Strategy.md) - Testing approaches
- [Developer Guide](Developer-Guide.md) - Development workflows