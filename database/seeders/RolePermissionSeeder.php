<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * RolePermissionSeeder
 * 
 * This seeder is responsible for assigning permissions to roles in the Multi-Role User
 * Authentication system. It creates the complete role hierarchy by assigning appropriate
 * permissions to each role based on their intended access level.
 * 
 * Key Features:
 * - Assigns permissions to all five core roles
 * - Implements role hierarchy with appropriate permission levels
 * - Provides granular access control for different user types
 * - Clears cached permissions to ensure fresh assignments
 * 
 * Role Permission Assignments:
 * 
 * Super Admin:
 * - All permissions (highest level access)
 * - Can manage the entire system
 * 
 * Admin:
 * - Most permissions except super admin specific ones
 * - Can manage users, content, media, and comments
 * - Cannot manage roles and permissions (super admin only)
 * 
 * Editor:
 * - Content management focused permissions
 * - Can create, edit, delete, and publish pages
 * - Can moderate comments and manage media
 * - Cannot manage users or system settings
 * 
 * Author:
 * - Limited content creation permissions
 * - Can create pages and edit/delete their own pages
 * - Can upload media and view comments
 * - Cannot manage other users' content
 * 
 * Subscriber:
 * - Read-only access
 * - Can view pages, dashboard, and comments
 * - Cannot create or modify content
 * 
 * Usage:
 * ```bash
 * # Run this seeder to assign permissions to roles
 * php artisan db:seed --class=RolePermissionSeeder
 * 
 * # Or run all seeders
 * php artisan db:seed
 * ```
 * 
 * Prerequisites:
 * - Roles must be created first (use RoleSeeder)
 * - Permissions must be created first (use PermissionSeeder)
 * 
 * @see https://laravel.com/docs/seeders
 * @see https://spatie.be/docs/laravel-permission
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This method assigns permissions to each role based on their intended access level.
     * It first clears any cached permissions to ensure a clean setup, then assigns
     * the appropriate permissions to each role in the hierarchy.
     */
    public function run(): void
    {
        // Reset cached roles and permissions to ensure fresh assignments
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Super Admin - All permissions (highest level access)
        $superAdmin = Role::findByName('Super Admin');
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Most permissions except super admin specific ones
        // Admins can manage users, content, media, and comments but not roles/permissions
        $admin = Role::findByName('Admin');
        $admin->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'delete users',
            'restore users',
            'manage users',
            'manage user roles',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'manage settings',
            'view system stats',
            'view audit logs',
            'view dashboard',
            'upload media',
            'manage media',
            'delete media',
            'view comments',
            'moderate comments',
            'delete comments',
        ]);

        // Editor - Content management focused
        // Editors can manage content and moderate comments but not users or system settings
        $editor = Role::findByName('Editor');
        $editor->givePermissionTo([
            'view users',
            'view pages',
            'create pages',
            'edit pages',
            'delete pages',
            'publish pages',
            'view dashboard',
            'upload media',
            'manage media',
            'view comments',
            'moderate comments',
            'delete comments',
        ]);

        // Author - Own content management
        // Authors can create content and manage their own pages but not others'
        $author = Role::findByName('Author');
        $author->givePermissionTo([
            'view pages',
            'create pages',
            'edit own pages',    // Limited to their own content
            'delete own pages',  // Limited to their own content
            'view dashboard',
            'upload media',
            'view comments',
        ]);

        // Subscriber - Read only access
        // Subscribers can view content but cannot create or modify anything
        $subscriber = Role::findByName('Subscriber');
        $subscriber->givePermissionTo([
            'view pages',
            'view dashboard',
            'view comments',
        ]);
    }
}
