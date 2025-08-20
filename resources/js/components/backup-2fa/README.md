# 2FA Components - Temporarily Disabled

This directory contains the 2FA components that were temporarily disabled during early development.

## Components Backed Up
- TwoFactorAuthentication.tsx - Complete 2FA management interface
- ApiClient.ts - Standardized API client for Laravel backend communication

## To Re-enable 2FA Later
1. Copy components back to main components directory
2. Update config/fortify.php to enable twoFactorAuthentication feature
3. Add 'ensure.2fa' middleware back to protected routes
4. Update login controller to check for 2FA requirements
5. Add 2FA settings page to user settings navigation

## Related Files
- `app/Http/Controllers/Auth/TwoFactorAuthenticationController.php`
- `app/Http/Controllers/Auth/TwoFactorChallengeController.php`
- `app/Http/Middleware/EnsureTwoFactorAuthentication.php`
- `routes/auth.php` (2FA routes)
- Various test files in `tests/Feature/`