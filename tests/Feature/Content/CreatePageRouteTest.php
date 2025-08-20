<?php

namespace Tests\Feature\Content;

use App\Models\User;
use App\Services\SchemaValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatePageRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'create pages']);
        Permission::create(['name' => 'view pages']);

        // Create role and assign permissions (using Editor which is allowed in routes)
        $this->role = Role::create(['name' => 'Editor']);
        $this->role->givePermissionTo(['create pages', 'view pages']);

        // Create user and assign role
        $this->user = User::factory()->create();
        $this->user->assignRole($this->role);
    }

    public function test_create_page_route_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('content.pages.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('content/pages/create'));
    }

    public function test_create_page_route_has_schema_types()
    {
        $response = $this->actingAs($this->user)->get(route('content.pages.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('schemaTypes'));
        
        $schemaTypes = $response->inertiaPage()['props']['schemaTypes'];
        $this->assertIsArray($schemaTypes);
        $this->assertNotEmpty($schemaTypes);
        
        // Check structure
        foreach ($schemaTypes as $type) {
            $this->assertArrayHasKey('value', $type);
            $this->assertArrayHasKey('label', $type);
        }
        
        // Check that expected types are present
        $typeValues = collect($schemaTypes)->pluck('value')->toArray();
        $this->assertContains('WebPage', $typeValues);
        $this->assertContains('Article', $typeValues);
        $this->assertContains('BlogPosting', $typeValues);
        $this->assertContains('NewsArticle', $typeValues);
    }

    public function test_schema_validation_service_is_working()
    {
        $service = app(SchemaValidationService::class);
        
        $this->assertInstanceOf(SchemaValidationService::class, $service);
        
        $types = $service->getAvailableTypes();
        $this->assertIsArray($types);
        $this->assertNotEmpty($types);
        
        // Test specific type configuration
        $webPageConfig = $service->getTypeConfig('WebPage');
        $this->assertIsArray($webPageConfig);
        $this->assertArrayHasKey('label', $webPageConfig);
        $this->assertEquals('Web Page (Default)', $webPageConfig['label']);
    }

    public function test_unauthorized_user_cannot_access_create_page()
    {
        $unauthorizedUser = User::factory()->create();
        
        $response = $this->actingAs($unauthorizedUser)->get(route('content.pages.create'));
        
        $response->assertForbidden();
    }

    public function test_guest_user_is_redirected_to_login()
    {
        $response = $this->get(route('content.pages.create'));
        
        $response->assertRedirect(route('login'));
    }
}