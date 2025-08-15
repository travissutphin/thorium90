<?php

namespace App\Core\Plugin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Plugin
{
    protected array $manifest;
    protected string $path;
    protected bool $booted = false;
    protected ?PluginServiceProvider $serviceProvider = null;

    public function __construct(array $manifest, string $path)
    {
        $this->manifest = $manifest;
        $this->path = $path;
    }

    /**
     * Boot the plugin
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        try {
            // Load plugin service provider if exists
            $this->loadServiceProvider();

            // Register plugin routes
            $this->registerRoutes();

            // Register plugin views
            $this->registerViews();

            // Register plugin translations
            $this->registerTranslations();

            // Register plugin assets
            $this->registerAssets();

            // Run plugin boot hook
            $this->runHook('boot');

            $this->booted = true;
            Log::info("Plugin booted: {$this->getId()}");

        } catch (\Exception $e) {
            Log::error("Failed to boot plugin: {$this->getId()}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Shutdown the plugin
     */
    public function shutdown(): void
    {
        if (!$this->booted) {
            return;
        }

        try {
            // Run plugin shutdown hook
            $this->runHook('shutdown');

            // Unregister service provider
            if ($this->serviceProvider) {
                // Laravel doesn't have a direct way to unregister providers
                // This would need custom implementation
            }

            $this->booted = false;
            Log::info("Plugin shutdown: {$this->getId()}");

        } catch (\Exception $e) {
            Log::error("Failed to shutdown plugin: {$this->getId()}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Install the plugin
     */
    public function install(): void
    {
        try {
            // Run database migrations if they exist
            $this->runMigrations();

            // Run plugin install hook
            $this->runHook('install');

            Log::info("Plugin installed: {$this->getId()}");

        } catch (\Exception $e) {
            Log::error("Failed to install plugin: {$this->getId()}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Uninstall the plugin
     */
    public function uninstall(): void
    {
        try {
            // Run plugin uninstall hook
            $this->runHook('uninstall');

            // Rollback database migrations if needed
            $this->rollbackMigrations();

            Log::info("Plugin uninstalled: {$this->getId()}");

        } catch (\Exception $e) {
            Log::error("Failed to uninstall plugin: {$this->getId()}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get plugin ID
     */
    public function getId(): string
    {
        return $this->manifest['id'];
    }

    /**
     * Get plugin name
     */
    public function getName(): string
    {
        return $this->manifest['name'];
    }

    /**
     * Get plugin version
     */
    public function getVersion(): string
    {
        return $this->manifest['version'];
    }

    /**
     * Get plugin description
     */
    public function getDescription(): string
    {
        return $this->manifest['description'];
    }

    /**
     * Get plugin author
     */
    public function getAuthor(): string
    {
        return $this->manifest['author'];
    }

    /**
     * Get plugin category
     */
    public function getCategory(): string
    {
        return $this->manifest['category'] ?? 'other';
    }

    /**
     * Get plugin path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get plugin manifest
     */
    public function getManifest(): array
    {
        return $this->manifest;
    }

    /**
     * Check if plugin is booted
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * Get plugin configuration
     */
    public function getConfig(string $key = null, $default = null)
    {
        $config = $this->manifest['config'] ?? [];
        
        if ($key === null) {
            return $config;
        }

        return data_get($config, $key, $default);
    }

    /**
     * Get plugin templates
     */
    public function getTemplates(): array
    {
        return $this->manifest['templates'] ?? [];
    }

    /**
     * Get plugin layouts
     */
    public function getLayouts(): array
    {
        return $this->manifest['layouts'] ?? [];
    }

    /**
     * Get plugin blocks
     */
    public function getBlocks(): array
    {
        return $this->manifest['blocks'] ?? [];
    }

    /**
     * Get plugin themes
     */
    public function getThemes(): array
    {
        return $this->manifest['themes'] ?? [];
    }

    /**
     * Load plugin service provider
     */
    protected function loadServiceProvider(): void
    {
        $providerClass = $this->manifest['provider'] ?? null;
        
        if (!$providerClass) {
            return;
        }

        $providerPath = $this->path . '/src/' . str_replace('\\', '/', $providerClass) . '.php';
        
        if (!File::exists($providerPath)) {
            Log::warning("Plugin service provider not found: {$providerPath}");
            return;
        }

        // Include the provider file
        require_once $providerPath;

        // Instantiate the provider
        $fullProviderClass = $this->getNamespace() . '\\' . $providerClass;
        
        if (class_exists($fullProviderClass)) {
            $this->serviceProvider = app($fullProviderClass);
            
            if (method_exists($this->serviceProvider, 'register')) {
                $this->serviceProvider->register();
            }
            
            if (method_exists($this->serviceProvider, 'boot')) {
                $this->serviceProvider->boot();
            }
        }
    }

    /**
     * Register plugin routes
     */
    protected function registerRoutes(): void
    {
        $routesPath = $this->path . '/routes';
        
        if (!File::exists($routesPath)) {
            return;
        }

        $routeFiles = ['web.php', 'api.php', 'admin.php'];
        
        foreach ($routeFiles as $routeFile) {
            $routePath = $routesPath . '/' . $routeFile;
            
            if (File::exists($routePath)) {
                $prefix = $routeFile === 'api.php' ? 'api' : '';
                $middleware = $routeFile === 'admin.php' ? ['web', 'auth', 'role:Admin'] : ['web'];
                
                app('router')->group([
                    'prefix' => $prefix,
                    'middleware' => $middleware,
                    'namespace' => $this->getNamespace() . '\\Controllers',
                ], function () use ($routePath) {
                    require $routePath;
                });
            }
        }
    }

    /**
     * Register plugin views
     */
    protected function registerViews(): void
    {
        $viewsPath = $this->path . '/resources/views';
        
        if (File::exists($viewsPath)) {
            app('view')->addNamespace($this->getId(), $viewsPath);
        }
    }

    /**
     * Register plugin translations
     */
    protected function registerTranslations(): void
    {
        $langPath = $this->path . '/resources/lang';
        
        if (File::exists($langPath)) {
            app('translator')->addNamespace($this->getId(), $langPath);
        }
    }

    /**
     * Register plugin assets
     */
    protected function registerAssets(): void
    {
        $assetsPath = $this->path . '/resources/assets';
        
        if (File::exists($assetsPath)) {
            // This would need integration with asset compilation
            // For now, just log that assets are available
            Log::info("Plugin assets available: {$this->getId()}");
        }
    }

    /**
     * Run database migrations
     */
    protected function runMigrations(): void
    {
        $migrationsPath = $this->path . '/database/migrations';
        
        if (File::exists($migrationsPath)) {
            // This would need integration with Laravel's migration system
            // For now, just log that migrations are available
            Log::info("Plugin migrations available: {$this->getId()}");
        }
    }

    /**
     * Rollback database migrations
     */
    protected function rollbackMigrations(): void
    {
        // Implementation would depend on how migrations are tracked
        Log::info("Plugin migrations rollback: {$this->getId()}");
    }

    /**
     * Run plugin hook
     */
    protected function runHook(string $hook): void
    {
        $hookFile = $this->path . '/hooks/' . $hook . '.php';
        
        if (File::exists($hookFile)) {
            try {
                require $hookFile;
            } catch (\Exception $e) {
                Log::error("Plugin hook failed: {$this->getId()}:{$hook}", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Get plugin namespace
     */
    protected function getNamespace(): string
    {
        return $this->manifest['namespace'] ?? 'Plugins\\' . ucfirst($this->getId());
    }

    /**
     * Get plugin asset URL
     */
    public function asset(string $path): string
    {
        return url("plugins/{$this->getId()}/assets/{$path}");
    }

    /**
     * Get plugin route URL
     */
    public function route(string $name, array $parameters = []): string
    {
        return route("{$this->getId()}.{$name}", $parameters);
    }

    /**
     * Get plugin view
     */
    public function view(string $view, array $data = []): \Illuminate\View\View
    {
        return view("{$this->getId()}::{$view}", $data);
    }

    /**
     * Get plugin translation
     */
    public function trans(string $key, array $replace = [], string $locale = null): string
    {
        return trans("{$this->getId()}::{$key}", $replace, $locale);
    }

    /**
     * Convert plugin to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'description' => $this->getDescription(),
            'author' => $this->getAuthor(),
            'category' => $this->getCategory(),
            'path' => $this->getPath(),
            'booted' => $this->isBooted(),
            'manifest' => $this->getManifest(),
        ];
    }
}
