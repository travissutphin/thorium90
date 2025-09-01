<?php

/**
 * Ziggy Route Checker
 * 
 * This script helps identify missing routes that might cause Ziggy errors
 * Usage: php scripts/check-ziggy-routes.php
 */

require_once 'vendor/autoload.php';

// Common route patterns that should exist
$expectedRoutes = [
    'admin.dashboard',
    'admin.blog.posts.index',
    'admin.blog.posts.create', 
    'admin.blog.posts.edit',
    'admin.blog.posts.update',
    'admin.blog.posts.destroy',
    'admin.blog.categories.index',
    'admin.blog.tags.index',
    'blog.index',
    'blog.posts.show'
];

echo "=================================\n";
echo "   ZIGGY ROUTE VALIDATION\n";
echo "=================================\n\n";

// Get all registered routes
$output = shell_exec('php artisan route:list --json');
$routes = json_decode($output, true);

if (!$routes) {
    echo "❌ ERROR: Could not fetch route list\n";
    exit(1);
}

// Extract route names
$routeNames = array_column($routes, 'name');
$routeNames = array_filter($routeNames); // Remove null values

echo "📊 Total routes found: " . count($routeNames) . "\n\n";

// Check expected routes
echo "🔍 Checking critical routes...\n";
$missingRoutes = [];
$foundRoutes = [];

foreach ($expectedRoutes as $expectedRoute) {
    if (in_array($expectedRoute, $routeNames)) {
        echo "✅ $expectedRoute\n";
        $foundRoutes[] = $expectedRoute;
    } else {
        echo "❌ MISSING: $expectedRoute\n";
        $missingRoutes[] = $expectedRoute;
    }
}

echo "\n";

// Show admin blog routes
echo "📋 Available admin.blog routes:\n";
$adminBlogRoutes = array_filter($routeNames, function($route) {
    return strpos($route, 'admin.blog') === 0;
});

if (empty($adminBlogRoutes)) {
    echo "⚠️  No admin.blog routes found!\n";
} else {
    foreach ($adminBlogRoutes as $route) {
        echo "   • $route\n";
    }
}

echo "\n";

// Summary
echo "=================================\n";
echo "   SUMMARY\n";
echo "=================================\n";
echo "✅ Found routes: " . count($foundRoutes) . "\n";
echo "❌ Missing routes: " . count($missingRoutes) . "\n";

if (!empty($missingRoutes)) {
    echo "\n🚨 MISSING ROUTES:\n";
    foreach ($missingRoutes as $route) {
        echo "   • $route\n";
    }
    echo "\n💡 These routes may cause Ziggy errors in frontend components.\n";
    echo "💡 Check your route files and ensure these routes are defined.\n";
}

if (count($missingRoutes) === 0) {
    echo "\n🎉 All critical routes are available!\n";
}

echo "\n";