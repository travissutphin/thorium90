# Laravel Fortify Integration Guide

## Overview

This document provides comprehensive information about the Laravel Fortify integration in the Thorium90 Multi-Role User Authentication system. Laravel Fortify provides headless authentication services including Two-Factor Authentication (2FA), enhanced email verification, and advanced password management.

## Table of Contents

1. [Installation and Configuration](#installation-and-configuration)
2. [Two-Factor Authentication](#two-factor-authentication)
3. [Enhanced Email Verification](#enhanced-email-verification)
4. [Enhanced Password Management](#enhanced-password-management)
5. [Role-Based Security Policies](#role-based-security-policies)
6. [API Integration](#api-integration)
7. [Frontend Components](#frontend-components)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

## Installation and Configuration

### Package Installation

Laravel Fortify has been installed and configured with the following components:

```bash
composer require laravel/fortify
```

### Configuration Files

#### `config/fortify.php`
- **Guard**: `web` (matches existing auth system)
- **Password Broker**: `users` (matches existing setup)
- **Username Field**: `email`
- **Home Path**: `/dashboard`
- **Views**: Disabled (headless mode for API/SPA usage)
- **Features Enabled**:
  - User registration
  - Password reset
  - Email verification
  - Profile information updates
  - Password updates
  - Two-factor authentication with confirmation

#### Service Provider Registration
Fortify is registered in `bootstrap/providers.php`:
```php
Laravel\Fortify\FortifyServiceProvider::class,
```

### Database Schema

#### Two-Factor Authentication Columns
Added to `users` table via migration:
```php
$table->text('two_factor_secret')->nullable();
$table->text('two_factor_recovery_codes')->nullable();
$table->timestamp('two_factor_confirmed_at')->nullable();
```

### Fortify Actions

Custom Fortify actions maintain compatibility with the existing system:

- **`CreateNewUser`**: Automatically assigns 'Subscriber' role to new users
- **`UpdateUserProfileInformation`**: Handles email verification on changes
- **`UpdateUserPassword`**: Supports users without passwords (social login)
- **`ResetUserPassword`**: Secure password reset with enhanced validation
- **`PasswordValidationRules`**: Consistent password validation across actions

## Two-Factor Authentication

### Features

#### Complete 2FA Lifecycle
1. **Enable 2FA**: Generate TOTP secret and QR code
2. **Setup**: Scan QR code with authenticator app
3. **Confirm**: Verify setup with authentication code
4. **Generate Recovery Codes**: Backup authentication method
5. **Authenticate**: Use TOTP codes or recovery codes during login
6. **Manage**: View status, regenerate codes, disable 2FA

#### Supported Authenticator Apps
- Google Authenticator
- Authy
- Microsoft Authenticator
- Any TOTP-compatible app

### API Endpoints

#### 2FA Management (Authenticated Users)
```http
GET    /user/two-factor-authentication           # Get 2FA status
POST   /user/two-factor-authentication           # Enable 2FA
DELETE /user/two-factor-authentication           # Disable 2FA
GET    /user/two-factor-authentication/qr-code   # Get QR code
GET    /user/two-factor-authentication/recovery-codes # Get recovery codes
POST   /user/two-factor-authentication/recovery-codes # Generate new codes
POST   /user/two-factor-authentication/confirm   # Confirm 2FA setup
```

#### 2FA Challenge (Guest Users)
```http
GET  /two-factor-challenge    # Show 2FA challenge form
POST /two-factor-challenge    # Verify 2FA code/recovery code
```

### Frontend Integration

#### User Data Structure
2FA status is automatically shared with all frontend components:
```typescript
interface AuthUser {
  // ... existing fields
  two_factor_enabled: boolean;      // 2FA is enabled
  two_factor_confirmed: boolean;    // 2FA setup is confirmed
  has_recovery_codes: boolean;      // Recovery codes exist
}
```

#### React Components
- **`TwoFactorAuthentication.tsx`**: Complete 2FA management interface
- **`TwoFactorChallenge.tsx`**: Login challenge interface

### Security Features

#### Encryption and Storage
- TOTP secrets encrypted in database
- Recovery codes encrypted and single-use
- QR codes generated server-side for security

#### Rate Limiting
- Login attempts: 5 per minute
- 2FA challenges: Rate limited via middleware
- Recovery code usage: Single-use with automatic replacement

## Enhanced Email Verification

### Features

#### MustVerifyEmail Implementation
The User model implements `MustVerifyEmail` interface:
```php
class User extends Authenticatable implements MustVerifyEmail
```

#### Enhanced Verification Flow
- Automatic re-verification when email changes
- Integration with existing Resend email configuration
- Fortify-powered verification notifications
- Seamless integration with existing auth flow

#### Email Verification Routes
```http
GET  /verify-email                    # Email verification prompt
GET  /verify-email/{id}/{hash}        # Verify email link
POST /email/verification-notification # Resend verification email
```

## Enhanced Password Management

### Role-Based Password Policies

#### Password Complexity Requirements
Implemented via `EnsurePasswordComplexity` middleware:

| Role | Min Length | Requirements |
|------|------------|--------------|
| Super Admin | 12 chars | Uppercase, lowercase, number, special char |
| Admin | 12 chars | Uppercase, lowercase, number, special char |
| Editor | 10 chars | Uppercase, lowercase, number |
| Author | 10 chars | Uppercase, lowercase, number |
| Subscriber | 8 chars | Uppercase, lowercase, number |

#### Security Features
- Common password detection
- Password history checking (prevents reuse)
- Character complexity validation
- Role-based minimum length requirements

### Password Validation Rules

Enhanced `PasswordValidationRules` trait provides:
- Consistent validation across all Fortify actions
- Integration with Laravel Fortify's Password rule
- Configurable password strength requirements

## Role-Based Security Policies

### 2FA Requirements by Role

Implemented via `EnsureTwoFactorAuthentication` middleware:

| Role | 2FA Requirement | Enforcement |
|------|----------------|-------------|
| Super Admin | **Required** | Mandatory - blocks access without 2FA |
| Admin | **Required** | Mandatory - blocks access without 2FA |
| Editor | **Recommended** | Warning message, access allowed |
| Author | Optional | No enforcement |
| Subscriber | Optional | No enforcement |

### Middleware Integration

#### Route Protection
```php
// Applied to protected routes
Route::middleware(['auth', 'ensure.2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // ... other protected routes
});
```

#### Password Complexity
```php
// Applied to password-related routes
Route::middleware(['password.complexity'])->group(function () {
    Route::post('/register', [RegisterController::class, 'store']);
    Route::post('/reset-password', [PasswordController::class, 'store']);
    // ... other password routes
});
```

## API Integration

### Laravel Sanctum Compatibility

Fortify works seamlessly with existing Sanctum API authentication:

#### API Token Management
```http
POST /api/tokens              # Create API token
GET  /api/tokens              # List user tokens
DELETE /api/tokens/{id}       # Revoke token
```

#### 2FA with API Authentication
- API tokens respect 2FA requirements
- 2FA status included in user API responses
- Role-based API access with 2FA enforcement

### API Response Examples

#### User with 2FA Status
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "roles": ["Admin"],
  "permissions": ["view dashboard", "manage users"],
  "two_factor_enabled": true,
  "two_factor_confirmed": true,
  "has_recovery_codes": true
}
```

#### 2FA Challenge Response
```json
{
  "message": "Two factor authentication successful.",
  "redirect_url": "/admin/dashboard",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role_names": ["Admin"],
    "is_admin": true
  }
}
```

## Frontend Components

### TwoFactorAuthentication Component

Complete 2FA management interface with:
- Enable/disable 2FA with visual status indicators
- QR code display for authenticator app setup
- Recovery codes management (view, copy, regenerate)
- Setup confirmation with verification codes
- Responsive design with proper error handling

#### Usage Example
```tsx
import TwoFactorAuthentication from '@/components/TwoFactorAuthentication';

function SecuritySettings() {
  return (
    <div>
      <h2>Security Settings</h2>
      <TwoFactorAuthentication />
    </div>
  );
}
```

### TwoFactorChallenge Component

Login challenge interface with:
- Dual-mode authentication (TOTP codes + recovery codes)
- Clean interface for code entry
- Role-based redirection after successful authentication
- Comprehensive error handling and user feedback

#### Usage Example
```tsx
import TwoFactorChallenge from '@/components/TwoFactorChallenge';

function LoginChallenge() {
  return <TwoFactorChallenge />;
}
```

## Testing

### Comprehensive Test Suite

#### Test Coverage
- 2FA enablement and disablement
- QR code generation and display
- Recovery code management
- 2FA confirmation process
- Role-based 2FA requirements
- Challenge authentication flow
- Integration with existing auth system

#### Running Tests
```bash
# Run all authentication tests
php artisan test --filter=Authentication

# Run specific 2FA tests
php artisan test --filter=TwoFactorAuthentication

# Run with coverage
php artisan test --coverage
```

#### Test Examples
```php
/** @test */
public function admin_users_are_required_to_have_2fa()
{
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this->actingAs($admin)->get('/dashboard');
    
    $response->assertRedirect();
    $this->assertTrue(str_contains($response->headers->get('Location'), 'two-factor'));
}
```

## Troubleshooting

### Common Issues

#### 2FA Not Working
1. **Check Database Migration**: Ensure 2FA columns exist in users table
2. **Verify Configuration**: Check `config/fortify.php` settings
3. **Clear Cache**: Run `php artisan config:clear`
4. **Check Routes**: Verify 2FA routes are registered

#### QR Code Not Displaying
1. **Enable 2FA First**: QR codes only available after enabling 2FA
2. **Check Permissions**: Ensure user is authenticated
3. **Verify Secret**: Check that `two_factor_secret` is set in database

#### Recovery Codes Issues
1. **Confirm 2FA**: Recovery codes only available after confirming 2FA
2. **Check Encryption**: Ensure recovery codes are properly encrypted
3. **Regenerate Codes**: Use the regenerate endpoint if codes are corrupted

#### Role-Based Enforcement Not Working
1. **Check Middleware**: Ensure `EnsureTwoFactorAuthentication` is applied
2. **Verify Roles**: Check user has correct roles assigned
3. **Route Configuration**: Ensure middleware is applied to correct routes

### Debug Commands

```bash
# Check user 2FA status
php artisan tinker
>>> $user = User::find(1);
>>> $user->two_factor_enabled;
>>> $user->two_factor_confirmed;

# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list --name=two-factor
```

### Log Files

Monitor these log files for issues:
- `storage/logs/laravel.log` - General application logs
- Authentication errors and 2FA issues are logged here

## Security Considerations

### Best Practices

1. **Regular Security Audits**: Review 2FA usage and role assignments
2. **Monitor Failed Attempts**: Watch for suspicious 2FA challenge failures
3. **Recovery Code Management**: Educate users on secure storage
4. **Role-Based Policies**: Regularly review and update 2FA requirements
5. **Password Policies**: Adjust complexity requirements as needed

### Production Deployment

1. **Environment Variables**: Ensure all Fortify settings are configured
2. **HTTPS Required**: 2FA requires secure connections
3. **Rate Limiting**: Configure appropriate rate limits for production
4. **Monitoring**: Set up alerts for authentication failures
5. **Backup Procedures**: Ensure user recovery processes are documented

## Related Documentation

- [Authentication Overview](README.md)
- [API Documentation](api.md)
- [Social Login Integration](social-login.md)
- [Testing Guide](../testing/authentication-tests.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Deployment Guide](deployment.md)
