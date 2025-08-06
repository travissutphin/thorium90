<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

/**
 * TwoFactorAuthenticationController
 * 
 * This controller handles Two-Factor Authentication (2FA) management for the Multi-Role
 * User Authentication system. It provides endpoints for enabling, disabling, and managing
 * 2FA while respecting the existing role-based access control system.
 * 
 * Key Features:
 * - Enable/disable 2FA for authenticated users
 * - Generate and display QR codes for authenticator apps
 * - Manage recovery codes
 * - Role-based 2FA requirements (can be enforced for specific roles)
 * - Integration with existing authentication system
 * 
 * Security Considerations:
 * - All endpoints require authentication
 * - Password confirmation required for sensitive operations
 * - Recovery codes are encrypted in database
 * - QR codes are generated server-side for security
 * 
 * Integration Points:
 * - Works with existing User model and roles
 * - Respects existing middleware and permissions
 * - Compatible with API and web authentication
 */
class TwoFactorAuthenticationController extends Controller
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
        $this->middleware('auth');
        $this->middleware('password.confirm')->only(['store', 'destroy']);
    }

    /**
     * Get the current 2FA status for the authenticated user.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'two_factor_enabled' => !is_null($user->two_factor_secret),
            'two_factor_confirmed' => !is_null($user->two_factor_confirmed_at),
            'recovery_codes_count' => $user->recoveryCodes()->count(),
        ]);
    }

    /**
     * Enable two factor authentication for the user.
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable): JsonResponse
    {
        $enable($request->user());

        return response()->json([
            'message' => 'Two factor authentication has been enabled.',
            'two_factor_enabled' => true,
            'two_factor_confirmed' => false,
        ]);
    }

    /**
     * Get the QR code SVG for the user's two factor authentication setup.
     */
    public function qrCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if (is_null($user->two_factor_secret)) {
            return response()->json([
                'error' => 'Two factor authentication is not enabled.',
            ], 400);
        }

        return response()->json([
            'svg' => $user->twoFactorQrCodeSvg(),
            'setup_key' => decrypt($user->two_factor_secret),
        ]);
    }

    /**
     * Get the recovery codes for two factor authentication.
     */
    public function recoveryCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        if (is_null($user->two_factor_secret)) {
            return response()->json([
                'error' => 'Two factor authentication is not enabled.',
            ], 400);
        }

        return response()->json([
            'recovery_codes' => $user->recoveryCodes(),
        ]);
    }

    /**
     * Generate new recovery codes for the user.
     */
    public function newRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate): JsonResponse
    {
        $generate($request->user());

        return response()->json([
            'message' => 'New recovery codes have been generated.',
            'recovery_codes' => $request->user()->recoveryCodes(),
        ]);
    }

    /**
     * Confirm two factor authentication for the user.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (is_null($user->two_factor_secret)) {
            return response()->json([
                'error' => 'Two factor authentication is not enabled.',
            ], 400);
        }

        if (!is_null($user->two_factor_confirmed_at)) {
            return response()->json([
                'error' => 'Two factor authentication is already confirmed.',
            ], 400);
        }

        if (!$this->provider->verify(decrypt($user->two_factor_secret), $request->code)) {
            return response()->json([
                'error' => 'The provided two factor authentication code was invalid.',
            ], 422);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Two factor authentication has been confirmed.',
            'two_factor_confirmed' => true,
        ]);
    }

    /**
     * Disable two factor authentication for the user.
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): JsonResponse
    {
        $disable($request->user());

        return response()->json([
            'message' => 'Two factor authentication has been disabled.',
            'two_factor_enabled' => false,
            'two_factor_confirmed' => false,
        ]);
    }
}
