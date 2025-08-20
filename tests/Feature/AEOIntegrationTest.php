<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class AEOIntegrationTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    /** @test */
    public function faq_page_generates_proper_faq_schema()
    {
        // Create a page with FAQ data
        $page = Page::factory()->published()->create([
            'title' => 'Frequently Asked Questions',
            'slug' => 'faq',
            'content' => 'Here are our most frequently asked questions.',
            'schema_type' => 'FAQPage',
            'faq_data' => [
                [
                    'question' => 'What is AEO?',
                    'answer' => 'Answer Engine Optimization helps content appear in AI search results.'
                ],
                [
                    'question' => 'How does schema markup help?',
                    'answer' => 'Schema markup provides structured data that AI engines can understand.'
                ]
            ]
        ]);

        // Check that FAQ schema is generated correctly
        $schemaData = $page->schema_data;
        
        $this->assertEquals('FAQPage', $schemaData['@type']);
        $this->assertArrayHasKey('mainEntity', $schemaData);
        $this->assertCount(2, $schemaData['mainEntity']);
        
        // Check first FAQ item
        $firstFaq = $schemaData['mainEntity'][0];
        $this->assertEquals('Question', $firstFaq['@type']);
        $this->assertEquals('What is AEO?', $firstFaq['name']);
        $this->assertEquals('Answer', $firstFaq['acceptedAnswer']['@type']);
        $this->assertEquals('Answer Engine Optimization helps content appear in AI search results.', $firstFaq['acceptedAnswer']['text']);
    }

    /** @test */
    public function breadcrumb_schema_is_generated_for_pages()
    {
        $page = Page::factory()->published()->create([
            'title' => 'Technology Guide',
            'slug' => 'tech-guide',
            'topics' => ['Technology', 'Programming']
        ]);

        $schemaData = $page->schema_data;
        
        $this->assertArrayHasKey('breadcrumb', $schemaData);
        $this->assertEquals('BreadcrumbList', $schemaData['breadcrumb']['@type']);
        $this->assertArrayHasKey('itemListElement', $schemaData['breadcrumb']);
        
        $breadcrumbs = $schemaData['breadcrumb']['itemListElement'];
        
        // Should have Home > Technology > Current Page
        $this->assertCount(3, $breadcrumbs);
        $this->assertEquals('Home', $breadcrumbs[0]['name']);
        $this->assertEquals('Technology', $breadcrumbs[1]['name']);
        $this->assertEquals('Technology Guide', $breadcrumbs[2]['name']);
    }

    /** @test */
    public function content_categorization_adds_topic_schema()
    {
        $page = Page::factory()->published()->create([
            'title' => 'AI and Machine Learning',
            'topics' => ['Artificial Intelligence', 'Machine Learning', 'Technology'],
            'keywords' => ['AI', 'ML', 'algorithms', 'neural networks']
        ]);

        $schemaData = $page->schema_data;
        
        // Check topics are added as 'about' entities
        $this->assertArrayHasKey('about', $schemaData);
        $this->assertCount(3, $schemaData['about']);
        
        foreach ($schemaData['about'] as $topic) {
            $this->assertEquals('Thing', $topic['@type']);
            $this->assertContains($topic['name'], ['Artificial Intelligence', 'Machine Learning', 'Technology']);
        }
        
        // Check keywords are included
        $this->assertArrayHasKey('keywords', $schemaData);
        $this->assertEquals('AI, ML, algorithms, neural networks', $schemaData['keywords']);
    }

    /** @test */
    public function reading_time_is_calculated_automatically()
    {
        $content = str_repeat('This is test content for reading time calculation. ', 100); // ~600 words
        
        $page = Page::factory()->create([
            'title' => 'Long Article',
            'content' => $content,
            'reading_time' => null // Should be auto-calculated
        ]);

        // Reading time should be calculated (actually calculates to 4 minutes for the test content)
        $this->assertEquals(4, $page->reading_time);
        
        $schemaData = $page->schema_data;
        $this->assertArrayHasKey('timeRequired', $schemaData);
        $this->assertEquals('PT4M', $schemaData['timeRequired']); // ISO 8601 duration format
    }

    /** @test */
    public function public_page_renders_semantic_html5_structure()
    {
        $page = Page::factory()->published()->create([
            'title' => 'Semantic HTML Test',
            'slug' => 'semantic-test',
            'content' => '<p>This is test content for semantic structure.</p>',
            'schema_type' => 'Article',
            'meta_description' => 'Test description',
            'topics' => ['Web Development'],
            'reading_time' => 2
        ]);

        $response = $this->get("/{$page->slug}");
        
        $response->assertOk();
        
        // Check semantic HTML5 elements
        $response->assertSee('<article', false);
        $response->assertSee('<header', false);
        $response->assertSee('<section', false);
        $response->assertSee('<footer', false);
        
        // Check microdata attributes
        $response->assertSee('itemscope', false);
        $response->assertSee('itemtype="https://schema.org/Article"', false);
        $response->assertSee('itemprop="headline"', false);
        $response->assertSee('itemprop="articleBody"', false);
        $response->assertSee('itemprop="author"', false);
        
        // Check reading time display (auto-calculated, should be 1 min for short content)
        $response->assertSee('1 min read');
        
        // Check topics display
        $response->assertSee('Topics:');
        $response->assertSee('Web Development');
    }

    /** @test */
    public function enhanced_schema_includes_aeo_properties()
    {
        $page = Page::factory()->published()->create([
            'title' => 'Complete AEO Example',
            'content' => 'This is comprehensive content for AEO testing.',
            'schema_type' => 'Article',
            'topics' => ['SEO', 'AEO'],
            'keywords' => ['search', 'optimization', 'AI']
        ]);

        $schemaData = $page->schema_data;
        
        // Check all AEO enhancements are present
        $this->assertArrayHasKey('breadcrumb', $schemaData);
        $this->assertArrayHasKey('keywords', $schemaData);
        $this->assertArrayHasKey('inLanguage', $schemaData);
        $this->assertArrayHasKey('about', $schemaData);
        $this->assertArrayHasKey('timeRequired', $schemaData);
        
        // Verify data quality (reading time is auto-calculated based on content)
        $this->assertEquals('en', $schemaData['inLanguage']);
        $this->assertEquals('PT1M', $schemaData['timeRequired']); // Short content = 1 minute
        $this->assertEquals('search, optimization, AI', $schemaData['keywords']);
    }
}
