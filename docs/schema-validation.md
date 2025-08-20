# Schema.org Validation System

## Overview

The enhanced schema.org validation system provides comprehensive type safety and validation for structured data in the Pages CMS. This system ensures that schema data adheres to Schema.org standards while maintaining flexibility for different content types.

## Architecture

### Configuration System

Schema types and validation rules are centrally managed in `config/schema.php`:

```php
'types' => [
    'WebPage' => [
        'label' => 'Web Page (Default)',
        'description' => 'A basic web page with standard metadata',
        'fields' => [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ],
        'required_properties' => ['name', 'description'],
    ],
    // ... other types
]
```

### Service Layer

The `SchemaValidationService` provides centralized schema operations:

- **Type Management**: Get available types and configurations
- **Validation**: Validate schema data against type-specific rules
- **Generation**: Auto-generate default schema data
- **Merging**: Combine user data with defaults

### Model Integration

The `Page` model automatically handles schema validation:

```php
// Automatic validation on assignment
$page->schema_data = $userSchemaData;

// Enhanced getter with computed properties
$schemaData = $page->schema_data;
```

## Public Page Integration

Schema markup is automatically rendered on public pages when schema data exists:

```blade
@if($page->schema_data)
<script type="application/ld+json">
{!! json_encode($page->schema_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
```

The system automatically generates default schema data from page attributes when no custom schema is provided, ensuring all published pages have structured data for SEO.

## AEO (Answer Engine Optimization) Features

The enhanced schema system includes Answer Engine Optimization features to improve content discoverability in AI-powered search engines:

### FAQ Schema Support
```php
// Create a FAQ page with structured Q&A data
$page = new Page([
    'schema_type' => 'FAQPage',
    'faq_data' => [
        [
            'question' => 'What is AEO?',
            'answer' => 'Answer Engine Optimization for AI search results.'
        ]
    ]
]);
```

### Breadcrumb Navigation
Automatically generates breadcrumb schema based on content categorization:
```json
{
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home"},
    {"@type": "ListItem", "position": 2, "name": "Technology"},
    {"@type": "ListItem", "position": 3, "name": "Current Page"}
  ]
}
```

### Content Categorization
Enhanced topic and keyword taxonomy for better AI understanding:
```php
$page->topics = ['Technology', 'AI', 'Machine Learning'];
$page->keywords = ['artificial intelligence', 'automation', 'algorithms'];
```

### Semantic HTML5 Structure
Public pages render with semantic HTML5 elements and microdata:
- `<article>` with appropriate schema.org itemtype
- `<header>` with headline and meta information
- `<section>` with structured content
- Microdata attributes (`itemprop`, `itemscope`, `itemtype`)

### Auto-Generated Content Signals
- **Reading Time**: Automatically calculated based on word count
- **Content Quality**: Language detection and content categorization
- **Topic Classification**: Structured entity relationships

## Usage

### Frontend Type Safety

TypeScript interfaces provide compile-time safety:

```typescript
interface ArticleSchema extends BaseSchema {
    '@type': 'Article';
    headline: string;
    articleBody: string;
    wordCount?: number;
}
```

### Controller Validation

Dynamic validation rules based on schema type:

```php
public function store(Request $request, SchemaValidationService $schemaService)
{
    $schemaValidationRules = $schemaService->getValidationRulesForRequest($schemaType);
    $validated = $request->validate($schemaValidationRules);
}
```

## Schema Types

### WebPage (Default)

Basic web page with standard metadata:
- **Required**: name, description
- **Optional**: url, mainEntityOfPage, breadcrumb

### Article

Editorial content with article-specific properties:
- **Required**: headline, articleBody
- **Optional**: wordCount, articleSection, keywords, inLanguage

### BlogPosting

Extends Article with blog-specific metadata:
- **Additional**: blogCategory, tags
- **Inherits**: All Article properties

### NewsArticle

Extends Article with journalistic properties:
- **Additional**: dateline, printColumn, printEdition, printPage, printSection
- **Inherits**: All Article properties

## Best Practices

### 1. Type Selection

Choose the most specific applicable type:
- Use `WebPage` for general pages (about, contact)
- Use `Article` for editorial content
- Use `BlogPosting` for blog posts
- Use `NewsArticle` for news content

### 2. Required Fields

Always provide required fields for your chosen schema type:

```php
// Article requires headline and articleBody
$schema_data = [
    'headline' => 'Article Title',
    'articleBody' => 'Full article content...'
];
```

### 3. Validation Handling

Handle validation gracefully in your application:

```php
try {
    $validated = $schemaService->validateSchemaData($type, $data);
} catch (ValidationException $e) {
    // Handle validation errors
}
```

### 4. Auto-Generation

Leverage auto-generation for common properties:

```php
// These are automatically generated if not provided
$schema_data = $schemaService->generateDefaultSchemaData($type, $pageData);
```

## Configuration

### Adding New Schema Types

1. Add type configuration to `config/schema.php`:

```php
'MyNewType' => [
    'label' => 'My New Type',
    'description' => 'Description of new type',
    'fields' => [
        'customField' => 'required|string|max:100',
    ],
    'required_properties' => ['customField'],
]
```

2. Add TypeScript interface:

```typescript
interface MyNewTypeSchema extends BaseSchema {
    '@type': 'MyNewType';
    customField: string;
}
```

3. Update union types:

```typescript
export type SchemaData = WebPageSchema | ArticleSchema | MyNewTypeSchema;
export type SchemaType = 'WebPage' | 'Article' | 'MyNewType';
```

### Extending Existing Types

Use the `extends` property to inherit from parent types:

```php
'SpecialArticle' => [
    'extends' => 'Article',
    'fields' => [
        'specialProperty' => 'nullable|string|max:200',
    ],
]
```

## Error Handling

### Validation Errors

The system provides detailed validation errors:

```php
try {
    $schemaService->validateSchemaData($type, $data);
} catch (ValidationException $e) {
    $errors = $e->validator->errors();
    foreach ($errors->all() as $error) {
        echo $error;
    }
}
```

### Graceful Degradation

Invalid schema data is logged but doesn't break the application:

```php
// Invalid data is stored with warning logged
\Log::warning('Schema data validation failed', [
    'page_id' => $page->id,
    'errors' => $e->getMessage()
]);
```

## Performance Considerations

### Caching

- Schema configurations are cached automatically
- Validation rules are memoized per request
- Consider caching generated schema data for high-traffic pages

### Database Optimization

- Schema data is stored as JSON for flexibility
- Use database indexes on schema_type for filtering
- Consider separate tables for complex schema requirements

## Testing

### Unit Tests

Test schema validation in isolation:

```php
public function test_article_schema_validation()
{
    $service = new SchemaValidationService();
    $data = ['headline' => 'Test', 'articleBody' => 'Content'];
    
    $validated = $service->validateSchemaData('Article', $data);
    
    $this->assertEquals('Test', $validated['headline']);
}
```

### Integration Tests

Test full page creation with schema validation:

```php
public function test_page_creation_with_valid_schema()
{
    $response = $this->post('/content/pages', [
        'title' => 'Test Article',
        'schema_type' => 'Article',
        'schema_data' => [
            'headline' => 'Test Headline',
            'articleBody' => 'Test content'
        ]
    ]);
    
    $response->assertRedirect();
}
```

## Migration Guide

### From Previous System

1. Existing pages with hardcoded schema types will continue working
2. New validation rules apply only to new/updated pages
3. Consider migrating existing schema_data to match new structure

### Breaking Changes

- Schema type validation is now stricter
- Some previously valid data may now fail validation
- Frontend components expect new schema type format

## Troubleshooting

### Common Issues

1. **Unknown schema type**: Check `config/schema.php` for available types
2. **Validation failures**: Review field requirements for your schema type
3. **TypeScript errors**: Ensure schema interfaces are updated
4. **Frontend dropdown empty**: Verify service injection in controller

### Debug Mode

Enable detailed logging for schema validation:

```php
// In config/logging.php
'channels' => [
    'schema' => [
        'driver' => 'single',
        'path' => storage_path('logs/schema.log'),
        'level' => 'debug',
    ],
]
```

## Related Documentation

- [Pages CMS Guide](Pages-CMS-Guide.md)
- [Template System Guide](Template-System-Guide.md)
- [API Reference](API-Reference.md)
- [Testing Strategy](Testing-Strategy.md)