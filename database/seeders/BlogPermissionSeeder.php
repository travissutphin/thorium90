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
        // Create blog permissions with dotted naming convention
        $permissions = [
            // Blog post permissions
            'blog.posts.view' => 'View blog posts',
            'blog.posts.create' => 'Create new blog posts',
            'blog.posts.edit' => 'Edit blog posts',
            'blog.posts.delete' => 'Delete blog posts',
            
            // Blog category permissions
            'blog.categories.view' => 'View blog categories',
            'blog.categories.create' => 'Create new blog categories',
            'blog.categories.edit' => 'Edit blog categories',
            'blog.categories.delete' => 'Delete blog categories',
            
            // Blog tag permissions
            'blog.tags.view' => 'View blog tags',
            'blog.tags.create' => 'Create new blog tags',
            'blog.tags.edit' => 'Edit blog tags',
            'blog.tags.delete' => 'Delete blog tags',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        $this->command->info('Blog permissions created and assigned to roles.');
    }

    /**
     * Assign blog permissions to roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Define role permissions
        $rolePermissions = [
            'Super Admin' => [
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.edit',
                'blog.posts.delete',
                'blog.categories.view',
                'blog.categories.create',
                'blog.categories.edit',
                'blog.categories.delete',
                'blog.tags.view',
                'blog.tags.create',
                'blog.tags.edit',
                'blog.tags.delete',
            ],
            'Admin' => [
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.edit',
                'blog.posts.delete',
                'blog.categories.view',
                'blog.categories.create',
                'blog.categories.edit',
                'blog.categories.delete',
                'blog.tags.view',
                'blog.tags.create',
                'blog.tags.edit',
                'blog.tags.delete',
            ],
            'Editor' => [
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.edit',
                'blog.posts.delete',
                'blog.categories.view',
                'blog.tags.view',
            ],
            'Author' => [
                'blog.posts.view',
                'blog.posts.create',
                'blog.posts.edit',
                'blog.categories.view',
                'blog.tags.view',
            ],
        ];

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