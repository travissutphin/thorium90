# Developer Guide

This guide provides comprehensive technical information for developers working with the Multi-Role User Authentication System.

## 🏗️ System Architecture

### Overview

The system follows a modern full-stack architecture with Laravel backend and React frontend connected via Inertia.js. The authentication system is built on multiple specialized components working together:

```
┌─────────────────────────────────────────────────────────────────┐
│                        Frontend (React + Inertia.js)             │
├─────────────────────────────────────────────────────────────────┤
│                     Authentication Middleware Layer              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │   Fortify    │  │   Sanctum   │  │  Socialite  │            │
│  │  (Headless)  │  │ (API/SPA)   │  │   (OAuth)   │            │
│  └─────────────┘  └─────────────┘  └─────────────┘            │
├─────────────────────────────────────────────────────────────────┤
│                    Laravel 12 Core Authentication                │
│              (Sessions, Guards, Providers, Middleware)           │
├─────────────────────────────────────────────────────────────────┤
│                  Spatie Laravel Permission                       │
│              (Roles, Permissions, Authorization)                 │
├─────────────────────────────────────────────────────────────────┤
│                         Database Layer                           │
│        (Users, Roles, Permissions, Tokens, Social Logins)       │
└─────────────────────────────────────────────────────────────────┘
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

### Authentication Components Overview

The authentication system leverages multiple Laravel packages, each serving a specific purpose:

#### Laravel 12 Core
- **Purpose**: Foundation for all authentication features
- **Features**: Sessions, guards, providers, middleware
- **Usage**: Always active, provides base authentication functionality

#### Laravel Fortify
- **Purpose**: Headless authentication backend
- **Features**: Registration, login, 2FA, password reset, email verification
- **Usage**: All user authentication flows except social login

#### Laravel Sanctum
- **Purpose**: API authentication and SPA sessions
- **Features**: Personal access tokens, SPA authentication, CSRF protection
- **Usage**: API endpoints, mobile apps, React SPA authentication

#### Laravel Socialite
- **Purpose**: OAuth/social login integration
- **Features**: Multiple provider support (Google, GitHub, Facebook, etc.)
- **Usage**: Social login buttons and OAuth flows

#### Spatie Laravel Permission
- **Purpose**: Role-based access control (RBAC)
- **Features**: Roles, permissions, middleware, caching
- **Usage**: All authorization decisions throughout the application

For detailed information about when and how to use each component, see the [Authentication Architecture](Authentication-Architecture.md) guide.

## 🔐 Authentication System

### User Model

The core of the authentication system is the `User` model with the `HasRoles` and `SoftDeletes` traits:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'avatar',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Soft delete methods are automatically available
    // $user->delete() - soft delete
    // $user->restore() - restore soft deleted user
    // $user->forceDelete() - permanently delete
    // User::withTrashed() - include soft deleted users
    // User::onlyTrashed() - only soft deleted users
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
$user->hasPermissionTo('create pages');
$user->hasAnyPermission(['create pages', 'edit pages']);
$user->hasAllPermissions(['create pages', 'edit pages']);

// Role assignment
$user->assignRole('Admin');
$user->syncRoles(['Admin', 'Editor']);
$user->removeRole('Editor');

// Permission assignment
$user->givePermissionTo('create pages');
$user->syncPermissions(['create pages', 'edit pages']);
$user->revokePermissionTo('create pages');

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
Route::middleware(['auth', 'permission:create pages'])->group(function () {
    Route::post('/content/pages', [PageController::class, 'store']);
});

// Multiple permissions (AND logic)
Route::middleware(['auth', 'permission:create pages,edit pages'])->group(function () {
    Route::get('/content/pages/manage', [PageController::class, 'manage']);
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

## ⚙️ Admin Settings System

### Overview

The Admin Settings system provides comprehensive configuration management for the Multi-Role User Authentication system. It allows administrators to configure system-wide settings across multiple categories with proper permission controls, validation, caching, and audit logging.

### Key Features

- **Category-based Organization**: Settings organized into logical categories
- **Type-safe Value Handling**: Support for string, integer, boolean, JSON, and array data types
- **Permission-based Access Control**: Different permission levels for general vs. security settings
- **Caching for Performance**: Automatic caching with cache invalidation
- **Import/Export Functionality**: Backup and restore configurations
- **System Statistics**: Real-time monitoring of system health
- **Audit Logging**: Track all setting changes for security compliance

### Settings Model

The `Setting` model provides a comprehensive API for managing configuration values:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    // Basic operations
    public static function get(string $key, $default = null);
    public static function set(string $key, $value, string $type = 'string', string $category = 'general', ?string $description = null, bool $isPublic = false): Setting;
    public static function has(string $key): bool;
    public static function forget(string $key): bool;
    
    // Category operations
    public static function getByCategory(string $category, bool $publicOnly = false);
    public static function getGroupedByCategory(bool $publicOnly = false);
    
    // Filtering
    public static function getAll(bool $publicOnly = false);
    
    // Type casting
    public function getCastedValue();
}
```

### Usage Examples

```php
// Basic operations
$appName = Setting::get('app.name', 'Default App');
Setting::set('app.name', 'My Application', 'string', 'application', 'App name', true);

// Category operations
$authSettings = Setting::getByCategory('authentication');
$allSettings = Setting::getGroupedByCategory();

// Type-specific examples
Setting::set('app.debug', true, 'boolean', 'application');
Setting::set('auth.login_attempts', 5, 'integer', 'authentication');
Setting::set('auth.social_providers', ['google', 'github'], 'array', 'authentication');
Setting::set('email.notification_preferences', ['user_registered' => true], 'json', 'email');
```

### Settings Categories

1. **Application**: Basic app configuration (name, version, maintenance mode)
2. **Authentication**: Login, registration, and security settings
3. **User Management**: User account and profile settings
4. **Security**: Password policies, 2FA, IP whitelisting, audit logging
5. **Email**: Email configuration and templates
6. **Features**: Feature toggles and experimental features
7. **System**: System-level configuration (caching, logging, performance)

### Controller Implementation

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:manage settings')->except(['stats']);
        $this->middleware('permission:view system stats')->only(['stats']);
    }

    public function index()
    {
        $settings = Setting::getGroupedByCategory();
        $stats = $this->getSystemStats();
        
        return Inertia::render('admin/settings/index', [
            'settings' => $settings,
            'stats' => $stats,
            'categories' => $this->getSettingsCategories(),
        ]);
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        $this->validateSettings($settings);
        
        foreach ($settings as $key => $data) {
            // Security settings require special permission
            if (str_starts_with($key, 'security.') && !auth()->user()->can('manage security settings')) {
                continue;
            }
            
            Setting::set($key, $data['value'], $data['type'], $data['category'], $data['description'], $data['is_public']);
            $this->logSettingChange($key, $data['value']);
        }
        
        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
```

### Frontend Integration

```tsx
// React component for settings management
export default function AdminSettingsIndex({ settings, categories, stats }) {
    const [formData, setFormData] = useState(settings);
    const [hasChanges, setHasChanges] = useState(false);

    const handleSettingChange = (category: string, key: string, value: unknown) => {
        setFormData(prev => ({
            ...prev,
            [category]: {
                ...prev[category],
                [key]: { ...prev[category][key], value }
            }
        }));
        setHasChanges(true);
    };

    const renderSettingInput = (category: string, key: string, setting: Setting) => {
        switch (setting.type) {
            case 'boolean':
                return (
                    <Switch
                        checked={setting.value}
                        onCheckedChange={(checked) => handleSettingChange(category, key, checked)}
                    />
                );
            case 'integer':
                return (
                    <Input
                        type="number"
                        value={setting.value}
                        onChange={(e) => handleSettingChange(category, key, parseInt(e.target.value))}
                    />
                );
            // ... other types
        }
    };

    return (
        <AdminLayout>
            <Tabs value={activeTab} onValueChange={setActiveTab}>
                {/* Settings form with category tabs */}
            </Tabs>
        </AdminLayout>
    );
}
```

### Permissions

The system uses granular permissions:

- **manage settings**: Basic settings management (Admin+)
- **view system stats**: View system statistics (Admin+)
- **manage security settings**: Manage security settings (Super Admin only)
- **view audit logs**: View audit logs (Admin+)

### Caching Strategy

Settings are automatically cached for performance:

```php
// Automatic caching with 24-hour expiration
$value = Setting::get('app.name'); // Cached after first access

// Cache invalidation on updates
Setting::set('app.name', 'New Name'); // Automatically clears cache

// Manual cache operations
Setting::clearCache(); // Clear all settings cache
```

### Testing

Comprehensive test coverage includes:

```php
public function test_admin_can_update_settings()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->put('/admin/settings', [
            'settings' => [
                'app.name' => [
                    'value' => 'Updated Name',
                    'type' => 'string',
                    'category' => 'application'
                ]
            ]
        ]);

    $response->assertRedirect();
    $this->assertEquals('Updated Name', Setting::get('app.name'));
}

public function test_security_settings_require_special_permission()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->put('/admin/settings/security.password_min_length', [
            'value' => 12,
            'type' => 'integer'
        ]);

    $response->assertStatus(403);
}
```

## 🗑️ Soft Delete Implementation

### Overview

The system implements Laravel's soft delete functionality for user management, providing data safety and recovery options while maintaining referential integrity.

### Database Schema

#### Soft Delete Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

### Controller Implementation

#### UserController Soft Delete Methods

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware('permission:view users')->only(['index', 'trashed']);
        $this->middleware('permission:delete users')->only(['destroy']);
        $this->middleware('permission:restore users')->only(['restore']);
        $this->middleware('permission:force delete users')->only(['forceDelete']);
    }

    /**
     * Soft delete a user
     */
    public function destroy(User $user)
    {
        // Security checks
        if ($user->hasRole('Super Admin')) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Cannot delete the last Super Admin user.');
            }
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete(); // Soft delete

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully. The user can be restored if needed.');
    }

    /**
     * Display soft-deleted users
     */
    public function trashed()
    {
        $users = User::onlyTrashed()
            ->with(['roles.permissions'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_deleted' => User::onlyTrashed()->count(),
            'deleted_administrators' => User::onlyTrashed()->role(['Super Admin', 'Admin'])->count(),
            'deleted_content_creators' => User::onlyTrashed()->role(['Editor', 'Author'])->count(),
            'deleted_subscribers' => User::onlyTrashed()->role('Subscriber')->count(),
        ];

        return Inertia::render('admin/users/trashed', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->back()
            ->with('success', "User '{$user->name}' has been restored successfully.");
    }

    /**
     * Permanently delete a user (Super Admin only)
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        
        if (!auth()->user()->hasRole('Super Admin')) {
            return redirect()->back()
                ->with('error', 'Only Super Admins can permanently delete users.');
        }

        $userName = $user->name;
        $user->forceDelete();

        return redirect()->back()
            ->with('success', "User '{$userName}' has been permanently deleted.");
    }
}
```

### Route Configuration

```php
<?php

// routes/admin.php

// Standard user management routes
Route::resource('users', UserController::class)->except(['show']);
Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');

// Soft delete routes with proper permissions
Route::middleware('permission:view users')->group(function () {
    Route::get('/users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
});

Route::middleware('permission:restore users')->group(function () {
    Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
});

Route::middleware('permission:force delete users')->group(function () {
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
});
```

### Permission System

#### New Permissions

```php
// database/seeders/PermissionSeeder.php

$permissions = [
    // Existing permissions
    'view users',
    'create users',
    'edit users',
    'delete users',
    'manage user roles',
    
    // New soft delete permissions
    'restore users',      // Restore soft-deleted users
    'force delete users', // Permanently delete users (Super Admin only)
];
```

#### Role Assignments

```php
// tests/Traits/WithRoles.php

protected function createAdminRole(): void
{
    $role = Role::firstOrCreate(['name' => 'Admin']);
    
    $permissions = [
        'view dashboard',
        'view users',
        'create users',
        'edit users',
        'delete users',
        'restore users',  // Admins can restore users
        'manage user roles',
        // Note: 'force delete users' is Super Admin only
    ];
    
    $role->syncPermissions($permissions);
}

protected function createSuperAdminRole(): void
{
    $role = Role::firstOrCreate(['name' => 'Super Admin']);
    
    // Super Admin gets all permissions including force delete
    $role->syncPermissions(Permission::all());
}
```

### Frontend Implementation

#### Trashed Users Component

```tsx
// resources/js/pages/admin/users/trashed.tsx

import { CanAccess } from '@/components/CanAccess';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';

interface TrashedUser {
    id: number;
    name: string;
    email: string;
    deleted_at: string;
    role_names: string[];
}

export default function TrashedUsers({ users, stats }) {
    const handleRestoreUser = (userId: number) => {
        if (confirm('Are you sure you want to restore this user?')) {
            router.patch(`/admin/users/${userId}/restore`);
        }
    };

    const handleForceDeleteUser = (userId: number) => {
        if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone!')) {
            router.delete(`/admin/users/${userId}/force-delete`);
        }
    };

    return (
        <div>
            <h1>Deleted Users</h1>
            
            {users.data.map((user: TrashedUser) => (
                <div key={user.id} className="user-row deleted">
                    <div className="user-info">
                        <h3>{user.name}</h3>
                        <p>{user.email}</p>
                        <p>Deleted: {new Date(user.deleted_at).toLocaleDateString()}</p>
                    </div>
                    
                    <div className="actions">
                        <CanAccess permission="restore users">
                            <Button 
                                variant="outline" 
                                onClick={() => handleRestoreUser(user.id)}
                                className="text-green-600"
                            >
                                Restore
                            </Button>
                        </CanAccess>
                        
                        <CanAccess permission="force delete users">
                            <Button 
                                variant="destructive" 
                                onClick={() => handleForceDeleteUser(user.id)}
                            >
                                Permanently Delete
                            </Button>
                        </CanAccess>
                    </div>
                </div>
            ))}
        </div>
    );
}
```

#### Updated Users Index

```tsx
// resources/js/pages/admin/users/index.tsx

export default function UsersIndex({ users, stats }) {
    const handleDeleteUser = (userId: number) => {
        if (confirm('Are you sure you want to delete this user? The user will be moved to the deleted users list and can be restored later.')) {
            router.delete(`/admin/users/${userId}`);
        }
    };

    return (
        <div>
            <div className="header">
                <h1>User Management</h1>
                <div className="actions">
                    <CanAccess permission="view users">
                        <Button variant="outline" asChild>
                            <Link href="/admin/users/trashed">
                                Deleted Users
                            </Link>
                        </Button>
                    </CanAccess>
                    
                    <CanAccess permission="create users">
                        <Button asChild>
                            <Link href="/admin/users/create">
                                Add User
                            </Link>
                        </Button>
                    </CanAccess>
                </div>
            </div>
            
            {/* User list with updated delete behavior */}
        </div>
    );
}
```

### Testing Implementation

#### Soft Delete Tests

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class UserSoftDeleteTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_user_is_soft_deleted_not_hard_deleted()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/users/{$user->id}");

        $response->assertRedirect('/admin/users');
        
        // User should be soft deleted, not hard deleted
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_super_admin_can_view_trashed_users()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();
        $user->delete(); // Soft delete

        $response = $this->actingAs($superAdmin)->get('/admin/users/trashed');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->component('admin/users/trashed')
                ->has('users')
                ->has('stats')
        );
    }

    public function test_super_admin_can_restore_user()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();
        $user->delete(); // Soft delete

        $response = $this->actingAs($superAdmin)
            ->patch("/admin/users/{$user->id}/restore");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNull($user->deleted_at);
    }

    public function test_super_admin_can_force_delete_user()
    {
        $superAdmin = $this->createSuperAdmin();
        $user = $this->createEditor();
        $user->delete(); // Soft delete first

        $response = $this->actingAs($superAdmin)
            ->delete("/admin/users/{$user->id}/force-delete");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_force_delete_user()
    {
        $admin = $this->createAdmin();
        $user = $this->createEditor();
        $user->delete(); // Soft delete first

        $response = $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}/force-delete");

        // Admin doesn't have force delete permission
        $response->assertStatus(403);

        // User should still exist (soft deleted)
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
```

### Security Considerations

#### Permission-Based Access

```php
// Only users with appropriate permissions can perform soft delete operations
$this->middleware('permission:delete users')->only(['destroy']);
$this->middleware('permission:restore users')->only(['restore']);
$this->middleware('permission:force delete users')->only(['forceDelete']);
```

#### Business Logic Protection

```php
// Prevent deletion of last Super Admin
if ($user->hasRole('Super Admin')) {
    $superAdminCount = User::role('Super Admin')->count();
    if ($superAdminCount <= 1) {
        return redirect()->back()->with('error', 'Cannot delete the last Super Admin user.');
    }
}

// Prevent self-deletion
if ($user->id === auth()->id()) {
    return redirect()->back()->with('error', 'You cannot delete your own account.');
}
```

### Performance Considerations

#### Database Indexing

```sql
-- Index on deleted_at for better query performance
CREATE INDEX idx_users_deleted_at ON users(deleted_at);

-- Composite indexes for role-based queries on soft-deleted users
CREATE INDEX idx_users_deleted_roles ON users(deleted_at, id);
```

#### Query Optimization

```php
// Efficient queries for soft-deleted users
$trashedUsers = User::onlyTrashed()
    ->with(['roles:id,name']) // Only load necessary role data
    ->select(['id', 'name', 'email', 'deleted_at']) // Only select needed columns
    ->orderBy('deleted_at', 'desc')
    ->paginate(20);

// Statistics queries with proper indexing
$stats = [
    'total_deleted' => User::onlyTrashed()->count(),
    'deleted_administrators' => User::onlyTrashed()
        ->whereHas('roles', fn($q) => $q->whereIn('name', ['Super Admin', 'Admin']))
        ->count(),
];
```

### Best Practices

#### Data Integrity

1. **Always use soft deletes** for user records to maintain referential integrity
2. **Preserve relationships** - soft-deleted users maintain their role and permission associations
3. **Audit trail** - deletion timestamps provide accountability
4. **Recovery options** - users can be restored with all their data intact

#### Security

1. **Permission-based access** - different permissions for delete, restore, and force delete
2. **Role hierarchy** - only Super Admins can permanently delete users
3. **Business logic protection** - prevent deletion of critical users (last Super Admin, self)
4. **Confirmation dialogs** - require explicit confirmation for destructive actions

#### User Experience

1. **Clear messaging** - inform users that deletion is reversible
2. **Visual indicators** - distinguish soft-deleted users in the interface
3. **Easy recovery** - simple restore process for administrators
4. **Comprehensive statistics** - show counts of deleted users by role

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

For comprehensive testing procedures, regression testing workflow, and CI/CD integration, see the [Testing Strategy](Testing-Strategy.md) guide.

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
