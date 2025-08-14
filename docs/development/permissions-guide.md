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
'create pages'     // Can create pages
'edit own pages'   // Can edit their own pages
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
    'view pages',
    'create pages',
    'edit pages',
    'publish pages'
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
if ($user->can('create pages')) {
    // User can create pages
}

// Check if user has any of the permissions
if ($user->hasAnyPermission(['create pages', 'edit pages'])) {
    // User can create or edit pages
}

// Check if user has all permissions
if ($user->hasAllPermissions(['create pages', 'edit pages'])) {
    // User can create AND edit pages
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
Route::middleware(['permission:create pages|edit pages'])->group(function () {
    Route::resource('pages', PageController::class);
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
class PageController extends Controller
{
    public function store(Request $request)
    {
        // Check permission before action
        if (!$request->user()->can('create pages')) {
            abort(403, 'You do not have permission to create pages.');
        }

        // Create page logic
        $page = Page::create($request->validated());

        return redirect()->route('pages.show', $page);
    }

    public function update(Request $request, Page $page)
    {
        $user = $request->user();

        // Check if user can edit any page or only their own
        if (!$user->can('edit pages') && 
            (!$user->can('edit own pages') || $page->user_id !== $user->id)) {
            abort(403, 'You do not have permission to edit this page.');
        }

        $page->update($request->validated());

        return redirect()->route('pages.show', $page);
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

function PageActions({ page }: PageActionsProps) {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  return (
    <div className="flex gap-2">
      {user.can('edit pages') && (
        <Link href={`/pages/${page.id}/edit`}>
          <Button>Edit Page</Button>
        </Link>
      )}
      
      {(user.can('delete pages') || 
        (user.can('delete own pages') && page.user_id === user.id)) && (
        <Button variant="destructive" onClick={() => deletePage(page.id)}>
          Delete Page
        </Button>
      )}
      
      {user.hasRole('Admin') && (
        <Button variant="outline" onClick={() => moderatePage(page.id)}>
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
{user.can('create pages') && <CreatePageButton />}

// Show different content based on roles
{user.hasRole('Admin') ? <AdminPanel /> : <UserPanel />}

// Multiple permission check
{user.hasAnyPermission(['create pages', 'edit pages']) && (
  <ContentManagementPanel />
)}

// Complex permission logic
{user.can('delete pages') || 
 (user.can('delete own pages') && page.author_id === user.id) ? (
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
public function updatePage(Request $request, Page $page)
{
    $user = $request->user();
    
    // Determine required permission based on page status
    $requiredPermission = $page->is_published ? 'edit published pages' : 'edit pages';
    
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
    'view pages', 'create pages', 'edit pages', 'delete pages',
    // ... other permissions
]);

// Editor gets content-related permissions
$editor->givePermissionTo([
    'view pages', 'create pages', 'edit pages', 'publish pages'
]);
```

### Custom Permission Logic
```php
// Extend User model with custom permission methods
class User extends Authenticatable
{
    use HasRoles;

    public function canManagePage(Page $page): bool
    {
        return $this->can('manage all pages') || 
               ($this->can('manage own pages') && $page->user_id === $this->id);
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

    public function test_user_can_edit_own_pages()
    {
        $user = $this->createAuthor();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('edit own pages'));
        $this->assertTrue($user->canManagePage($page));
    }

    public function test_user_cannot_edit_others_pages()
    {
        $user = $this->createAuthor();
        $otherPage = Page::factory()->create();

        $this->assertFalse($user->canManagePage($otherPage));
    }
}
```

### Feature Testing with Permissions
```php
class PageManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_editor_can_create_pages()
    {
        $editor = $this->createEditor();

        $response = $this->actingAs($editor)
            ->post('/pages', [
                'title' => 'Test Page',
                'content' => 'Test content'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', ['title' => 'Test Page']);
    }

    public function test_subscriber_cannot_create_pages()
    {
        $subscriber = $this->createSubscriber();

        $response = $this->actingAs($subscriber)
            ->post('/pages', [
                'title' => 'Test Page',
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
    if ($user->can('create pages')) {
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
- Be specific: `edit own pages` vs `edit pages`
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
