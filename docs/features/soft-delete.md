# Soft Delete Guide

This guide provides comprehensive information about the soft delete functionality implemented in the Multi-Role User Authentication System.

## 📋 Overview

The system implements Laravel's soft delete functionality for user management, providing data safety, recovery options, and audit trails while maintaining referential integrity. When users are "deleted," they are not permanently removed from the database but marked as deleted with a timestamp.

## 🎯 Key Benefits

### Data Safety
- **Accidental deletion recovery**: Users can be restored if deleted by mistake
- **Data preservation**: All user data and relationships remain intact
- **Referential integrity**: Related data (posts, comments, etc.) remains connected
- **Zero data loss**: No information is permanently lost during soft deletion

### Audit & Compliance
- **Audit trail**: Complete record of when users were deleted and by whom
- **Compliance support**: Meets regulatory requirements for data retention
- **Historical reporting**: Ability to analyze user lifecycle patterns
- **Accountability**: Clear tracking of administrative actions

### Security & Governance
- **Permission-based access**: Different levels of delete permissions
- **Role hierarchy**: Only Super Admins can permanently delete users
- **Business logic protection**: Prevents deletion of critical users
- **Recovery controls**: Structured process for user restoration

## 🏗️ Technical Implementation

### Database Schema

#### Migration Structure
```sql
-- Add soft delete column and index
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
CREATE INDEX idx_users_deleted_at ON users(deleted_at);
```

#### Query Behavior
```php
// Default queries exclude soft-deleted records
User::all(); // Only active users

// Include soft-deleted records
User::withTrashed()->get(); // All users (active + deleted)

// Only soft-deleted records
User::onlyTrashed()->get(); // Only deleted users
```

### Model Implementation

#### User Model with SoftDeletes
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;

    // Soft delete methods automatically available:
    // $user->delete() - soft delete
    // $user->restore() - restore deleted user
    // $user->forceDelete() - permanently delete
    // $user->trashed() - check if soft deleted
}
```

#### Available Methods
```php
// Soft delete operations
$user->delete();           // Soft delete (sets deleted_at timestamp)
$user->restore();          // Restore soft-deleted user (clears deleted_at)
$user->forceDelete();      // Permanently delete from database
$user->trashed();          // Returns true if soft deleted

// Query scopes
User::withTrashed();       // Include soft-deleted users
User::onlyTrashed();       // Only soft-deleted users
User::withoutTrashed();    // Only active users (default behavior)
```

## 🔐 Permission System

### New Permissions

The soft delete implementation introduces two new permissions:

#### `restore users`
- **Purpose**: Allows restoring soft-deleted users
- **Assigned to**: Admin, Super Admin
- **Usage**: Required to access restore functionality

#### `force delete users`
- **Purpose**: Allows permanent deletion of users
- **Assigned to**: Super Admin only
- **Usage**: Required for irreversible user deletion

### Permission Hierarchy

```
Super Admin
├── delete users (soft delete)
├── restore users
└── force delete users (permanent)

Admin
├── delete users (soft delete)
└── restore users

Editor/Author/Subscriber
└── (no delete permissions)
```

## 🎛️ User Interface

### Main Users Page

#### Delete Button Behavior
- **Action**: Soft delete (not permanent)
- **Confirmation**: "User will be moved to deleted users list and can be restored later"
- **Result**: User disappears from main list but can be found in "Deleted Users"

#### New "Deleted Users" Button
- **Location**: Top right of users page
- **Access**: Users with "view users" permission
- **Function**: Navigate to trashed users management page

### Deleted Users Page

#### Features
- **User List**: All soft-deleted users with deletion timestamps
- **Statistics**: Counts of deleted users by role
- **Actions**: Restore and force delete buttons (permission-based)
- **Visual Design**: Red-tinted cards to indicate deleted status

#### Action Buttons
```tsx
// Restore button (green)
<Button onClick={() => restoreUser(userId)}>
    <RotateCcw /> Restore
</Button>

// Force delete button (red, Super Admin only)
<Button variant="destructive" onClick={() => forceDeleteUser(userId)}>
    <Trash2 /> Permanently Delete
</Button>
```

## 🛡️ Security Features

### Business Logic Protection

#### Last Super Admin Protection
```php
// Prevent deletion of last Super Admin
if ($user->hasRole('Super Admin')) {
    $superAdminCount = User::role('Super Admin')->count();
    if ($superAdminCount <= 1) {
        return redirect()->back()
            ->with('error', 'Cannot delete the last Super Admin user.');
    }
}
```

#### Self-Deletion Prevention
```php
// Prevent users from deleting themselves
if ($user->id === auth()->id()) {
    return redirect()->back()
        ->with('error', 'You cannot delete your own account.');
}
```

### Permission Checks

#### Route Protection
```php
// Middleware protection for all soft delete routes
Route::middleware('permission:delete users')->only(['destroy']);
Route::middleware('permission:restore users')->only(['restore']);
Route::middleware('permission:force delete users')->only(['forceDelete']);
```

#### Controller Validation
```php
// Additional validation in force delete method
if (!auth()->user()->hasRole('Super Admin')) {
    return redirect()->back()
        ->with('error', 'Only Super Admins can permanently delete users.');
}
```

## 📊 Statistics & Reporting

### Deleted User Statistics

The system provides comprehensive statistics about deleted users:

```php
$stats = [
    'total_deleted' => User::onlyTrashed()->count(),
    'deleted_administrators' => User::onlyTrashed()->role(['Super Admin', 'Admin'])->count(),
    'deleted_content_creators' => User::onlyTrashed()->role(['Editor', 'Author'])->count(),
    'deleted_subscribers' => User::onlyTrashed()->role('Subscriber')->count(),
];
```

### Dashboard Cards
- **Total Deleted**: Overall count of soft-deleted users
- **Deleted Administrators**: Count of deleted admin-level users
- **Deleted Content Creators**: Count of deleted editors and authors
- **Deleted Subscribers**: Count of deleted subscriber-level users

## 🔄 User Workflows

### Standard Deletion Workflow

1. **Administrator Action**
   - Admin clicks delete button on user
   - Confirmation dialog explains soft delete behavior
   - Admin confirms deletion

2. **System Processing**
   - Security checks (last Super Admin, self-deletion)
   - User record marked with `deleted_at` timestamp
   - User disappears from main users list
   - Success message confirms deletion and restoration option

3. **Post-Deletion State**
   - User data preserved in database
   - Relationships maintained
   - User appears in "Deleted Users" section
   - Statistics updated

### User Restoration Workflow

1. **Access Deleted Users**
   - Admin navigates to "Deleted Users" page
   - Views list of soft-deleted users with deletion dates

2. **Restore User**
   - Admin clicks restore button
   - Confirmation dialog for restoration
   - Admin confirms restoration

3. **System Processing**
   - `deleted_at` timestamp cleared
   - User reappears in main users list
   - All data and relationships intact
   - Success message confirms restoration

### Permanent Deletion Workflow (Super Admin Only)

1. **Access Deleted Users**
   - Super Admin navigates to "Deleted Users" page
   - Views soft-deleted users

2. **Force Delete**
   - Super Admin clicks "Permanently Delete" button
   - Strong warning about irreversible action
   - Super Admin confirms permanent deletion

3. **System Processing**
   - User record completely removed from database
   - All related data handling (cascade or orphan)
   - Success message confirms permanent deletion

## 🧪 Testing

### Test Coverage

The soft delete implementation includes comprehensive tests:

#### Soft Delete Tests
```php
public function test_user_is_soft_deleted_not_hard_deleted()
{
    $user = $this->createEditor();
    $user->delete();
    
    $this->assertSoftDeleted('users', ['id' => $user->id]);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
}
```

#### Permission Tests
```php
public function test_admin_cannot_force_delete_user()
{
    $admin = $this->createAdmin();
    $user = $this->createEditor();
    $user->delete();

    $response = $this->actingAs($admin)
        ->delete("/admin/users/{$user->id}/force-delete");

    $response->assertStatus(403);
}
```

#### Business Logic Tests
```php
public function test_cannot_delete_last_super_admin()
{
    $superAdmin = $this->createSuperAdmin();

    $response = $this->actingAs($superAdmin)
        ->delete("/admin/users/{$superAdmin->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $superAdmin->id]);
}
```

### Test Categories

1. **Soft Delete Operations**: Verify soft delete behavior
2. **Permission Enforcement**: Test role-based access control
3. **Security Checks**: Validate business logic protection
4. **Data Integrity**: Ensure relationships are preserved
5. **UI Functionality**: Test frontend components and workflows

## 🚀 Performance Considerations

### Database Optimization

#### Indexing Strategy
```sql
-- Primary index on deleted_at for query performance
CREATE INDEX idx_users_deleted_at ON users(deleted_at);

-- Composite indexes for complex queries
CREATE INDEX idx_users_deleted_created ON users(deleted_at, created_at);
CREATE INDEX idx_users_deleted_role ON users(deleted_at, id);
```

#### Query Optimization
```php
// Efficient queries for large datasets
$trashedUsers = User::onlyTrashed()
    ->select(['id', 'name', 'email', 'deleted_at']) // Only needed columns
    ->with(['roles:id,name']) // Eager load only necessary role data
    ->orderBy('deleted_at', 'desc')
    ->paginate(20);
```

### Caching Considerations

#### Statistics Caching
```php
// Cache expensive statistics queries
$stats = Cache::remember('deleted_users_stats', 3600, function () {
    return [
        'total_deleted' => User::onlyTrashed()->count(),
        'deleted_administrators' => User::onlyTrashed()->role(['Super Admin', 'Admin'])->count(),
        // ... other statistics
    ];
});
```

## 🔧 Configuration

### Environment Variables

No additional environment variables are required for soft delete functionality. The feature uses Laravel's built-in soft delete capabilities.

### Configuration Options

#### Soft Delete Behavior
```php
// In User model - customize soft delete column name if needed
protected $dates = ['deleted_at']; // Default column name

// Custom deleted_at column name (if required)
const DELETED_AT = 'removed_at';
```

## 🎯 Best Practices

### Development Guidelines

1. **Always Use Soft Deletes**: Never use hard delete for user records
2. **Permission Checks**: Always verify permissions before delete operations
3. **Business Logic**: Implement protection for critical users
4. **User Feedback**: Provide clear messaging about soft delete behavior
5. **Testing**: Comprehensive test coverage for all scenarios

### Security Guidelines

1. **Role-Based Access**: Different permissions for different operations
2. **Confirmation Dialogs**: Require explicit confirmation for destructive actions
3. **Audit Logging**: Log all delete and restore operations
4. **Regular Cleanup**: Periodic review of soft-deleted users

### Performance Guidelines

1. **Database Indexing**: Proper indexes on deleted_at column
2. **Query Optimization**: Use select() to limit returned columns
3. **Eager Loading**: Load only necessary relationships
4. **Pagination**: Always paginate large result sets

## 🔍 Troubleshooting

### Common Issues

#### Users Not Appearing in Deleted List
**Problem**: Soft-deleted users don't appear in trashed users page
**Solution**: Ensure `onlyTrashed()` scope is used in controller query

#### Permission Denied Errors
**Problem**: Users can't access restore/force delete functions
**Solution**: Verify user has appropriate permissions (`restore users`, `force delete users`)

#### Last Super Admin Deletion
**Problem**: System prevents deletion of last Super Admin
**Solution**: This is intentional security behavior - create another Super Admin first

### Debugging Queries

```php
// Debug soft delete queries
DB::enableQueryLog();
User::onlyTrashed()->get();
dd(DB::getQueryLog());

// Check if user is soft deleted
$user = User::withTrashed()->find($id);
if ($user->trashed()) {
    echo "User is soft deleted";
}
```

## 📚 API Reference

### Eloquent Methods

| Method | Description | Example |
|--------|-------------|---------|
| `delete()` | Soft delete user | `$user->delete()` |
| `restore()` | Restore soft-deleted user | `$user->restore()` |
| `forceDelete()` | Permanently delete user | `$user->forceDelete()` |
| `trashed()` | Check if user is soft deleted | `$user->trashed()` |

### Query Scopes

| Scope | Description | Example |
|-------|-------------|---------|
| `withTrashed()` | Include soft-deleted records | `User::withTrashed()->get()` |
| `onlyTrashed()` | Only soft-deleted records | `User::onlyTrashed()->get()` |
| `withoutTrashed()` | Exclude soft-deleted records | `User::withoutTrashed()->get()` |

### Route Names

| Route | Method | Description |
|-------|--------|-------------|
| `admin.users.destroy` | DELETE | Soft delete user |
| `admin.users.trashed` | GET | View deleted users |
| `admin.users.restore` | PATCH | Restore user |
| `admin.users.force-delete` | DELETE | Permanently delete user |

---

**Need more help?** Check out our [Developer Guide](Developer-Guide) for technical implementation details or [User Guide](User-Guide) for end-user instructions.
