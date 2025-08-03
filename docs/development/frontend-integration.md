# Frontend Integration Guide

## Overview

This guide covers how to integrate the Multi-Role User Authentication system with the React frontend using Inertia.js, including best practices for permission checking, conditional rendering, and component patterns.

## User Data Structure

### Authentication Context
The user's authentication data is automatically shared with all React components via Inertia.js:

```typescript
interface SharedData {
  auth: {
    user: AuthUser | null;
  };
}

interface AuthUser {
  id: number;
  name: string;
  email: string;
  role_names: string[];
  permission_names: string[];
  is_admin: boolean;
  is_content_manager: boolean;
  is_content_creator: boolean;
  can: (permission: string) => boolean;
  hasRole: (role: string) => boolean;
  hasAnyRole: (roles: string[]) => boolean;
  hasPermissionTo: (permission: string) => boolean;
  hasAnyPermission: (permissions: string[]) => boolean;
}
```

### Accessing User Data
```typescript
import { usePage } from '@inertiajs/react';

function MyComponent() {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  if (!user) {
    return <div>Please log in</div>;
  }

  return (
    <div>
      <h1>Welcome, {user.name}!</h1>
      <p>Roles: {user.role_names.join(', ')}</p>
    </div>
  );
}
```

## Permission Checking Patterns

### Basic Permission Checks
```typescript
function PostActions({ post }: { post: Post }) {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  return (
    <div className="flex gap-2">
      {user.can('edit posts') && (
        <Button>Edit Post</Button>
      )}
      
      {user.can('delete posts') && (
        <Button variant="destructive">Delete Post</Button>
      )}
      
      {user.hasRole('Admin') && (
        <Button variant="outline">Moderate</Button>
      )}
    </div>
  );
}
```

### Multiple Permission Checks
```typescript
function ContentManagementPanel() {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  // Check if user has any of the specified permissions
  if (!user.hasAnyPermission(['create posts', 'edit posts', 'delete posts'])) {
    return null;
  }

  return (
    <div className="p-4 border rounded">
      <h3>Content Management</h3>
      {user.can('create posts') && <CreatePostButton />}
      {user.can('edit posts') && <EditPostsButton />}
      {user.can('delete posts') && <DeletePostsButton />}
    </div>
  );
}
```

### Complex Permission Logic
```typescript
function PostManagement({ post }: { post: Post }) {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  const canEdit = user.can('edit posts') || 
                  (user.can('edit own posts') && post.author_id === user.id);

  const canDelete = user.can('delete posts') || 
                    (user.can('delete own posts') && post.author_id === user.id);

  return (
    <div className="flex gap-2">
      {canEdit && <EditButton post={post} />}
      {canDelete && <DeleteButton post={post} />}
    </div>
  );
}
```

## Custom Hooks

### Permission Hook
```typescript
// hooks/usePermissions.ts
import { usePage } from '@inertiajs/react';

export function usePermissions() {
  const { auth } = usePage<SharedData>().props;
  const { user } = auth;

  if (!user) {
    return {
      can: () => false,
      hasRole: () => false,
      hasAnyRole: () => false,
      isAdmin: false,
      isContentCreator: false,
    };
  }

  return {
    can: (permission: string) => user.can(permission),
    hasRole: (role: string) => user.hasRole(role),
    hasAnyRole: (roles: string[]) => user.hasAnyRole(roles),
    hasPermission: (permission: string) => user.hasPermissionTo(permission),
    hasAnyPermission: (permissions: string[]) => user.hasAnyPermission(permissions),
    isAdmin: user.is_admin,
    isContentCreator: user.is_content_creator,
    isContentManager: user.is_content_manager,
  };
}
```

### Usage with Custom Hook
```typescript
function AdminPanel() {
  const { can, isAdmin, hasRole } = usePermissions();

  if (!isAdmin) {
    return null;
  }

  return (
    <div className="admin-panel">
      <h2>Admin Panel</h2>
      
      {can('manage users') && (
        <div>
          <h3>User Management</h3>
          <UserManagementComponent />
        </div>
      )}
      
      {can('manage settings') && (
        <div>
          <h3>System Settings</h3>
          <SystemSettingsComponent />
        </div>
      )}
      
      {hasRole('Super Admin') && (
        <div>
          <h3>Super Admin Tools</h3>
          <SuperAdminTools />
        </div>
      )}
    </div>
  );
}
```

## Component Patterns

### Protected Components
```typescript
interface ProtectedComponentProps {
  permission?: string;
  role?: string;
  fallback?: React.ReactNode;
  children: React.ReactNode;
}

function ProtectedComponent({ 
  permission, 
  role, 
  fallback = null, 
  children 
}: ProtectedComponentProps) {
  const { can, hasRole } = usePermissions();

  if (permission && !can(permission)) {
    return <>{fallback}</>;
  }

  if (role && !hasRole(role)) {
    return <>{fallback}</>;
  }

  return <>{children}</>;
}

// Usage
function MyPage() {
  return (
    <div>
      <h1>My Page</h1>
      
      <ProtectedComponent permission="create posts">
        <CreatePostForm />
      </ProtectedComponent>
      
      <ProtectedComponent role="Admin" fallback={<p>Admin access required</p>}>
        <AdminTools />
      </ProtectedComponent>
    </div>
  );
}
```

### Conditional Navigation
```typescript
function Navigation() {
  const { can, hasRole, isAdmin } = usePermissions();

  return (
    <nav className="flex gap-4">
      <Link href="/dashboard">Dashboard</Link>
      
      {can('view posts') && (
        <Link href="/posts">Posts</Link>
      )}
      
      {can('create posts') && (
        <Link href="/posts/create">Create Post</Link>
      )}
      
      {isAdmin && (
        <Link href="/admin">Admin Panel</Link>
      )}
      
      {hasRole('Super Admin') && (
        <Link href="/super-admin">Super Admin</Link>
      )}
    </nav>
  );
}
```

### Role-Based Layouts
```typescript
function AppLayout({ children }: { children: React.ReactNode }) {
  const { isAdmin, isContentCreator } = usePermissions();

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />
      
      <div className="flex">
        <Sidebar />
        
        <main className="flex-1 p-6">
          {children}
        </main>
        
        {isAdmin && <AdminSidebar />}
        {isContentCreator && <ContentCreatorPanel />}
      </div>
      
      <Footer />
    </div>
  );
}
```

## Form Handling with Permissions

### Conditional Form Fields
```typescript
function PostForm({ post }: { post?: Post }) {
  const { can, hasRole } = usePermissions();
  const isEditing = !!post;

  return (
    <form onSubmit={handleSubmit}>
      <div className="space-y-4">
        <input
          type="text"
          name="title"
          placeholder="Post title"
          required
        />
        
        <textarea
          name="content"
          placeholder="Post content"
          required
        />
        
        {/* Only show publish option to users with publish permission */}
        {can('publish posts') && (
          <div>
            <label>
              <input
                type="checkbox"
                name="is_published"
                defaultChecked={post?.is_published}
              />
              Publish immediately
            </label>
          </div>
        )}
        
        {/* Only show author selection to admins */}
        {hasRole('Admin') && (
          <select name="author_id">
            <option value="">Select Author</option>
            {/* Author options */}
          </select>
        )}
        
        <button type="submit">
          {isEditing ? 'Update Post' : 'Create Post'}
        </button>
      </div>
    </form>
  );
}
```

### Permission-Based Validation
```typescript
function useFormValidation() {
  const { can } = usePermissions();

  const validateForm = (data: FormData) => {
    const errors: Record<string, string> = {};

    // Basic validation
    if (!data.title) {
      errors.title = 'Title is required';
    }

    if (!data.content) {
      errors.content = 'Content is required';
    }

    // Permission-based validation
    if (data.is_published && !can('publish posts')) {
      errors.is_published = 'You do not have permission to publish posts';
    }

    if (data.author_id && !can('assign authors')) {
      errors.author_id = 'You do not have permission to assign authors';
    }

    return errors;
  };

  return { validateForm };
}
```

## Error Handling

### Permission Denied Components
```typescript
function PermissionDenied({ 
  permission, 
  role, 
  message 
}: { 
  permission?: string; 
  role?: string; 
  message?: string; 
}) {
  const defaultMessage = permission 
    ? `You need the "${permission}" permission to access this feature.`
    : role 
    ? `You need the "${role}" role to access this feature.`
    : 'You do not have permission to access this feature.';

  return (
    <div className="p-4 bg-red-50 border border-red-200 rounded">
      <div className="flex">
        <div className="flex-shrink-0">
          <LockIcon className="h-5 w-5 text-red-400" />
        </div>
        <div className="ml-3">
          <h3 className="text-sm font-medium text-red-800">
            Access Denied
          </h3>
          <p className="mt-1 text-sm text-red-700">
            {message || defaultMessage}
          </p>
        </div>
      </div>
    </div>
  );
}

// Usage
function AdminPage() {
  const { isAdmin } = usePermissions();

  if (!isAdmin) {
    return (
      <PermissionDenied 
        role="Admin" 
        message="This page is only accessible to administrators."
      />
    );
  }

  return <AdminContent />;
}
```

### Loading States
```typescript
function useAuthLoading() {
  const { auth } = usePage<SharedData>().props;
  
  return {
    isLoading: auth.user === undefined,
    isAuthenticated: !!auth.user,
    user: auth.user,
  };
}

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isLoading, isAuthenticated } = useAuthLoading();

  if (isLoading) {
    return <LoadingSpinner />;
  }

  if (!isAuthenticated) {
    return <LoginPrompt />;
  }

  return <>{children}</>;
}
```

## Performance Optimization

### Memoization
```typescript
import { useMemo } from 'react';

function UserPermissions() {
  const { user } = usePage<SharedData>().props.auth;
  
  const permissions = useMemo(() => {
    if (!user) return [];
    return user.permission_names;
  }, [user?.permission_names]);

  const hasAdminAccess = useMemo(() => {
    return user?.is_admin ?? false;
  }, [user?.is_admin]);

  return (
    <div>
      <h3>Your Permissions</h3>
      <ul>
        {permissions.map(permission => (
          <li key={permission}>{permission}</li>
        ))}
      </ul>
      
      {hasAdminAccess && <AdminIndicator />}
    </div>
  );
}
```

### Conditional Rendering Optimization
```typescript
function OptimizedComponent() {
  const { can, hasRole } = usePermissions();

  // Early return for better performance
  if (!can('view posts')) {
    return <PermissionDenied permission="view posts" />;
  }

  return (
    <div>
      <h1>Posts</h1>
      
      {/* Render expensive components only when needed */}
      {can('create posts') && <CreatePostButton />}
      {hasRole('Admin') && <AdminTools />}
      
      <PostsList />
    </div>
  );
}
```

## Testing Frontend Permissions

### Component Testing
```typescript
// __tests__/components/PostActions.test.tsx
import { render, screen } from '@testing-library/react';
import { createInertiaApp } from '@inertiajs/react';
import PostActions from '../PostActions';

// Mock Inertia page props
const mockUser = {
  id: 1,
  name: 'Test User',
  can: (permission: string) => permission === 'edit posts',
  hasRole: (role: string) => role === 'Admin',
  is_admin: false,
};

const mockPageProps = {
  auth: { user: mockUser },
};

// Mock Inertia
createInertiaApp({
  resolve: () => Promise.resolve(() => null),
  setup: () => {},
});

describe('PostActions', () => {
  it('shows edit button when user can edit posts', () => {
    render(<PostActions post={{ id: 1, title: 'Test Post' }} />);
    
    expect(screen.getByText('Edit Post')).toBeInTheDocument();
  });

  it('does not show delete button when user cannot delete posts', () => {
    render(<PostActions post={{ id: 1, title: 'Test Post' }} />);
    
    expect(screen.queryByText('Delete Post')).not.toBeInTheDocument();
  });
});
```

### Hook Testing
```typescript
// __tests__/hooks/usePermissions.test.ts
import { renderHook } from '@testing-library/react';
import { usePermissions } from '../usePermissions';

describe('usePermissions', () => {
  it('returns correct permission checks', () => {
    const mockUser = {
      can: (permission: string) => permission === 'create posts',
      hasRole: (role: string) => role === 'Editor',
      is_admin: false,
    };

    // Mock Inertia page props
    jest.spyOn(require('@inertiajs/react'), 'usePage').mockReturnValue({
      props: { auth: { user: mockUser } },
    });

    const { result } = renderHook(() => usePermissions());

    expect(result.current.can('create posts')).toBe(true);
    expect(result.current.can('delete posts')).toBe(false);
    expect(result.current.hasRole('Editor')).toBe(true);
    expect(result.current.isAdmin).toBe(false);
  });
});
```

## Best Practices

### 1. Always Check Server-Side
- Frontend permission checks are for UI display only
- Always validate permissions on the server
- Never rely solely on frontend checks for security

### 2. Use Consistent Patterns
- Use the same permission checking patterns throughout the app
- Create reusable components for common permission scenarios
- Use custom hooks for complex permission logic

### 3. Optimize Performance
- Memoize expensive permission calculations
- Use early returns to avoid unnecessary rendering
- Lazy load components based on permissions

### 4. Handle Edge Cases
- Always handle unauthenticated users
- Provide meaningful error messages
- Gracefully degrade when permissions are missing

### 5. Test Thoroughly
- Test all permission scenarios
- Mock authentication state in tests
- Test both positive and negative cases

## Related Documentation
- [Authentication Overview](../authentication/README.md)
- [API Documentation](../authentication/api.md)
- [Permissions Guide](permissions-guide.md)
- [Testing Guide](../testing/authentication-tests.md) 