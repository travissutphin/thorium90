<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

/**
 * TwoFactorChallengeController
 * 
 * This controller handles Two-Factor Authentication challenges during the login process
 * for the Multi-Role User Authentication system. It provides endpoints for verifying
 * 2FA codes and recovery codes while maintaining compatibility with the existing
 * authentication flow.
 * 
 * Key Features:
 * - Handle 2FA code verification during login
 * - Support recovery code authentication
 * - Maintain session state during 2FA challenge
 * - Integration with existing login flow
 * - Role-based redirection after successful 2FA
 * 
 * Security Considerations:
 * - Rate limiting on 2FA attempts
 * - Recovery codes are single-use and replaced after use
 * - Session validation to prevent bypass attempts
 * - Secure handling of authentication state
 * 
 * Integration Points:
 * - Works with existing authentication middleware
 * - Respects role-based redirections
 * - Compatible with API and web authentication
 * - Maintains existing session management
 */
class TwoFactorChallengeController extends Controller
{
    /**
     * The two factor authentication provider.
     */
    protected TwoFactorAuthenticationProvider $provider;

    /**
     * Create a new controller instance.
     */
    public function __construct(TwoFactorAuthenticationProvider $provider)
    {
        $this->provider = $provider;
        $this->middleware('guest');
        $this->middleware('throttle:two-factor');
    }

    /**
     * Show the two factor authentication challenge form.
     */
    public function create(Request $request): JsonResponse
    {
        if (!$request->session()->has('login.id')) {
            return response()->json([
                'error' => 'Two factor authentication challenge not available.',
            ], 400);
        }

        return response()->json([
            'two_factor_challenge' => true,
            'recovery_codes_available' => true,
        ]);
    }

    /**
     * Attempt to authenticate using a two factor authentication code.
     */
    public function store(TwoFactorLoginRequest $request): JsonResponse
    {
        $user = $request->challengedUser();

        if ($code = $request->validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new RecoveryCodeReplaced($user, $code));
        } elseif (!$request->hasValidCode()) {
            return response()->json([
                'error' => 'The provided two factor authentication code was invalid.',
            ], 422);
        }

        Auth::login($user, $request->remember());

        $request->session()->regenerate();

        // Clear the 2FA challenge session data
        $request->session()->forget('login.id');
        $request->session()->forget('login.remember');

        return response()->json([
            'message' => 'Two factor authentication successful.',
            'redirect_url' => $this->getRedirectUrl($user),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_names' => $user->roles->pluck('name')->toArray(),
                'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
            ],
        ]);
    }

    /**
     * Get the appropriate redirect URL based on user role.
     */
    protected function getRedirectUrl($user): string
    {
        // Admin users go to admin dashboard
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return '/admin/dashboard';
        }

        // Content creators go to content dashboard
        if ($user->hasAnyRole(['Editor', 'Author'])) {
            return '/dashboard';
        }

        // Default redirect for subscribers and other users
        return '/dashboard';
    }
}
