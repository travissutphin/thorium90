# Permissions Development Guide

## Overview

This guide provides comprehensive information for developers working with the Multi-Role User Authentication system, including best practices, common patterns, and advanced usage scenarios.

## Core Concepts

### Roles vs Permissions
- **Roles**: Collections of permissions that define user capabilities
- **Permissions**: Granular actions that users can perform
- **Inheritance**: Users inherit permissions from their assigned roles

### Permission Structure
```php
// Permission format: action resource
'create posts'     // Can create posts
'edit own posts'   // Can edit their own posts
'delete users'     // Can delete user accounts
'manage settings'  // Can access system settings
```

## Working with Roles and Permissions

### Creating Roles
```php
use Spatie\Permission\Models\Role;

// Create a new role
$editorRole = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

// Assign permissions to role
$editorRole->givePermissionTo([
    'view posts',
    'create posts',
    'edit posts',
    'publish posts'
]);
```

### Creating Permissions
```php
use Spatie\Permission\Models\Permission;

// Create individual permissions
Permission::create(['name' => 'manage comments', 'guard_name' => 'web']);

// Create multiple permissions
$permissions = [
    'view comments',
    'moderate comments',
    'delete comments'
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission, 'guard_name' => 'web']);
}
```

### Assigning Roles to Users
```php
// Assign a single role
$user->assignRole('Editor');

// Assign multiple roles
$user->assignRole(['Editor', 'Author']);

// Remove a role
$user->removeRole('Editor');

// Sync roles (removes all existing and assigns new)
$user->syncRoles(['Admin', 'Editor']);
```

### Checking Permissions
```php
// Check if user has specific permission
if ($user->can('create posts')) {
    // User can create posts
}

// Check if user has any of the permissions
if ($user->hasAnyPermission(['create posts', 'edit posts'])) {
    // User can create or edit posts
}

// Check if user has all permissions
if ($user->hasAllPermissions(['create posts', 'edit posts'])) {
    // User can create AND edit posts
}
```

### Checking Roles
```php
// Check if user has specific role
if ($user->hasRole('Admin')) {
    // User is an admin
}

// Check if user has any of the roles
if ($user->hasAnyRole(['Admin', 'Editor'])) {
    // User is admin or editor
}

// Check if user has all roles
if ($user->hasAllRoles(['Admin', 'Editor'])) {
    // User has both admin and editor roles
}
```

## Middleware Integration

### Route Protection
```php
// Protect routes with role middleware
Route::middleware(['role:Admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Protect routes with permission middleware
Route::middleware(['permission:manage users'])->group(function () {
    Route::resource('users', UserController::class);
});

// Protect routes with multiple permissions
Route::middleware(['permission:create posts|edit posts'])->group(function () {
    Route::resource('posts', PostController::class);
});
```

### Custom Middleware
```php
// app/Http/Middleware/EnsureUserHasRole.php
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
```

## Controller Patterns

### Permission Checking in Controllers
```php
class PostController extends Controller
{
    public function store(Request $request)
    {
        // Check permission before action
        if (!$request->user()->can('create posts')) {
            abort(403, 'You do not have permission to create posts.');
        }

        // Create post logic
        $post = Post::create($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user();

        // Check if user can edit any post or only their own
        if (!$user->can('edit posts') && 
            (!$user->can('edit own posts') || $post->user_id !== $user->id)) {
            abort(403, 'You do not have permission to edit this post.');
        }

        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }
}
```

### Resource Controllers with Permissions
```php
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    public function index()
    {
        $users = User::with('roles')->paginate(20);
        return Inertia::render('Users/Index', compact('users'));
    }
}
```

## Frontend Integration

### React Components with Permissions
```typescript
import { usePage } from '@inertiajs/react';

interface PostActionsProps {
  post: Post;
}

function PostActions({ post }: PostActionsProps) {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  return (
    <div className="flex gap-2">
      {user.can('edit posts') && (
        <Link href={`/posts/${post.id}/edit`}>
          <Button>Edit Post</Button>
        </Link>
      )}
      
      {(user.can('delete posts') || 
        (user.can('delete own posts') && post.user_id === user.id)) && (
        <Button variant="destructive" onClick={() => deletePost(post.id)}>
          Delete Post
        </Button>
      )}
      
      {user.hasRole('Admin') && (
        <Button variant="outline" onClick={() => moderatePost(post.id)}>
          Moderate
        </Button>
      )}
    </div>
  );
}
```

### Conditional Rendering Patterns
```typescript
// Show/hide based on permissions
{user.can('create posts') && <CreatePostButton />}

// Show different content based on roles
{user.hasRole('Admin') ? <AdminPanel /> : <UserPanel />}

// Multiple permission check
{user.hasAnyPermission(['create posts', 'edit posts']) && (
  <ContentManagementPanel />
)}

// Complex permission logic
{user.can('delete posts') || 
 (user.can('delete own posts') && post.author_id === user.id) ? (
  <DeleteButton />
) : null}
```

### Permission Hooks
```typescript
// Custom hook for permission checking
function usePermissions() {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  return {
    can: (permission: string) => user.can(permission),
    hasRole: (role: string) => user.hasRole(role),
    hasAnyRole: (roles: string[]) => user.hasAnyRole(roles),
    hasPermission: (permission: string) => user.hasPermissionTo(permission),
    isAdmin: user.is_admin,
    isContentCreator: user.is_content_creator,
  };
}

// Usage in components
function AdminPanel() {
  const { can, isAdmin } = usePermissions();

  if (!isAdmin) return null;

  return (
    <div>
      {can('manage users') && <UserManagement />}
      {can('manage settings') && <SystemSettings />}
    </div>
  );
}
```

## Advanced Patterns

### Dynamic Permission Checking
```php
// Check permissions based on context
public function updatePost(Request $request, Post $post)
{
    $user = $request->user();
    
    // Determine required permission based on post status
    $requiredPermission = $post->is_published ? 'edit published posts' : 'edit posts';
    
    if (!$user->can($requiredPermission)) {
        abort(403, 'Insufficient permissions.');
    }
    
    // Update logic
}
```

### Permission Inheritance
```php
// Create hierarchical roles
$superAdmin = Role::create(['name' => 'Super Admin']);
$admin = Role::create(['name' => 'Admin']);
$editor = Role::create(['name' => 'Editor']);

// Super Admin gets all permissions
$superAdmin->givePermissionTo(Permission::all());

// Admin gets most permissions except system-level
$admin->givePermissionTo([
    'view users', 'create users', 'edit users', 'delete users',
    'view posts', 'create posts', 'edit posts', 'delete posts',
    // ... other permissions
]);

// Editor gets content-related permissions
$editor->givePermissionTo([
    'view posts', 'create posts', 'edit posts', 'publish posts'
]);
```

### Custom Permission Logic
```php
// Extend User model with custom permission methods
class User extends Authenticatable
{
    use HasRoles;

    public function canManagePost(Post $post): bool
    {
        return $this->can('manage all posts') || 
               ($this->can('manage own posts') && $post->user_id === $this->id);
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->hasAnyRole(['Super Admin', 'Admin']) && 
               $this->can('view admin panel');
    }
}
```

## Testing Patterns

### Unit Testing Permissions
```php
class UserPermissionTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_user_can_edit_own_posts()
    {
        $user = $this->createAuthor();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('edit own posts'));
        $this->assertTrue($user->canManagePost($post));
    }

    public function test_user_cannot_edit_others_posts()
    {
        $user = $this->createAuthor();
        $otherPost = Post::factory()->create();

        $this->assertFalse($user->canManagePost($otherPost));
    }
}
```

### Feature Testing with Permissions
```php
class PostManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_editor_can_create_posts()
    {
        $editor = $this->createEditor();

        $response = $this->actingAs($editor)
            ->post('/posts', [
                'title' => 'Test Post',
                'content' => 'Test content'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
    }

    public function test_subscriber_cannot_create_posts()
    {
        $subscriber = $this->createSubscriber();

        $response = $this->actingAs($subscriber)
            ->post('/posts', [
                'title' => 'Test Post',
                'content' => 'Test content'
            ]);

        $response->assertForbidden();
    }
}
```

## Performance Optimization

### Eager Loading
```php
// Load roles and permissions efficiently
$users = User::with(['roles.permissions', 'permissions'])->get();

// Check permissions without additional queries
foreach ($users as $user) {
    if ($user->can('create posts')) {
        // Permission check uses loaded data
    }
}
```

### Caching Permissions
```php
// Cache permission checks for frequently accessed data
$userPermissions = Cache::remember("user_permissions_{$user->id}", 3600, function () use ($user) {
    return $user->getAllPermissions()->pluck('name')->toArray();
});
```

### Database Optimization
```sql
-- Ensure proper indexes for permission tables
CREATE INDEX idx_model_has_roles_model_id ON model_has_roles(model_id);
CREATE INDEX idx_model_has_permissions_model_id ON model_has_permissions(model_id);
CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);
```

## Best Practices

### 1. Permission Naming
- Use consistent naming conventions: `action resource`
- Be specific: `edit own posts` vs `edit posts`
- Use lowercase with spaces: `manage user roles`

### 2. Role Design
- Keep roles focused and specific
- Avoid role proliferation
- Use permissions for granular control

### 3. Security
- Always validate permissions server-side
- Use middleware for route protection
- Log permission-related actions

### 4. Performance
- Cache permission checks when appropriate
- Use eager loading for related data
- Optimize database queries

### 5. Testing
- Test all permission scenarios
- Use factories for consistent test data
- Test both positive and negative cases

## Related Documentation
- [Authentication Overview](../authentication/README.md)
- [API Documentation](../authentication/api.md)
- [Testing Guide](../testing/authentication-tests.md) 