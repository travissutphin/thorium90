<?php

namespace Tests\Unit;

use App\Services\SchemaValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchemaValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SchemaValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchemaValidationService();
    }

    public function test_gets_available_schema_types()
    {
        $types = $this->service->getAvailableTypes();

        $this->assertIsArray($types);
        $this->assertNotEmpty($types);
        
        // Check required fields for each type
        foreach ($types as $type) {
            $this->assertArrayHasKey('value', $type);
            $this->assertArrayHasKey('label', $type);
        }

        // Verify specific types exist
        $typeValues = collect($types)->pluck('value')->toArray();
        $this->assertContains('WebPage', $typeValues);
        $this->assertContains('Article', $typeValues);
        $this->assertContains('BlogPosting', $typeValues);
        $this->assertContains('NewsArticle', $typeValues);
    }

    public function test_gets_type_configuration()
    {
        $config = $this->service->getTypeConfig('WebPage');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('label', $config);
        $this->assertArrayHasKey('fields', $config);
        $this->assertArrayHasKey('required_properties', $config);
        $this->assertEquals('Web Page (Default)', $config['label']);
    }

    public function test_returns_null_for_unknown_type()
    {
        $config = $this->service->getTypeConfig('UnknownType');
        $this->assertNull($config);
    }

    public function test_validates_webpage_schema_data()
    {
        $data = [
            'schema_data' => [
                'name' => 'Test Page',
                'description' => 'Test description'
            ]
        ];

        $validated = $this->service->validateSchemaData('WebPage', $data);

        $this->assertArrayHasKey('schema_data', $validated);
        $this->assertEquals('Test Page', $validated['schema_data']['name']);
    }

    public function test_validates_article_schema_data()
    {
        $data = [
            'schema_data' => [
                'headline' => 'Test Article',
                'articleBody' => 'This is the article content',
                'wordCount' => 100
            ]
        ];

        $validated = $this->service->validateSchemaData('Article', $data);

        $this->assertArrayHasKey('schema_data', $validated);
        $this->assertEquals('Test Article', $validated['schema_data']['headline']);
        $this->assertEquals(100, $validated['schema_data']['wordCount']);
    }

    public function test_validates_blog_posting_schema_data()
    {
        $data = [
            'schema_data' => [
                'headline' => 'Test Blog Post',
                'articleBody' => 'Blog post content',
                'blogCategory' => 'Technology',
                'tags' => ['tech', 'coding']
            ]
        ];

        $validated = $this->service->validateSchemaData('BlogPosting', $data);

        $this->assertArrayHasKey('schema_data', $validated);
        $this->assertEquals('Technology', $validated['schema_data']['blogCategory']);
        $this->assertContains('tech', $validated['schema_data']['tags']);
    }

    public function test_validates_news_article_schema_data()
    {
        $data = [
            'schema_data' => [
                'headline' => 'Breaking News',
                'articleBody' => 'News article content',
                'dateline' => 'New York, NY',
                'printSection' => 'Front Page'
            ]
        ];

        $validated = $this->service->validateSchemaData('NewsArticle', $data);

        $this->assertArrayHasKey('schema_data', $validated);
        $this->assertEquals('New York, NY', $validated['schema_data']['dateline']);
    }

    public function test_fails_validation_for_missing_required_fields()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'schema_data' => [
                // Missing required 'headline' for Article
                'articleBody' => 'Content without headline'
            ]
        ];

        $this->service->validateSchemaData('Article', $data);
    }

    public function test_fails_validation_for_invalid_field_types()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'schema_data' => [
                'headline' => 'Valid headline',
                'articleBody' => 'Valid content',
                'wordCount' => 'not-a-number' // Should be integer
            ]
        ];

        $this->service->validateSchemaData('Article', $data);
    }

    public function test_fails_validation_for_unknown_schema_type()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'schema_data' => [
                'someField' => 'someValue'
            ]
        ];

        $this->service->validateSchemaData('UnknownType', $data);
    }

    public function test_generates_default_webpage_schema()
    {
        $pageData = [
            'title' => 'Test Page',
            'excerpt' => 'Test excerpt',
            'content' => 'Test content'
        ];

        $schema = $this->service->generateDefaultSchemaData('WebPage', $pageData);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('WebPage', $schema['@type']);
        $this->assertEquals('Test Page', $schema['name']);
        $this->assertEquals('Test excerpt', $schema['description']);
    }

    public function test_generates_default_article_schema()
    {
        $pageData = [
            'title' => 'Test Article',
            'content' => 'Article content here',
            'excerpt' => 'Article excerpt'
        ];

        $schema = $this->service->generateDefaultSchemaData('Article', $pageData);

        $this->assertEquals('Article', $schema['@type']);
        $this->assertEquals('Test Article', $schema['name']);
        $this->assertEquals('Test Article', $schema['headline']);
        $this->assertEquals('Article excerpt', $schema['description']);
    }

    public function test_merges_user_data_with_defaults()
    {
        $pageData = [
            'title' => 'Test Article',
            'content' => 'Content'
        ];

        $userData = [
            'headline' => 'Custom Headline',
            'keywords' => 'custom, keywords'
        ];

        $merged = $this->service->mergeWithDefaults('Article', $userData, $pageData);

        // User data should take precedence
        $this->assertEquals('Custom Headline', $merged['headline']);
        $this->assertEquals('custom, keywords', $merged['keywords']);
        
        // Defaults should be preserved
        $this->assertEquals('https://schema.org', $merged['@context']);
        $this->assertEquals('Article', $merged['@type']);
        $this->assertEquals('Test Article', $merged['name']);
    }

    public function test_gets_validation_rules_for_request()
    {
        $rules = $this->service->getValidationRulesForRequest('Article');

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('schema_data', $rules);
        $this->assertArrayHasKey('schema_data.headline', $rules);
        $this->assertArrayHasKey('schema_data.articleBody', $rules);
    }

    public function test_handles_inheritance_correctly()
    {
        // BlogPosting extends Article, so should include Article rules
        $rules = $this->service->getValidationRulesForRequest('BlogPosting');

        // Should have Article fields
        $this->assertArrayHasKey('schema_data.headline', $rules);
        $this->assertArrayHasKey('schema_data.articleBody', $rules);
        
        // Should have BlogPosting specific fields
        $this->assertArrayHasKey('schema_data.blogCategory', $rules);
        $this->assertArrayHasKey('schema_data.tags', $rules);
    }

    public function test_validates_with_empty_schema_data()
    {
        $data = [
            'schema_data' => [
                'name' => 'Test Page',
                'description' => 'Test description'
            ]
        ];

        $validated = $this->service->validateSchemaData('WebPage', $data);

        $this->assertIsArray($validated);
    }

    public function test_auto_generates_computed_properties()
    {
        $pageData = [
            'title' => 'Test Page',
            'content' => 'This is test content with multiple words for counting'
        ];

        $schema = $this->service->generateDefaultSchemaData('Article', $pageData);

        $this->assertEquals('Test Page', $schema['headline']);
        $this->assertEquals('en', $schema['inLanguage']); // Should default to app locale
    }

    public function test_handles_null_page_data_gracefully()
    {
        $schema = $this->service->generateDefaultSchemaData('WebPage', []);

        $this->assertEquals('https://schema.org', $schema['@context']);
        $this->assertEquals('WebPage', $schema['@type']);
        $this->assertArrayHasKey('name', $schema);
    }

    public function test_validates_array_fields_correctly()
    {
        $data = [
            'schema_data' => [
                'headline' => 'Test Blog',
                'articleBody' => 'Content',
                'tags' => ['valid', 'tags', 'array']
            ]
        ];

        $validated = $this->service->validateSchemaData('BlogPosting', $data);

        $this->assertIsArray($validated['schema_data']['tags']);
        $this->assertCount(3, $validated['schema_data']['tags']);
    }

    public function test_validates_string_length_limits()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'schema_data' => [
                'headline' => str_repeat('a', 200), // Exceeds 110 character limit
                'articleBody' => 'Valid content'
            ]
        ];

        $this->service->validateSchemaData('Article', $data);
    }
}