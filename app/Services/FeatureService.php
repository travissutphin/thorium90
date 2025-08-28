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
            // Check modules (complex features)
            if (str_starts_with($feature, 'module.')) {
                $module = str_replace('module.', '', $feature);
                return config("features.modules.{$module}", false);
            }
            
            // Check custom features (simple on/off)
            return config("features.custom.{$feature}", false);
        });
    }
    
    /**
     * Get all enabled modules
     */
    public function enabledModules(): array
    {
        return array_keys(
            array_filter(config('features.modules', []))
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
        if (str_starts_with($feature, 'module.')) {
            $module = str_replace('module.', '', $feature);
            Config::set("features.modules.{$module}", $enabled);
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
            'modules' => [
                'total' => count(config('features.modules', [])),
                'enabled' => count($this->enabledModules()),
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
        $modules = config('features.modules', []);
        $custom = config('features.custom', []);
        
        return [
            'modules' => array_map(fn($enabled) => [
                'enabled' => $enabled,
                'type' => 'module'
            ], $modules),
            'custom' => array_map(fn($enabled) => [
                'enabled' => $enabled,
                'type' => 'custom'
            ], $custom)
        ];
    }
}
