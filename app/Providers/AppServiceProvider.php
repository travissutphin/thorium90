<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

/**
 * AppServiceProvider
 * 
 * This service provider is responsible for bootstrapping the Multi-Role User Authentication system
 * by defining Laravel Gates that integrate with the Spatie Laravel Permission package. Gates provide
 * a clean, expressive way to define authorization logic throughout the application.
 * 
 * Key Features:
 * - Defines Gates for all permission categories (User Management, Content Management, etc.)
 * - Provides role-based Gates for common permission combinations
 * - Implements "own content" logic for Authors and other limited users
 * - Integrates seamlessly with Laravel's authorization system
 * 
 * Gate Categories:
 * - User Management: view-users, create-users, edit-users, delete-users, manage-user-roles
 * - Content Management: view-pages, create-pages, edit-pages, delete-pages, publish-pages
 * - Own Content: edit-own-pages, delete-own-pages (with ownership logic)
 * - System Management: manage-settings, manage-roles, manage-permissions
 * - Media Management: upload-media, manage-media, delete-media
 * - Comment Management: view-comments, moderate-comments, delete-comments
 * - Role-based: is-admin, is-content-manager, is-content-creator
 * 
 * Usage Examples:
 * ```php
 * // In controllers or blade templates
 * if (Gate::allows('edit-pages', $page)) {
 *     // User can edit this page
 * }
 * 
 * // In blade templates
 * @can('create-users')
 *     <button>Create User</button>
 * @endcan
 * 
 * // In policies
 * public function update(User $user, Page $page)
 * {
 *     return Gate::allows('edit-own-pages', $page);
 * }
 * ```
 * 
 * @see https://laravel.com/docs/authorization#gates
 * @see https://spatie.be/docs/laravel-permission
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Schema Validation Service
        $this->app->singleton(\App\Services\SchemaValidationService::class);
        
        // Register Media Picker Interface with Core Implementation
        $this->app->bind(
            \App\Contracts\MediaPickerInterface::class,
            \App\Services\CoreMediaService::class
        );
    }

    /**
     * Bootstrap any application services.
     * 
     * This method defines all the Laravel Gates that integrate with the Multi-Role User
     * Authentication system. Gates provide a clean way to check permissions throughout
     * the application using Laravel's built-in authorization system.
     */
    public function boot(): void
    {
        // User Management Gates
        // These gates control access to user management functionality
        
        Gate::define('view-users', function (User $user) {
            return $user->hasPermissionTo('view users');
        });

        Gate::define('create-users', function (User $user) {
            return $user->hasPermissionTo('create users');
        });

        Gate::define('edit-users', function (User $user) {
            return $user->hasPermissionTo('edit users');
        });

        Gate::define('delete-users', function (User $user) {
            return $user->hasPermissionTo('delete users');
        });

        Gate::define('manage-user-roles', function (User $user) {
            return $user->hasPermissionTo('manage user roles');
        });

        // Content Management Gates
        // These gates control access to content creation and management
        
        Gate::define('view-pages', function (User $user) {
            return $user->hasPermissionTo('view pages');
        });

        Gate::define('create-pages', function (User $user) {
            return $user->hasPermissionTo('create pages');
        });

        Gate::define('edit-pages', function (User $user) {
            return $user->hasPermissionTo('edit pages');
        });

        Gate::define('delete-pages', function (User $user) {
            return $user->hasPermissionTo('delete pages');
        });

        Gate::define('publish-pages', function (User $user) {
            return $user->hasPermissionTo('publish pages');
        });

        // Own Content Gates
        // These gates implement ownership logic for Authors and other limited users
        // They check if the user can edit/delete any page or only their own pages
        
        Gate::define('edit-own-pages', function (User $user, $page = null) {
            // If user has general edit permission, they can edit any page
            if ($user->hasPermissionTo('edit pages')) {
                return true; // Can edit any page
            }
            
            // If user has own edit permission, check if it's their page
            if ($user->hasPermissionTo('edit own pages')) {
                return $page ? $page->user_id === $user->id : true;
            }
            
            return false;
        });

        Gate::define('delete-own-pages', function (User $user, $page = null) {
            // If user has general delete permission, they can delete any page
            if ($user->hasPermissionTo('delete pages')) {
                return true; // Can delete any page
            }
            
            // If user has own delete permission, check if it's their page
            if ($user->hasPermissionTo('delete own pages')) {
                return $page ? $page->user_id === $user->id : true;
            }
            
            return false;
        });

        // System Management Gates
        // These gates control access to system administration features
        
        Gate::define('manage-settings', function (User $user) {
            return $user->hasPermissionTo('manage settings');
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasPermissionTo('manage roles');
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->hasPermissionTo('manage permissions');
        });

        // Media Management Gates
        // These gates control access to media upload and management
        
        Gate::define('upload-media', function (User $user) {
            return $user->hasPermissionTo('upload media');
        });

        Gate::define('manage-media', function (User $user) {
            return $user->hasPermissionTo('manage media');
        });

        Gate::define('delete-media', function (User $user) {
            return $user->hasPermissionTo('delete media');
        });

        // Comment Management Gates
        // These gates control access to comment viewing and moderation
        
        Gate::define('view-comments', function (User $user) {
            return $user->hasPermissionTo('view comments');
        });

        Gate::define('moderate-comments', function (User $user) {
            return $user->hasPermissionTo('moderate comments');
        });

        Gate::define('delete-comments', function (User $user) {
            return $user->hasPermissionTo('delete comments');
        });

        // Role-based Gates for common combinations
        // These gates provide convenient ways to check role-based access patterns
        
        Gate::define('is-admin', function (User $user) {
            return $user->hasAnyRole(['Super Admin', 'Admin']);
        });

        Gate::define('is-content-manager', function (User $user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Editor']);
        });

        Gate::define('is-content-creator', function (User $user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']);
        });

        // Configure Rate Limiters
        // These rate limiters provide security by limiting authentication attempts
        
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Configure Laravel Fortify
        // Fortify provides headless authentication services (2FA, email verification, password reset)
        // while maintaining compatibility with our existing role-based authentication system
        
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(\App\Actions\Fortify\UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(\App\Actions\Fortify\UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(\App\Actions\Fortify\ResetUserPassword::class);
    }
}
