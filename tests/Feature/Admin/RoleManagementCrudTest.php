<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RoleManagementCrudTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    // CREATE TESTS

    public function test_super_admin_can_create_role_with_valid_data()
    {
        $superAdmin = $this->createSuperAdmin();

        $roleData = [
            'name' => 'Content Manager',
            'permissions' => ['view dashboard', 'create pages', 'edit pages'],
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', $roleData);

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Content Manager']);
        
        $role = Role::where('name', 'Content Manager')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('view dashboard'));
        $this->assertTrue($role->hasPermissionTo('create pages'));
        $this->assertTrue($role->hasPermissionTo('edit pages'));
        $this->assertFalse($role->hasPermissionTo('delete pages'));
    }

    public function test_role_creation_validates_required_name()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'permissions' => ['view dashboard'],
            ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertDatabaseMissing('roles', ['guard_name' => 'web', 'name' => '']);
    }

    public function test_role_creation_validates_unique_name()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Admin', // Already exists
                'permissions' => ['view dashboard'],
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_role_creation_validates_reserved_names()
    {
        $superAdmin = $this->createSuperAdmin();

        $reservedNames = ['Super Admin', 'Admin', 'Editor', 'Author', 'Subscriber'];

        foreach ($reservedNames as $reservedName) {
            $response = $this->actingAs($superAdmin)
                ->post('/admin/roles', [
                    'name' => $reservedName,
                    'permissions' => ['view dashboard'],
                ]);

            $response->assertSessionHasErrors(['name']);
        }
    }

    public function test_role_creation_validates_permission_existence()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Test Role',
                'permissions' => ['nonexistent permission'],
            ]);

        $response->assertSessionHasErrors(['permissions.0']);
    }

    public function test_role_creation_requires_at_least_one_permission()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Test Role',
                'permissions' => [],
            ]);

        $response->assertSessionHasErrors(['permissions']);
    }

    public function test_role_creation_validates_name_format()
    {
        $superAdmin = $this->createSuperAdmin();

        // Test invalid characters
        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Test@Role!',
                'permissions' => ['view dashboard'],
            ]);

        $response->assertSessionHasErrors(['name']);

        // Test valid characters
        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Test-Role_123',
                'permissions' => ['view dashboard'],
            ]);

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', ['name' => 'Test-Role_123']);
    }

    public function test_admin_cannot_create_roles()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post('/admin/roles', [
                'name' => 'Test Role',
                'permissions' => ['view dashboard'],
            ]);

        $response->assertStatus(403);
    }

    // READ TESTS

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

    public function test_create_role_page_includes_grouped_permissions()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/roles/create');

        $response->assertInertia(fn ($page) => 
            $page->has('permissions')
                ->has('permissions.pages') // Should have pages group
                ->has('permissions.users') // Should have users group
        );
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
                ->where('role.name', 'Editor')
        );
    }

    public function test_edit_role_page_includes_current_permissions()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Editor')->first();

        $response = $this->actingAs($superAdmin)
            ->get("/admin/roles/{$role->id}/edit");

        $response->assertInertia(fn ($page) => 
            $page->has('role.permissions')
                ->has('role.id')
                ->has('role.name')
                ->where('role.name', 'Editor')
        );
    }

    // UPDATE TESTS

    public function test_super_admin_can_update_role_name()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Editor')->first();
        $originalPermissions = $role->permissions->pluck('name')->toArray();

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", [
                'name' => 'Senior Editor',
                'permissions' => $originalPermissions,
            ]);

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $role->refresh();
        $this->assertEquals('Senior Editor', $role->name);
    }

    public function test_super_admin_can_update_role_permissions()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Author')->first();

        $newPermissions = ['view dashboard', 'create pages', 'edit pages', 'delete pages'];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", [
                'name' => $role->name,
                'permissions' => $newPermissions,
            ]);

        $response->assertRedirect('/admin/roles');
        $response->assertSessionHas('success');

        $role->refresh();
        foreach ($newPermissions as $permission) {
            $this->assertTrue($role->hasPermissionTo($permission));
        }
    }

    public function test_cannot_change_super_admin_role_name()
    {
        $superAdmin = $this->createSuperAdmin();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$superAdminRole->id}", [
                'name' => 'Ultimate Admin',
                'permissions' => $superAdminRole->permissions->pluck('name')->toArray(),
            ]);

        $response->assertSessionHasErrors(['name']);
        
        $superAdminRole->refresh();
        $this->assertEquals('Super Admin', $superAdminRole->name);
    }

    public function test_super_admin_role_must_retain_critical_permissions()
    {
        $superAdmin = $this->createSuperAdmin();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$superAdminRole->id}", [
                'name' => 'Super Admin',
                'permissions' => ['view dashboard'], // Missing critical permissions
            ]);

        $response->assertSessionHasErrors(['permissions']);
    }

    public function test_cannot_remove_critical_permissions_from_role_with_users()
    {
        $superAdmin = $this->createSuperAdmin();
        $subscriber = $this->createSubscriber();
        $subscriberRole = Role::where('name', 'Subscriber')->first();

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$subscriberRole->id}", [
                'name' => 'Subscriber',
                'permissions' => [], // Removing view dashboard
            ]);

        $response->assertSessionHasErrors(['permissions']);
    }

    public function test_role_update_validates_unique_name_except_current()
    {
        $superAdmin = $this->createSuperAdmin();
        $editorRole = Role::where('name', 'Editor')->first();

        // Should allow keeping the same name
        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$editorRole->id}", [
                'name' => 'Editor',
                'permissions' => ['view dashboard'],
            ]);

        $response->assertRedirect('/admin/roles');

        // Should not allow using another role's name
        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$editorRole->id}", [
                'name' => 'Admin',
                'permissions' => ['view dashboard'],
            ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_success_message_includes_permission_changes()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Author')->first();
        $originalPermissions = $role->permissions->pluck('name')->toArray();
        
        $newPermissions = array_merge($originalPermissions, ['delete pages']);

        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", [
                'name' => $role->name,
                'permissions' => $newPermissions,
            ]);

        $response->assertSessionHas('success');
        $successMessage = session('success');
        $this->assertStringContainsString('1 permission added', $successMessage);
    }

    // DELETE TESTS

    public function test_super_admin_can_delete_role_without_users()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Create a test role without users
        $testRole = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);
        $testRole->givePermissionTo('view dashboard');

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

    // AUTHORIZATION TESTS

    public function test_admin_cannot_perform_any_role_crud_operations()
    {
        $admin = $this->createAdmin();
        $role = Role::where('name', 'Editor')->first();

        // Cannot view create page
        $response = $this->actingAs($admin)->get('/admin/roles/create');
        $response->assertStatus(403);

        // Cannot create
        $response = $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'Test Role',
            'permissions' => ['view dashboard'],
        ]);
        $response->assertStatus(403);

        // Cannot view edit page
        $response = $this->actingAs($admin)->get("/admin/roles/{$role->id}/edit");
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

    public function test_guest_cannot_access_role_management()
    {
        $role = Role::where('name', 'Editor')->first();

        // All routes should redirect to login
        $this->get('/admin/roles/create')->assertRedirect('/login');
        $this->post('/admin/roles', [])->assertRedirect('/login');
        $this->get("/admin/roles/{$role->id}/edit")->assertRedirect('/login');
        $this->put("/admin/roles/{$role->id}", [])->assertRedirect('/login');
        $this->delete("/admin/roles/{$role->id}")->assertRedirect('/login');
    }

    // INTEGRATION TESTS

    public function test_complete_role_lifecycle()
    {
        $superAdmin = $this->createSuperAdmin();

        // 1. Create role
        $response = $this->actingAs($superAdmin)
            ->post('/admin/roles', [
                'name' => 'Content Moderator',
                'permissions' => ['view dashboard', 'moderate comments'],
            ]);

        $response->assertRedirect('/admin/roles');
        $role = Role::where('name', 'Content Moderator')->first();
        $this->assertNotNull($role);

        // 2. Update role
        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", [
                'name' => 'Senior Content Moderator',
                'permissions' => ['view dashboard', 'moderate comments', 'delete comments'],
            ]);

        $response->assertRedirect('/admin/roles');
        $role->refresh();
        $this->assertEquals('Senior Content Moderator', $role->name);
        $this->assertTrue($role->hasPermissionTo('delete comments'));

        // 3. Delete role
        $response = $this->actingAs($superAdmin)
            ->delete("/admin/roles/{$role->id}");

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_role_permissions_are_properly_synced()
    {
        $superAdmin = $this->createSuperAdmin();
        $role = Role::where('name', 'Author')->first();

        // Get initial permissions
        $initialPermissions = $role->permissions->pluck('name')->toArray();
        $this->assertContains('create pages', $initialPermissions);

        // Update with completely different permissions
        $newPermissions = ['view dashboard', 'upload media'];
        
        $response = $this->actingAs($superAdmin)
            ->put("/admin/roles/{$role->id}", [
                'name' => $role->name,
                'permissions' => $newPermissions,
            ]);

        $response->assertRedirect('/admin/roles');
        
        $role->refresh();
        
        // Should have new permissions
        $this->assertTrue($role->hasPermissionTo('view dashboard'));
        $this->assertTrue($role->hasPermissionTo('upload media'));
        
        // Should not have old permissions
        $this->assertFalse($role->hasPermissionTo('create pages'));
        $this->assertFalse($role->hasPermissionTo('edit own pages'));
    }
}
