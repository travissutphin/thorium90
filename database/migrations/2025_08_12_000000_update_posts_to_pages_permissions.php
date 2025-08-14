<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates all permission names from "posts" to "pages"
     * to reflect the new terminology throughout the application.
     */
    public function up(): void
    {
        // Map of old permission names to new permission names
        $permissionMap = [
            'view posts' => 'view pages',
            'create posts' => 'create pages',
            'edit posts' => 'edit pages',
            'delete posts' => 'delete pages',
            'publish posts' => 'publish pages',
            'edit own posts' => 'edit own pages',
            'delete own posts' => 'delete own pages',
        ];

        // Update each permission
        foreach ($permissionMap as $oldName => $newName) {
            $permission = Permission::where('name', $oldName)->first();
            if ($permission) {
                $permission->name = $newName;
                $permission->save();
            }
        }

        // Clear permission cache to ensure changes take effect
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     * 
     * This will revert the permission names back from "pages" to "posts"
     */
    public function down(): void
    {
        // Map of new permission names back to old permission names
        $permissionMap = [
            'view pages' => 'view posts',
            'create pages' => 'create posts',
            'edit pages' => 'edit posts',
            'delete pages' => 'delete posts',
            'publish pages' => 'publish posts',
            'edit own pages' => 'edit own posts',
            'delete own pages' => 'delete own posts',
        ];

        // Revert each permission
        foreach ($permissionMap as $newName => $oldName) {
            $permission = Permission::where('name', $newName)->first();
            if ($permission) {
                $permission->name = $oldName;
                $permission->save();
            }
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
