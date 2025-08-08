<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_super_admin_can_view_users_index()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/index')
                ->has('users')
                ->has('stats')
        );
    }

    public function test_admin_can_view_users_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/index')
                ->has('users')
                ->has('stats')
        );
    }

    public function test_editor_cannot_view_users_index()
    {
        $editor = $this->createEditor();

        $response = $this->actingAs($editor)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_users_index_includes_correct_data_structure()
    {
        $superAdmin = $this->createSuperAdmin();
        $this->createAdmin();
        $this->createEditor();

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('users.data')
                ->has('users.links')
                ->has('users.meta')
                ->has('stats.total_users')
                ->has('stats.administrators')
                ->has('stats.content_creators')
                ->has('stats.subscribers')
        );
    }

    public function test_super_admin_can_view_create_user_page()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/admin/users/create');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/create')
                ->has('roles')
        );
    }

    public function test_admin_can_view_create_user_page()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk();
    }

    public function test_editor_cannot_view_create_user_page()
    {
        $editor = $this->createEditor();

        $response = $this->actingAs($editor)->get('/admin/users/create');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_user()
    {
        $superAdmin = $this->createSuperAdmin();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['Editor'],
            'email_verified' => true,
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('Editor'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_admin_can_create_user()
    {
        $admin = $this->createAdmin();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['Author'],
        ];

        $response = $this->actingAs($admin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_creation_validates_required_fields()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_user_creation_validates_unique_email()
    {
        $superAdmin = $this->createSuperAdmin();
        $existingUser = $this->createEditor();

        $userData = [
            'name' => 'Test User',
            'email' => $existingUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users', $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_creation_validates_password_confirmation()
    {
        $superAdmin = $this->createSuperAdmin();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users', $userData);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_user_creation_validates_roles_exist()
    {
        $superAdmin = $this->createSuperAdmin();

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['NonexistentRole'],
        ];

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users', $userData);

        $response->assertSessionHasErrors(['roles.0']);
    }

    public function test_super_admin_can_view_edit_user_page()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($superAdmin)
            ->get("/admin/users/{$user->id}/edit");

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/edit')
                ->has('user')
                ->has('roles')
        );
    }

    public function test_admin_can_view_edit_user_page()
    {
        $admin = $this->createAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($admin)
            ->get("/admin/users/{$user->id}/edit");

        $response->assertOk();
    }

    public function test_editor_cannot_view_edit_user_page()
    {
        $editor = $this->createEditor();
        $user = $this->createAuthor();

        $response = $this->actingAs($editor)
            ->get("/admin/users/{$user->id}/edit");

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_user()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['Author'],
            'email_verified' => false,
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$user->id}", $updateData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('updated@example.com', $user->email);
        $this->assertTrue($user->hasRole('Author'));
        $this->assertFalse($user->hasRole('Editor'));
        $this->assertNull($user->email_verified_at);
    }

    public function test_admin_can_update_user()
    {
        $admin = $this->createAdmin();
        $user = $this->createEditor();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['Author'],
        ];

        $response = $this->actingAs($admin)
            ->put("/admin/users/{$user->id}", $updateData);

        $response->assertRedirect('/admin/users');
        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
    }

    public function test_user_update_validates_unique_email_except_current()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        // Should allow keeping the same email
        $updateData = [
            'name' => 'Updated Name',
            'email' => $user1->email,
            'roles' => ['Editor'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$user1->id}", $updateData);

        $response->assertRedirect('/admin/users');

        // Should not allow using another user's email
        $updateData = [
            'name' => 'Updated Name',
            'email' => $user2->email,
            'roles' => ['Editor'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$user1->id}", $updateData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_update_with_password()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();
        $originalPassword = $user->password;

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'roles' => ['Editor'],
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$user->id}", $updateData);

        $response->assertRedirect('/admin/users');

        $user->refresh();
        $this->assertNotEquals($originalPassword, $user->password);
    }

    public function test_cannot_remove_super_admin_role_from_last_super_admin()
    {
        $superAdmin = $this->createSuperAdmin();

        $updateData = [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'roles' => ['Admin'], // Remove Super Admin role
        ];

        $response = $this->actingAs($superAdmin)
            ->put("/admin/users/{$superAdmin->id}", $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $superAdmin->refresh();
        $this->assertTrue($superAdmin->hasRole('Super Admin'));
    }

    public function test_can_remove_super_admin_role_when_multiple_exist()
    {
        $superAdmin1 = $this->createSuperAdmin();
        $superAdmin2 = $this->createSuperAdmin(['email' => 'super2@example.com']);

        $updateData = [
            'name' => $superAdmin2->name,
            'email' => $superAdmin2->email,
            'roles' => ['Admin'], // Remove Super Admin role
        ];

        $response = $this->actingAs($superAdmin1)
            ->put("/admin/users/{$superAdmin2->id}", $updateData);

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $superAdmin2->refresh();
        $this->assertFalse($superAdmin2->hasRole('Super Admin'));
        $this->assertTrue($superAdmin2->hasRole('Admin'));
    }

    public function test_super_admin_can_delete_user()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_delete_user()
    {
        $admin = $this->createAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_last_super_admin()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/users/{$superAdmin->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
    }

    public function test_cannot_delete_self()
    {
        $superAdmin1 = $this->createSuperAdmin();
        $superAdmin2 = $this->createSuperAdmin(['email' => 'super2@example.com']);

        $response = $this->actingAs($superAdmin1)
            ->delete("/admin/users/{$superAdmin1->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $superAdmin1->id]);
    }

    public function test_can_delete_super_admin_when_multiple_exist_and_not_self()
    {
        $superAdmin1 = $this->createSuperAdmin();
        $superAdmin2 = $this->createSuperAdmin(['email' => 'super2@example.com']);

        $response = $this->actingAs($superAdmin1)
            ->delete("/admin/users/{$superAdmin2->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $superAdmin2->id]);
    }

    public function test_bulk_delete_users()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'delete',
                'user_ids' => [$user1->id, $user2->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
    }

    public function test_bulk_assign_roles()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'assign_role',
                'user_ids' => [$user1->id, $user2->id],
                'role' => 'Admin',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();
        $this->assertTrue($user1->hasRole('Admin'));
        $this->assertTrue($user2->hasRole('Admin'));
    }

    public function test_bulk_remove_roles()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'remove_role',
                'user_ids' => [$user1->id, $user2->id],
                'role' => 'Editor',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $this->assertFalse($user1->hasRole('Editor'));
    }

    public function test_bulk_verify_emails()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor(['email_verified_at' => null]);
        $user2 = $this->createAuthor(['email_verified_at' => null]);

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'verify_email',
                'user_ids' => [$user1->id, $user2->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();
        $this->assertNotNull($user1->email_verified_at);
        $this->assertNotNull($user2->email_verified_at);
    }

    public function test_bulk_unverify_emails()
    {
        $superAdmin = $this->createSuperAdmin();
        $user1 = $this->createEditor();
        $user2 = $this->createAuthor();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'unverify_email',
                'user_ids' => [$user1->id, $user2->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user1->refresh();
        $user2->refresh();
        $this->assertNull($user1->email_verified_at);
        $this->assertNull($user2->email_verified_at);
    }

    public function test_bulk_actions_validate_required_fields()
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', []);

        $response->assertSessionHasErrors(['action', 'user_ids']);
    }

    public function test_bulk_role_actions_require_role_parameter()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'assign_role',
                'user_ids' => [$user->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_editor_cannot_perform_user_operations()
    {
        $editor = $this->createEditor();
        $user = $this->createAuthor();

        // Cannot create
        $response = $this->actingAs($editor)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertStatus(403);

        // Cannot update
        $response = $this->actingAs($editor)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => ['Author'],
        ]);
        $response->assertStatus(403);

        // Cannot delete
        $response = $this->actingAs($editor)->delete("/admin/users/{$user->id}");
        $response->assertStatus(403);
    }

    public function test_user_data_includes_computed_properties()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('users.data.0.role_names')
                ->has('users.data.0.all_permissions')
                ->has('users.data.0.is_social_user')
                ->has('users.data.0.avatar_url')
        );
    }

    public function test_stats_are_calculated_correctly()
    {
        $superAdmin = $this->createSuperAdmin();
        $admin = $this->createAdmin();
        $editor = $this->createEditor();
        $author = $this->createAuthor();
        $subscriber = $this->createSubscriber();

        $response = $this->actingAs($superAdmin)->get('/admin/users');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->where('stats.total_users', 5)
                ->where('stats.administrators', 2) // Super Admin + Admin
                ->where('stats.content_creators', 2) // Editor + Author
                ->where('stats.subscribers', 1) // Subscriber
        );
    }
}
