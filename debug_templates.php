<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Template System ===\n\n";

// Check if the page exists and what template it's using
$page = \App\Models\Page::where('slug', 'client-about-page')->first();

if ($page) {
    echo "✓ Page found:\n";
    echo "  - Title: " . $page->title . "\n";
    echo "  - Slug: " . $page->slug . "\n";
    echo "  - Status: " . $page->status . "\n";
    echo "  - Template: " . ($page->template ?? 'null') . "\n";
    echo "  - Layout: " . ($page->layout ?? 'null') . "\n";
    echo "  - Theme: " . ($page->theme ?? 'null') . "\n";
    echo "  - Template Config: " . json_encode($page->template_config ?? []) . "\n";
} else {
    echo "✗ Page 'client-about-page' not found\n";
}

echo "\n";

// Check feature system
echo "Feature System Status:\n";
try {
    $featureService = app(\App\Services\FeatureService::class);
    echo "✓ FeatureService loaded\n";
    
    echo "✓ Blog plugin: " . (feature('plugin.blog') ? 'enabled' : 'disabled') . "\n";
    echo "✓ Testimonials: " . (feature('testimonials') ? 'enabled' : 'disabled') . "\n";
    echo "✓ Team page: " . (feature('team_page') ? 'enabled' : 'disabled') . "\n";
    
} catch (Exception $e) {
    echo "✗ Feature system error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check if template files exist
echo "Template Files:\n";
$homeTemplate = 'resources/js/templates/public/HomePage.tsx';
$aboutTemplate = 'resources/js/templates/public/AboutPage.tsx';
$registerFile = 'resources/js/templates/register.ts';

echo ($homeTemplate && file_exists($homeTemplate) ? "✓" : "✗") . " HomePage.tsx\n";
echo ($aboutTemplate && file_exists($aboutTemplate) ? "✓" : "✗") . " AboutPage.tsx\n";
echo ($registerFile && file_exists($registerFile) ? "✓" : "✗") . " register.ts\n";

echo "\n";

// Check build files
echo "Build Files:\n";
$manifestPath = 'public/build/manifest.json';
if (file_exists($manifestPath)) {
    echo "✓ Build manifest exists\n";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (isset($manifest['resources/js/app.tsx'])) {
        echo "✓ Main app.tsx compiled\n";
    } else {
        echo "✗ Main app.tsx not found in manifest\n";
    }
} else {
    echo "✗ Build manifest not found\n";
}

echo "\n=== Debug Complete ===\n";
echo "Next steps:\n";
echo "1. Visit /pages/client-about-page in your browser\n";
echo "2. Open browser developer tools (F12)\n";
echo "3. Check Console tab for JavaScript errors\n";
echo "4. Look for template registration messages\n";
