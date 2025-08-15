<?php

namespace App\Core\Plugin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class NavigationManager
{
    protected string $cacheKey = 'plugin_navigation';
    protected int $cacheTtl = 3600; // 1 hour

    /**
     * Register navigation items for a plugin
     */
    public function registerNavigation(string $pluginId, array $navigationItems): void
    {
        // Store navigation in plugin_states table
        DB::table('plugin_states')
            ->where('plugin_id', $pluginId)
            ->update([
                'navigation' => json_encode($navigationItems),
                'updated_at' => now(),
            ]);

        // Clear cache to force rebuild
        $this->clearCache();
    }

    /**
     * Get all navigation items from active plugins
     */
    public function getNavigation(): Collection
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            return $this->buildNavigation();
        });
    }

    /**
     * Build navigation from active plugins
     */
    protected function buildNavigation(): Collection
    {
        $pluginStates = DB::table('plugin_states')
            ->where('enabled', true)
            ->whereNotNull('navigation')
            ->get();

        $navigation = collect();

        foreach ($pluginStates as $state) {
            $pluginNavigation = json_decode($state->navigation, true);
            
            if (!empty($pluginNavigation)) {
                $navigation = $navigation->merge($this->processPluginNavigation($state->plugin_id, $pluginNavigation));
            }
        }

        // Sort navigation by position
        return $navigation->sortBy('position');
    }

    /**
     * Process navigation items for a plugin
     */
    protected function processPluginNavigation(string $pluginId, array $navigationItems): Collection
    {
        $processed = collect();

        foreach ($navigationItems as $item) {
            // Add plugin context to navigation item
            $item['plugin_id'] = $pluginId;
            $item['id'] = $pluginId . '.' . ($item['id'] ?? uniqid());
            
            // Set default position if not specified
            if (!isset($item['position'])) {
                $item['position'] = 1000;
            }

            // Process child items if they exist
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->processChildNavigation($pluginId, $item['children']);
            }

            $processed->push($item);
        }

        return $processed;
    }

    /**
     * Process child navigation items
     */
    protected function processChildNavigation(string $pluginId, array $children): array
    {
        $processed = [];

        foreach ($children as $child) {
            $child['plugin_id'] = $pluginId;
            $child['id'] = $pluginId . '.' . ($child['id'] ?? uniqid());
            
            if (!isset($child['position'])) {
                $child['position'] = 1000;
            }

            $processed[] = $child;
        }

        // Sort children by position
        usort($processed, function ($a, $b) {
            return ($a['position'] ?? 1000) <=> ($b['position'] ?? 1000);
        });

        return $processed;
    }

    /**
     * Get navigation for admin sidebar
     */
    public function getAdminNavigation(): array
    {
        $navigation = $this->getNavigation();
        
        // Group navigation by section
        $grouped = [
            'content' => [],
            'management' => [],
            'settings' => [],
            'other' => [],
        ];

        foreach ($navigation as $item) {
            $group = $item['group'] ?? 'other';
            
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            
            $grouped[$group][] = $item;
        }

        // Remove empty groups
        return array_filter($grouped, function ($items) {
            return !empty($items);
        });
    }

    /**
     * Check if user has permission for navigation item
     */
    public function hasPermission(array $navigationItem, $user = null): bool
    {
        if (!isset($navigationItem['permission'])) {
            return true;
        }

        $user = $user ?: auth()->user();
        
        if (!$user) {
            return false;
        }

        $permission = $navigationItem['permission'];
        
        // Check if user has the required permission
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }

        // Fallback to checking permission_names attribute
        $permissions = $user->permission_names ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Filter navigation items by user permissions
     */
    public function filterByPermissions(Collection $navigation, $user = null): Collection
    {
        return $navigation->filter(function ($item) use ($user) {
            if (!$this->hasPermission($item, $user)) {
                return false;
            }

            // Filter child items by permissions
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = array_filter($item['children'], function ($child) use ($user) {
                    return $this->hasPermission($child, $user);
                });
            }

            return true;
        });
    }

    /**
     * Get navigation breadcrumbs for current route
     */
    public function getBreadcrumbs(string $currentRoute): array
    {
        $navigation = $this->getNavigation();
        $breadcrumbs = [];

        foreach ($navigation as $item) {
            if ($this->matchesRoute($item, $currentRoute)) {
                $breadcrumbs[] = [
                    'label' => $item['label'],
                    'url' => $item['url'] ?? null,
                ];
                break;
            }

            // Check child items
            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $child) {
                    if ($this->matchesRoute($child, $currentRoute)) {
                        $breadcrumbs[] = [
                            'label' => $item['label'],
                            'url' => $item['url'] ?? null,
                        ];
                        $breadcrumbs[] = [
                            'label' => $child['label'],
                            'url' => $child['url'] ?? null,
                        ];
                        break 2;
                    }
                }
            }
        }

        return $breadcrumbs;
    }

    /**
     * Check if navigation item matches current route
     */
    protected function matchesRoute(array $item, string $currentRoute): bool
    {
        if (isset($item['route']) && $item['route'] === $currentRoute) {
            return true;
        }

        if (isset($item['url']) && $item['url'] === request()->path()) {
            return true;
        }

        return false;
    }

    /**
     * Remove navigation for a plugin
     */
    public function removeNavigation(string $pluginId): void
    {
        DB::table('plugin_states')
            ->where('plugin_id', $pluginId)
            ->update([
                'navigation' => null,
                'updated_at' => now(),
            ]);

        $this->clearCache();
    }

    /**
     * Clear navigation cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get navigation statistics
     */
    public function getStats(): array
    {
        $navigation = $this->getNavigation();
        
        $stats = [
            'total_items' => $navigation->count(),
            'by_plugin' => [],
            'by_group' => [],
        ];

        foreach ($navigation as $item) {
            $pluginId = $item['plugin_id'] ?? 'core';
            $group = $item['group'] ?? 'other';

            if (!isset($stats['by_plugin'][$pluginId])) {
                $stats['by_plugin'][$pluginId] = 0;
            }
            $stats['by_plugin'][$pluginId]++;

            if (!isset($stats['by_group'][$group])) {
                $stats['by_group'][$group] = 0;
            }
            $stats['by_group'][$group]++;
        }

        return $stats;
    }

    /**
     * Validate navigation structure
     */
    public function validateNavigation(array $navigationItems): array
    {
        $errors = [];

        foreach ($navigationItems as $index => $item) {
            if (!isset($item['label']) || empty($item['label'])) {
                $errors[] = "Navigation item at index {$index} is missing required 'label' field";
            }

            if (!isset($item['url']) && !isset($item['route'])) {
                $errors[] = "Navigation item '{$item['label']}' must have either 'url' or 'route' field";
            }

            // Validate children if present
            if (isset($item['children']) && is_array($item['children'])) {
                foreach ($item['children'] as $childIndex => $child) {
                    if (!isset($child['label']) || empty($child['label'])) {
                        $errors[] = "Child navigation item at index {$childIndex} is missing required 'label' field";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Export navigation configuration
     */
    public function exportNavigation(): array
    {
        $pluginStates = DB::table('plugin_states')
            ->where('enabled', true)
            ->whereNotNull('navigation')
            ->get();

        $export = [];

        foreach ($pluginStates as $state) {
            $export[$state->plugin_id] = json_decode($state->navigation, true);
        }

        return $export;
    }
}
