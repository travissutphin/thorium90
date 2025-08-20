<?php

namespace Tests\Feature\Content;

use App\Models\Page;
use App\Models\User;
use App\Services\SchemaValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageSchemaValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'create pages']);
        Permission::create(['name' => 'edit pages']);
        Permission::create(['name' => 'edit own pages']);
        Permission::create(['name' => 'view pages']);

        // Create role and assign permissions (using Editor which is allowed in routes)
        $this->role = Role::create(['name' => 'Editor']);
        $this->role->givePermissionTo(['create pages', 'edit pages', 'edit own pages', 'view pages']);

        // Create user and assign role
        $this->user = User::factory()->create();
        $this->user->assignRole($this->role);
    }

    public function test_page_creation_with_valid_webpage_schema()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Page',
            'content' => 'Test content',
            'status' => 'draft',
            'schema_type' => 'WebPage',
            'schema_data' => [
                'name' => 'Test Page Name',
                'description' => 'Test page description'
            ]
        ]);

        $response->assertRedirect(route('content.pages.index'));
        $response->assertSessionHas('success');

        $page = Page::where('title', 'Test Page')->first();
        $this->assertNotNull($page);
        $this->assertEquals('WebPage', $page->schema_type);
        $this->assertArrayHasKey('name', $page->schema_data);
        $this->assertEquals('Test Page Name', $page->schema_data['name']);
    }

    public function test_page_creation_with_valid_article_schema()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Article',
            'content' => 'Article content here',
            'status' => 'draft',
            'schema_type' => 'Article',
            'schema_data' => [
                'headline' => 'Test Article Headline',
                'articleBody' => 'This is the article body content',
                'wordCount' => 150,
                'keywords' => 'test, article, content'
            ]
        ]);

        $response->assertRedirect(route('content.pages.index'));

        $page = Page::where('title', 'Test Article')->first();
        $this->assertNotNull($page);
        $this->assertEquals('Article', $page->schema_type);
        $this->assertEquals('Test Article Headline', $page->schema_data['headline']);
        // wordCount should be computed from actual content, not user-provided value
        $this->assertEquals(3, $page->schema_data['wordCount']); // "Article content here" = 3 words
    }

    public function test_page_creation_with_blog_posting_schema()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Blog Post',
            'content' => 'Blog post content',
            'status' => 'draft',
            'schema_type' => 'BlogPosting',
            'schema_data' => [
                'headline' => 'Blog Post Headline',
                'articleBody' => 'Blog post body content',
                'blogCategory' => 'Technology',
                'tags' => ['tech', 'coding', 'web']
            ]
        ]);

        $response->assertRedirect(route('content.pages.index'));

        $page = Page::where('title', 'Test Blog Post')->first();
        $this->assertNotNull($page);
        $this->assertEquals('BlogPosting', $page->schema_type);
        $this->assertEquals('Technology', $page->schema_data['blogCategory']);
        $this->assertContains('tech', $page->schema_data['tags']);
    }

    public function test_page_creation_with_news_article_schema()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Breaking News',
            'content' => 'News article content',
            'status' => 'draft',
            'schema_type' => 'NewsArticle',
            'schema_data' => [
                'headline' => 'Breaking News Headline',
                'articleBody' => 'News article body content',
                'dateline' => 'New York, NY',
                'printSection' => 'Front Page'
            ]
        ]);

        $response->assertRedirect(route('content.pages.index'));

        $page = Page::where('title', 'Breaking News')->first();
        $this->assertNotNull($page);
        $this->assertEquals('NewsArticle', $page->schema_type);
        $this->assertEquals('New York, NY', $page->schema_data['dateline']);
    }

    public function test_page_creation_fails_with_invalid_schema_type()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Page',
            'content' => 'Test content',
            'status' => 'draft',
            'schema_type' => 'InvalidType',
            'schema_data' => []
        ]);

        $response->assertSessionHasErrors(['schema_type']);
    }

    public function test_page_creation_fails_with_missing_required_schema_fields()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Article',
            'content' => 'Test content',
            'status' => 'draft',
            'schema_type' => 'Article',
            'schema_data' => [
                // Missing required 'headline' and 'articleBody'
                'keywords' => 'test'
            ]
        ]);

        $response->assertSessionHasErrors(['schema_data.headline', 'schema_data.articleBody']);
    }

    public function test_page_creation_fails_with_invalid_schema_field_types()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Article',
            'content' => 'Test content',
            'status' => 'draft',
            'schema_type' => 'Article',
            'schema_data' => [
                'headline' => 'Valid headline',
                'articleBody' => 'Valid content',
                'wordCount' => 'not-a-number' // Should be integer
            ]
        ]);

        $response->assertSessionHasErrors(['schema_data.wordCount']);
    }

    public function test_page_update_with_schema_validation()
    {
        $page = Page::factory()->create([
            'user_id' => $this->user->id,
            'schema_type' => 'WebPage'
        ]);

        $response = $this->actingAs($this->user)->put(route('content.pages.update', $page), [
            'title' => 'Updated Page',
            'content' => 'Updated content',
            'status' => 'published',
            'schema_type' => 'Article',
            'schema_data' => [
                'headline' => 'Updated Article Headline',
                'articleBody' => 'Updated article content'
            ]
        ]);

        $response->assertRedirect(route('content.pages.index'));

        $page->refresh();
        $this->assertEquals('Article', $page->schema_type);
        $this->assertEquals('Updated Article Headline', $page->schema_data['headline']);
    }

    public function test_page_schema_data_auto_enhancement()
    {
        $page = Page::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Page',
            'content' => 'Test content',
            'schema_type' => 'Article',
            'schema_data' => [
                'headline' => 'Custom Headline',
                'articleBody' => 'Custom content'
            ]
        ]);

        $schemaData = $page->schema_data;

        // Should have enhanced properties
        $this->assertEquals('https://schema.org', $schemaData['@context']);
        $this->assertEquals('Article', $schemaData['@type']);
        $this->assertArrayHasKey('dateModified', $schemaData);
        $this->assertArrayHasKey('author', $schemaData);
        $this->assertArrayHasKey('publisher', $schemaData);
        $this->assertArrayHasKey('wordCount', $schemaData);
    }

    public function test_page_schema_data_generation_without_explicit_data()
    {
        $page = Page::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Auto Generated Schema',
            'content' => 'This content will be used for auto generation',
            'excerpt' => 'This is the excerpt',
            'schema_type' => 'WebPage'
        ]);

        $schemaData = $page->schema_data;

        // Should auto-generate from page data
        $this->assertEquals('Auto Generated Schema', $schemaData['name']);
        $this->assertEquals('This is the excerpt', $schemaData['description']);
        $this->assertEquals('WebPage', $schemaData['@type']);
    }

    public function test_schema_types_dropdown_in_create_form()
    {
        $response = $this->actingAs($this->user)->get(route('content.pages.create'));

        $response->assertOk();
        
        // Should pass schema types to the view
        $response->assertInertia(fn ($page) => $page->has('schemaTypes'));
        
        $schemaTypes = $response->inertiaPage()['props']['schemaTypes'];
        $this->assertIsArray($schemaTypes);
        
        // Check that all expected types are present
        $typeValues = collect($schemaTypes)->pluck('value')->toArray();
        $this->assertContains('WebPage', $typeValues);
        $this->assertContains('Article', $typeValues);
        $this->assertContains('BlogPosting', $typeValues);
        $this->assertContains('NewsArticle', $typeValues);
    }

    public function test_schema_types_dropdown_in_edit_form()
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('content.pages.edit', $page));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('schemaTypes'));
    }

    public function test_page_creation_without_schema_data_uses_defaults()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Page',
            'content' => 'Test content',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'schema_type' => 'WebPage'
            // No schema_data provided
        ]);

        $response->assertRedirect(route('content.pages.index'));

        $page = Page::where('title', 'Test Page')->first();
        $schemaData = $page->schema_data;

        // Should have auto-generated data
        $this->assertEquals('Test Page', $schemaData['name']);
        $this->assertEquals('Test excerpt', $schemaData['description']);
    }

    public function test_page_validates_array_schema_fields()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Blog',
            'content' => 'Blog content',
            'status' => 'draft',
            'schema_type' => 'BlogPosting',
            'schema_data' => [
                'headline' => 'Blog Headline',
                'articleBody' => 'Blog content',
                'tags' => 'not-an-array' // Should be array
            ]
        ]);

        $response->assertSessionHasErrors(['schema_data.tags']);
    }

    public function test_page_validates_string_length_limits()
    {
        $response = $this->actingAs($this->user)->post(route('content.pages.store'), [
            'title' => 'Test Article',
            'content' => 'Article content',
            'status' => 'draft',
            'schema_type' => 'Article',
            'schema_data' => [
                'headline' => str_repeat('a', 200), // Exceeds max length
                'articleBody' => 'Valid content'
            ]
        ]);

        $response->assertSessionHasErrors(['schema_data.headline']);
    }

    public function test_schema_service_integration()
    {
        $service = app(SchemaValidationService::class);
        
        // Test service is properly bound
        $this->assertInstanceOf(SchemaValidationService::class, $service);
        
        // Test it works with real config
        $types = $service->getAvailableTypes();
        $this->assertNotEmpty($types);
    }

    public function test_page_model_schema_validation_on_assignment()
    {
        $page = Page::factory()->make([
            'title' => 'Test Page',
            'schema_type' => 'Article',
            'user_id' => $this->user->id
        ]);

        // Valid schema data should work
        $page->schema_data = [
            'headline' => 'Valid Headline',
            'articleBody' => 'Valid content'
        ];

        $this->assertArrayHasKey('headline', $page->schema_data);

        // Invalid schema data should be handled gracefully
        $page->schema_data = [
            'headline' => str_repeat('a', 200), // Too long
            'articleBody' => 'Valid content'
        ];

        // Should still be set but validation errors logged
        $this->assertNotNull($page->getAttributes()['schema_data']);
    }
}