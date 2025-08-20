<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTwoFactorAuthentication Middleware
 * 
 * This middleware enforces role-based Two-Factor Authentication requirements
 * for the Multi-Role User Authentication system. It ensures that users in
 * specific roles have 2FA enabled and confirmed before accessing protected resources.
 * 
 * Key Features:
 * - Role-based 2FA enforcement
 * - Graceful handling of 2FA setup process
 * - Integration with existing authentication flow
 * - Configurable role requirements
 * 
 * Security Policies:
 * - Super Admin and Admin roles require mandatory 2FA
 * - Editor role encouraged to use 2FA (warning only)
 * - Author and Subscriber roles optional 2FA
 * 
 * Integration Points:
 * - Works with existing User model and roles
 * - Compatible with Fortify 2FA system
 * - Respects existing middleware stack
 * - Integrates with Inertia.js responses
 */
class EnsureTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TEMPORARY: 2FA enforcement disabled for development
        // TODO: Re-enable 2FA enforcement when ready for production
        return $next($request);
        
        /* DISABLED 2FA ENFORCEMENT CODE:
        $user = $request->user();

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip for 2FA setup routes to avoid infinite redirects
        if ($this->is2FASetupRoute($request)) {
            return $next($request);
        }

        // Check if user requires 2FA based on their role
        if ($this->userRequires2FA($user)) {
            // If 2FA is not enabled, redirect to setup
            if (!$user->two_factor_secret) {
                return $this->redirectTo2FASetup($request, 'Two-factor authentication is required for your role. Please set it up to continue.');
            }

            // If 2FA is enabled but not confirmed, redirect to confirmation
            if (!$user->two_factor_confirmed_at) {
                return $this->redirectTo2FASetup($request, 'Please complete your two-factor authentication setup.');
            }
        }

        // Check if user should be encouraged to use 2FA
        if ($this->userShouldUse2FA($user) && !$user->two_factor_secret) {
            // Add a flash message encouraging 2FA setup
            session()->flash('2fa_recommendation', 'We recommend enabling two-factor authentication for enhanced security.');
        }

        return $next($request);
        */
    }

    /**
     * Check if the current route is part of 2FA setup process.
     */
    private function is2FASetupRoute(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        $setupRoutes = [
            'two-factor.show',
            'two-factor.enable',
            'two-factor.disable',
            'two-factor.qr-code',
            'two-factor.recovery-codes',
            'two-factor.new-recovery-codes',
            'two-factor.confirm',
            'password.confirm',
            'logout'
        ];

        return in_array($routeName, $setupRoutes) || 
               str_starts_with($request->path(), 'user/two-factor-authentication');
    }

    /**
     * Check if user is required to have 2FA based on their role.
     */
    private function userRequires2FA($user): bool
    {
        // Super Admin and Admin roles require mandatory 2FA
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * Check if user should be encouraged to use 2FA.
     */
    private function userShouldUse2FA($user): bool
    {
        // Editor role should be encouraged to use 2FA
        return $user->hasRole('Editor');
    }

    /**
     * Redirect user to 2FA setup with appropriate message.
     */
    private function redirectTo2FASetup(Request $request, string $message): Response
    {
        // For API requests, return JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'requires_2fa_setup' => true,
                'redirect_url' => route('two-factor.show')
            ], 403);
        }

        // For web requests, redirect with flash message
        return redirect()->route('two-factor.show')
            ->with('error', $message);
    }

    /**
     * Get 2FA requirement level for a user.
     */
    public static function get2FARequirement($user): string
    {
        if (!$user) {
            return 'none';
        }

        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return 'required';
        }

        if ($user->hasRole('Editor')) {
            return 'recommended';
        }

        return 'optional';
    }

    /**
     * Check if user meets 2FA requirements for their role.
     */
    public static function userMeets2FARequirements($user): bool
    {
        if (!$user) {
            return true;
        }

        $requirement = self::get2FARequirement($user);

        switch ($requirement) {
            case 'required':
                return $user->two_factor_secret && $user->two_factor_confirmed_at;
            case 'recommended':
            case 'optional':
            default:
                return true; // No enforcement for these levels
        }
    }

    /**
     * Get 2FA status message for user.
     */
    public static function get2FAStatusMessage($user): ?string
    {
        if (!$user) {
            return null;
        }

        $requirement = self::get2FARequirement($user);

        if ($requirement === 'required' && !$user->two_factor_secret) {
            return 'Two-factor authentication is required for your role.';
        }

        if ($requirement === 'required' && $user->two_factor_secret && !$user->two_factor_confirmed_at) {
            return 'Please complete your two-factor authentication setup.';
        }

        if ($requirement === 'recommended' && !$user->two_factor_secret) {
            return 'We recommend enabling two-factor authentication for enhanced security.';
        }

        return null;
    }
}
