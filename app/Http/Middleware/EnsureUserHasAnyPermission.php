<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserHasAnyPermission Middleware
 * 
 * This middleware is a component of the Multi-Role User Authentication system that
 * provides route-level permission-based access control with OR logic. It ensures that
 * users have at least one of the required permissions before allowing access to protected routes.
 * 
 * Key Features:
 * - Validates user authentication before permission checking
 * - Supports multiple permission requirements (OR logic - user needs ANY permission)
 * - Provides clear error messages for unauthorized access
 * - Integrates with Laravel's middleware system
 * 
 * Usage:
 * ```php
 * // In routes/web.php
 * Route::middleware(['auth', 'permission.any:create-pages,edit-pages'])->group(function () {
 *     Route::get('/pages', [PageController::class, 'index']);
 * });
 * 
 * // In RouteServiceProvider or bootstrap/app.php
 * Route::middleware('permission.any')->group(function () {
 *     // Routes that require any of multiple permissions
 * });
 * ```
 * 
 * Permission Logic:
 * - Multiple permissions are treated as OR conditions (user needs ANY of the specified permissions)
 * - If no user is authenticated, redirects to login
 * - If user lacks all required permissions, returns 403 Forbidden response
 * - Uses hasAnyPermission() method from Spatie Laravel Permission package
 * 
 * Difference from EnsureUserHasPermission:
 * - This middleware uses OR logic (any permission suffices)
 * - EnsureUserHasPermission uses AND logic (all permissions required)
 * 
 * @see https://laravel.com/docs/middleware
 * @see https://spatie.be/docs/laravel-permission
 */
class EnsureUserHasAnyPermission
{
    /**
     * Handle an incoming request.
     * 
     * This method validates that the authenticated user has at least one of the
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

        // Check if user has any of the required permissions
        if (!$request->user()->hasAnyPermission($permissions)) {
            // Return 403 Forbidden for users without any of the required permissions
            abort(403, 'You do not have any of the required permissions to access this resource.');
        }

        // User has at least one required permission, continue to next middleware/controller
        return $next($request);
    }
}
