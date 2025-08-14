<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_admin_can_view_user_role_management_page()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->get("/admin/users/{$user->id}/roles");

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/roles')
                ->has('user')
                ->has('availableRoles')
        );
    }

    public function test_user_without_manage_user_roles_permission_cannot_access()
    {
        $author = $this->createAuthor();
        $user = $this->createSubscriber();

        $response = $this->actingAs($author)
            ->get("/admin/users/{$user->id}/roles");

        $response->assertStatus(403);
    }

    public function test_admin_can_assign_roles_to_user()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $this->assertUserHasRole($user, 'Subscriber');
        $this->assertUserDoesNotHavePermission($user, 'create pages');

        $response = $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", [
                'roles' => ['Author', 'Editor'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertUserHasRole($user, 'Author');
        $this->assertUserHasRole($user, 'Editor');
        $this->assertUserHasPermission($user, 'create pages');
        $this->assertUserHasPermission($user, 'edit pages');
    }

    public function test_admin_can_remove_roles_from_user()
    {
        $admin = $this->createAdmin();
        $user = $this->createEditor();

        $this->assertUserHasRole($user, 'Editor');
        $this->assertUserHasPermission($user, 'edit pages');

        $response = $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", [
                'roles' => ['Subscriber'], // Remove Editor, assign Subscriber
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->hasRole('Editor'));
        $this->assertUserHasRole($user, 'Subscriber');
        $this->assertUserDoesNotHavePermission($user, 'edit pages');
    }

    public function test_cannot_remove_super_admin_role_from_last_super_admin()
    {
        $superAdmin = $this->createSuperAdmin();
        
        // Ensure this is the only Super Admin
        $superAdminCount = User::role('Super Admin')->count();
        $this->assertEquals(1, $superAdminCount);

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$superAdmin->id}/roles", [
                'roles' => ['Admin'], // Try to remove Super Admin role
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $superAdmin->refresh();
        $this->assertUserHasRole($superAdmin, 'Super Admin');
    }

    public function test_can_remove_super_admin_role_when_multiple_super_admins_exist()
    {
        $superAdmin1 = $this->createSuperAdmin();
        $superAdmin2 = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin1)
            ->put("/admin/users/{$superAdmin2->id}/roles", [
                'roles' => ['Admin'], // Remove Super Admin role
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $superAdmin2->refresh();
        $this->assertFalse($superAdmin2->hasRole('Super Admin'));
        $this->assertUserHasRole($superAdmin2, 'Admin');
    }

    public function test_role_assignment_validates_required_roles()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", []);

        $response->assertSessionHasErrors(['roles']);
    }

    public function test_role_assignment_validates_roles_exist()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", [
                'roles' => ['Nonexistent Role'],
            ]);

        $response->assertSessionHasErrors(['roles.0']);
    }

    public function test_bulk_role_assignment_assign_action()
    {
        $admin = $this->createAdmin();
        $user1 = $this->createSubscriber();
        $user2 = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', [
                'user_ids' => [$user1->id, $user2->id],
                'roles' => ['Author'],
                'action' => 'assign',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();

        // Users should have both their original role and the new role
        $this->assertUserHasRole($user1, 'Subscriber');
        $this->assertUserHasRole($user1, 'Author');
        $this->assertUserHasRole($user2, 'Subscriber');
        $this->assertUserHasRole($user2, 'Author');
    }

    public function test_bulk_role_assignment_remove_action()
    {
        $admin = $this->createAdmin();
        $user1 = User::factory()->create();
        $user1->assignRole(['Subscriber', 'Author']);
        $user2 = User::factory()->create();
        $user2->assignRole(['Subscriber', 'Author']);

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', [
                'user_ids' => [$user1->id, $user2->id],
                'roles' => ['Author'],
                'action' => 'remove',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();

        // Users should have Subscriber but not Author
        $this->assertUserHasRole($user1, 'Subscriber');
        $this->assertFalse($user1->hasRole('Author'));
        $this->assertUserHasRole($user2, 'Subscriber');
        $this->assertFalse($user2->hasRole('Author'));
    }

    public function test_bulk_role_assignment_replace_action()
    {
        $admin = $this->createAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', [
                'user_ids' => [$user1->id, $user2->id],
                'roles' => ['Subscriber'],
                'action' => 'replace',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();

        // Users should only have Subscriber role
        $this->assertUserHasRole($user1, 'Subscriber');
        $this->assertFalse($user1->hasRole('Editor'));
        $this->assertUserHasRole($user2, 'Subscriber');
        $this->assertFalse($user2->hasRole('Author'));
    }

    public function test_bulk_role_assignment_validates_required_fields()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', []);

        $response->assertSessionHasErrors(['user_ids', 'roles', 'action']);
    }

    public function test_bulk_role_assignment_validates_action_values()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', [
                'user_ids' => [$user->id],
                'roles' => ['Author'],
                'action' => 'invalid_action',
            ]);

        $response->assertSessionHasErrors(['action']);
    }

    public function test_bulk_role_assignment_validates_users_exist()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post('/admin/users/roles/bulk', [
                'user_ids' => [99999], // Non-existent user ID
                'roles' => ['Author'],
                'action' => 'assign',
            ]);

        $response->assertSessionHasErrors(['user_ids.0']);
    }

    public function test_user_role_page_shows_current_permissions()
    {
        $admin = $this->createAdmin();
        $editor = $this->createEditor();

        $response = $this->actingAs($admin)
            ->get("/admin/users/{$editor->id}/roles");

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('user.all_permissions')
                ->has('user.all_permissions.0') // Just check that permissions exist
        );
    }

    public function test_user_role_page_shows_available_roles_with_stats()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $response = $this->actingAs($admin)
            ->get("/admin/users/{$user->id}/roles");

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('availableRoles')
                ->has('availableRoles.0.permissions_count')
                ->has('availableRoles.0.users_count')
        );
    }

    public function test_role_changes_affect_user_permissions_immediately()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        // Initially cannot create pages
        $this->assertUserDoesNotHavePermission($user, 'create pages');

        // Assign Author role
        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", [
                'roles' => ['Author'],
            ]);

        $user->refresh();

        // Now can create pages
        $this->assertUserHasPermission($user, 'create pages');
    }

    public function test_multiple_role_assignment_combines_permissions()
    {
        $admin = $this->createAdmin();
        $user = $this->createSubscriber();

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/roles", [
                'roles' => ['Author', 'Editor'],
            ]);

        $user->refresh();

        // Should have permissions from both roles
        $this->assertUserHasPermission($user, 'create pages'); // From Author
        $this->assertUserHasPermission($user, 'edit pages'); // From Editor
        $this->assertUserHasPermission($user, 'publish pages'); // From Editor
    }
}
