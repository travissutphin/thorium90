<?php

namespace App\Core\Plugin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

abstract class PluginServiceProvider extends ServiceProvider
{
    protected Plugin $plugin;

    public function __construct($app, Plugin $plugin = null)
    {
        parent::__construct($app);
        $this->plugin = $plugin;
    }

    /**
     * Register plugin services
     */
    public function register(): void
    {
        // Override in plugin service providers
    }

    /**
     * Boot plugin services
     */
    public function boot(): void
    {
        // Override in plugin service providers
    }

    /**
     * Register plugin routes
     */
    protected function registerRoutes(string $routesPath = null): void
    {
        $routesPath = $routesPath ?: $this->plugin->getPath() . '/routes';

        if (file_exists($routesPath . '/web.php')) {
            Route::middleware('web')
                ->namespace($this->getControllerNamespace())
                ->group($routesPath . '/web.php');
        }

        if (file_exists($routesPath . '/api.php')) {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->getControllerNamespace())
                ->group($routesPath . '/api.php');
        }

        if (file_exists($routesPath . '/admin.php')) {
            Route::prefix('admin')
                ->middleware(['web', 'auth', 'role:Admin'])
                ->namespace($this->getControllerNamespace())
                ->group($routesPath . '/admin.php');
        }
    }

    /**
     * Register plugin views
     */
    protected function registerViews(string $viewsPath = null): void
    {
        $viewsPath = $viewsPath ?: $this->plugin->getPath() . '/resources/views';

        if (is_dir($viewsPath)) {
            View::addNamespace($this->plugin->getId(), $viewsPath);
        }
    }

    /**
     * Register plugin translations
     */
    protected function registerTranslations(string $langPath = null): void
    {
        $langPath = $langPath ?: $this->plugin->getPath() . '/resources/lang';

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->plugin->getId());
        }
    }

    /**
     * Register plugin migrations
     */
    protected function registerMigrations(string $migrationsPath = null): void
    {
        $migrationsPath = $migrationsPath ?: $this->plugin->getPath() . '/database/migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    /**
     * Register plugin configuration
     */
    protected function registerConfig(string $configPath = null): void
    {
        $configPath = $configPath ?: $this->plugin->getPath() . '/config';

        if (is_dir($configPath)) {
            $configFiles = glob($configPath . '/*.php');
            
            foreach ($configFiles as $configFile) {
                $configName = $this->plugin->getId() . '.' . basename($configFile, '.php');
                $this->mergeConfigFrom($configFile, $configName);
            }
        }
    }

    /**
     * Publish plugin assets
     */
    protected function publishAssets(string $assetsPath = null): void
    {
        $assetsPath = $assetsPath ?: $this->plugin->getPath() . '/resources/assets';

        if (is_dir($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path('plugins/' . $this->plugin->getId()),
            ], $this->plugin->getId() . '-assets');
        }
    }

    /**
     * Publish plugin configuration
     */
    protected function publishConfig(string $configPath = null): void
    {
        $configPath = $configPath ?: $this->plugin->getPath() . '/config';

        if (is_dir($configPath)) {
            $configFiles = glob($configPath . '/*.php');
            
            foreach ($configFiles as $configFile) {
                $configName = basename($configFile, '.php');
                $this->publishes([
                    $configFile => config_path($this->plugin->getId() . '_' . $configName . '.php'),
                ], $this->plugin->getId() . '-config');
            }
        }
    }

    /**
     * Register plugin commands
     */
    protected function registerCommands(array $commands): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($commands);
        }
    }

    /**
     * Register plugin event listeners
     */
    protected function registerEventListeners(array $listeners): void
    {
        foreach ($listeners as $event => $listener) {
            $this->app['events']->listen($event, $listener);
        }
    }

    /**
     * Register plugin middleware
     */
    protected function registerMiddleware(array $middleware): void
    {
        $router = $this->app['router'];

        foreach ($middleware as $name => $class) {
            $router->aliasMiddleware($name, $class);
        }
    }

    /**
     * Register plugin view composers
     */
    protected function registerViewComposers(array $composers): void
    {
        foreach ($composers as $view => $composer) {
            View::composer($view, $composer);
        }
    }

    /**
     * Get controller namespace
     */
    protected function getControllerNamespace(): string
    {
        $namespace = $this->plugin->getManifest()['namespace'] ?? 'Plugins\\' . ucfirst($this->plugin->getId());
        return $namespace . '\\Controllers';
    }

    /**
     * Get plugin instance
     */
    protected function getPlugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Helper method to get plugin path
     */
    protected function pluginPath(string $path = ''): string
    {
        return $this->plugin->getPath() . ($path ? '/' . ltrim($path, '/') : '');
    }

    /**
     * Helper method to get plugin resource path
     */
    protected function resourcePath(string $path = ''): string
    {
        return $this->pluginPath('resources' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Helper method to get plugin database path
     */
    protected function databasePath(string $path = ''): string
    {
        return $this->pluginPath('database' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Helper method to get plugin config path
     */
    protected function configPath(string $path = ''): string
    {
        return $this->pluginPath('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}
