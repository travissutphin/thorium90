<?php

namespace Tests\Feature\Content;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class PageSEOTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function page_generates_meta_title_from_title_if_not_provided()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Test Page Title',
            'content' => 'Content',
            'status' => 'draft',
        ]);
        
        $page = Page::where('title', 'Test Page Title')->first();
        
        $this->assertNotNull($page);
        $this->assertEquals('Test Page Title', $page->meta_title);
    }

    /** @test */
    public function page_generates_meta_description_from_excerpt_or_content()
    {
        $page = Page::factory()->create([
            'content' => 'This is a long content that should be truncated for meta description. ' . str_repeat('Lorem ipsum dolor sit amet. ', 20),
            'excerpt' => 'This is the excerpt',
            'meta_description' => null,
        ]);
        
        // Should use excerpt when meta_description is null
        $this->assertEquals('This is the excerpt', $page->meta_description);
        
        // Test without excerpt
        $page2 = Page::factory()->create([
            'content' => 'This is content without excerpt',
            'excerpt' => null,
            'meta_description' => null,
        ]);
        
        $this->assertStringContainsString('This is content without excerpt', $page2->meta_description);
    }

    /** @test */
    public function page_stores_seo_metadata()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $seoData = [
            'title' => 'Page with SEO',
            'content' => 'Content',
            'status' => 'draft',
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom meta description for search engines',
            'meta_keywords' => 'keyword1, keyword2, keyword3',
        ];
        
        $response = $this->actingAs($admin)->post('/content/pages', $seoData);
        
        $response->assertRedirect();
        
        $this->assertDatabaseHas('pages', [
            'title' => 'Page with SEO',
            'meta_title' => 'Custom Meta Title',
            'meta_description' => 'Custom meta description for search engines',
            'meta_keywords' => 'keyword1, keyword2, keyword3',
        ]);
    }

    /** @test */
    public function page_generates_schema_data()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $page = Page::factory()->create([
            'title' => 'Test Article',
            'schema_type' => 'Article',
            'user_id' => $user->id,
            'published_at' => now(),
        ]);
        
        $schemaData = $page->schema_data;
        
        $this->assertIsArray($schemaData);
        $this->assertEquals('https://schema.org', $schemaData['@context']);
        $this->assertEquals('Article', $schemaData['@type']);
        $this->assertEquals('Test Article', $schemaData['name']);
        $this->assertEquals('John Doe', $schemaData['author']['name']);
        $this->assertArrayHasKey('datePublished', $schemaData);
        $this->assertArrayHasKey('dateModified', $schemaData);
    }

    /** @test */
    public function page_meta_title_has_character_limit()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $longTitle = str_repeat('a', 256); // Exceeds 255 character limit
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Test',
            'content' => 'Content',
            'status' => 'draft',
            'meta_title' => $longTitle,
        ]);
        
        $response->assertSessionHasErrors(['meta_title']);
    }

    /** @test */
    public function page_meta_description_has_character_limit()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $longDescription = str_repeat('a', 501); // Exceeds 500 character limit
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Test',
            'content' => 'Content',
            'status' => 'draft',
            'meta_description' => $longDescription,
        ]);
        
        $response->assertSessionHasErrors(['meta_description']);
    }

    /** @test */
    public function page_generates_full_meta_title_with_site_name()
    {
        config(['app.name' => 'Test Site']);
        
        $page = Page::factory()->create([
            'title' => 'Page Title',
            'meta_title' => 'Custom Meta Title',
        ]);
        
        $fullMetaTitle = $page->full_meta_title;
        
        $this->assertEquals('Custom Meta Title | Test Site', $fullMetaTitle);
    }

    /** @test */
    public function page_calculates_reading_time()
    {
        // Average reading speed is 200 words per minute
        $shortContent = str_repeat('word ', 100); // 100 words = ~0.5 min, rounds to 1
        $mediumContent = str_repeat('word ', 600); // 600 words = 3 minutes
        $longContent = str_repeat('word ', 2000); // 2000 words = 10 minutes
        
        $shortPage = Page::factory()->create(['content' => $shortContent]);
        $mediumPage = Page::factory()->create(['content' => $mediumContent]);
        $longPage = Page::factory()->create(['content' => $longContent]);
        
        $this->assertEquals(1, $shortPage->reading_time); // Minimum 1 minute
        $this->assertEquals(3, $mediumPage->reading_time);
        $this->assertEquals(10, $longPage->reading_time);
    }

    /** @test */
    public function page_url_is_generated_correctly()
    {
        $page = Page::factory()->create(['slug' => 'test-page-slug']);
        
        $expectedUrl = route('pages.show', 'test-page-slug');
        
        $this->assertEquals($expectedUrl, $page->url);
    }

    /** @test */
    public function page_with_open_graph_data()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $ogData = [
            'title' => 'Page with OG',
            'content' => 'Content',
            'status' => 'published',
            'og_title' => 'Open Graph Title',
            'og_description' => 'Open Graph Description',
            'og_type' => 'article',
        ];
        
        $response = $this->actingAs($admin)->post('/content/pages', $ogData);
        
        $page = Page::where('title', 'Page with OG')->first();
        
        $this->assertNotNull($page);
        $this->assertEquals('Open Graph Title', $page->og_title);
        $this->assertEquals('Open Graph Description', $page->og_description);
        $this->assertEquals('article', $page->og_type);
    }

    /** @test */
    public function page_with_twitter_card_data()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $twitterData = [
            'title' => 'Page with Twitter',
            'content' => 'Content',
            'status' => 'published',
            'twitter_title' => 'Twitter Card Title',
            'twitter_description' => 'Twitter Card Description',
            'twitter_card' => 'summary_large_image',
        ];
        
        $response = $this->actingAs($admin)->post('/content/pages', $twitterData);
        
        $page = Page::where('title', 'Page with Twitter')->first();
        
        $this->assertNotNull($page);
        $this->assertEquals('Twitter Card Title', $page->twitter_title);
        $this->assertEquals('Twitter Card Description', $page->twitter_description);
        $this->assertEquals('summary_large_image', $page->twitter_card);
    }

    /** @test */
    public function page_canonical_url_can_be_set()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Page with Canonical',
            'content' => 'Content',
            'status' => 'published',
            'canonical_url' => 'https://example.com/canonical-page',
        ]);
        
        $page = Page::where('title', 'Page with Canonical')->first();
        
        $this->assertNotNull($page);
        $this->assertEquals('https://example.com/canonical-page', $page->canonical_url);
    }

    /** @test */
    public function page_robots_meta_can_be_configured()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'Page with Robots',
            'content' => 'Content',
            'status' => 'published',
            'robots_meta' => 'noindex,nofollow',
        ]);
        
        $page = Page::where('title', 'Page with Robots')->first();
        
        $this->assertNotNull($page);
        $this->assertEquals('noindex,nofollow', $page->robots_meta);
    }

    /** @test */
    public function featured_pages_can_be_queried()
    {
        Page::factory()->featured()->count(3)->create();
        Page::factory()->notFeatured()->count(5)->create();
        
        $featuredPages = Page::featured()->get();
        
        $this->assertCount(3, $featuredPages);
        $this->assertTrue($featuredPages->every(fn($page) => $page->is_featured === true));
    }

    /** @test */
    public function published_pages_scope_filters_correctly()
    {
        // Create pages with different statuses
        Page::factory()->published()->count(3)->create();
        Page::factory()->draft()->count(2)->create();
        Page::factory()->private()->count(1)->create();
        
        // Create a scheduled page (published status but future date)
        Page::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDays(7),
        ]);
        
        $publishedPages = Page::published()->get();
        
        $this->assertCount(3, $publishedPages);
        $this->assertTrue($publishedPages->every(function ($page) {
            return $page->status === 'published' && 
                   $page->published_at !== null && 
                   $page->published_at <= now();
        }));
    }

    /** @test */
    public function public_page_renders_schema_markup_when_schema_data_exists()
    {
        // Create a published page with schema data
        $page = Page::factory()->published()->create([
            'title' => 'Test Article Page',
            'slug' => 'test-article-page',
            'content' => 'This is test article content for schema markup testing.',
            'schema_type' => 'Article',
            'schema_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => 'Custom Article Headline',
                'articleBody' => 'Custom article body content',
                'name' => 'Custom Name for Article'
            ]
        ]);

        // Make request to public page
        $response = $this->get("/{$page->slug}");

        $response->assertOk();
        
        // Check that JSON-LD schema markup is present
        $response->assertSee('<script type="application/ld+json">', false);
        $response->assertSee('"@context": "https://schema.org"', false);
        $response->assertSee('"@type": "Article"', false);
        $response->assertSee('"headline": "Custom Article Headline"', false);
        $response->assertSee('"name": "Custom Name for Article"', false);
    }

    /** @test */
    public function public_page_renders_default_schema_markup_when_no_custom_schema_data()
    {
        // Create a published page without custom schema data
        $page = Page::factory()->published()->create([
            'title' => 'Test Page Default Schema',
            'slug' => 'test-page-default-schema',
            'content' => 'This page uses default schema data.',
            'schema_type' => 'WebPage',
            'schema_data' => null
        ]);

        // Make request to public page
        $response = $this->get("/{$page->slug}");

        $response->assertOk();
        
        // Check that JSON-LD schema markup IS present (auto-generated from page data)
        $response->assertSee('<script type="application/ld+json">', false);
        $response->assertSee('"@context": "https://schema.org"', false);
        $response->assertSee('"@type": "WebPage"', false);
        $response->assertSee('"name": "Test Page Default Schema"', false);
    }
}
