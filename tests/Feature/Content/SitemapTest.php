<?php

namespace Tests\Feature\Content;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    /** @test */
    public function sitemap_is_accessible()
    {
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    /** @test */
    public function sitemap_contains_published_pages()
    {
        // Create published pages
        $publishedPages = Page::factory()->published()->count(3)->create();
        
        // Create draft pages (should not appear in sitemap)
        Page::factory()->draft()->count(2)->create();
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        
        // Check that published pages are in the sitemap
        foreach ($publishedPages as $page) {
            $response->assertSee("<loc>" . url('/pages/' . $page->slug) . "</loc>", false);
        }
        
        // Verify XML structure
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('</urlset>', false);
    }

    /** @test */
    public function sitemap_excludes_draft_pages()
    {
        $draftPage = Page::factory()->draft()->create(['slug' => 'draft-page']);
        $publishedPage = Page::factory()->published()->create(['slug' => 'published-page']);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('published-page');
        $response->assertDontSee('draft-page');
    }

    /** @test */
    public function sitemap_excludes_private_pages()
    {
        $privatePage = Page::factory()->private()->create(['slug' => 'private-page']);
        $publishedPage = Page::factory()->published()->create(['slug' => 'public-page']);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('public-page');
        $response->assertDontSee('private-page');
    }

    /** @test */
    public function sitemap_excludes_future_scheduled_pages()
    {
        // Create a page scheduled for the future
        $futurePage = Page::factory()->create([
            'slug' => 'future-page',
            'status' => 'published',
            'published_at' => now()->addDays(7),
        ]);
        
        // Create a normally published page
        $currentPage = Page::factory()->published()->create(['slug' => 'current-page']);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('current-page');
        $response->assertDontSee('future-page');
    }

    /** @test */
    public function sitemap_includes_lastmod_date()
    {
        $page = Page::factory()->published()->create([
            'slug' => 'test-page',
            'updated_at' => now()->subDays(3),
        ]);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('<lastmod>' . $page->updated_at->toW3cString() . '</lastmod>', false);
    }

    /** @test */
    public function sitemap_includes_changefreq_and_priority()
    {
        Page::factory()->published()->create();
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('<changefreq>weekly</changefreq>', false);
        $response->assertSee('<priority>0.8</priority>', false);
    }

    /** @test */
    public function sitemap_orders_pages_by_updated_date()
    {
        $oldPage = Page::factory()->published()->create([
            'slug' => 'old-page',
            'updated_at' => now()->subDays(10),
        ]);
        
        $newPage = Page::factory()->published()->create([
            'slug' => 'new-page',
            'updated_at' => now()->subDays(1),
        ]);
        
        $middlePage = Page::factory()->published()->create([
            'slug' => 'middle-page',
            'updated_at' => now()->subDays(5),
        ]);
        
        $response = $this->get('/sitemap.xml');
        
        $content = $response->getContent();
        
        // Check that pages appear in the correct order (newest first)
        $newPos = strpos($content, 'new-page');
        $middlePos = strpos($content, 'middle-page');
        $oldPos = strpos($content, 'old-page');
        
        $this->assertLessThan($middlePos, $newPos);
        $this->assertLessThan($oldPos, $middlePos);
    }

    /** @test */
    public function sitemap_handles_special_characters_in_urls()
    {
        $page = Page::factory()->published()->create([
            'slug' => 'page-with-special-chars-&-symbols',
        ]);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        // Check that special characters are properly encoded
        $response->assertSee('page-with-special-chars-&amp;-symbols');
    }

    /** @test */
    public function sitemap_is_valid_xml()
    {
        Page::factory()->published()->count(5)->create();
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        
        // Attempt to parse the XML to ensure it's valid
        $xml = simplexml_load_string($response->getContent());
        
        $this->assertNotFalse($xml);
        $this->assertEquals('urlset', $xml->getName());
        $this->assertGreaterThan(0, count($xml->url));
    }

    /** @test */
    public function empty_sitemap_returns_valid_xml()
    {
        // No pages created
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        
        // Should still have valid XML structure
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('</urlset>', false);
    }

    /** @test */
    public function sitemap_excludes_soft_deleted_pages()
    {
        $page = Page::factory()->published()->create(['slug' => 'deleted-page']);
        $page->delete(); // Soft delete
        
        $activePage = Page::factory()->published()->create(['slug' => 'active-page']);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('active-page');
        $response->assertDontSee('deleted-page');
    }

    /** @test */
    public function sitemap_performance_with_many_pages()
    {
        // Create a large number of pages to test performance
        Page::factory()->published()->count(100)->create();
        
        $startTime = microtime(true);
        $response = $this->get('/sitemap.xml');
        $endTime = microtime(true);
        
        $response->assertStatus(200);
        
        // Ensure the sitemap generates in a reasonable time (less than 2 seconds)
        $executionTime = $endTime - $startTime;
        $this->assertLessThan(2, $executionTime, 'Sitemap generation took too long');
        
        // Verify all pages are included
        $xml = simplexml_load_string($response->getContent());
        $this->assertCount(100, $xml->url);
    }

    /** @test */
    public function sitemap_respects_base_url_configuration()
    {
        config(['app.url' => 'https://example.com']);
        
        $page = Page::factory()->published()->create(['slug' => 'test-page']);
        
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertSee('<loc>https://example.com/pages/test-page</loc>', false);
    }
}
