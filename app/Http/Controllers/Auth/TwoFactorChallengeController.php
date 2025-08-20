<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;
use Laravel\Fortify\RecoveryCode;

/**
 * TwoFactorChallengeController
 * 
 * This controller handles the Two-Factor Authentication challenge process for users
 * who have 2FA enabled. It provides endpoints for displaying the 2FA challenge
 * and processing 2FA codes or recovery codes during authentication.
 * 
 * Key Features:
 * - Displays 2FA challenge form/data for frontend
 * - Processes 2FA authentication codes
 * - Handles recovery code authentication
 * - Integrates with existing role-based authentication system
 * - Follows Laravel Fortify patterns and best practices
 * 
 * Security Considerations:
 * - Validates session state before allowing 2FA challenge
 * - Rate limits 2FA attempts
 * - Properly handles recovery code consumption
 * - Logs authentication events for security monitoring
 * 
 * Integration Points:
 * - Works with existing User model and roles
 * - Respects existing middleware and permissions
 * - Compatible with Inertia.js frontend
 * - Follows established authentication flow
 * 
 * @see https://laravel.com/docs/fortify#two-factor-authentication
 * @see https://laravel.com/docs/authentication
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
     * Show the two factor authentication challenge screen.
     * 
     * This method displays the 2FA challenge form for users who have 2FA enabled.
     * It validates that the user has a valid login session before showing the challenge.
     *
     * @param Request $request
     * @return JsonResponse|Response|RedirectResponse
     */
    public function create(Request $request): JsonResponse|Response|RedirectResponse
    {
        // Ensure the user has a valid login session
        if (!$request->session()->has('login.id')) {
            if ($request->header('X-Inertia')) {
                return redirect()->route('login')->with('error', 'Authentication session not found.');
            }
            return response()->json([
                'error' => 'Authentication session not found.',
                'redirect_url' => route('login'),
            ], 401);
        }

        // Get the user from the session
        $user = \App\Models\User::find($request->session()->get('login.id'));
        
        if (!$user || !$user->two_factor_secret) {
            if ($request->header('X-Inertia')) {
                return redirect()->route('login')->with('error', 'Two factor authentication is not enabled.');
            }
            return response()->json([
                'error' => 'Two factor authentication is not enabled.',
                'redirect_url' => route('login'),
            ], 400);
        }

        // Fire the challenged event
        event(new TwoFactorAuthenticationChallenged($user));

        $data = [
            'two_factor_challenge' => true,
            'recovery_codes_available' => !is_null($user->two_factor_recovery_codes),
            'message' => 'Please enter your two-factor authentication code or recovery code.',
        ];

        // Return Inertia response for web requests
        if ($request->header('X-Inertia')) {
            return Inertia::render('auth/two-factor-challenge', $data);
        }

        return response()->json($data);
    }

    /**
     * Attempt to authenticate a new session using the two factor authentication code.
     * 
     * This method processes the 2FA challenge by validating either a TOTP code
     * or a recovery code. Upon successful validation, it logs the user in.
     *
     * @param TwoFactorLoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(TwoFactorLoginRequest $request): JsonResponse
    {
        // Get the user from the session
        $user = $request->challengedUser();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'code' => ['Authentication session expired. Please log in again.'],
            ]);
        }

        // Determine if we're using a code or recovery code
        if ($request->filled('code')) {
            return $this->authenticateUsingCode($request, $user);
        } elseif ($request->filled('recovery_code')) {
            return $this->authenticateUsingRecoveryCode($request, $user);
        }

        throw ValidationException::withMessages([
            'code' => ['Please provide a two-factor authentication code or recovery code.'],
        ]);
    }

    /**
     * Authenticate using a two-factor authentication code.
     *
     * @param TwoFactorLoginRequest $request
     * @param \App\Models\User $user
     * @return JsonResponse
     * @throws ValidationException
     */
    protected function authenticateUsingCode(TwoFactorLoginRequest $request, $user): JsonResponse
    {
        $code = $request->input('code');
        
        if (!$this->provider->verify(decrypt($user->two_factor_secret), $code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided two factor authentication code was invalid.'],
            ]);
        }

        return $this->loginUser($request, $user);
    }

    /**
     * Authenticate using a recovery code.
     *
     * @param TwoFactorLoginRequest $request
     * @param \App\Models\User $user
     * @return JsonResponse
     * @throws ValidationException
     */
    protected function authenticateUsingRecoveryCode(TwoFactorLoginRequest $request, $user): JsonResponse
    {
        $recoveryCode = $request->input('recovery_code');
        
        if (!$user->two_factor_recovery_codes) {
            throw ValidationException::withMessages([
                'recovery_code' => ['Recovery codes are not available for this account.'],
            ]);
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        if (!in_array($recoveryCode, $recoveryCodes)) {
            throw ValidationException::withMessages([
                'recovery_code' => ['The provided recovery code was invalid.'],
            ]);
        }

        // Remove the used recovery code
        $user->replaceRecoveryCode($recoveryCode);

        return $this->loginUser($request, $user);
    }

    /**
     * Log the user in after successful 2FA verification.
     *
     * @param TwoFactorLoginRequest $request
     * @param \App\Models\User $user
     * @return JsonResponse
     */
    protected function loginUser(TwoFactorLoginRequest $request, $user): JsonResponse
    {
        // Fire the valid code provided event
        event(new ValidTwoFactorAuthenticationCodeProvided($user));

        // Log the user in
        Auth::login($user, $request->session()->get('login.remember', false));

        // Regenerate the session
        $request->session()->regenerate();

        // Clear the login session data
        $request->session()->forget(['login.id', 'login.remember']);

        // Load user relationships for frontend
        $user->load(['roles.permissions', 'permissions']);

        // Determine redirect URL based on user role
        $redirectUrl = $this->getRedirectUrl($user);

        return response()->json([
            'message' => 'Two-factor authentication successful.',
            'redirect_url' => $redirectUrl,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_names' => $user->roles->pluck('name')->toArray(),
                'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
                'two_factor_enabled' => !is_null($user->two_factor_secret),
                'two_factor_confirmed' => !is_null($user->two_factor_confirmed_at),
            ],
        ]);
    }

    /**
     * Get the appropriate redirect URL based on user role.
     *
     * @param \App\Models\User $user
     * @return string
     */
    protected function getRedirectUrl($user): string
    {
        // Check for intended URL first
        if (session()->has('url.intended')) {
            return session()->pull('url.intended');
        }

        // Role-based redirects
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return route('admin.dashboard', absolute: false);
        }

        // Default redirect
        return route('dashboard', absolute: false);
    }
}
