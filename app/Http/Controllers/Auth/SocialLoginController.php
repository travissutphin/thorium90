<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

/**
 * Social Login Controller
 * 
 * Handles OAuth authentication for multiple providers including Google, GitHub,
 * Facebook, LinkedIn, X (Twitter), and GitLab. Integrates with the existing
 * authentication system and role management.
 * 
 * Supported Providers:
 * - Google OAuth 2.0
 * - GitHub OAuth
 * - Facebook Login
 * - LinkedIn OAuth 2.0
 * - X (Twitter) OAuth 2.0
 * - GitLab OAuth 2.0
 * 
 * Features:
 * - Automatic user creation for new social logins
 * - Integration with existing users via email matching
 * - Default role assignment for new social users
 * - Comprehensive error handling
 * - Security validation and CSRF protection
 * 
 * @see https://laravel.com/docs/socialite
 * @see https://spatie.be/docs/laravel-permission
 */
class SocialLoginController extends Controller
{
    /**
     * List of supported OAuth providers.
     */
    private const SUPPORTED_PROVIDERS = [
        'google',
        'github',
        'facebook',
        'linkedin',
        'twitter',
        'gitlab',
    ];

    /**
     * Redirect the user to the OAuth provider's authentication page.
     *
     * @param Request $request
     * @param string $provider The OAuth provider name
     * @return RedirectResponse
     */
    public function redirectToProvider(Request $request, string $provider): RedirectResponse
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')
                    ->withErrors(['provider' => "The {$provider} provider is not supported."]);
            }

            // Check if provider is configured
            if (!$this->isProviderConfigured($provider)) {
                return redirect()->route('login')
                    ->withErrors(['provider' => "The {$provider} provider is not configured."]);
            }

            // Store the intended URL in session for post-login redirect
            if ($request->has('redirect')) {
                session(['url.intended' => $request->get('redirect')]);
            }

            return Socialite::driver($provider)->redirect();

        } catch (Throwable $e) {
            report($e);
            
            return redirect()->route('login')
                ->withErrors(['provider' => 'Unable to connect to the authentication provider. Please try again.']);
        }
    }

    /**
     * Handle the OAuth provider callback.
     *
     * @param Request $request
     * @param string $provider The OAuth provider name
     * @return RedirectResponse
     */
    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')
                    ->withErrors(['provider' => "The {$provider} provider is not supported."]);
            }

            // Get user data from OAuth provider
            $socialUser = Socialite::driver($provider)->user();

            // Validate required user data
            if (!$socialUser->getEmail()) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Unable to retrieve email from the authentication provider.']);
            }

            // Find or create user
            $user = $this->findOrCreateUser($provider, $socialUser);

            // Log the user in
            Auth::login($user, true);

            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect to intended URL or dashboard
            return redirect()->intended(route('dashboard'))
                ->with('success', "Successfully logged in with {$this->getProviderDisplayName($provider)}!");

        } catch (InvalidStateException $e) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Invalid authentication state. Please try logging in again.']);

        } catch (Throwable $e) {
            report($e);
            
            return redirect()->route('login')
                ->withErrors(['provider' => 'Authentication failed. Please try again or use a different login method.']);
        }
    }

    /**
     * Find an existing user or create a new one from social provider data.
     *
     * @param string $provider The OAuth provider name
     * @param \Laravel\Socialite\Contracts\User $socialUser
     * @return User
     */
    private function findOrCreateUser(string $provider, $socialUser): User
    {
        // First, try to find user by provider and provider_id
        $user = User::findForSocialLogin($provider, $socialUser->getId());

        if ($user) {
            // Update avatar if it's changed
            if ($socialUser->getAvatar() && $user->avatar !== $socialUser->getAvatar()) {
                $user->update(['avatar' => $socialUser->getAvatar()]);
            }
            return $user;
        }

        // Try to find existing user by email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Link the social account to existing user
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?: $existingUser->avatar,
            ]);

            return $existingUser;
        }

        // Create new user from social provider data
        $userData = [
            'id' => $socialUser->getId(),
            'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Unknown User',
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ];

        $user = User::createFromSocialProvider($provider, $userData);

        // Assign default role to new social users
        $this->assignDefaultRole($user);

        return $user;
    }

    /**
     * Assign default role to newly created social users.
     *
     * @param User $user
     * @return void
     */
    private function assignDefaultRole(User $user): void
    {
        try {
            // Check if a default role exists and assign it
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $defaultRole = \Spatie\Permission\Models\Role::where('name', 'user')->first();
                
                if ($defaultRole) {
                    $user->assignRole($defaultRole);
                }
            }
        } catch (Throwable $e) {
            // Log the error but don't fail the login process
            report($e);
        }
    }

    /**
     * Check if the given provider is supported.
     *
     * @param string $provider
     * @return bool
     */
    private function isProviderSupported(string $provider): bool
    {
        return in_array(strtolower($provider), self::SUPPORTED_PROVIDERS);
    }

    /**
     * Check if the given provider is properly configured.
     *
     * @param string $provider
     * @return bool
     */
    private function isProviderConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");
        
        return $config && 
               !empty($config['client_id']) && 
               !empty($config['client_secret']) && 
               !empty($config['redirect']);
    }

    /**
     * Get the display name for a provider.
     *
     * @param string $provider
     * @return string
     */
    private function getProviderDisplayName(string $provider): string
    {
        return match (strtolower($provider)) {
            'google' => 'Google',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            'twitter' => 'X (Twitter)',
            'gitlab' => 'GitLab',
            default => ucfirst($provider),
        };
    }
}
