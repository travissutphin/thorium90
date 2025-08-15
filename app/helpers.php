<?php

use App\Services\FeatureService;

if (!function_exists('feature')) {
    /**
     * Check if a feature is enabled
     *
     * @param string $name Feature name (e.g., 'plugin.blog', 'testimonials')
     * @return bool
     */
    function feature(string $name): bool
    {
        return app(FeatureService::class)->isEnabled($name);
    }
}

if (!function_exists('features')) {
    /**
     * Get the FeatureService instance
     *
     * @return FeatureService
     */
    function features(): FeatureService
    {
        return app(FeatureService::class);
    }
}

if (!function_exists('enabled_plugins')) {
    /**
     * Get all enabled plugins
     *
     * @return array
     */
    function enabled_plugins(): array
    {
        return app(FeatureService::class)->enabledPlugins();
    }
}

if (!function_exists('enabled_features')) {
    /**
     * Get all enabled custom features
     *
     * @return array
     */
    function enabled_features(): array
    {
        return app(FeatureService::class)->enabledCustomFeatures();
    }
}
