<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * WithRoles Trait
 * 
 * This trait provides comprehensive testing utilities for the Multi-Role User Authentication system.
 * It centralizes the creation of roles, permissions, and test users with specific roles,
 * making tests more maintainable and consistent.
 * 
 * Key Features:
 * - Creates a complete set of roles and permissions for testing
 * - Provides helper methods to create users with specific roles
 * - Includes assertion methods for role and permission validation
 * - Ensures consistent test data across all authentication tests
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
 * 
 * @see https://spatie.be/docs/laravel-permission
 * @see https://laravel.com/docs/testing
 */
trait WithRoles
{
    /**
     * Create all roles and permissions for testing.
     * 
     * This method sets up the complete role hierarchy and permission structure
     * that matches the production environment. It should be called in the setUp()
     * method of any test that needs to work with roles and permissions.
     * 
     * Role Hierarchy:
     * - Super Admin: All permissions
     * - Admin: Most permissions (except super admin specific ones)
     * - Editor: Content management permissions
     * - Author: Limited content creation permissions
     * - Subscriber: Basic dashboard access only
     * 
     * Permission Categories:
     * - User Management: view, create, edit, delete users, manage roles
     * - Content Management: view, create, edit, delete, publish posts
     * - Media Management: upload, manage, delete media
     * - Comment Management: view, moderate, delete comments
     * - System Management: manage settings, roles, permissions
     */
    protected function createRolesAndPermissions()
    {
        // Create all available permissions
        $permissions = [
            // User Management Permissions
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',
            
            // Content Management Permissions
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'edit own posts',
            'delete own posts',
            
            // System Management Permissions
            'manage settings',
            'manage roles',
            'manage permissions',
            
            // Media Management Permissions
            'upload media',
            'manage media',
            'delete media',
            
            // Comment Management Permissions
            'view comments',
            'moderate comments',
            'delete comments',
        ];

        // Create each permission in the database
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign appropriate permissions
        
        // Super Admin: Has all permissions
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin: Has most permissions but not super admin specific ones
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view dashboard',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'manage settings',
            'upload media',
            'manage media',
            'delete media',
            'view comments',
            'moderate comments',
            'delete comments',
        ]);

        // Editor: Content management focused permissions
        $editor = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $editor->givePermissionTo([
            'view dashboard',
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'upload media',
            'manage media',
            'view comments',
            'moderate comments',
        ]);

        // Author: Limited content creation permissions
        $author = Role::create(['name' => 'Author', 'guard_name' => 'web']);
        $author->givePermissionTo([
            'view dashboard',
            'view posts',
            'create posts',
            'edit own posts',
            'delete own posts',
            'upload media',
        ]);

        // Subscriber: Basic access only
        $subscriber = Role::create(['name' => 'Subscriber', 'guard_name' => 'web']);
        $subscriber->givePermissionTo([
            'view dashboard',
        ]);
    }

    /**
     * Create a user with a specific role.
     * 
     * This is a generic helper method that creates a user and assigns them
     * the specified role. It's used by the more specific role creation methods.
     * 
     * @param string $roleName The name of the role to assign
     * @return User The created user with the assigned role
     */
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole($roleName);
        return $user;
    }

    /**
     * Create a Super Admin user.
     * 
     * Super Admins have access to all system features and permissions.
     * 
     * @return User A user with Super Admin role
     */
    protected function createSuperAdmin(): User
    {
        return $this->createUserWithRole('Super Admin');
    }

    /**
     * Create an Admin user.
     * 
     * Admins have extensive permissions but not super admin specific ones.
     * 
     * @return User A user with Admin role
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('Admin');
    }

    /**
     * Create an Editor user.
     * 
     * Editors can manage content and moderate comments.
     * 
     * @return User A user with Editor role
     */
    protected function createEditor(): User
    {
        return $this->createUserWithRole('Editor');
    }

    /**
     * Create an Author user.
     * 
     * Authors can create content but have limited management capabilities.
     * 
     * @return User A user with Author role
     */
    protected function createAuthor(): User
    {
        return $this->createUserWithRole('Author');
    }

    /**
     * Create a Subscriber user.
     * 
     * Subscribers have basic access to view content only.
     * 
     * @return User A user with Subscriber role
     */
    protected function createSubscriber(): User
    {
        return $this->createUserWithRole('Subscriber');
    }

    /**
     * Assert that a user has a specific role.
     * 
     * This assertion method provides clear error messages when role checks fail.
     * 
     * @param User $user The user to check
     * @param string $roleName The role name to verify
     * @throws \PHPUnit\Framework\AssertionFailedError If the user doesn't have the role
     */
    protected function assertUserHasRole(User $user, string $roleName): void
    {
        $this->assertTrue($user->hasRole($roleName), "User should have role: {$roleName}");
    }

    /**
     * Assert that a user has a specific permission.
     * 
     * This assertion method provides clear error messages when permission checks fail.
     * 
     * @param User $user The user to check
     * @param string $permission The permission name to verify
     * @throws \PHPUnit\Framework\AssertionFailedError If the user doesn't have the permission
     */
    protected function assertUserHasPermission(User $user, string $permission): void
    {
        $this->assertTrue($user->hasPermissionTo($permission), "User should have permission: {$permission}");
    }

    /**
     * Assert that a user lacks a specific permission.
     * 
     * This assertion method is useful for testing that users don't have
     * permissions they shouldn't have.
     * 
     * @param User $user The user to check
     * @param string $permission The permission name to verify absence of
     * @throws \PHPUnit\Framework\AssertionFailedError If the user has the permission
     */
    protected function assertUserLacksPermission(User $user, string $permission): void
    {
        $this->assertFalse($user->hasPermissionTo($permission), "User should not have permission: {$permission}");
    }
}
