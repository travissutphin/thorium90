<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_guests_cannot_access_protected_routes()
    {
        $protectedRoutes = [
            '/dashboard',
            '/admin',
            '/admin/users',
            '/admin/roles',
            '/admin/settings',
            '/content/pages',
            '/content/media',
        ];

        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertRedirect('/login');
        }
    }

    public function test_dashboard_requires_view_dashboard_permission()
    {
        // User without permission cannot access
        $userWithoutPermission = User::factory()->create();
        $this->actingAs($userWithoutPermission)
            ->get('/dashboard')
            ->assertStatus(403);

        // User with permission can access
        $subscriber = $this->createSubscriber();
        $this->actingAs($subscriber)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_admin_routes_require_admin_role()
    {
        $adminRoutes = [
            '/admin',
            '/admin/users',
            '/admin/settings',
        ];

        // Subscriber cannot access admin routes
        $subscriber = $this->createSubscriber();
        foreach ($adminRoutes as $route) {
            $this->actingAs($subscriber)
                ->get($route)
                ->assertStatus(403);
        }

        // Admin can access admin routes
        $admin = $this->createAdmin();
        foreach ($adminRoutes as $route) {
            $this->actingAs($admin)
                ->get($route)
                ->assertOk();
        }

        // Super Admin can access admin routes
        $superAdmin = $this->createSuperAdmin();
        foreach ($adminRoutes as $route) {
            $this->actingAs($superAdmin)
                ->get($route)
                ->assertOk();
        }
    }

    public function test_role_management_requires_super_admin()
    {
        $roleRoutes = [
            '/admin/roles',
            '/admin/roles/create',
        ];

        // Admin cannot access role management
        $admin = $this->createAdmin();
        foreach ($roleRoutes as $route) {
            $this->actingAs($admin)
                ->get($route)
                ->assertStatus(403);
        }

        // Super Admin can access role management
        $superAdmin = $this->createSuperAdmin();
        foreach ($roleRoutes as $route) {
            $this->actingAs($superAdmin)
                ->get($route)
                ->assertOk();
        }
    }

    public function test_content_routes_require_content_creator_roles()
    {
        $contentRoutes = [
            '/content/pages',
            '/content/pages/create',
        ];

        // Subscriber cannot access content routes
        $subscriber = $this->createSubscriber();
        foreach ($contentRoutes as $route) {
            $this->actingAs($subscriber)
                ->get($route)
                ->assertStatus(403);
        }

        // Author can access content routes
        $author = $this->createAuthor();
        foreach ($contentRoutes as $route) {
            $this->actingAs($author)
                ->get($route)
                ->assertOk();
        }

        // Editor can access content routes
        $editor = $this->createEditor();
        foreach ($contentRoutes as $route) {
            $this->actingAs($editor)
                ->get($route)
                ->assertOk();
        }
    }

    public function test_media_routes_require_upload_media_permission()
    {
        // User without permission cannot access
        $subscriber = $this->createSubscriber();
        $this->actingAs($subscriber)
            ->get('/content/media')
            ->assertStatus(403);

        // User with permission can access
        $author = $this->createAuthor();
        $this->actingAs($author)
            ->get('/content/media')
            ->assertOk();
    }

    public function test_user_management_requires_view_users_permission()
    {
        // User without permission cannot access
        $author = $this->createAuthor();
        $this->actingAs($author)
            ->get('/admin/users')
            ->assertStatus(403);

        // User with permission can access
        $admin = $this->createAdmin();
        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_user_role_management_requires_manage_user_roles_permission()
    {
        $user = $this->createSubscriber();
        
        // User without permission cannot access
        $author = $this->createAuthor();
        $this->actingAs($author)
            ->get("/admin/users/{$user->id}/roles")
            ->assertStatus(403);

        // User with permission can access
        $admin = $this->createAdmin();
        $this->actingAs($admin)
            ->get("/admin/users/{$user->id}/roles")
            ->assertOk();
    }

    public function test_settings_require_manage_settings_permission()
    {
        // User without permission cannot access
        $author = $this->createAuthor();
        $this->actingAs($author)
            ->get('/admin/settings')
            ->assertStatus(403);

        // User with permission can access
        $admin = $this->createAdmin();
        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertOk();
    }

    public function test_role_hierarchy_permissions()
    {
        $subscriber = $this->createSubscriber();
        $author = $this->createAuthor();
        $editor = $this->createEditor();
        $admin = $this->createAdmin();
        $superAdmin = $this->createSuperAdmin();

        // Test permission hierarchy
        $this->assertUserHasPermission($subscriber, 'view dashboard');
        $this->assertUserLacksPermission($subscriber, 'create pages');

        $this->assertUserHasPermission($author, 'view dashboard');
        $this->assertUserHasPermission($author, 'create pages');
        $this->assertUserLacksPermission($author, 'edit pages');

        $this->assertUserHasPermission($editor, 'create pages');
        $this->assertUserHasPermission($editor, 'edit pages');
        $this->assertUserLacksPermission($editor, 'manage users');

        $this->assertUserHasPermission($admin, 'edit pages');
        $this->assertUserHasPermission($admin, 'view users');
        $this->assertUserLacksPermission($admin, 'manage roles');

        $this->assertUserHasPermission($superAdmin, 'manage roles');
        $this->assertUserHasPermission($superAdmin, 'manage permissions');
    }

    public function test_multiple_roles_combine_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole(['Author', 'Editor']);

        // User should have permissions from both roles
        $this->assertUserHasPermission($user, 'create pages'); // From Author
        $this->assertUserHasPermission($user, 'edit pages'); // From Editor
        $this->assertUserHasPermission($user, 'publish pages'); // From Editor
    }

    public function test_direct_permission_assignment()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view dashboard');

        $this->assertUserHasPermission($user, 'view dashboard');
        $this->assertUserLacksPermission($user, 'create pages');
    }

    /**
     * Assert that a user has a specific permission
     */
    protected function assertUserHasPermission($user, $permission)
    {
        $this->assertTrue($user->hasPermissionTo($permission), "User should have permission: {$permission}");
    }

    /**
     * Assert that a user lacks a specific permission
     */
    protected function assertUserLacksPermission($user, $permission)
    {
        $this->assertFalse($user->hasPermissionTo($permission), "User should not have permission: {$permission}");
    }
}
