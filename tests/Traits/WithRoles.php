<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * WithRoles Trait
 * 
 * This trait provides helper methods for setting up roles and permissions in tests.
 * It's essential for the Multi-Role User Authentication system tests to work properly.
 * 
 * Key Features:
 * - Creates all required roles and permissions for testing
 * - Provides helper methods to create users with specific roles
 * - Handles permission cache clearing for test isolation
 * - Follows the exact role hierarchy defined in the documentation
 * 
 * Usage:
 * ```php
 * class MyTest extends TestCase
 * {
 *     use RefreshDatabase, WithRoles;
 * 
 *     protected function setUp(): void
 *     {
 *         parent::setUp();
 *         $this->createRolesAndPermissions();
 *     }
 * }
 * ```
 */
trait WithRoles
{
    /**
     * Set up roles and permissions for testing.
     * This is a convenience method that calls createRolesAndPermissions().
     */
    protected function setupRoles(): void
    {
        $this->createRolesAndPermissions();
    }

    /**
     * Create all roles and permissions required for the authentication system.
     * This method should be called in the setUp() method of test classes.
     */
    protected function createRolesAndPermissions(): void
    {
        // Reset cached roles and permissions to ensure clean test state
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions first
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
            'force delete users',
            'manage user roles',
            
            // Content Management
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'edit own pages',
            'delete own pages',
            
            // System Administration
            'view dashboard',
            'manage settings',
            'manage security settings',
            'view system stats',
            'view audit logs',
            'manage roles',
            'manage permissions',
            
            // Media Management
            'upload media',
            'manage media',
            'delete media',
            
            // Comment Management
            'view comments',
            'moderate comments',
            'delete comments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions according to hierarchy
        $this->createSuperAdminRole();
        $this->createAdminRole();
        $this->createEditorRole();
        $this->createAuthorRole();
        $this->createSubscriberRole();
    }

    /**
     * Create the Super Admin role with all permissions.
     * Super Admins have complete system access.
     */
    protected function createSuperAdminRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        
        // Super Admin gets all permissions
        $role->syncPermissions(Permission::all());
    }

    /**
     * Create the Admin role with high-level administrative permissions.
     * Admins can manage users and content but not system-level settings.
     */
    protected function createAdminRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin']);
        
        $permissions = [
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
            'manage user roles',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'upload media',
            'manage media',
            'delete media',
            'view comments',
            'moderate comments',
            'delete comments',
            'manage settings',
            'view system stats',
        ];
        
        $role->syncPermissions($permissions);
    }

    /**
     * Create the Editor role with content management permissions.
     * Editors can manage all content but cannot manage users.
     */
    protected function createEditorRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'Editor']);
        
        $permissions = [
            'view dashboard',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'upload media',
            'manage media',
            'view comments',
            'moderate comments',
            'delete comments',
        ];
        
        $role->syncPermissions($permissions);
    }

    /**
     * Create the Author role with limited content creation permissions.
     * Authors can create and manage their own content.
     */
    protected function createAuthorRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'Author']);
        
        $permissions = [
            'view dashboard',
            'view pages',
            'create pages',
            'edit own pages',
            'delete own pages',
            'upload media',
            'view comments',
        ];
        
        $role->syncPermissions($permissions);
    }

    /**
     * Create the Subscriber role with basic read-only access.
     * Subscribers have minimal permissions, mainly dashboard access.
     */
    protected function createSubscriberRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'Subscriber']);
        
        $permissions = [
            'view dashboard',
        ];
        
        $role->syncPermissions($permissions);
    }

    // Helper methods for creating users with specific roles in tests

    /**
     * Create a user with Super Admin role.
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createSuperAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('Super Admin');
        return $user->fresh(); // Reload to get updated relationships
    }

    /**
     * Create a user with Admin role.
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('Admin');
        return $user->fresh();
    }

    /**
     * Create a user with Editor role.
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createEditor(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('Editor');
        return $user->fresh();
    }

    /**
     * Create a user with Author role.
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createAuthor(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('Author');
        return $user->fresh();
    }

    /**
     * Create a user with Subscriber role.
     *
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createSubscriber(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('Subscriber');
        return $user->fresh();
    }

    /**
     * Create a user with a specific role.
     *
     * @param string $role Role name
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user->fresh();
    }

    /**
     * Create a user with multiple roles.
     *
     * @param array $roles Array of role names
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createUserWithRoles(array $roles, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($roles);
        return $user->fresh();
    }

    /**
     * Create a user with specific permissions (bypassing roles).
     *
     * @param array $permissions Array of permission names
     * @param array $attributes Additional user attributes
     * @return User
     */
    protected function createUserWithPermissions(array $permissions, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->givePermissionTo($permissions);
        return $user->fresh();
    }

    /**
     * Assert that a user has the expected role.
     *
     * @param User $user
     * @param string $roleName
     * @return void
     */
    protected function assertUserHasRole(User $user, string $roleName): void
    {
        $this->assertTrue(
            $user->hasRole($roleName),
            "User does not have the '{$roleName}' role."
        );
    }

    /**
     * Assert that a user has the expected permission.
     *
     * @param User $user
     * @param string $permissionName
     * @return void
     */
    protected function assertUserHasPermission(User $user, string $permissionName): void
    {
        $this->assertTrue(
            $user->hasPermissionTo($permissionName),
            "User does not have the '{$permissionName}' permission."
        );
    }

    /**
     * Assert that a user does not have the specified permission.
     *
     * @param User $user
     * @param string $permissionName
     * @return void
     */
    protected function assertUserDoesNotHavePermission(User $user, string $permissionName): void
    {
        $this->assertFalse(
            $user->hasPermissionTo($permissionName),
            "User should not have the '{$permissionName}' permission."
        );
    }
}
