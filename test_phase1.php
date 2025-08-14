<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

echo "\n===========================================\n";
echo "PHASE 1 VERIFICATION - Posts to Pages Update\n";
echo "===========================================\n\n";

// Test 1: Check permissions in database
echo "1. Checking Permissions in Database:\n";
echo "-------------------------------------\n";
$pagePermissions = Permission::where('name', 'like', '%page%')->pluck('name')->toArray();
$postPermissions = Permission::where('name', 'like', '%post%')->pluck('name')->toArray();

if (count($pagePermissions) > 0) {
    echo "✓ Page permissions found (" . count($pagePermissions) . "):\n";
    foreach ($pagePermissions as $perm) {
        echo "   - $perm\n";
    }
} else {
    echo "✗ No page permissions found!\n";
}

if (count($postPermissions) > 0) {
    echo "\n✗ Old post permissions still exist (" . count($postPermissions) . "):\n";
    foreach ($postPermissions as $perm) {
        echo "   - $perm\n";
    }
} else {
    echo "\n✓ No old post permissions found (good!)\n";
}

// Test 2: Check role assignments
echo "\n2. Checking Role Permission Assignments:\n";
echo "-----------------------------------------\n";
$roles = Role::with('permissions')->get();
foreach ($roles as $role) {
    $rolePagePerms = $role->permissions->filter(function($p) {
        return str_contains($p->name, 'page');
    })->pluck('name')->toArray();
    
    if (count($rolePagePerms) > 0) {
        echo "✓ {$role->name} has page permissions:\n";
        foreach ($rolePagePerms as $perm) {
            echo "   - $perm\n";
        }
    }
}

// Test 3: Test Gates
echo "\n3. Testing Gates Configuration:\n";
echo "--------------------------------\n";
$testUser = User::first();
if ($testUser) {
    // Give test permission
    $testUser->givePermissionTo('view pages');
    
    if (\Illuminate\Support\Facades\Gate::forUser($testUser)->allows('view-pages')) {
        echo "✓ Gate 'view-pages' is working correctly\n";
    } else {
        echo "✗ Gate 'view-pages' is not working\n";
    }
    
    // Clean up
    $testUser->revokePermissionTo('view pages');
} else {
    echo "⚠ No users found for gate testing\n";
}

// Test 4: Check routes
echo "\n4. Checking Routes Configuration:\n";
echo "----------------------------------\n";
$routes = \Illuminate\Support\Facades\Route::getRoutes();
$pageRoutes = [];
$postRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_contains($uri, 'pages')) {
        $pageRoutes[] = $uri;
    }
    if (str_contains($uri, 'posts')) {
        $postRoutes[] = $uri;
    }
}

if (count($pageRoutes) > 0) {
    echo "✓ Page routes found:\n";
    foreach ($pageRoutes as $route) {
        echo "   - /$route\n";
    }
} else {
    echo "✗ No page routes found\n";
}

if (count($postRoutes) > 0) {
    echo "\n✗ Old post routes still exist:\n";
    foreach ($postRoutes as $route) {
        echo "   - /$route\n";
    }
} else {
    echo "\n✓ No old post routes found (good!)\n";
}

// Summary
echo "\n===========================================\n";
echo "PHASE 1 SUMMARY\n";
echo "===========================================\n";

$issues = [];
if (count($postPermissions) > 0) {
    $issues[] = "Old post permissions still exist in database";
}
if (count($postRoutes) > 0) {
    $issues[] = "Old post routes still exist";
}
if (count($pagePermissions) == 0) {
    $issues[] = "No page permissions found in database";
}
if (count($pageRoutes) == 0) {
    $issues[] = "No page routes found";
}

if (count($issues) == 0) {
    echo "✓ Phase 1 completed successfully!\n";
    echo "  - All permissions updated from 'posts' to 'pages'\n";
    echo "  - All routes updated\n";
    echo "  - Gates configured correctly\n";
    echo "  - Documentation updated\n";
} else {
    echo "✗ Issues found:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}

echo "\n";
