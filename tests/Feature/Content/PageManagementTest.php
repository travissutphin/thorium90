<?php

namespace Tests\Feature\Content;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class PageManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    /** @test */
    public function admin_can_view_pages_index()
    {
        $admin = $this->createUserWithRole('Admin');
        
        Page::factory()->count(3)->create();
        
        $response = $this->actingAs($admin)->get('/content/pages');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('content/pages/index')
            ->has('pages')
            ->has('stats')
        );
    }

    /** @test */
    public function admin_can_create_page()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'This is test content for the page.',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'is_featured' => false,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'meta_keywords' => 'test, keywords',
        ];
        
        $response = $this->actingAs($admin)->post('/content/pages', $pageData);
        
        $response->assertRedirect('/content/pages');
        $response->assertSessionHas('success', 'Page created successfully.');
        
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'user_id' => $admin->id,
        ]);
    }

    /** @test */
    public function page_slug_is_auto_generated_if_not_provided()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $pageData = [
            'title' => 'Test Page With Auto Slug',
            'content' => 'Content',
            'status' => 'draft',
        ];
        
        $response = $this->actingAs($admin)->post('/content/pages', $pageData);
        
        $response->assertRedirect('/content/pages');
        
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page With Auto Slug',
            'slug' => 'test-page-with-auto-slug',
        ]);
    }

    /** @test */
    public function admin_can_update_page()
    {
        $admin = $this->createUserWithRole('Admin');
        $page = Page::factory()->create(['user_id' => $admin->id]);
        
        $updateData = [
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'content' => 'Updated content',
            'status' => 'published',
            'is_featured' => true,
        ];
        
        $response = $this->actingAs($admin)->put("/content/pages/{$page->id}", $updateData);
        
        $response->assertRedirect('/content/pages');
        $response->assertSessionHas('success', 'Page updated successfully.');
        
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
            'slug' => 'updated-slug',
            'status' => 'published',
        ]);
    }

    /** @test */
    public function admin_can_delete_page()
    {
        $admin = $this->createUserWithRole('Admin');
        $page = Page::factory()->create();
        
        $response = $this->actingAs($admin)->delete("/content/pages/{$page->id}");
        
        $response->assertRedirect('/content/pages');
        $response->assertSessionHas('success', 'Page deleted successfully.');
        
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    /** @test */
    public function admin_can_publish_page()
    {
        $admin = $this->createUserWithRole('Admin');
        $page = Page::factory()->create(['status' => 'draft']);
        
        $response = $this->actingAs($admin)->patch("/content/pages/{$page->id}/publish");
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Page published successfully.');
        
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 'published',
        ]);
        
        $page->refresh();
        $this->assertNotNull($page->published_at);
    }

    /** @test */
    public function admin_can_unpublish_page()
    {
        $admin = $this->createUserWithRole('Admin');
        $page = Page::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);
        
        $response = $this->actingAs($admin)->patch("/content/pages/{$page->id}/unpublish");
        
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Page unpublished successfully.');
        
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /** @test */
    public function author_can_only_edit_own_pages()
    {
        $author = $this->createUserWithRole('Author');
        $ownPage = Page::factory()->create(['user_id' => $author->id]);
        $otherPage = Page::factory()->create();
        
        // Can edit own page
        $response = $this->actingAs($author)->get("/content/pages/{$ownPage->id}/edit");
        $response->assertStatus(200);
        
        // Cannot edit other's page
        $response = $this->actingAs($author)->get("/content/pages/{$otherPage->id}/edit");
        $response->assertStatus(403);
    }

    /** @test */
    public function subscriber_cannot_create_pages()
    {
        $subscriber = $this->createUserWithRole('Subscriber');
        
        $response = $this->actingAs($subscriber)->get('/content/pages/create');
        $response->assertStatus(403);
        
        $response = $this->actingAs($subscriber)->post('/content/pages', [
            'title' => 'Test',
            'content' => 'Content',
            'status' => 'draft',
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_page_management()
    {
        $response = $this->get('/content/pages');
        $response->assertRedirect('/login');
        
        $response = $this->post('/content/pages', []);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function bulk_publish_pages()
    {
        $admin = $this->createUserWithRole('Admin');
        $pages = Page::factory()->count(3)->create(['status' => 'draft']);
        
        $response = $this->actingAs($admin)->post('/content/pages/bulk-action', [
            'action' => 'publish',
            'page_ids' => $pages->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        foreach ($pages as $page) {
            $this->assertDatabaseHas('pages', [
                'id' => $page->id,
                'status' => 'published',
            ]);
        }
    }

    /** @test */
    public function bulk_feature_pages()
    {
        $admin = $this->createUserWithRole('Admin');
        $pages = Page::factory()->count(3)->create(['is_featured' => false]);
        
        $response = $this->actingAs($admin)->post('/content/pages/bulk-action', [
            'action' => 'feature',
            'page_ids' => $pages->pluck('id')->toArray(),
        ]);
        
        $response->assertRedirect();
        
        foreach ($pages as $page) {
            $this->assertDatabaseHas('pages', [
                'id' => $page->id,
                'is_featured' => true,
            ]);
        }
    }

    /** @test */
    public function page_validation_rules_are_enforced()
    {
        $admin = $this->createUserWithRole('Admin');
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => '', // Required
            'status' => 'invalid', // Invalid enum
        ]);
        
        $response->assertSessionHasErrors(['title', 'status']);
    }

    /** @test */
    public function duplicate_slug_is_handled()
    {
        $admin = $this->createUserWithRole('Admin');
        Page::factory()->create(['slug' => 'existing-slug']);
        
        $response = $this->actingAs($admin)->post('/content/pages', [
            'title' => 'New Page',
            'slug' => 'existing-slug',
            'content' => 'Content',
            'status' => 'draft',
        ]);
        
        $response->assertSessionHasErrors(['slug']);
    }

    /** @test */
    public function page_search_functionality_works()
    {
        $admin = $this->createUserWithRole('Admin');
        
        Page::factory()->create(['title' => 'Laravel Tutorial']);
        Page::factory()->create(['title' => 'PHP Guide']);
        Page::factory()->create(['content' => 'This mentions Laravel in content']);
        
        $response = $this->actingAs($admin)->get('/content/pages?search=Laravel');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('content/pages/index')
            ->where('pages.data', function ($pages) {
                return count($pages) === 2; // Should find 2 pages with "Laravel"
            })
        );
    }

    /** @test */
    public function page_status_filter_works()
    {
        $admin = $this->createUserWithRole('Admin');
        
        Page::factory()->count(2)->create(['status' => 'published']);
        Page::factory()->count(3)->create(['status' => 'draft']);
        
        $response = $this->actingAs($admin)->get('/content/pages?status=published');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('content/pages/index')
            ->where('pages.data', function ($pages) {
                return count($pages) === 2;
            })
        );
    }
}
