# Authentication Quick Reference

This quick reference guide provides a concise overview of the authentication system components, common use cases, and troubleshooting tips for the Thorium90 Multi-Role User Authentication system.

## Component Responsibility Matrix

| Component | Primary Responsibility | Key Features | When to Use |
|-----------|----------------------|--------------|-------------|
| **Laravel 12 Core** | Foundation authentication | Sessions, Guards, Middleware | Always active, custom auth logic |
| **Fortify** | Headless auth backend | Registration, Login, 2FA, Password reset | User registration, password management |
| **Sanctum** | API authentication | Tokens, SPA auth, CSRF | API access, mobile apps, SPAs |
| **Socialite** | OAuth integration | Social logins, OAuth flows | Google/GitHub/Facebook login |
| **Spatie Permission** | RBAC system | Roles, Permissions, Gates | All authorization decisions |

## Quick Setup Commands

```bash
# Initial setup
composer install
npm install && npm run build
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Quick regression test
./regression-test.sh --quick
```

## Authentication Flows

### Standard Login
```php
// Route: POST /login
// Handled by: Fortify
// Middleware: guest
[
    'email' => 'user@example.com',
    'password' => 'password',
    'remember' => true
]
```

### API Token Creation
```php
// Route: POST /api/tokens
// Handled by: Sanctum
// Middleware: auth
[
    'name' => 'My API Token',
    'abilities' => ['read', 'write']
]
// Returns: { "token": "1|plainTextToken..." }
```

### Social Login
```php
// Route: GET /auth/{provider}
// Handled by: Socialite
// Providers: google, github, facebook, linkedin, twitter, gitlab
// Callback: GET /auth/{provider}/callback
```

### Two-Factor Authentication
```php
// Enable 2FA: POST /user/two-factor-authentication
// Confirm 2FA: POST /user/two-factor-authentication/confirm
// Challenge: POST /two-factor-challenge
[
    'code' => '123456' // or 'recovery_code' => 'xxxxx-xxxxx'
]
```

## Role & Permission Quick Reference

### Default Roles
| Role | Description | Key Permissions |
|------|-------------|-----------------|
| **Super Admin** | Full system access | All permissions |
| **Admin** | Administrative access | User management, settings |
| **Editor** | Content management | Create, edit, delete posts |
| **Author** | Content creation | Create posts, edit own |
| **Subscriber** | Basic access | View dashboard only |

### Common Permission Checks

```php
// Backend (PHP)
$user->hasRole('Admin');
$user->can('create posts');
$user->hasAnyRole(['Admin', 'Editor']);
$user->hasPermissionTo('manage users');

// Frontend (React/TypeScript)
auth.user.hasRole('Admin')
auth.user.can('create posts')
auth.user.is_admin
auth.user.role_names.includes('Editor')
```

### Middleware Usage

```php
// Single role
Route::middleware(['auth', 'role:Admin'])->group(...);

// Multiple roles (OR)
Route::middleware(['auth', 'role:Admin,Editor'])->group(...);

// Any role
Route::middleware(['auth', 'role.any:Admin,Editor'])->group(...);

// Single permission
Route::middleware(['auth', 'permission:create posts'])->group(...);

// Multiple permissions (AND)
Route::middleware(['auth', 'permission:create posts,edit posts'])->group(...);
```

## Common Use Cases

### 1. User Registration with Role
```php
// app/Actions/Fortify/CreateNewUser.php
public function create(array $input)
{
    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
    ]);
    
    // Assign default role
    $user->assignRole('Subscriber');
    
    return $user;
}
```

### 2. Protect API Endpoint
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'permission:view users'])
    ->get('/users', [UserController::class, 'index']);
```

### 3. Social Login with Existing User
```php
// Find or create user
$user = User::firstOrCreate(
    ['email' => $socialUser->email],
    [
        'name' => $socialUser->name,
        'provider' => $provider,
        'provider_id' => $socialUser->id,
        'email_verified_at' => now(),
    ]
);

// Assign role if new user
if ($user->wasRecentlyCreated) {
    $user->assignRole('Subscriber');
}
```

### 4. Conditional UI Rendering
```tsx
// React component
function Navigation() {
    const { auth } = usePage().props;
    
    return (
        <nav>
            {auth.user.can('create posts') && (
                <Link href="/posts/create">New Post</Link>
            )}
            
            {auth.user.is_admin && (
                <Link href="/admin">Admin Panel</Link>
            )}
        </nav>
    );
}
```

### 5. Force 2FA for Admin Roles
```php
// app/Http/Middleware/EnsureTwoFactorAuthentication.php
if ($user->hasRole(['Admin', 'Super Admin'])) {
    if (!$user->two_factor_enabled) {
        return redirect()->route('profile.show')
            ->with('error', '2FA is required for administrators');
    }
}
```

## Environment Variables

### Essential Auth Variables
```env
# Application
APP_URL=http://localhost:8000

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail (for password resets)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025

# Social Login (example for Google)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback
```

## Troubleshooting Flowchart

```
Authentication Issue?
│
├─> Login Problems?
│   ├─> Check credentials
│   ├─> Clear session: php artisan session:clear
│   ├─> Check rate limiting
│   └─> Verify user exists and is active
│
├─> Permission Denied?
│   ├─> Check user roles: $user->getRoleNames()
│   ├─> Check permissions: $user->getAllPermissions()
│   ├─> Clear permission cache: php artisan permission:cache-reset
│   └─> Verify middleware on route
│
├─> 2FA Issues?
│   ├─> Check two_factor_enabled field
│   ├─> Verify TOTP time sync
│   ├─> Test with recovery code
│   └─> Check two_factor_confirmed_at
│
├─> API Token Problems?
│   ├─> Verify token format: Bearer {token}
│   ├─> Check token abilities
│   ├─> Verify Sanctum middleware
│   └─> Check token expiration
│
└─> Social Login Failed?
    ├─> Verify OAuth credentials
    ├─> Check callback URL
    ├─> Review provider scopes
    └─> Check user email uniqueness
```

## Common Errors & Solutions

### 1. "Unauthenticated" (401)
```bash
# Check authentication guard
php artisan tinker
>>> Auth::check() # Should return true if logged in

# For API requests, verify token
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/user
```

### 2. "Forbidden" (403)
```php
// Check user permissions
$user = Auth::user();
dd($user->getAllPermissions()->pluck('name'));
dd($user->getRoleNames());
```

### 3. "Too Many Requests" (429)
```bash
# Clear rate limiter
php artisan cache:clear

# Check throttle settings in LoginController
'throttle:6,1' # 6 attempts per minute
```

### 4. "CSRF Token Mismatch" (419)
```javascript
// Ensure CSRF token in forms
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

// For API requests
axios.defaults.headers.common['X-CSRF-TOKEN'] = 
    document.querySelector('meta[name="csrf-token"]').content;
```

### 5. Social Login Email Conflict
```php
// Handle existing email
$existingUser = User::where('email', $socialUser->email)->first();
if ($existingUser && !$existingUser->provider) {
    // Link social account to existing user
    $existingUser->update([
        'provider' => $provider,
        'provider_id' => $socialUser->id,
    ]);
}
```

## Testing Commands

### Quick Test Specific Features
```bash
# Test authentication
php artisan test --filter=Auth

# Test permissions
php artisan test --filter=Permission

# Test API
php artisan test --filter=Sanctum

# Test social login
php artisan test --filter=Social

# Run with coverage
php artisan test --coverage --min=80
```

### Debug Authentication State
```php
// In tinker
php artisan tinker

// Check current user
>>> Auth::user()

// Check specific user's roles
>>> User::find(1)->getRoleNames()

// Check permissions
>>> User::find(1)->getAllPermissions()->pluck('name')

// Test permission check
>>> User::find(1)->can('create posts')
```

## Performance Tips

### 1. Cache Permissions
```php
// Permissions are cached automatically
// Clear cache when roles/permissions change
php artisan permission:cache-reset
```

### 2. Eager Load Relationships
```php
// Load user with roles and permissions
$user = User::with(['roles', 'permissions'])->find($id);

// For multiple users
$users = User::with('roles.permissions')->paginate();
```

### 3. Optimize Session Driver
```env
# For production, use Redis or database
SESSION_DRIVER=redis
CACHE_DRIVER=redis
```

### 4. API Token Cleanup
```php
// Remove expired tokens periodically
php artisan sanctum:prune-expired --hours=24
```

## Security Checklist

- [ ] HTTPS enabled in production
- [ ] Strong password policy configured
- [ ] 2FA required for admin roles
- [ ] Rate limiting on auth endpoints
- [ ] CSRF protection enabled
- [ ] Session security configured
- [ ] API tokens have expiration
- [ ] Social login emails verified
- [ ] Permission cache cleared after changes
- [ ] Audit logs for auth events

## Useful Artisan Commands

```bash
# User management
php artisan user:create              # Create new user
php artisan user:assign-role         # Assign role to user

# Permission management
php artisan permission:create-role    # Create new role
php artisan permission:create-permission # Create permission
php artisan permission:cache-reset    # Clear permission cache

# Testing
php artisan test                      # Run all tests
php artisan test --parallel           # Run tests in parallel
php artisan test --filter=Auth        # Run auth tests only

# Debugging
php artisan route:list --name=auth    # List auth routes
php artisan config:show auth          # Show auth config
php artisan tinker                    # Interactive shell
```

## Related Documentation

- [Authentication Architecture](Authentication-Architecture.md) - Detailed component overview
- [Testing Strategy](Testing-Strategy.md) - Comprehensive testing guide
- [Developer Guide](Developer-Guide.md) - Full implementation details
- [Troubleshooting](Troubleshooting.md) - Detailed troubleshooting guide
