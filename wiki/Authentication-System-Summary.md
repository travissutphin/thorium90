# Authentication System Summary

This document provides a high-level summary of the Thorium90 Multi-Role User Authentication system, serving as a quick reference for understanding the complete authentication architecture.

## System Components

### Core Authentication Stack

1. **Laravel 12 Core**
   - Base authentication framework
   - Session management
   - Guards and providers
   - Core middleware (`auth`, `guest`)

2. **Laravel Fortify**
   - User registration and login
   - Two-factor authentication (2FA)
   - Password reset and management
   - Email verification
   - Profile updates

3. **Laravel Sanctum**
   - API token authentication
   - SPA session authentication
   - Personal access tokens
   - Token abilities/scopes

4. **Laravel Socialite**
   - OAuth integration
   - Social login providers (Google, GitHub, Facebook, LinkedIn, Twitter, GitLab)
   - Account linking
   - Avatar synchronization

5. **Spatie Laravel Permission**
   - Role-based access control (RBAC)
   - 5 default roles: Super Admin, Admin, Editor, Author, Subscriber
   - 22+ granular permissions
   - Permission caching

## Authentication Flows

### 1. Standard Authentication
- **Registration**: Fortify → Create User → Assign Default Role → Login
- **Login**: Fortify → Validate Credentials → Create Session → Redirect
- **2FA**: Enable 2FA → Generate QR Code → Confirm Setup → Challenge on Login

### 2. API Authentication
- **Token Creation**: Login → Sanctum → Generate Token → Return to Client
- **API Access**: Request with Bearer Token → Sanctum Validates → Access Granted

### 3. Social Authentication
- **OAuth Flow**: Click Social Login → Socialite → OAuth Provider → Callback → Create/Link User → Login

## Role Hierarchy

| Role | Permissions | Use Case |
|------|-------------|----------|
| **Super Admin** | All permissions | System administrators |
| **Admin** | User management, settings | Site administrators |
| **Editor** | Content management | Content managers |
| **Author** | Create/edit own content | Content creators |
| **Subscriber** | View dashboard only | Regular users |

## Testing Strategy

### Regression Testing Order
1. **Environment Setup** (30s)
2. **Database Integrity** (1m)
3. **Core Authentication** (3-5m)
4. **API Authentication** (2-3m)
5. **Social Authentication** (2-3m)
6. **Authorization/Permissions** (2-3m)
7. **Frontend Integration** (1-2m)
8. **Performance Validation** (1-2m)
9. **Security Testing** (2-3m)

### When to Test
- **During Development**: After each feature change
- **Before Commits**: Run full test suite
- **Pre-Deployment**: Full regression test
- **Post-Deployment**: Smoke tests
- **Scheduled**: Daily CI/CD, weekly regression, monthly security audit

## Quick Reference

### Common Commands
```bash
# Setup
php artisan migrate:fresh --seed
php artisan optimize:clear

# Testing
php artisan test
./regression-test.sh

# Permissions
php artisan permission:cache-reset
```

### Middleware Usage
```php
// Role-based
Route::middleware(['auth', 'role:Admin'])->group(...);

// Permission-based
Route::middleware(['auth', 'permission:create posts'])->group(...);

// API routes
Route::middleware(['auth:sanctum'])->group(...);
```

### Frontend Checks
```tsx
// React/TypeScript
if (auth.user.can('create posts')) { }
if (auth.user.hasRole('Admin')) { }
if (auth.user.is_admin) { }
```

## Security Features

- **Password Policies**: Role-based complexity requirements
- **2FA Enforcement**: Required for Admin/Super Admin roles
- **Rate Limiting**: 5 login attempts per minute
- **CSRF Protection**: Enabled for all POST requests
- **Session Security**: Secure cookies, session regeneration
- **API Token Security**: Token abilities, expiration

## Performance Optimizations

- **Permission Caching**: 24-hour cache, automatic invalidation
- **Eager Loading**: Roles and permissions loaded with users
- **Database Indexes**: Optimized for permission lookups
- **Frontend Memoization**: Permission checks cached in React

## Documentation Structure

1. **[Authentication Architecture](Authentication-Architecture.md)** - Detailed component overview
2. **[Testing Strategy](Testing-Strategy.md)** - Comprehensive testing guide
3. **[Authentication Quick Reference](Authentication-Quick-Reference.md)** - Common tasks and troubleshooting
4. **[Developer Guide](Developer-Guide.md)** - Technical implementation details
5. **[API Reference](API-Reference.md)** - Complete API documentation

## Key Integration Points

### Fortify + Sanctum
- User registers via Fortify
- Can immediately create API tokens via Sanctum
- 2FA applies to both web and API authentication

### Socialite + Permissions
- Social users automatically assigned default role
- Existing users can link social accounts
- Social users bypass email verification

### All Components
- Unified user model with traits
- Consistent permission checking across web/API
- Shared authentication state via Inertia.js

## Best Practices

1. **Always use Fortify actions** for user creation
2. **Cache permissions** for performance
3. **Test authentication flows** after changes
4. **Enforce 2FA** for administrative roles
5. **Use middleware** for route protection
6. **Validate permissions** server-side
7. **Log authentication events** for security
8. **Regular security audits** of roles/permissions

## Troubleshooting Quick Guide

| Issue | Solution |
|-------|----------|
| Login fails | Check credentials, clear cache, verify user exists |
| Permission denied | Check roles, clear permission cache, verify middleware |
| 2FA not working | Check time sync, test recovery codes |
| API token invalid | Verify format, check abilities, ensure not expired |
| Social login fails | Check OAuth credentials, verify callback URL |

---

For detailed information on any topic, refer to the specific documentation guides linked above.
