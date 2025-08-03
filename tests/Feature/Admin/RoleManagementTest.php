<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_super_admin_can_view_roles_index()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/roles');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/roles/index')
                ->has('roles')
                ->has('permissions')
        );
    }

    public function test_admin_cannot_view_roles_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/roles');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_create_role_page()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/roles/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/roles/create')
                ->has('permissions')
        );
    }

    public function test_super_admin_can_create_role()
    {
        $superAdmin = $this->createSuperAdmin();

        $roleData = [
            'name' => 'Test Role',
            'permissions' => ['view dashboard', 'create posts'],
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', $roleData);

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
        
        $role = Role::where('name', 'Test Role')->first();
        $this->assertTrue($role->hasPermissionTo('view dashboard'));
        $this->assertTrue($role->hasPermissionTo('create posts'));
    }

    public function test_role_creation_validates_required_fields()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', []);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_role_creation_validates_unique_name()
    {
        $superAdmin = $this->createSuperAdmin();

        $roleData = [
            'name' => 'Admin', // Already exists
            'permissions' => ['view dashboard'],
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', $roleData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_role_creation_validates_permissions_exist()
    {
        $superAdmin = $this->createSuperAdmin();

        $roleData = [
            'name' => 'Test Role',
            'permissions' => ['nonexistent permission'],
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', $roleData);

        $response->assertSessionHasErrors(['permissions.0']);
    }

    public function test_super_admin_can_view_edit_role_page()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Editor')->first();

        $response = $this->actingAs($superAdmin)
            ->get("/admin/roles/{$role->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/roles/edit')
                ->has('role')
                ->has('permissions')
        );
    }

    public function test_super_admin_can_update_role()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Editor')->first();

        $updateData = [
            'name' => 'Updated Editor',
            'permissions' => ['view dashboard', 'create posts', 'edit posts'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", $updateData);

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Updated Editor', $role->name);
        $this->assertTrue($role->hasPermissionTo('view dashboard'));
        $this->assertTrue($role->hasPermissionTo('create posts'));
        $this->assertTrue($role->hasPermissionTo('edit posts'));
    }

    public function test_role_update_validates_unique_name_except_current()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Editor')->first();

        // Should allow keeping the same name
        $updateData = [
            'name' => 'Editor',
            'permissions' => ['view dashboard'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", $updateData);

        $response->assertRedirect('/admin/roles');

        // Should not allow using another role's name
        $updateData = [
            'name' => 'Admin',
            'permissions' => ['view dashboard'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", $updateData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_super_admin_can_delete_role()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Create a test role
        $testRole = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/roles/{$testRole->id}");

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $testRole->id]);
    }

    public function test_cannot_delete_super_admin_role()
    {
        $superAdmin = $this->createSuperAdmin();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/roles/{$superAdminRole->id}");

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $superAdminRole->id]);
    }

    public function test_cannot_delete_role_with_assigned_users()
    {
        $superAdmin = $this->createSuperAdmin();
        $adminRole = Role::where('name', 'Admin')->first();
        
        // Create a user with the Admin role
        $this->createAdmin();

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/roles/{$adminRole->id}");

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    public function test_role_permissions_are_synced_correctly()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Author')->first();

        // Initially has certain permissions
        $this->assertTrue($role->hasPermissionTo('create posts'));
        $this->assertFalse($role->hasPermissionTo('delete posts'));

        // Update with different permissions
        $updateData = [
            'name' => 'Author',
            'permissions' => ['view dashboard', 'delete posts'], // Remove create posts, add delete posts
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", $updateData);

        $response->assertRedirect('/admin/roles');

        $role->refresh();
        $this->assertFalse($role->hasPermissionTo('create posts')); // Should be removed
        $this->assertTrue($role->hasPermissionTo('delete posts')); // Should be added
        $this->assertTrue($role->hasPermissionTo('view dashboard')); // Should remain
    }

    public function test_role_data_includes_user_and_permission_counts()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Create some users with different roles
        $this->createAdmin();
        $this->createEditor();
        $this->createAuthor();

        $response = $this->actingAs($superAdmin)->get('/admin/roles');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('roles.0.users_count')
                ->has('roles.0.permissions_count')
        );
    }

    public function test_permissions_are_grouped_by_category()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/roles');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('permissions')
                ->where('permissions', fn ($permissions) => 
                    is_array($permissions) && count($permissions) > 0
                )
        );
    }

    public function test_admin_cannot_perform_role_operations()
    {
        $admin = $this->createAdmin();
        $role = Role::where('name', 'Editor')->first();

        // Cannot create
        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Test Role',
            'permissions' => ['view dashboard'],
        ]);
        $response->assertStatus(403);

        // Cannot update
        $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'name' => 'Updated Role',
            'permissions' => ['view dashboard'],
        ]);
        $response->assertStatus(403);

        // Cannot delete
        $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");
        $response->assertStatus(403);
    }
}
