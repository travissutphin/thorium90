<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Page;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Pages CMS ===\n\n";

// Check permissions
echo "1. Checking page-related permissions:\n";
$pagePermissions = Permission::where('name', 'like', '%page%')->pluck('name')->toArray();
if (empty($pagePermissions)) {
    echo "   ❌ No page permissions found!\n";
} else {
    echo "   ✅ Found permissions: " . implode(', ', $pagePermissions) . "\n";
}

// Check if pages table exists
echo "\n2. Checking pages table:\n";
try {
    $count = Page::count();
    echo "   ✅ Pages table exists with {$count} records\n";
} catch (Exception $e) {
    echo "   ❌ Error accessing pages table: " . $e->getMessage() . "\n";
}

// Test creating a page
echo "\n3. Testing page creation:\n";
$admin = User::whereHas('roles', function($q) {
    $q->where('name', 'Admin');
})->first();

if (!$admin) {
    $admin = User::first();
    if ($admin) {
        $admin->assignRole('Admin');
        echo "   ℹ️ Assigned Admin role to user: {$admin->name}\n";
    }
}

if ($admin) {
    Auth::login($admin);
    echo "   ✅ Logged in as: {$admin->name} (ID: {$admin->id})\n";
    
    // Check user permissions
    $userPermissions = $admin->getAllPermissions()->pluck('name')->toArray();
    $hasCreatePermission = in_array('create pages', $userPermissions);
    echo "   " . ($hasCreatePermission ? "✅" : "❌") . " User has 'create pages' permission\n";
    
    // Try to create a test page
    try {
        $testData = [
            'title' => 'Test Page ' . time(),
            'slug' => 'test-page-' . time(),
            'content' => 'This is test content.',
            'excerpt' => 'Test excerpt',
            'status' => 'draft',
            'is_featured' => false,
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'user_id' => $admin->id,
        ];
        
        $page = Page::create($testData);
        echo "   ✅ Successfully created page: {$page->title} (ID: {$page->id})\n";
        
        // Clean up
        $page->delete();
        echo "   ✅ Test page deleted\n";
    } catch (Exception $e) {
        echo "   ❌ Failed to create page: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ No admin user found\n";
}

// Check the fillable fields
echo "\n4. Checking Page model fillable fields:\n";
$page = new Page();
$fillable = $page->getFillable();
echo "   Fillable fields: " . implode(', ', $fillable) . "\n";

// Check if required fields are fillable
$requiredFields = ['title', 'slug', 'content', 'status', 'user_id'];
$missingFields = array_diff($requiredFields, $fillable);
if (empty($missingFields)) {
    echo "   ✅ All required fields are fillable\n";
} else {
    echo "   ❌ Missing fillable fields: " . implode(', ', $missingFields) . "\n";
}

echo "\n=== Test Complete ===\n";
