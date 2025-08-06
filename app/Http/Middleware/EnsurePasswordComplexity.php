<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsurePasswordComplexity Middleware
 * 
 * This middleware enforces enhanced password policies for the Multi-Role User
 * Authentication system. It integrates with Laravel Fortify to provide
 * comprehensive password security requirements.
 * 
 * Key Features:
 * - Role-based password complexity requirements
 * - Password history checking (prevents reuse)
 * - Configurable password policies
 * - Integration with existing authentication system
 * 
 * Security Policies:
 * - Minimum length requirements (role-based)
 * - Character complexity requirements
 * - Password history prevention
 * - Common password detection
 * 
 * Integration Points:
 * - Works with existing User model and roles
 * - Compatible with Fortify password actions
 * - Respects existing middleware stack
 */
class EnsurePasswordComplexity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to password-related requests
        if (!$this->isPasswordRequest($request)) {
            return $next($request);
        }

        $password = $request->input('password');
        $user = $request->user();

        if ($password && !$this->validatePasswordComplexity($password, $user)) {
            return response()->json([
                'message' => 'The password does not meet complexity requirements.',
                'errors' => [
                    'password' => $this->getPasswordErrors($password, $user)
                ]
            ], 422);
        }

        return $next($request);
    }

    /**
     * Check if this is a password-related request.
     */
    private function isPasswordRequest(Request $request): bool
    {
        return $request->has('password') && 
               in_array($request->route()?->getName(), [
                   'password.store',
                   'register',
                   'two-factor.enable',
                   'user.password.update'
               ]);
    }

    /**
     * Validate password complexity based on user role.
     */
    private function validatePasswordComplexity(string $password, $user = null): bool
    {
        $minLength = $this->getMinimumLength($user);
        
        // Basic length check
        if (strlen($password) < $minLength) {
            return false;
        }

        // Character complexity requirements
        if (!$this->hasRequiredCharacters($password, $user)) {
            return false;
        }

        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            return false;
        }

        // Check password history (if user exists)
        if ($user && $this->isPasswordReused($password, $user)) {
            return false;
        }

        return true;
    }

    /**
     * Get minimum password length based on user role.
     */
    private function getMinimumLength($user = null): int
    {
        if (!$user) {
            return 8; // Default for registration
        }

        // Admin users require longer passwords
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return 12;
        }

        // Content managers require moderate length
        if ($user->hasAnyRole(['Editor', 'Author'])) {
            return 10;
        }

        // Default for subscribers
        return 8;
    }

    /**
     * Check if password has required character types.
     */
    private function hasRequiredCharacters(string $password, $user = null): bool
    {
        $requirements = $this->getCharacterRequirements($user);
        
        foreach ($requirements as $pattern => $required) {
            if ($required && !preg_match($pattern, $password)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get character requirements based on user role.
     */
    private function getCharacterRequirements($user = null): array
    {
        $isAdmin = $user && $user->hasAnyRole(['Super Admin', 'Admin']);
        
        return [
            '/[a-z]/' => true,  // Lowercase letter
            '/[A-Z]/' => true,  // Uppercase letter
            '/[0-9]/' => true,  // Number
            '/[^a-zA-Z0-9]/' => $isAdmin, // Special character (required for admins)
        ];
    }

    /**
     * Check if password is in common password list.
     */
    private function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password', 'password123', '123456', '123456789', 'qwerty',
            'abc123', 'password1', 'admin', 'letmein', 'welcome',
            'monkey', '1234567890', 'dragon', 'master', 'hello',
            'login', 'pass', 'admin123', 'root', 'user'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }

    /**
     * Check if password has been used recently.
     */
    private function isPasswordReused(string $password, $user): bool
    {
        // Check current password
        if ($user->password && Hash::check($password, $user->password)) {
            return true;
        }

        // In a production system, you might store password history
        // For now, we'll just check the current password
        return false;
    }

    /**
     * Get password validation error messages.
     */
    private function getPasswordErrors(string $password, $user = null): array
    {
        $errors = [];
        $minLength = $this->getMinimumLength($user);

        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long.";
        }

        $requirements = $this->getCharacterRequirements($user);
        
        if ($requirements['/[a-z]/'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if ($requirements['/[A-Z]/'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if ($requirements['/[0-9]/'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if ($requirements['/[^a-zA-Z0-9]/'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common. Please choose a more secure password.';
        }

        if ($user && $this->isPasswordReused($password, $user)) {
            $errors[] = 'Password has been used recently. Please choose a different password.';
        }

        return $errors;
    }
}
