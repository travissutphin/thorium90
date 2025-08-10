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
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);
        Permission::firstOrCreate(['name' => 'restore users']);      // Restore soft-deleted users
        Permission::firstOrCreate(['name' => 'force delete users']); // Permanently delete users
        Permission::firstOrCreate(['name' => 'manage user roles']);

        // Content Management Permissions
        // These permissions control access to content creation and management
        Permission::firstOrCreate(['name' => 'view posts']);
        Permission::firstOrCreate(['name' => 'create posts']);
        Permission::firstOrCreate(['name' => 'edit posts']);
        Permission::firstOrCreate(['name' => 'delete posts']);
        Permission::firstOrCreate(['name' => 'publish posts']);
        Permission::firstOrCreate(['name' => 'edit own posts']);    // Limited to user's own content
        Permission::firstOrCreate(['name' => 'delete own posts']);  // Limited to user's own content

        // System Management Permissions
        // These permissions control access to system administration features
        Permission::firstOrCreate(['name' => 'manage settings']);
        Permission::firstOrCreate(['name' => 'view system stats']);
        Permission::firstOrCreate(['name' => 'manage security settings']);
        Permission::firstOrCreate(['name' => 'view audit logs']);
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'manage roles']);
        Permission::firstOrCreate(['name' => 'manage permissions']);

        // Media Management Permissions
        // These permissions control access to media upload and management
        Permission::firstOrCreate(['name' => 'upload media']);
        Permission::firstOrCreate(['name' => 'manage media']);
        Permission::firstOrCreate(['name' => 'delete media']);

        // Comment Management Permissions
        // These permissions control access to comment viewing and moderation
        Permission::firstOrCreate(['name' => 'view comments']);
        Permission::firstOrCreate(['name' => 'moderate comments']);
        Permission::firstOrCreate(['name' => 'delete comments']);
    }
}
