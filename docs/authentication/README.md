# Multi-Role User Authentication System

## Overview

This application implements a comprehensive role-based access control (RBAC) system using Spatie Laravel Permission package, providing granular permissions and role management for a modern web application.

## Architecture

### Backend Components
- **Package**: Spatie Laravel Permission v6.21
- **Model**: `User` model with `HasRoles` trait
- **Middleware**: `HandleInertiaRequests` for data sharing
- **Database**: 4 tables (roles, permissions, role_has_permissions, model_has_roles)

### Frontend Integration
- **Framework**: Inertia.js with React/TypeScript
- **Shared Data**: User permissions and roles available in all components
- **Helper Functions**: `can()`, `hasRole()`, `hasPermissionTo()` available globally

## User Roles

### 1. Super Admin
- **Description**: Full system access with all permissions
- **Use Case**: System administrators, developers
- **Permissions**: All available permissions

### 2. Admin
- **Description**: High-level administrative access
- **Use Case**: Site administrators, content managers
- **Permissions**: All except system-level permissions

### 3. Editor
- **Description**: Content management and editing capabilities
- **Use Case**: Content editors, moderators
- **Permissions**: Content and media management

### 4. Author
- **Description**: Content creation with limited management
- **Use Case**: Content creators, bloggers
- **Permissions**: Own content management and media upload

### 5. Subscriber
- **Description**: Basic read-only access
- **Use Case**: Regular users, readers
- **Permissions**: Dashboard access only

## Permission Categories

### User Management
- `view users` - View user list and profiles
- `create users` - Create new user accounts
- `edit users` - Modify user information
- `delete users` - Remove user accounts
- `manage user roles` - Assign/remove user roles

### Content Management
- `view posts` - View published content
- `create posts` - Create new content
- `edit posts` - Modify any content
- `delete posts` - Remove any content
- `publish posts` - Publish content
- `edit own posts` - Modify own content only
- `delete own posts` - Remove own content only

### System Administration
- `manage settings` - Access system settings
- `manage roles` - Create/modify roles
- `manage permissions` - Assign permissions to roles

### Media Management
- `upload media` - Upload files
- `manage media` - Organize media library
- `delete media` - Remove media files

### Comment Management
- `view comments` - View user comments
- `moderate comments` - Approve/reject comments
- `delete comments` - Remove comments

## Implementation Details

### Database Schema
```sql
-- Core tables created by Spatie Laravel Permission
roles                    -- Role definitions
permissions              -- Permission definitions
role_has_permissions     -- Role-permission relationships
model_has_roles          -- User-role assignments
model_has_permissions    -- Direct user permissions
```

### Middleware Integration
The `HandleInertiaRequests` middleware automatically loads and shares user permissions with the frontend:

```php
// User data shared with frontend
$authUser = [
    'role_names' => $user->roles->pluck('name')->toArray(),
    'permission_names' => $user->getAllPermissions()->pluck('name')->toArray(),
    'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
    'is_content_creator' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']),
    'can' => function (string $permission) use ($user) {
        return $user->hasPermissionTo($permission);
    },
    // ... more helper functions
];
```

### Frontend Data Structure
```typescript
interface AuthUser {
  id: number;
  name: string;
  email: string;
  role_names: string[];
  permission_names: string[];
  is_admin: boolean;
  is_content_creator: boolean;
  can: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
  hasPermissionTo: (permission: string) => boolean;
}
```

## Security Considerations

### Server-Side Validation
- All permission checks are validated server-side
- Frontend permissions are for UI display only
- Database constraints prevent unauthorized access
- Role assignments require proper authorization

### Best Practices
- Always validate permissions on the server
- Use middleware for route protection
- Implement proper error handling
- Log permission-related actions
- Regular security audits

## Performance Optimization

### Database Optimization
- Permission caching enabled by default
- Eager loading of roles and permissions
- Indexed foreign keys for fast lookups
- Optimized queries for permission checks

### Frontend Optimization
- Computed properties reduce redundant checks
- Permission data shared globally via Inertia
- Minimal re-renders with React optimization
- Efficient permission checking functions

## Quick Reference

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

// Single permission
Route::middleware(['auth', 'permission:create posts'])->group(...);
```

### Environment Variables
```env
# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Social Login (example for Google)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback
```

## Related Documentation
- [API Documentation](api.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Deployment Guide](deployment.md)
- [Testing Guide](../testing/authentication-tests.md) 