<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * RoleSeeder
 * 
 * This seeder is responsible for creating the basic roles for the Multi-Role User
 * Authentication system. It creates the five core roles that form the foundation
 * of the role hierarchy.
 * 
 * Key Features:
 * - Creates all five core roles (Super Admin, Admin, Editor, Author, Subscriber)
 * - Clears cached permissions to ensure fresh role creation
 * - Provides a clean slate for role setup
 * 
 * Role Hierarchy:
 * - Super Admin: Highest level access (all permissions)
 * - Admin: Administrative access (most permissions)
 * - Editor: Content management access
 * - Author: Content creation access (limited)
 * - Subscriber: Basic access only
 * 
 * Usage:
 * ```bash
 * # Run this seeder to create basic roles
 * php artisan db:seed --class=RoleSeeder
 * 
 * # Or run all seeders
 * php artisan db:seed
 * ```
 * 
 * Note: This seeder only creates roles without permissions. Use RolePermissionSeeder
 * to create both roles and their associated permissions.
 * 
 * @see https://laravel.com/docs/seeders
 * @see https://spatie.be/docs/laravel-permission
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * 
     * This method creates the five core roles for the Multi-Role User Authentication system.
     * It first clears any cached permissions to ensure a clean setup, then creates
     * each role with the default 'web' guard.
     */
    public function run(): void
    {
        // Reset cached roles and permissions to ensure fresh creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the five core roles for the system
        Role::create(['name' => 'Super Admin']);  // Highest level access
        Role::create(['name' => 'Admin']);        // Administrative access
        Role::create(['name' => 'Editor']);       // Content management
        Role::create(['name' => 'Author']);       // Content creation
        Role::create(['name' => 'Subscriber']);   // Basic access
    }
}
