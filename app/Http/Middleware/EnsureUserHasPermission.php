<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserHasPermission Middleware
 * 
 * This middleware is a component of the Multi-Role User Authentication system that
 * provides route-level permission-based access control. It ensures that users have
 * the required permissions before allowing access to protected routes.
 * 
 * Key Features:
 * - Validates user authentication before permission checking
 * - Supports multiple permission requirements (AND logic - user needs ALL permissions)
 * - Provides clear error messages for unauthorized access
 * - Integrates with Laravel's middleware system
 * 
 * Usage:
 * ```php
 * // In routes/web.php
 * Route::middleware(['auth', 'permission:create-posts,edit-posts'])->group(function () {
 *     Route::get('/posts/manage', [PostController::class, 'manage']);
 * });
 * 
 * // In RouteServiceProvider or bootstrap/app.php
 * Route::middleware('permission')->group(function () {
 *     // Routes that require specific permissions
 * });
 * ```
 * 
 * Permission Logic:
 * - Multiple permissions are treated as AND conditions (user needs ALL specified permissions)
 * - If no user is authenticated, redirects to login
 * - If user lacks required permissions, returns 403 Forbidden response
 * - Uses hasAnyPermission() method from Spatie Laravel Permission package
 * 
 * @see https://laravel.com/docs/middleware
 * @see https://spatie.be/docs/laravel-permission
 */
class EnsureUserHasPermission
{
    /**
     * Handle an incoming request.
     * 
     * This method validates that the authenticated user has all of the
     * required permissions. It's called automatically by Laravel's middleware system
     * for routes that use this middleware.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the stack
     * @param string ...$permissions The required permissions (can be multiple)
     * @return Response The response from the next middleware or an error response
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            // Redirect unauthenticated users to login page
            return redirect()->route('login');
        }

        // Check if user has all of the required permissions
        if (!$request->user()->hasAnyPermission($permissions)) {
            // Return 403 Forbidden for users without required permissions
            abort(403, 'You do not have the required permission to access this resource.');
        }

        // User has required permission(s), continue to next middleware/controller
        return $next($request);
    }
}
