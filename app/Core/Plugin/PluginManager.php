<?php

namespace App\Core\Plugin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PluginManager
{
    protected Collection $plugins;
    protected Collection $activePlugins;
    protected string $pluginsPath;
    protected PluginMigrationRunner $migrationRunner;
    protected NavigationManager $navigationManager;

    public function __construct(PluginMigrationRunner $migrationRunner, NavigationManager $navigationManager)
    {
        $this->plugins = new Collection();
        $this->activePlugins = new Collection();
        $this->pluginsPath = base_path('plugins');
        $this->migrationRunner = $migrationRunner;
        $this->navigationManager = $navigationManager;
    }

    /**
     * Discover and load all plugins
     */
    public function discoverPlugins(): void
    {
        if (!File::exists($this->pluginsPath)) {
            File::makeDirectory($this->pluginsPath, 0755, true);
            return;
        }

        $pluginDirectories = File::directories($this->pluginsPath);

        foreach ($pluginDirectories as $pluginDir) {
            $this->loadPlugin($pluginDir);
        }
    }

    /**
     * Load a specific plugin
     */
    public function loadPlugin(string $pluginPath): ?Plugin
    {
        $manifestPath = $pluginPath . '/plugin.json';
        
        if (!File::exists($manifestPath)) {
            Log::warning("Plugin manifest not found: {$manifestPath}");
            return null;
        }

        try {
            $manifest = json_decode(File::get($manifestPath), true);
            
            if (!$this->validateManifest($manifest)) {
                Log::error("Invalid plugin manifest: {$manifestPath}");
                return null;
            }

            $plugin = new Plugin($manifest, $pluginPath);
            
            // Check if plugin is enabled
            if ($this->isPluginEnabled($plugin->getId())) {
                $this->activePlugins->put($plugin->getId(), $plugin);
                $plugin->boot();
            }
            
            $this->plugins->put($plugin->getId(), $plugin);
            
            return $plugin;
            
        } catch (\Exception $e) {
            Log::error("Failed to load plugin: {$pluginPath}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Enable a plugin
     */
    public function enablePlugin(string $pluginId): bool
    {
        $plugin = $this->plugins->get($pluginId);
        
        if (!$plugin) {
            return false;
        }

        try {
            // Check dependencies
            if (!$this->checkDependencies($plugin)) {
                throw new \Exception("Plugin dependencies not met");
            }

            // Boot the plugin
            $plugin->boot();
            
            // Add to active plugins
            $this->activePlugins->put($pluginId, $plugin);
            
            // Update enabled plugins list
            $this->updateEnabledPlugins($pluginId, true);
            
            Log::info("Plugin enabled: {$pluginId}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to enable plugin: {$pluginId}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Disable a plugin
     */
    public function disablePlugin(string $pluginId): bool
    {
        $plugin = $this->activePlugins->get($pluginId);
        
        if (!$plugin) {
            return false;
        }

        try {
            // Check if other plugins depend on this one
            if ($this->hasDependendPlugins($pluginId)) {
                throw new \Exception("Other plugins depend on this plugin");
            }

            // Shutdown the plugin
            $plugin->shutdown();
            
            // Remove from active plugins
            $this->activePlugins->forget($pluginId);
            
            // Update enabled plugins list
            $this->updateEnabledPlugins($pluginId, false);
            
            Log::info("Plugin disabled: {$pluginId}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to disable plugin: {$pluginId}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get all plugins
     */
    public function getAllPlugins(): Collection
    {
        return $this->plugins;
    }

    /**
     * Get active plugins
     */
    public function getActivePlugins(): Collection
    {
        return $this->activePlugins;
    }

    /**
     * Get plugin by ID
     */
    public function getPlugin(string $pluginId): ?Plugin
    {
        return $this->plugins->get($pluginId);
    }

    /**
     * Check if plugin is enabled
     */
    public function isPluginEnabled(string $pluginId): bool
    {
        return DB::table('plugin_states')
            ->where('plugin_id', $pluginId)
            ->where('enabled', true)
            ->exists();
    }

    /**
     * Install a plugin from a zip file
     */
    public function installPlugin(string $zipPath): bool
    {
        try {
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath) !== true) {
                throw new \Exception("Cannot open zip file");
            }

            // Extract to temporary directory first
            $tempDir = storage_path('app/temp/plugin_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);
            
            $zip->extractTo($tempDir);
            $zip->close();

            // Validate plugin structure
            $manifestPath = $tempDir . '/plugin.json';
            if (!File::exists($manifestPath)) {
                throw new \Exception("Plugin manifest not found");
            }

            $manifest = json_decode(File::get($manifestPath), true);
            if (!$this->validateManifest($manifest)) {
                throw new \Exception("Invalid plugin manifest");
            }

            // Check if plugin already exists
            $pluginId = $manifest['id'];
            if ($this->plugins->has($pluginId)) {
                throw new \Exception("Plugin already installed");
            }

            // Move to plugins directory
            $pluginDir = $this->pluginsPath . '/' . $pluginId;
            File::moveDirectory($tempDir, $pluginDir);

            // Load the plugin
            $this->loadPlugin($pluginDir);

            Log::info("Plugin installed: {$pluginId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to install plugin", ['error' => $e->getMessage()]);
            
            // Cleanup
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            
            return false;
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin(string $pluginId): bool
    {
        try {
            // Disable plugin first
            if ($this->isPluginEnabled($pluginId)) {
                $this->disablePlugin($pluginId);
            }

            $plugin = $this->plugins->get($pluginId);
            if (!$plugin) {
                return false;
            }

            // Run uninstall hook
            $plugin->uninstall();

            // Remove plugin directory
            File::deleteDirectory($plugin->getPath());

            // Remove from plugins collection
            $this->plugins->forget($pluginId);

            Log::info("Plugin uninstalled: {$pluginId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to uninstall plugin: {$pluginId}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get plugin statistics
     */
    public function getStats(): array
    {
        return [
            'total' => $this->plugins->count(),
            'active' => $this->activePlugins->count(),
            'inactive' => $this->plugins->count() - $this->activePlugins->count(),
            'by_category' => $this->plugins->groupBy(function ($plugin) {
                return $plugin->getManifest()['category'] ?? 'other';
            })->map->count()->toArray(),
        ];
    }

    /**
     * Validate plugin manifest
     */
    protected function validateManifest(array $manifest): bool
    {
        $required = ['id', 'name', 'version', 'description', 'author'];
        
        foreach ($required as $field) {
            if (!isset($manifest[$field]) || empty($manifest[$field])) {
                return false;
            }
        }

        // Validate ID format
        if (!preg_match('/^[a-z0-9_-]+$/', $manifest['id'])) {
            return false;
        }

        // Validate version format
        if (!preg_match('/^\d+\.\d+\.\d+$/', $manifest['version'])) {
            return false;
        }

        return true;
    }

    /**
     * Check plugin dependencies
     */
    protected function checkDependencies(Plugin $plugin): bool
    {
        $dependencies = $plugin->getManifest()['dependencies'] ?? [];
        
        foreach ($dependencies as $depId => $version) {
            $depPlugin = $this->plugins->get($depId);
            
            if (!$depPlugin || !$this->isPluginEnabled($depId)) {
                return false;
            }

            // Simple version check (could be enhanced)
            if (version_compare($depPlugin->getVersion(), $version, '<')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if other plugins depend on this one
     */
    protected function hasDependendPlugins(string $pluginId): bool
    {
        foreach ($this->activePlugins as $plugin) {
            $dependencies = $plugin->getManifest()['dependencies'] ?? [];
            if (array_key_exists($pluginId, $dependencies)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get enabled plugins from cache/config
     */
    protected function getEnabledPlugins(): array
    {
        return Cache::get('enabled_plugins', []);
    }

    /**
     * Update enabled plugins list
     */
    protected function updateEnabledPlugins(string $pluginId, bool $enabled): void
    {
        $enabledPlugins = $this->getEnabledPlugins();
        
        if ($enabled) {
            if (!in_array($pluginId, $enabledPlugins)) {
                $enabledPlugins[] = $pluginId;
            }
        } else {
            $enabledPlugins = array_filter($enabledPlugins, fn($id) => $id !== $pluginId);
        }

        Cache::put('enabled_plugins', array_values($enabledPlugins));
    }

    /**
     * Clear plugin cache
     */
    public function clearCache(): void
    {
        Cache::forget('enabled_plugins');
    }
}
