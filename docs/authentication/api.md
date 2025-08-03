# Authentication API Documentation

## Overview

This document describes the API endpoints and data structures for the Multi-Role User Authentication system.

## User Data Structure

### Authenticated User Object
When a user is authenticated, the following data structure is shared with the frontend:

```json
{
  "auth": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2024-01-01T00:00:00.000000Z",
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z",
      "role_names": ["Admin"],
      "permission_names": ["view dashboard", "create posts", "edit posts"],
      "is_admin": true,
      "is_content_manager": true,
      "is_content_creator": true,
      "can": "function",
      "hasRole": "function",
      "hasAnyRole": "function",
      "hasPermissionTo": "function",
      "hasAnyPermission": "function"
    }
  }
}
```

### Computed Properties

| Property | Type | Description |
|----------|------|-------------|
| `role_names` | string[] | Array of role names assigned to the user |
| `permission_names` | string[] | Array of all permissions the user has |
| `is_admin` | boolean | True if user has Super Admin or Admin role |
| `is_content_manager` | boolean | True if user can manage content |
| `is_content_creator` | boolean | True if user can create content |

### Helper Functions

| Function | Parameters | Returns | Description |
|----------|------------|---------|-------------|
| `can(permission)` | string | boolean | Check if user has specific permission |
| `hasRole(role)` | string | boolean | Check if user has specific role |
| `hasAnyRole(roles)` | string[] | boolean | Check if user has any of the specified roles |
| `hasPermissionTo(permission)` | string | boolean | Alias for `can()` function |
| `hasAnyPermission(permissions)` | string[] | boolean | Check if user has any of the specified permissions |

## Authentication Endpoints

### Login
```http
POST /login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "remember": false
}
```

**Response:**
- `302` - Redirect to intended URL or dashboard
- `422` - Validation errors

### Logout
```http
POST /logout
```

**Response:**
- `302` - Redirect to home page

### Register
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

**Response:**
- `302` - Redirect to intended URL or dashboard
- `422` - Validation errors

## Permission Checking Examples

### Backend Examples

#### Controller Method
```php
public function createPost(Request $request)
{
    if (!$request->user()->can('create posts')) {
        abort(403, 'Unauthorized action.');
    }
    
    // Create post logic
}
```

#### Blade Template
```php
@if(auth()->user()->can('edit posts'))
    <a href="{{ route('posts.edit', $post) }}">Edit Post</a>
@endif
```

#### Middleware
```php
Route::middleware(['permission:manage users'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

### Frontend Examples

#### React Component
```typescript
import { usePage } from '@inertiajs/react';

function PostActions({ post }) {
  const { auth } = usePage<SharedData>().props;
  
  return (
    <div>
      {auth.user.can('edit posts') && (
        <button>Edit Post</button>
      )}
      
      {auth.user.hasRole('Admin') && (
        <button>Delete Post</button>
      )}
      
      {auth.user.is_admin && (
        <AdminPanel />
      )}
    </div>
  );
}
```

#### Conditional Rendering
```typescript
// Check multiple permissions
if (auth.user.hasAnyPermission(['create posts', 'edit posts'])) {
  // Show content management options
}

// Check multiple roles
if (auth.user.hasAnyRole(['Admin', 'Editor'])) {
  // Show management interface
}
```

## Error Handling

### Permission Denied
When a user lacks required permissions, the system returns:

```json
{
  "message": "Unauthorized action.",
  "status": 403
}
```

### Role Assignment Errors
```json
{
  "message": "Role assignment failed.",
  "errors": {
    "role": ["Invalid role specified."]
  }
}
```

## Rate Limiting

### Login Attempts
- Maximum 5 attempts per minute
- Lockout after 5 failed attempts
- 60-second lockout period

### Password Reset
- Maximum 6 attempts per minute
- 60-second throttle between requests

## Security Headers

The application includes the following security headers:

```http
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
```

## Testing API Endpoints

### Using PHPUnit
```php
public function test_user_can_access_admin_panel()
{
    $admin = $this->createAdmin();
    
    $response = $this->actingAs($admin)
        ->get('/admin');
    
    $response->assertOk();
}
```

### Using Postman/Insomnia
1. Set up authentication headers
2. Include CSRF token for POST requests
3. Test permission-based endpoints
4. Verify response status codes

## Related Documentation
- [Authentication Overview](README.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Testing Guide](../testing/authentication-tests.md) 