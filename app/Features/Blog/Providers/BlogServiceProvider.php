<?php

namespace App\Features\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Merge blog configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../../../config/blog.php', 'blog'
        );

        // Register blog services
        $this->app->singleton('blog.service', function ($app) {
            return new \App\Features\Blog\Services\BlogService();
        });

        $this->app->singleton('blog.seo', function ($app) {
            return new \App\Features\Blog\Services\BlogSeoService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Only boot blog features if enabled
        if (!config('blog.enabled', true)) {
            return;
        }

        $this->loadMigrations();
        $this->loadRoutes();
        $this->loadViews();
        $this->registerMiddleware();
        $this->registerPermissions();
        $this->publishAssets();
    }

    /**
     * Load blog migrations.
     */
    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../../../database/migrations/blog');
    }

    /**
     * Load blog routes.
     */
    protected function loadRoutes()
    {
        // Load public blog routes
        Route::middleware('web')
            ->namespace('App\Features\Blog\Controllers')
            ->group(__DIR__.'/../routes/web.php');

        // Load admin blog routes
        Route::middleware(['web', 'auth', 'verified', 'role.any:Super Admin,Admin'])
            ->prefix('admin')
            ->name('admin.')
            ->namespace('App\Features\Blog\Controllers\Admin')
            ->group(__DIR__.'/../routes/admin.php');

        // Load API routes if enabled
        if (config('blog.features.api', false)) {
            Route::middleware('api')
                ->prefix('api/blog')
                ->name('api.blog.')
                ->namespace('App\Features\Blog\Controllers\Api')
                ->group(__DIR__.'/../routes/api.php');
        }
    }

    /**
     * Load blog views.
     */
    protected function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');
        
        // Publish views for customization
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/blog'),
        ], 'blog-views');
    }

    /**
     * Register blog middleware.
     */
    protected function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('blog.enabled', \App\Http\Middleware\EnsureBlogEnabled::class);
    }

    /**
     * Register blog permissions.
     */
    protected function registerPermissions()
    {
        // Blog permissions are handled by the BlogPermissionSeeder
        // This ensures consistency and proper permission management
        // No need to create permissions here as they should be seeded
    }

    /**
     * Assign blog permissions to roles based on configuration.
     * Permissions are now handled by BlogPermissionSeeder for consistency.
     */
    protected function assignPermissionsToRoles()
    {
        // Permissions assignment is now handled by BlogPermissionSeeder
        // This ensures consistent permission management across the application
    }

    /**
     * Publish blog assets.
     */
    protected function publishAssets()
    {
        // Publish CSS
        $this->publishes([
            __DIR__.'/../resources/css' => public_path('css/features/blog'),
        ], 'blog-css');

        // Publish JS
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/features/blog'),
        ], 'blog-js');

        // Publish config
        $this->publishes([
            __DIR__.'/../../../../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides()
    {
        return [
            'blog.service',
            'blog.seo',
        ];
    }
}