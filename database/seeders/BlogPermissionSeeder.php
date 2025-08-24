<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BlogPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create blog permissions
        $permissions = [
            // Blog admin access
            'view blog admin' => 'Access blog administration panel',
            
            // Blog post permissions
            'create blog posts' => 'Create new blog posts',
            'edit blog posts' => 'Edit blog posts',
            'edit own blog posts' => 'Edit own blog posts only',
            'delete blog posts' => 'Delete blog posts',
            'delete own blog posts' => 'Delete own blog posts only',
            'publish blog posts' => 'Publish and unpublish blog posts',
            
            // Blog category permissions
            'manage blog categories' => 'Create, edit, and delete blog categories',
            'view blog categories' => 'View blog categories in admin',
            
            // Blog tag permissions
            'manage blog tags' => 'Create, edit, and delete blog tags',
            'view blog tags' => 'View blog tags in admin',
            
            // Blog comment permissions
            'moderate blog comments' => 'Approve, reject, and delete blog comments',
            'view blog comments' => 'View blog comments in admin',
            'reply to blog comments' => 'Reply to blog comments as admin',
            
            // Blog settings permissions
            'manage blog settings' => 'Configure blog settings and features',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Assign permissions to roles based on blog configuration
        $this->assignPermissionsToRoles();

        $this->command->info('Blog permissions created and assigned to roles.');
    }

    /**
     * Assign blog permissions to roles based on configuration.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Get role permission mappings from blog config
        $rolePermissions = config('blog.permissions', []);

        // Default role assignments if config is empty
        if (empty($rolePermissions)) {
            $rolePermissions = [
                'Super Admin' => [
                    'view blog admin',
                    'create blog posts',
                    'edit blog posts',
                    'delete blog posts',
                    'publish blog posts',
                    'manage blog categories',
                    'view blog categories',
                    'manage blog tags',
                    'view blog tags',
                    'moderate blog comments',
                    'view blog comments',
                    'reply to blog comments',
                    'manage blog settings',
                ],
                'Admin' => [
                    'view blog admin',
                    'create blog posts',
                    'edit blog posts',
                    'edit own blog posts',
                    'delete own blog posts',
                    'publish blog posts',
                    'manage blog categories',
                    'view blog categories',
                    'manage blog tags',
                    'view blog tags',
                    'moderate blog comments',
                    'view blog comments',
                    'reply to blog comments',
                ],
                'Manager' => [
                    'view blog admin',
                    'create blog posts',
                    'edit own blog posts',
                    'delete own blog posts',
                    'view blog categories',
                    'view blog tags',
                    'view blog comments',
                ],
            ];
        } else {
            // Convert config format to permission names
            $formattedRolePermissions = [];
            foreach ($rolePermissions as $configKey => $roles) {
                $permissionName = str_replace('_', ' ', $configKey);
                foreach ($roles as $role) {
                    $formattedRolePermissions[$role][] = $permissionName;
                }
            }
            $rolePermissions = $formattedRolePermissions;
        }

        // Assign permissions to roles
        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            
            if ($role) {
                foreach ($permissions as $permissionName) {
                    $permission = Permission::where('name', $permissionName)->first();
                    
                    if ($permission && !$role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                        $this->command->info("Assigned '{$permissionName}' to '{$roleName}'");
                    }
                }
            } else {
                $this->command->warn("Role '{$roleName}' not found. Skipping permission assignment.");
            }
        }
    }
}