<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

/**
 * HandleInertiaRequests Middleware
 * 
 * This middleware is responsible for sharing data between the Laravel backend and React frontend
 * via Inertia.js. It's a critical component of the Multi-Role User Authentication system.
 * 
 * Key Responsibilities:
 * - Loads user roles and permissions from the Spatie Laravel Permission package
 * - Exposes permission checking functions to the frontend
 * - Provides computed properties for easier frontend access
 * - Shares authentication data with all Inertia responses
 * 
 * @see https://inertiajs.com/shared-data
 * @see https://spatie.be/docs/laravel-permission
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default with the frontend.
     * 
     * This method is the core of the Multi-Role User Authentication system's frontend integration.
     * It loads user roles and permissions and exposes them along with helper functions
     * and computed properties to the React frontend.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @param Request $request The incoming HTTP request
     * @return array<string, mixed> Data to be shared with the frontend
     */
    public function share(Request $request): array
    {
        // Get a random inspirational quote for the dashboard
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Get the authenticated user (if any)
        $user = $request->user();
        $authUser = null;

        if ($user) {
            // Load user roles and permissions with their relationships
            // This is crucial for the permission system to work correctly
            $user->load(['roles.permissions', 'permissions']);
            
            // Build the enhanced user object with permission functionality
            $authUser = [
                // Include all standard user attributes
                ...$user->toArray(),
                
                // Array of role names for easy frontend access
                'role_names' => $user->roles->pluck('name')->toArray(),
                
                // Array of all permission names (including inherited from roles)
                'permission_names' => $user->getAllPermissions()->pluck('name')->toArray(),
                
                // Computed boolean properties for common permission checks
                'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
                'is_content_manager' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor']),
                'is_content_creator' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']),
            ];
        }

        // Return all shared data for Inertia.js
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            
            // Authentication data - this is where the permission system data is exposed
            'auth' => [
                'user' => $authUser,
            ],
            
            // Ziggy route generation for frontend
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            
            // Sidebar state management
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
