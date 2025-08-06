<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Models\User;

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
 * - Content Management: view-posts, create-posts, edit-posts, delete-posts, publish-posts
 * - Own Content: edit-own-posts, delete-own-posts (with ownership logic)
 * - System Management: manage-settings, manage-roles, manage-permissions
 * - Media Management: upload-media, manage-media, delete-media
 * - Comment Management: view-comments, moderate-comments, delete-comments
 * - Role-based: is-admin, is-content-manager, is-content-creator
 * 
 * Usage Examples:
 * ```php
 * // In controllers or blade templates
 * if (Gate::allows('edit-posts', $post)) {
 *     // User can edit this post
 * }
 * 
 * // In blade templates
 * @can('create-users')
 *     <button>Create User</button>
 * @endcan
 * 
 * // In policies
 * public function update(User $user, Post $post)
 * {
 *     return Gate::allows('edit-own-posts', $post);
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
        //
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
        
        Gate::define('view-posts', function (User $user) {
            return $user->hasPermissionTo('view posts');
        });

        Gate::define('create-posts', function (User $user) {
            return $user->hasPermissionTo('create posts');
        });

        Gate::define('edit-posts', function (User $user) {
            return $user->hasPermissionTo('edit posts');
        });

        Gate::define('delete-posts', function (User $user) {
            return $user->hasPermissionTo('delete posts');
        });

        Gate::define('publish-posts', function (User $user) {
            return $user->hasPermissionTo('publish posts');
        });

        // Own Content Gates
        // These gates implement ownership logic for Authors and other limited users
        // They check if the user can edit/delete any post or only their own posts
        
        Gate::define('edit-own-posts', function (User $user, $post = null) {
            // If user has general edit permission, they can edit any post
            if ($user->hasPermissionTo('edit posts')) {
                return true; // Can edit any post
            }
            
            // If user has own edit permission, check if it's their post
            if ($user->hasPermissionTo('edit own posts')) {
                return $post ? $post->user_id === $user->id : true;
            }
            
            return false;
        });

        Gate::define('delete-own-posts', function (User $user, $post = null) {
            // If user has general delete permission, they can delete any post
            if ($user->hasPermissionTo('delete posts')) {
                return true; // Can delete any post
            }
            
            // If user has own delete permission, check if it's their post
            if ($user->hasPermissionTo('delete own posts')) {
                return $post ? $post->user_id === $user->id : true;
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

        // Configure Laravel Fortify
        // Fortify provides headless authentication services (2FA, email verification, password reset)
        // while maintaining compatibility with our existing role-based authentication system
        
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(\App\Actions\Fortify\UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(\App\Actions\Fortify\UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(\App\Actions\Fortify\ResetUserPassword::class);
    }
}
