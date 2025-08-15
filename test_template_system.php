<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Hybrid Template & Feature System ===\n\n";

// Test 1: Feature Service
echo "1. Testing Feature Service:\n";
try {
    $featureService = app(\App\Services\FeatureService::class);
    echo "   ✓ FeatureService loaded successfully\n";
    
    $stats = $featureService->getStats();
    echo "   ✓ Stats: " . json_encode($stats) . "\n";
    
    // Test feature checks
    echo "   ✓ Blog plugin enabled: " . (feature('plugin.blog') ? 'Yes' : 'No') . "\n";
    echo "   ✓ Testimonials enabled: " . (feature('testimonials') ? 'Yes' : 'No') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Helper Functions
echo "2. Testing Helper Functions:\n";
try {
    $enabledPlugins = enabled_plugins();
    echo "   ✓ Enabled plugins: " . implode(', ', $enabledPlugins) . "\n";
    
    $enabledFeatures = enabled_features();
    echo "   ✓ Enabled features: " . implode(', ', $enabledFeatures) . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Database Schema
echo "3. Testing Database Schema:\n";
try {
    $page = \App\Models\Page::first();
    if ($page) {
        echo "   ✓ Found existing page: " . $page->title . "\n";
        echo "   ✓ Template: " . ($page->template ?? 'null') . "\n";
        echo "   ✓ Layout: " . ($page->layout ?? 'null') . "\n";
        echo "   ✓ Theme: " . ($page->theme ?? 'null') . "\n";
    } else {
        echo "   ℹ No pages found in database\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Create Test Page
echo "4. Creating Test Page:\n";
try {
    $testPage = \App\Models\Page::create([
        'title' => 'Test Home Page',
        'slug' => 'test-home-' . time(),
        'content' => '<p>This is a test page using the client home template.</p>',
        'excerpt' => 'Test page for the hybrid template system',
        'status' => 'published',
        'template' => 'client-home',
        'layout' => 'default',
        'theme' => 'default',
        'template_config' => [
            'custom_class' => 'test-page',
            'showTestimonials' => true
        ],
        'meta_title' => 'Test Home Page',
        'meta_description' => 'Testing the hybrid template system',
        'schema_type' => 'WebPage'
    ]);
    
    echo "   ✓ Test page created successfully\n";
    echo "   ✓ ID: " . $testPage->id . "\n";
    echo "   ✓ URL: /pages/" . $testPage->slug . "\n";
    echo "   ✓ Template: " . $testPage->template . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Create About Page
echo "5. Creating Test About Page:\n";
try {
    $aboutPage = \App\Models\Page::create([
        'title' => 'Test About Page',
        'slug' => 'test-about-' . time(),
        'content' => '<p>This is a test about page using the client about template.</p>',
        'excerpt' => 'Learn about our test company',
        'status' => 'published',
        'template' => 'client-about',
        'layout' => 'default',
        'theme' => 'default',
        'template_config' => [
            'custom_class' => 'about-page',
            'showTeam' => true,
            'showValues' => true
        ],
        'meta_title' => 'About Us - Test Page',
        'meta_description' => 'Learn about our test company and values',
        'schema_type' => 'AboutPage'
    ]);
    
    echo "   ✓ About page created successfully\n";
    echo "   ✓ ID: " . $aboutPage->id . "\n";
    echo "   ✓ URL: /pages/" . $aboutPage->slug . "\n";
    echo "   ✓ Template: " . $aboutPage->template . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "You can now test the system by:\n";
echo "1. Going to /content/pages/create in the admin\n";
echo "2. Selecting 'Home Page Template' or 'About Page Template'\n";
echo "3. Creating a page and viewing it on the frontend\n";
