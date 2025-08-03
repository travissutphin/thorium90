# Developer Guide

This guide provides comprehensive technical information for developers working with the Multi-Role User Authentication System.

## 🏗️ System Architecture

### Overview

The system follows a modern full-stack architecture with Laravel backend and React frontend connected via Inertia.js:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   React Frontend│    │  Inertia.js     │    │  Laravel Backend│
│                 │◄──►│   Adapter       │◄──►│                 │
│ - Components    │    │ - Data Sharing  │    │ - Controllers   │
│ - State Mgmt    │    │ - Navigation    │    │ - Middleware    │
│ - Permission UI │    │ - SPA Features  │    │ - Models        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   Database      │
                       │                 │
                       │ - Users         │
                       │ - Roles         │
                       │ - Permissions   │
                       │ - Pivot Tables  │
                       └─────────────────┘
```

### Key Components

#### Backend (Laravel)
- **Models**: User, Role, Permission with relationships
- **Middleware**: Route protection and permission checking
- **Controllers**: Business logic and API endpoints
- **Gates**: Laravel authorization system integration
- **Seeders**: Database initialization and testing data

#### Frontend (React)
- **Components**: Reusable UI components with permission checks
- **Hooks**: Custom hooks for permission management
- **Context**: Global state management for user data
- **Routes**: Protected routes and navigation

#### Integration (Inertia.js)
- **Data Sharing**: User permissions and roles shared with frontend
- **Navigation**: SPA-like navigation with server-side rendering
- **State Management**: Synchronized state between frontend and backend

## 🔐 Authentication System

### User Model

The core of the authentication system is the `User` model with the `HasRoles` trait:

```php
<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Available Methods

The `HasRoles` trait provides these key methods:

```php
// Role checking
$user->hasRole('Admin');
$user->hasAnyRole(['Admin', 'Editor']);
$user->hasAllRoles(['Admin', 'Editor']);

// Permission checking
$user->hasPermissionTo('create-posts');
$user->hasAnyPermission(['create-posts', 'edit-posts']);
$user->hasAllPermissions(['create-posts', 'edit-posts']);

// Role assignment
$user->assignRole('Admin');
$user->syncRoles(['Admin', 'Editor']);
$user->removeRole('Editor');

// Permission assignment
$user->givePermissionTo('create-posts');
$user->syncPermissions(['create-posts', 'edit-posts']);
$user->revokePermissionTo('create-posts');

// Getting all permissions (including inherited)
$user->getAllPermissions();
$user->getDirectPermissions();
$user->getPermissionsViaRoles();
```

## 🛡️ Middleware System

### Route Protection

The system provides four middleware classes for route protection:

#### 1. EnsureUserHasRole

```php
// Protect routes requiring specific role(s)
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Multiple roles (OR logic)
Route::middleware(['auth', 'role:Admin,Editor'])->group(function () {
    Route::get('/content', [ContentController::class, 'index']);
});
```

#### 2. EnsureUserHasPermission

```php
// Protect routes requiring specific permission(s)
Route::middleware(['auth', 'permission:create-posts'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});

// Multiple permissions (AND logic)
Route::middleware(['auth', 'permission:create-posts,edit-posts'])->group(function () {
    Route::get('/posts/manage', [PostController::class, 'manage']);
});
```

#### 3. EnsureUserHasAnyRole

```php
// Protect routes requiring any of multiple roles
Route::middleware(['auth', 'role.any:Admin,Editor'])->group(function () {
    Route::get('/content/manage', [ContentController::class, 'manage']);
});
```

#### 4. EnsureUserHasAnyPermission

```php
// Protect routes requiring any of multiple permissions
Route::middleware(['auth', 'permission.any:create-posts,edit-posts'])->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
});
```

### Middleware Implementation

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->hasAnyRole($roles)) {
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
```

## 🚪 Laravel Gates

### Gate Definitions

The system defines comprehensive Gates in `AppServiceProvider`:

```php
public function boot(): void
{
    // User Management Gates
    Gate::define('view-users', function (User $user) {
        return $user->hasPermissionTo('view users');
    });

    Gate::define('create-users', function (User $user) {
        return $user->hasPermissionTo('create users');
    });

    // Content Management Gates
    Gate::define('create-posts', function (User $user) {
        return $user->hasPermissionTo('create posts');
    });

    Gate::define('edit-posts', function (User $user) {
        return $user->hasPermissionTo('edit posts');
    });

    // Own Content Gates
    Gate::define('edit-own-posts', function (User $user, $post = null) {
        if ($user->hasPermissionTo('edit posts')) {
            return true; // Can edit any post
        }
        
        if ($user->hasPermissionTo('edit own posts')) {
            return $post ? $post->user_id === $user->id : true;
        }
        
        return false;
    });

    // Role-based Gates
    Gate::define('is-admin', function (User $user) {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    });

    Gate::define('is-content-creator', function (User $user) {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']);
    });
}
```

### Using Gates

```php
// In controllers
if (Gate::allows('edit-posts', $post)) {
    // User can edit this post
}

// In blade templates
@can('create-users')
    <button>Create User</button>
@endcan

// In policies
public function update(User $user, Post $post)
{
    return Gate::allows('edit-own-posts', $post);
}
```

## 🔄 Frontend Integration

### Inertia.js Data Sharing

The `HandleInertiaRequests` middleware shares user data with the frontend:

```php
public function share(Request $request): array
{
    $user = $request->user();
    $authUser = null;

    if ($user) {
        $user->load(['roles.permissions', 'permissions']);
        
        $authUser = [
            ...$user->toArray(),
            
            // Permission checking functions
            'can' => function (string $permission) use ($user) {
                return $user->hasPermissionTo($permission);
            },
            'hasRole' => function (string $role) use ($user) {
                return $user->hasRole($role);
            },
            'hasAnyRole' => function (array $roles) use ($user) {
                return $user->hasAnyRole($roles);
            },
            
            // Arrays for easy access
            'role_names' => $user->roles->pluck('name')->toArray(),
            'permission_names' => $user->getAllPermissions()->pluck('name')->toArray(),
            
            // Computed properties
            'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
            'is_content_creator' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']),
        ];
    }

    return [
        'auth' => ['user' => $authUser],
        // ... other shared data
    ];
}
```

### React Components

#### Permission Checking

```tsx
import { usePage } from '@inertiajs/react';

interface User {
  can: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
  hasAnyRole: (roles: string[]) => boolean;
  is_admin: boolean;
  is_content_creator: boolean;
  role_names: string[];
  permission_names: string[];
}

export default function PostEditor() {
  const { auth } = usePage().props;
  const user = auth.user as User;

  if (!user.can('edit-posts')) {
    return <div>Access denied</div>;
  }

  return (
    <div>
      <h1>Edit Post</h1>
      {/* Editor content */}
    </div>
  );
}
```

#### Conditional Rendering

```tsx
export default function Navigation() {
  const { auth } = usePage().props;
  const user = auth.user as User;

  return (
    <nav>
      <Link href="/dashboard">Dashboard</Link>
      
      {user.can('create-posts') && (
        <Link href="/posts/create">Create Post</Link>
      )}
      
      {user.is_admin && (
        <Link href="/admin">Admin Panel</Link>
      )}
      
      {user.hasAnyRole(['Admin', 'Editor']) && (
        <Link href="/content/manage">Manage Content</Link>
      )}
    </nav>
  );
}
```

#### Custom Hooks

```tsx
// hooks/usePermissions.ts
import { usePage } from '@inertiajs/react';

export function usePermissions() {
  const { auth } = usePage().props;
  const user = auth.user as User;

  return {
    can: user.can,
    hasRole: user.hasRole,
    hasAnyRole: user.hasAnyRole,
    isAdmin: user.is_admin,
    isContentCreator: user.is_content_creator,
    roles: user.role_names,
    permissions: user.permission_names,
  };
}

// Usage in components
export default function MyComponent() {
  const { can, isAdmin } = usePermissions();

  if (!can('view-posts')) {
    return <div>Access denied</div>;
  }

  return (
    <div>
      {isAdmin && <AdminPanel />}
      <PostList />
    </div>
  );
}
```

## 🗄️ Database Schema

### Core Tables

#### Users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### Roles Table
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### Permissions Table
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### Pivot Tables
```sql
-- User roles
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- User permissions
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Role permissions
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### Relationships

```php
// User Model
public function roles()
{
    return $this->morphToMany(Role::class, 'model', 'model_has_roles');
}

public function permissions()
{
    return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
}

// Role Model
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'role_has_permissions');
}

public function users()
{
    return $this->morphedByMany(User::class, 'model', 'model_has_roles');
}

// Permission Model
public function roles()
{
    return $this->belongsToMany(Role::class, 'role_has_permissions');
}

public function users()
{
    return $this->morphedByMany(User::class, 'model', 'model_has_permissions');
}
```

## 🧪 Testing

### Test Structure

The system includes comprehensive tests for all components:

#### Feature Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithRoles;

class UIPermissionTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_inertia_shares_user_data_correctly()
    {
        $admin = $this->createAdmin();
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('auth.user.role_names')
                ->has('auth.user.permission_names')
                ->has('auth.user.is_admin')
                ->where('auth.user.is_admin', true)
        );
    }
}
```

#### WithRoles Trait

```php
trait WithRoles
{
    protected function createRolesAndPermissions()
    {
        // Create permissions
        $permissions = [
            'view dashboard',
            'create posts',
            'edit posts',
            // ... more permissions
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles with permissions
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'view dashboard',
            'create posts',
            'edit posts',
        ]);
    }

    protected function createAdmin(): User
    {
        return $this->createUserWithRole('Admin');
    }

    protected function assertUserHasRole(User $user, string $roleName): void
    {
        $this->assertTrue($user->hasRole($roleName));
    }
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/UIPermissionTest.php

# Run with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel
```

## 🔧 Configuration

### Permission Configuration

The Spatie Laravel Permission package configuration is in `config/permission.php`:

```php
return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
    ],

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
```

### Environment Variables

```env
# Application
APP_NAME="Multi-Role Auth System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

## 🚀 Performance Optimization

### Caching

```php
// Cache permissions for better performance
$user->getAllPermissions(); // Uses cache automatically

// Clear cache when permissions change
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

### Database Optimization

```sql
-- Add indexes for better performance
CREATE INDEX idx_model_has_roles_model_id ON model_has_roles(model_id);
CREATE INDEX idx_model_has_permissions_model_id ON model_has_permissions(model_id);
CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);
```

### Frontend Optimization

```tsx
// Memoize permission checks
const canCreatePosts = useMemo(() => user.can('create-posts'), [user]);

// Conditional rendering optimization
const AdminPanel = lazy(() => import('./AdminPanel'));

{user.is_admin && <Suspense fallback={<div>Loading...</div>}><AdminPanel /></Suspense>}
```

## 🔒 Security Best Practices

### Input Validation

```php
// Validate role assignments
public function assignRole(Request $request, User $user)
{
    $request->validate([
        'role' => 'required|exists:roles,name'
    ]);

    $user->assignRole($request->role);
}
```

### SQL Injection Prevention

```php
// Use Eloquent relationships (already protected)
$user->roles; // Safe

// Use parameterized queries
DB::table('users')->where('role', $role)->get(); // Safe
```

### XSS Prevention

```tsx
// Use React's built-in XSS protection
const userInput = "<script>alert('xss')</script>";
return <div>{userInput}</div>; // Automatically escaped
```

### CSRF Protection

```php
// Laravel automatically includes CSRF protection
// Ensure forms include CSRF token
<form method="POST" action="/users">
    @csrf
    <!-- form fields -->
</form>
```

## 🔄 Extending the System

### Adding New Permissions

1. **Create Migration**
```bash
php artisan make:migration add_new_permissions
```

2. **Add Permissions**
```php
public function up()
{
    Permission::create(['name' => 'manage-reports']);
    Permission::create(['name' => 'view-analytics']);
}
```

3. **Update Seeders**
```php
// In PermissionSeeder
$permissions = [
    // ... existing permissions
    'manage-reports',
    'view-analytics',
];
```

4. **Add Gates**
```php
// In AppServiceProvider
Gate::define('manage-reports', function (User $user) {
    return $user->hasPermissionTo('manage-reports');
});
```

### Adding New Roles

1. **Create Role**
```php
$moderator = Role::create(['name' => 'Moderator']);
$moderator->givePermissionTo([
    'view-posts',
    'moderate-comments',
    'view-reports',
]);
```

2. **Update Frontend**
```tsx
// Add to computed properties
'is_moderator' => $user->hasRole('Moderator'),
```

### Custom Permission Logic

```php
// Complex permission logic
Gate::define('edit-post', function (User $user, Post $post) {
    // Super admins can edit anything
    if ($user->hasRole('Super Admin')) {
        return true;
    }
    
    // Admins can edit any post
    if ($user->hasRole('Admin')) {
        return true;
    }
    
    // Authors can edit their own posts
    if ($user->hasRole('Author')) {
        return $post->user_id === $user->id;
    }
    
    return false;
});
```

## 📚 API Reference

### User Model Methods

| Method | Description | Example |
|--------|-------------|---------|
| `hasRole($role)` | Check if user has specific role | `$user->hasRole('Admin')` |
| `hasAnyRole($roles)` | Check if user has any of the roles | `$user->hasAnyRole(['Admin', 'Editor'])` |
| `hasAllRoles($roles)` | Check if user has all roles | `$user->hasAllRoles(['Admin', 'Editor'])` |
| `hasPermissionTo($permission)` | Check if user has permission | `$user->hasPermissionTo('create-posts')` |
| `hasAnyPermission($permissions)` | Check if user has any permission | `$user->hasAnyPermission(['create-posts', 'edit-posts'])` |
| `getAllPermissions()` | Get all permissions (direct + inherited) | `$user->getAllPermissions()` |
| `assignRole($role)` | Assign role to user | `$user->assignRole('Admin')` |
| `syncRoles($roles)` | Replace all user roles | `$user->syncRoles(['Admin', 'Editor'])` |

### Middleware Aliases

| Alias | Class | Description |
|-------|-------|-------------|
| `role` | `EnsureUserHasRole` | Check for specific role(s) |
| `permission` | `EnsureUserHasPermission` | Check for specific permission(s) |
| `role.any` | `EnsureUserHasAnyRole` | Check for any of multiple roles |
| `permission.any` | `EnsureUserHasAnyPermission` | Check for any of multiple permissions |

### Frontend Properties

| Property | Type | Description |
|----------|------|-------------|
| `auth.user.can` | Function | Check permission |
| `auth.user.hasRole` | Function | Check role |
| `auth.user.hasAnyRole` | Function | Check any role |
| `auth.user.role_names` | Array | User's role names |
| `auth.user.permission_names` | Array | User's permission names |
| `auth.user.is_admin` | Boolean | Is admin user |
| `auth.user.is_content_creator` | Boolean | Is content creator |

---

**Need more technical details?** Check out our [API Reference](API-Reference) or [Database Schema](Database-Schema) pages. 