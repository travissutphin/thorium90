<?php

use App\Http\Middleware\EnsureUserHasAnyPermission;
use App\Http\Middleware\EnsureUserHasAnyRole;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

/**
 * Application Bootstrap Configuration
 * 
 * This file configures the Laravel application and registers the Multi-Role User
 * Authentication system middleware. It's the entry point for the application
 * and sets up all necessary middleware aliases for role and permission checking.
 * 
 * Key Components:
 * - Application routing configuration
 * - Middleware registration and aliases
 * - Role and permission middleware setup
 * - Inertia.js integration for frontend data sharing
 * 
 * Middleware Aliases:
 * - 'role': EnsureUserHasRole - Check for specific role(s)
 * - 'permission': EnsureUserHasPermission - Check for specific permission(s)
 * - 'role.any': EnsureUserHasAnyRole - Check for any of multiple roles
 * - 'permission.any': EnsureUserHasAnyPermission - Check for any of multiple permissions
 * 
 * Usage Examples:
 * ```php
 * // In routes/web.php
 * Route::middleware(['auth', 'role:Admin'])->group(function () {
 *     Route::get('/admin', [AdminController::class, 'index']);
 * });
 * 
 * Route::middleware(['auth', 'permission:create-posts'])->group(function () {
 *     Route::post('/posts', [PostController::class, 'store']);
 * });
 * 
 * Route::middleware(['auth', 'role.any:Admin,Editor'])->group(function () {
 *     Route::get('/content', [ContentController::class, 'index']);
 * });
 * ```
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Register admin routes with web middleware
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configure cookie encryption (exclude appearance and sidebar state)
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Register web middleware stack with Inertia.js integration
        $middleware->web(append: [
            HandleAppearance::class,        // Handle theme/appearance preferences
            HandleInertiaRequests::class,   // Share auth data with React frontend
            AddLinkHeadersForPreloadedAssets::class, // Optimize asset loading
        ]);

        // Register API middleware stack with Sanctum authentication
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Register role and permission middleware aliases for the Multi-Role User Authentication system
        // These aliases provide convenient ways to protect routes based on user roles and permissions
        $middleware->alias([
            'role' => EnsureUserHasRole::class,           // Check for specific role(s)
            'permission' => EnsureUserHasPermission::class, // Check for specific permission(s)
            'role.any' => EnsureUserHasAnyRole::class,    // Check for any of multiple roles
            'permission.any' => EnsureUserHasAnyPermission::class, // Check for any of multiple permissions
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class, // Password confirmation for sensitive operations
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration can be added here
    })->create();
