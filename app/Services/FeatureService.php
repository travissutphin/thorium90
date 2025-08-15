<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class FeatureService
{
    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $feature): bool
    {
        // Cache feature checks for performance
        return Cache::remember("feature.{$feature}", 3600, function () use ($feature) {
            // Check plugins (complex features)
            if (str_starts_with($feature, 'plugin.')) {
                $plugin = str_replace('plugin.', '', $feature);
                return config("features.plugins.{$plugin}", false);
            }
            
            // Check custom features (simple on/off)
            return config("features.custom.{$feature}", false);
        });
    }
    
    /**
     * Get all enabled plugins
     */
    public function enabledPlugins(): array
    {
        return array_keys(
            array_filter(config('features.plugins', []))
        );
    }
    
    /**
     * Get all enabled custom features
     */
    public function enabledCustomFeatures(): array
    {
        return array_keys(
            array_filter(config('features.custom', []))
        );
    }
    
    /**
     * Enable a feature
     */
    public function enable(string $feature): void
    {
        $this->setFeature($feature, true);
    }
    
    /**
     * Disable a feature
     */
    public function disable(string $feature): void
    {
        $this->setFeature($feature, false);
    }
    
    /**
     * Set feature state
     */
    private function setFeature(string $feature, bool $enabled): void
    {
        if (str_starts_with($feature, 'plugin.')) {
            $plugin = str_replace('plugin.', '', $feature);
            Config::set("features.plugins.{$plugin}", $enabled);
        } else {
            Config::set("features.custom.{$feature}", $enabled);
        }
        
        // Clear cache
        Cache::forget("feature.{$feature}");
    }
    
    /**
     * Get feature statistics
     */
    public function getStats(): array
    {
        return [
            'plugins' => [
                'total' => count(config('features.plugins', [])),
                'enabled' => count($this->enabledPlugins()),
            ],
            'custom' => [
                'total' => count(config('features.custom', [])),
                'enabled' => count($this->enabledCustomFeatures()),
            ]
        ];
    }
    
    /**
     * Get all available features with their status
     */
    public function getAllFeatures(): array
    {
        $plugins = config('features.plugins', []);
        $custom = config('features.custom', []);
        
        return [
            'plugins' => array_map(fn($enabled) => [
                'enabled' => $enabled,
                'type' => 'plugin'
            ], $plugins),
            'custom' => array_map(fn($enabled) => [
                'enabled' => $enabled,
                'type' => 'custom'
            ], $custom)
        ];
    }
}
