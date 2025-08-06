# Laravel Fortify Testing Guide

## Overview

This document provides comprehensive testing procedures for the Laravel Fortify integration in the Thorium90 Multi-Role User Authentication system. It covers testing for Two-Factor Authentication (2FA), enhanced email verification, password management, and role-based security policies.

## Table of Contents

1. [Test Environment Setup](#test-environment-setup)
2. [Two-Factor Authentication Testing](#two-factor-authentication-testing)
3. [Enhanced Email Verification Testing](#enhanced-email-verification-testing)
4. [Password Management Testing](#password-management-testing)
5. [Role-Based Security Testing](#role-based-security-testing)
6. [Automated Test Suite](#automated-test-suite)
7. [Manual Testing Procedures](#manual-testing-procedures)
8. [Performance Testing](#performance-testing)
9. [Security Validation](#security-validation)
10. [Troubleshooting](#troubleshooting)

## Test Environment Setup

### Prerequisites for Fortify Testing

```bash
# Ensure Laravel Fortify is installed
composer show laravel/fortify

# Verify 2FA database columns exist
php artisan tinker
>>> \Schema::hasColumn('users', 'two_factor_secret')  # Should return true
>>> \Schema::hasColumn('users', 'two_factor_recovery_codes')  # Should return true
>>> \Schema::hasColumn('users', 'two_factor_confirmed_at')  # Should return true
>>> exit
```

### Configuration Verification

```bash
# Check Fortify configuration
php artisan config:show fortify

# Verify routes are registered
php artisan route:list --name=two-factor

# Check middleware registration
php artisan route:list | grep -E "(password|2fa|fortify)"
```

## Two-Factor Authentication Testing

### Automated 2FA Tests

The comprehensive test suite is located in `tests/Feature/TwoFactorAuthenticationTest.php`:

```bash
# Run all 2FA tests
php artisan test tests/Feature/TwoFactorAuthenticationTest.php

# Run specific test methods
php artisan test tests/Feature/TwoFactorAuthenticationTest.php --filter=user_can_enable_two_factor_authentication
php artisan test tests/Feature/TwoFactorAuthenticationTest.php --filter=admin_users_are_required_to_have_2fa
```

### Test Coverage

The automated test suite covers:

#### ✅ 2FA Enablement and Management
- `user_can_enable_two_factor_authentication`
- `user_can_get_qr_code_after_enabling_2fa`
- `user_can_confirm_two_factor_authentication`
- `user_can_disable_two_factor_authentication`

#### ✅ Recovery Code Management
- `user_can_get_recovery_codes_after_confirming_2fa`
- `user_can_generate_new_recovery_codes`

#### ✅ Role-Based 2FA Requirements
- `admin_users_are_required_to_have_2fa`
- `super_admin_users_are_required_to_have_2fa`
- `editor_users_receive_2fa_recommendation`
- `subscriber_users_can_access_without_2fa`

#### ✅ Authentication Flow
- `user_can_authenticate_with_valid_2fa_code`
- `user_cannot_authenticate_with_invalid_2fa_code`
- `user_can_authenticate_with_recovery_code`

#### ✅ Security Validation
- `guest_cannot_access_2fa_management_routes`
- `user_cannot_get_qr_code_without_enabling_2fa_first`
- `user_cannot_confirm_2fa_without_enabling_first`
- `user_cannot_confirm_2fa_twice`

### Manual 2FA Testing Procedures

#### 1. Complete 2FA Setup Flow

```bash
# Create test user
php artisan tinker
>>> $user = \App\Models\User::factory()->create(['email' => 'test@example.com']);
>>> $user->assignRole('Subscriber');
>>> exit

# Test the complete flow:
# 1. Login as test user
# 2. Navigate to security settings
# 3. Enable 2FA
# 4. Scan QR code with authenticator app
# 5. Enter verification code to confirm
# 6. View and save recovery codes
# 7. Test login with 2FA code
# 8. Test login with recovery code
```

#### 2. Role-Based 2FA Enforcement

```bash
# Test Admin user 2FA requirement
php artisan tinker
>>> $admin = \App\Models\User::factory()->create(['email' => 'admin@example.com']);
>>> $admin->assignRole('Admin');
>>> exit

# Login as admin - should be redirected to 2FA setup
# Try to access dashboard without 2FA - should be blocked
```

#### 3. API Testing

```bash
# Test 2FA status endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://localhost:8000/user/two-factor-authentication

# Test 2FA enablement
curl -X POST \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     http://localhost:8000/user/two-factor-authentication

# Test QR code retrieval
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://localhost:8000/user/two-factor-authentication/qr-code
```

## Enhanced Email Verification Testing

### Email Verification Flow

```bash
# Test email verification requirement
php artisan tinker
>>> $user = \App\Models\User::factory()->create(['email_verified_at' => null]);
>>> $user->assignRole('Subscriber');
>>> exit

# Login as unverified user
# Should be prompted to verify email
# Check that verification email is sent
# Test verification link functionality
```

### Email Change Re-verification

```bash
# Test email change triggers re-verification
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->email = 'newemail@example.com';
>>> $user->save();
>>> $user->email_verified_at; # Should be null
>>> exit
```

## Password Management Testing

### Password Complexity Testing

```bash
# Test role-based password requirements
php artisan test --filter=password

# Manual testing for different roles:
# Subscriber: 8+ chars, upper, lower, number
# Author: 10+ chars, upper, lower, number  
# Editor: 10+ chars, upper, lower, number
# Admin: 12+ chars, upper, lower, number, special
# Super Admin: 12+ chars, upper, lower, number, special
```

### Password Validation Examples

```bash
# Test weak passwords (should fail)
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"password": "password123"}' \
     http://localhost:8000/register

# Test strong passwords (should pass)
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"password": "MyStr0ng!P@ssw0rd"}' \
     http://localhost:8000/register
```

## Role-Based Security Testing

### 2FA Requirement Testing by Role

| Role | 2FA Requirement | Test Procedure |
|------|----------------|----------------|
| Super Admin | **Required** | Login → Should redirect to 2FA setup |
| Admin | **Required** | Login → Should redirect to 2FA setup |
| Editor | **Recommended** | Login → Should show warning message |
| Author | Optional | Login → No 2FA enforcement |
| Subscriber | Optional | Login → No 2FA enforcement |

### Testing Commands

```bash
# Test Super Admin 2FA requirement
php artisan tinker
>>> $user = \App\Models\User::factory()->create();
>>> $user->assignRole('Super Admin');
>>> # Login and try to access dashboard - should be blocked
>>> exit

# Test Editor 2FA recommendation
php artisan tinker
>>> $user = \App\Models\User::factory()->create();
>>> $user->assignRole('Editor');
>>> # Login and check for recommendation message
>>> exit
```

## Automated Test Suite

### Running the Complete Fortify Test Suite

```bash
# Run all Fortify-related tests
php artisan test tests/Feature/TwoFactorAuthenticationTest.php --verbose

# Run with coverage
php artisan test tests/Feature/TwoFactorAuthenticationTest.php --coverage

# Run specific test groups
php artisan test --group=2fa
php artisan test --group=fortify
php artisan test --group=security
```

### Expected Test Results

```
✅ TwoFactorAuthenticationTest
   ✅ user can enable two factor authentication
   ✅ user can get qr code after enabling 2fa
   ✅ user can confirm two factor authentication
   ✅ user can get recovery codes after confirming 2fa
   ✅ user can generate new recovery codes
   ✅ user can disable two factor authentication
   ✅ admin users are required to have 2fa
   ✅ super admin users are required to have 2fa
   ✅ editor users receive 2fa recommendation
   ✅ subscriber users can access without 2fa
   ✅ user with confirmed 2fa can access protected routes
   ✅ two factor challenge shows for users with 2fa
   ✅ user can authenticate with valid 2fa code
   ✅ user cannot authenticate with invalid 2fa code
   ✅ user can authenticate with recovery code
   ✅ guest cannot access 2fa management routes
   ✅ user cannot get qr code without enabling 2fa first
   ✅ user cannot confirm 2fa without enabling first
   ✅ user cannot confirm 2fa twice

Tests:  19 passed
Time:   5.23s
```

## Manual Testing Procedures

### 1. Complete User Journey Testing

#### New User Registration with 2FA
1. Register new user
2. Verify email address
3. Login successfully
4. Navigate to security settings
5. Enable 2FA
6. Scan QR code with authenticator app
7. Confirm 2FA with verification code
8. Save recovery codes
9. Logout and login with 2FA
10. Test recovery code login

#### Admin User 2FA Enforcement
1. Create admin user
2. Login - should redirect to 2FA setup
3. Try to access dashboard - should be blocked
4. Complete 2FA setup
5. Access dashboard successfully

### 2. Frontend Component Testing

#### TwoFactorAuthentication Component
```javascript
// Test component rendering
// Navigate to /settings/security
// Verify component loads correctly
// Test enable/disable functionality
// Test QR code display
// Test recovery code management
```

#### TwoFactorChallenge Component
```javascript
// Test challenge page rendering
// Navigate to /two-factor-challenge
// Test authenticator code input
// Test recovery code input
// Test error handling
// Test successful authentication
```

### 3. API Endpoint Testing

```bash
# Test all 2FA endpoints
curl -X GET http://localhost:8000/user/two-factor-authentication
curl -X POST http://localhost:8000/user/two-factor-authentication
curl -X DELETE http://localhost:8000/user/two-factor-authentication
curl -X GET http://localhost:8000/user/two-factor-authentication/qr-code
curl -X GET http://localhost:8000/user/two-factor-authentication/recovery-codes
curl -X POST http://localhost:8000/user/two-factor-authentication/recovery-codes
curl -X POST http://localhost:8000/user/two-factor-authentication/confirm
```

## Performance Testing

### 2FA Performance Benchmarks

```bash
# Test 2FA enablement performance
php artisan tinker
>>> $start = microtime(true);
>>> $user = \App\Models\User::factory()->create();
>>> $user->assignRole('Admin');
>>> // Enable 2FA via API call
>>> $end = microtime(true);
>>> echo 'Time: ' . round(($end - $start) * 1000, 2) . 'ms';
>>> exit

# Expected benchmarks:
# - 2FA enablement: < 500ms
# - QR code generation: < 200ms
# - Code verification: < 100ms
# - Recovery code generation: < 300ms
```

### Database Query Performance

```bash
# Test user loading with 2FA data
php artisan tinker
>>> $start = microtime(true);
>>> $user = \App\Models\User::with(['roles.permissions'])->first();
>>> $user->two_factor_enabled;
>>> $user->two_factor_confirmed;
>>> $end = microtime(true);
>>> echo 'Query time: ' . round(($end - $start) * 1000, 2) . 'ms';
>>> exit

# Expected: < 50ms for user data loading
```

## Security Validation

### Security Test Checklist

#### ✅ 2FA Security
- [ ] TOTP secrets are encrypted in database
- [ ] Recovery codes are encrypted and single-use
- [ ] QR codes generated server-side only
- [ ] Rate limiting on 2FA attempts
- [ ] Session validation prevents bypass

#### ✅ Password Security
- [ ] Role-based complexity requirements enforced
- [ ] Common passwords rejected
- [ ] Password history checking works
- [ ] Secure password reset flow

#### ✅ Access Control
- [ ] Role-based 2FA requirements enforced
- [ ] Unauthorized access blocked
- [ ] Admin routes protected
- [ ] API endpoints secured

### Security Testing Commands

```bash
# Test unauthorized 2FA access
curl -X POST http://localhost:8000/user/two-factor-authentication
# Should return 401/403

# Test 2FA bypass attempts
# Try to access protected routes without completing 2FA
# Should be redirected to 2FA setup

# Test recovery code reuse
# Use same recovery code twice
# Second attempt should fail
```

## Troubleshooting

### Common Issues and Solutions

#### 1. 2FA Tests Failing

**Issue**: `TwoFactorAuthenticationTest` failures
**Symptoms**: 
- QR code not generating
- Recovery codes not working
- Authentication failures

**Solutions**:
```bash
# Check Fortify configuration
php artisan config:show fortify

# Verify database schema
php artisan migrate:status

# Clear caches
php artisan optimize:clear

# Re-run migrations
php artisan migrate:fresh --seed
```

#### 2. Role-Based 2FA Not Working

**Issue**: Admin users not required to have 2FA
**Symptoms**:
- Admin users can access dashboard without 2FA
- No redirect to 2FA setup

**Solutions**:
```bash
# Check middleware registration
php artisan route:list | grep dashboard

# Verify user roles
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->roles->pluck('name');
>>> exit

# Check middleware logic
# Review EnsureTwoFactorAuthentication middleware
```

#### 3. Frontend Components Not Working

**Issue**: 2FA components not rendering
**Symptoms**:
- Blank pages
- JavaScript errors
- Missing user data

**Solutions**:
```bash
# Check Inertia data sharing
# Verify HandleInertiaRequests middleware

# Check user data structure
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->two_factor_enabled;
>>> $user->two_factor_confirmed;
>>> exit

# Clear view cache
php artisan view:clear
```

#### 4. Email Verification Issues

**Issue**: Email verification not working
**Symptoms**:
- Verification emails not sent
- Verification links not working
- Users not marked as verified

**Solutions**:
```bash
# Check mail configuration
php artisan config:show mail

# Test email sending
php artisan tinker
>>> \Mail::raw('Test email', function($msg) {
>>>     $msg->to('test@example.com')->subject('Test');
>>> });
>>> exit

# Check User model implements MustVerifyEmail
# Verify routes are registered
php artisan route:list | grep verify
```

### Debug Commands

```bash
# Check 2FA status for user
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->two_factor_enabled;
>>> $user->two_factor_confirmed;
>>> $user->two_factor_secret ? 'Has secret' : 'No secret';
>>> exit

# Check Fortify routes
php artisan route:list --name=two-factor

# Check middleware application
php artisan route:list | grep -E "(dashboard|admin)" | head -5

# Test 2FA provider
php artisan tinker
>>> $provider = app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
>>> $secret = $provider->generateSecretKey();
>>> $code = $provider->getCurrentOtp($secret);
>>> echo "Secret: $secret, Code: $code";
>>> exit
```

### Log Analysis

Monitor these log files for Fortify-related issues:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Look for these patterns:
# - "Two factor authentication" errors
# - "Fortify" related errors
# - Authentication failures
# - Database query errors
```

## Integration with Regression Testing

### Adding to Automated Scripts

The Fortify tests are integrated into the main regression testing script:

```bash
# Full regression test (includes Fortify)
./regression-test.sh

# Quick test (includes 2FA)
./regression-test.sh --quick
```

### CI/CD Integration

```yaml
# GitHub Actions example
name: Fortify Tests
on: [push, pull_request]
jobs:
  fortify-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run Fortify tests
        run: php artisan test tests/Feature/TwoFactorAuthenticationTest.php
```

## Conclusion

This comprehensive testing guide ensures that all Laravel Fortify features are thoroughly validated:

- ✅ **Two-Factor Authentication**: Complete lifecycle testing
- ✅ **Enhanced Email Verification**: Verification flow validation
- ✅ **Password Management**: Role-based complexity testing
- ✅ **Role-Based Security**: 2FA requirement enforcement
- ✅ **Performance**: Benchmark validation
- ✅ **Security**: Comprehensive security testing

Regular execution of these tests ensures the authentication system remains secure, functional, and performant across all user roles and scenarios.
