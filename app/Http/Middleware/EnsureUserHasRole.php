<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserHasRole Middleware
 * 
 * This middleware is a key component of the Multi-Role User Authentication system that
 * provides route-level role-based access control. It ensures that users have the required
 * roles before allowing access to protected routes.
 * 
 * Key Features:
 * - Validates user authentication before role checking
 * - Supports multiple role requirements (OR logic)
 * - Provides clear error messages for unauthorized access
 * - Integrates with Laravel's middleware system
 * 
 * Usage:
 * ```php
 * // In routes/web.php
 * Route::middleware(['auth', 'role:Admin,Editor'])->group(function () {
 *     Route::get('/admin', [AdminController::class, 'index']);
 * });
 * 
 * // In RouteServiceProvider or bootstrap/app.php
 * Route::middleware('role')->group(function () {
 *     // Routes that require specific roles
 * });
 * ```
 * 
 * Role Logic:
 * - Multiple roles are treated as OR conditions (user needs ANY of the specified roles)
 * - If no user is authenticated, redirects to login
 * - If user lacks required roles, returns 403 Forbidden response
 * 
 * @see https://laravel.com/docs/middleware
 * @see https://spatie.be/docs/laravel-permission
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     * 
     * This method validates that the authenticated user has at least one of the
     * required roles. It's called automatically by Laravel's middleware system
     * for routes that use this middleware.
     *
     * @param Request $request The incoming HTTP request
     * @param Closure $next The next middleware in the stack
     * @param string ...$roles The required roles (can be multiple)
     * @return Response The response from the next middleware or an error response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            // Redirect unauthenticated users to login page
            return redirect()->route('login');
        }

        // Check if user has any of the required roles
        if (!$request->user()->hasAnyRole($roles)) {
            // Return 403 Forbidden for users without required roles
            abort(403, 'You do not have the required role to access this resource.');
        }

        // User has required role(s), continue to next middleware/controller
        return $next($request);
    }
}
