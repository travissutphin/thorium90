<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * PermissionSeeder
 * 
 * This seeder is responsible for creating all the permissions for the Multi-Role User
 * Authentication system. It creates permissions across all major system categories
 * that can be assigned to roles and users.
 * 
 * Key Features:
 * - Creates permissions for all major system categories
 * - Organizes permissions by functional area
 * - Provides granular access control capabilities
 * - Clears cached permissions to ensure fresh creation
 * 
 * Permission Categories:
 * - User Management: view, create, edit, delete users, manage roles
 * - Content Management: view, create, edit, delete, publish posts, own content
 * - System Management: manage settings, view dashboard, manage roles/permissions
 * - Media Management: upload, manage, delete media
 * - Comment Management: view, moderate, delete comments
 * 
 * Usage:
 * ```bash
 * # Run this seeder to create all permissions
 * php artisan db:seed --class=PermissionSeeder
 * 
 * # Or run all seeders
 * php artisan db:seed
 * ```
 * 
 * Note: This seeder only creates permissions without assigning them to roles.
 * Use RolePermissionSeeder to create both permissions and their role assignments.
 * 
 * @see https://laravel.com/docs/seeders
 * @see https://spatie.be/docs/laravel-permission
 */
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This method creates all the permissions for the Multi-Role User Authentication system.
     * It first clears any cached permissions to ensure a clean setup, then creates
     * permissions organized by functional categories.
     */
    public function run(): void
    {
        // Reset cached roles and permissions to ensure fresh creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // User Management Permissions
        // These permissions control access to user management functionality
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'restore users']);      // Restore soft-deleted users
        Permission::create(['name' => 'force delete users']); // Permanently delete users
        Permission::create(['name' => 'manage user roles']);

        // Content Management Permissions
        // These permissions control access to content creation and management
        Permission::create(['name' => 'view posts']);
        Permission::create(['name' => 'create posts']);
        Permission::create(['name' => 'edit posts']);
        Permission::create(['name' => 'delete posts']);
        Permission::create(['name' => 'publish posts']);
        Permission::create(['name' => 'edit own posts']);    // Limited to user's own content
        Permission::create(['name' => 'delete own posts']);  // Limited to user's own content

        // System Management Permissions
        // These permissions control access to system administration features
        Permission::create(['name' => 'manage settings']);
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'manage roles']);
        Permission::create(['name' => 'manage permissions']);

        // Media Management Permissions
        // These permissions control access to media upload and management
        Permission::create(['name' => 'upload media']);
        Permission::create(['name' => 'manage media']);
        Permission::create(['name' => 'delete media']);

        // Comment Management Permissions
        // These permissions control access to comment viewing and moderation
        Permission::create(['name' => 'view comments']);
        Permission::create(['name' => 'moderate comments']);
        Permission::create(['name' => 'delete comments']);
    }
}
