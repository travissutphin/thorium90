<?php

namespace App\Providers;

use App\Core\Plugin\PluginManager;
use App\Core\Plugin\PluginMigrationRunner;
use App\Core\Plugin\NavigationManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register PluginMigrationRunner as singleton
        $this->app->singleton(PluginMigrationRunner::class);

        // Register NavigationManager as singleton
        $this->app->singleton(NavigationManager::class);

        // Register PluginManager as singleton with proper dependency injection
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager(
                $app->make(PluginMigrationRunner::class),
                $app->make(NavigationManager::class)
            );
        });

        // Aliases for easier access
        $this->app->alias(PluginManager::class, 'plugin.manager');
        $this->app->alias(PluginMigrationRunner::class, 'plugin.migration');
        $this->app->alias(NavigationManager::class, 'plugin.navigation');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load plugin routes
        $this->loadPluginRoutes();

        // Discover and boot plugins
        $this->bootPlugins();

        // Register template system integration
        $this->registerTemplateSystemIntegration();
    }

    /**
     * Load plugin routes
     */
    protected function loadPluginRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/plugins.php'));
    }

    /**
     * Boot all enabled plugins
     */
    protected function bootPlugins(): void
    {
        if ($this->app->runningInConsole()) {
            // Don't boot plugins during console commands like migrations
            // unless it's a specific plugin command
            return;
        }

        try {
            /** @var PluginManager $pluginManager */
            $pluginManager = $this->app->make(PluginManager::class);
            $pluginManager->discoverPlugins();
        } catch (\Exception $e) {
            // Log error but don't break the application
            logger()->error('Failed to boot plugins', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Register template system integration
     */
    protected function registerTemplateSystemIntegration(): void
    {
        // This will be called after plugins are loaded
        $this->app->booted(function () {
            $this->integratePluginTemplates();
        });
    }

    /**
     * Integrate plugin templates with the core template system
     */
    protected function integratePluginTemplates(): void
    {
        try {
            /** @var PluginManager $pluginManager */
            $pluginManager = $this->app->make(PluginManager::class);
            
            // Get active plugins
            $activePlugins = $pluginManager->getActivePlugins();

            foreach ($activePlugins as $plugin) {
                $this->registerPluginTemplates($plugin);
                $this->registerPluginLayouts($plugin);
                $this->registerPluginBlocks($plugin);
                $this->registerPluginThemes($plugin);
            }
        } catch (\Exception $e) {
            logger()->error('Failed to integrate plugin templates', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Register plugin templates
     */
    protected function registerPluginTemplates($plugin): void
    {
        $templates = $plugin->getTemplates();
        
        foreach ($templates as $templateConfig) {
            // This would integrate with the TemplateRegistry
            // For now, just log that templates are available
            logger()->info("Plugin template available: {$plugin->getId()}:{$templateConfig['id']}");
        }
    }

    /**
     * Register plugin layouts
     */
    protected function registerPluginLayouts($plugin): void
    {
        $layouts = $plugin->getLayouts();
        
        foreach ($layouts as $layoutConfig) {
            // This would integrate with the LayoutRegistry
            // For now, just log that layouts are available
            logger()->info("Plugin layout available: {$plugin->getId()}:{$layoutConfig['id']}");
        }
    }

    /**
     * Register plugin blocks
     */
    protected function registerPluginBlocks($plugin): void
    {
        $blocks = $plugin->getBlocks();
        
        foreach ($blocks as $blockConfig) {
            // This would integrate with the BlockRegistry
            // For now, just log that blocks are available
            logger()->info("Plugin block available: {$plugin->getId()}:{$blockConfig['id']}");
        }
    }

    /**
     * Register plugin themes
     */
    protected function registerPluginThemes($plugin): void
    {
        $themes = $plugin->getThemes();
        
        foreach ($themes as $themeConfig) {
            // This would integrate with the ThemeRegistry (when created)
            // For now, just log that themes are available
            logger()->info("Plugin theme available: {$plugin->getId()}:{$themeConfig['id']}");
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PluginManager::class,
            PluginMigrationRunner::class,
            NavigationManager::class,
            'plugin.manager',
            'plugin.migration',
            'plugin.navigation',
        ];
    }
}
