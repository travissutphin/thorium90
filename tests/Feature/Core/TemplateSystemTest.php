<?php

namespace Tests\Feature\Core;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\WithRoles;
use App\Models\User;
use App\Models\Page;

class TemplateSystemTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
        ]);
    }

    /** @test */
    public function it_can_render_pages_with_new_template_system()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => '<p>This is test content</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'template' => 'core-page',
            'layout' => 'default',
            'theme' => 'default'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee($page->title);
        $response->assertSee('This is test content', false);
    }

    /** @test */
    public function it_can_render_pages_with_sidebar_layout()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Sidebar Page',
            'slug' => 'sidebar-page',
            'content' => '<p>Content with sidebar</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'template' => 'core-page',
            'layout' => 'sidebar'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee($page->title);
        $response->assertSee('Content with sidebar', false);
    }

    /** @test */
    public function it_can_render_pages_with_full_width_layout()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Full Width Page',
            'slug' => 'full-width-page',
            'content' => '<p>Full width content</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'template' => 'core-page',
            'layout' => 'full-width'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee($page->title);
        $response->assertSee('Full width content', false);
    }

    /** @test */
    public function it_falls_back_to_default_template_for_unknown_templates()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Unknown Template Page',
            'slug' => 'unknown-template-page',
            'content' => '<p>Content with unknown template</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'template' => 'non-existent-template'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee($page->title);
        $response->assertSee('Content with unknown template', false);
    }

    /** @test */
    public function it_includes_seo_meta_tags_in_rendered_pages()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'SEO Test Page',
            'slug' => 'seo-test-page',
            'content' => '<p>SEO content</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'meta_title' => 'Custom SEO Title',
            'meta_description' => 'Custom SEO description for testing',
            'meta_keywords' => 'seo, testing, meta'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee('Custom SEO Title', false);
        $response->assertSee('Custom SEO description for testing', false);
        $response->assertSee('seo, testing, meta', false);
    }

    /** @test */
    public function it_includes_schema_markup_in_rendered_pages()
    {
        $user = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Schema Test Page',
            'slug' => 'schema-test-page',
            'content' => '<p>Schema content</p>',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $user->id,
            'schema_type' => 'Article'
        ]);

        $response = $this->get("/{$page->slug}");

        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Article"', false);
        $response->assertSee($page->title, false);
    }

    /** @test */
    public function admin_can_create_page_with_template_settings()
    {
        $admin = $this->createUserWithRole('Admin');

        $pageData = [
            'title' => 'Template Test Page',
            'slug' => 'template-test-page',
            'content' => '<p>Template test content</p>',
            'status' => 'published',
            'template' => 'core-page',
            'layout' => 'sidebar',
            'theme' => 'default',
            'meta_title' => 'Template Test SEO Title',
            'meta_description' => 'Template test description'
        ];

        $response = $this->actingAs($admin)
                         ->post(route('content.pages.store'), $pageData);

        $response->assertRedirect(route('content.pages.index'));
        
        $this->assertDatabaseHas('pages', [
            'slug' => 'template-test-page',
            'template' => 'core-page',
            'layout' => 'sidebar',
            'theme' => 'default'
        ]);
    }

    /** @test */
    public function it_can_update_page_template_settings()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $page = Page::factory()->create([
            'title' => 'Update Template Test',
            'slug' => 'update-template-test',
            'content' => '<p>Original content</p>',
            'user_id' => $admin->id,
            'template' => 'core-page',
            'layout' => 'default'
        ]);

        $updateData = [
            'title' => 'Updated Template Test',
            'slug' => 'update-template-test',
            'content' => '<p>Updated content</p>',
            'status' => 'published',
            'template' => 'core-page',
            'layout' => 'sidebar',
            'theme' => 'modern'
        ];

        $response = $this->actingAs($admin)
                         ->put(route('content.pages.update', $page), $updateData);

        $response->assertRedirect(route('content.pages.index'));
        
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Template Test',
            'layout' => 'sidebar',
            'theme' => 'modern'
        ]);
    }
}
