# Authentication Troubleshooting Guide

## Common Issues and Solutions

### 1. Permissions Not Loading

#### Symptoms
- User permissions are empty in frontend
- `auth.user.permission_names` is empty array
- Permission checks return false unexpectedly

#### Causes
- Cache issues
- Database connection problems
- Middleware configuration errors
- Role assignment issues

#### Solutions

**Clear Application Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Check Database Connection**
```bash
php artisan tinker
>>> App\Models\User::first()->getAllPermissions()
```

**Verify Middleware Configuration**
```php
// Check bootstrap/app.php
use App\Http\Middleware\HandleInertiaRequests;
```

**Check Role Assignment**
```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->roles; // Should show assigned roles
>>> $user->getAllPermissions(); // Should show all permissions
```

### 2. Frontend Permissions Missing

#### Symptoms
- `auth.user` is null in React components
- Permission functions not available
- Inertia.js errors

#### Causes
- User not authenticated
- Middleware not loading user data
- Inertia configuration issues

#### Solutions

**Check Authentication Status**
```typescript
// In React component
const { auth } = usePage<SharedData>().props;
console.log('Auth user:', auth.user);
```

**Verify Middleware**
```php
// Check HandleInertiaRequests.php
public function share(Request $request): array
{
    $user = $request->user();
    // Ensure user data is being loaded
}
```

**Check Inertia Configuration**
```typescript
// Check app.tsx
createInertiaApp({
  resolve: name => {
    // Ensure proper page resolution
  }
})
```

### 3. Role Assignment Failures

#### Symptoms
- Users not getting assigned roles
- Role assignment errors
- Permission inheritance not working

#### Causes
- Role doesn't exist
- Database transaction failures
- Permission configuration issues

#### Solutions

**Verify Role Exists**
```bash
php artisan tinker
>>> Spatie\Permission\Models\Role::all()->pluck('name');
```

**Check Role Assignment**
```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->assignRole('Admin');
>>> $user->fresh()->roles;
```

**Verify Permissions**
```bash
php artisan tinker
>>> $role = Spatie\Permission\Models\Role::where('name', 'Admin')->first();
>>> $role->permissions;
```

### 4. Test Failures

#### Symptoms
- `UIPermissionTest` failing
- Role creation errors in tests
- Permission inheritance test failures

#### Causes
- Test database not migrated
- Trait not properly loaded
- Test isolation issues

#### Solutions

**Reset Test Database**
```bash
php artisan migrate:fresh --env=testing
```

**Check Test Configuration**
```php
// In test class
use Tests\Traits\WithRoles;

class UIPermissionTest extends TestCase
{
    use RefreshDatabase, WithRoles;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }
}
```

**Run Specific Tests**
```bash
php artisan test --filter=UIPermissionTest
```

### 5. Performance Issues

#### Symptoms
- Slow page loads
- High database queries
- Memory usage spikes

#### Causes
- N+1 query problems
- Missing database indexes
- Cache not working

#### Solutions

**Enable Query Logging**
```php
// In AppServiceProvider
DB::listen(function ($query) {
    Log::info($query->sql, $query->bindings);
});
```

**Check Database Indexes**
```sql
-- Ensure proper indexes exist
SHOW INDEX FROM roles;
SHOW INDEX FROM permissions;
SHOW INDEX FROM role_has_permissions;
SHOW INDEX FROM model_has_roles;
```

**Verify Cache Configuration**
```php
// Check config/permission.php
'cache' => [
    'expiration_time' => 60 * 24, // 24 hours
    'key' => 'spatie.permission.cache',
    'store' => 'default',
],
```

### 6. Deployment Issues

#### Symptoms
- Permissions not working in production
- Role assignments lost
- Cache issues

#### Causes
- Missing migrations
- Environment configuration
- Cache not cleared

#### Solutions

**Run Production Migrations**
```bash
php artisan migrate --force
```

**Clear Production Cache**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Check Environment Variables**
```bash
# Verify database connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Debugging Tools

### 1. Permission Debug Command
Create a custom Artisan command for debugging:

```php
// app/Console/Commands/DebugPermissions.php
class DebugPermissions extends Command
{
    protected $signature = 'debug:permissions {user_id?}';
    
    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = $userId ? User::find($userId) : User::first();
        
        $this->info("User: {$user->name}");
        $this->info("Roles: " . $user->roles->pluck('name')->implode(', '));
        $this->info("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
    }
}
```

### 2. Database Queries
```sql
-- Check user roles
SELECT u.name, r.name as role_name
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.id = 1;

-- Check role permissions
SELECT r.name as role_name, p.name as permission_name
FROM roles r
JOIN role_has_permissions rhp ON r.id = rhp.role_id
JOIN permissions p ON rhp.permission_id = p.id
ORDER BY r.name, p.name;
```

### 3. Logging
```php
// Enable detailed logging
Log::info('Permission check', [
    'user_id' => $user->id,
    'permission' => $permission,
    'result' => $user->hasPermissionTo($permission)
]);
```

## Prevention Best Practices

### 1. Regular Maintenance
- Monitor permission cache performance
- Regular database optimization
- Update Spatie Laravel Permission package

### 2. Testing
- Run tests before deployment
- Test permission inheritance
- Verify role assignments

### 3. Monitoring
- Log permission-related errors
- Monitor database performance
- Track permission usage patterns

## Getting Help

### 1. Check Logs
```bash
tail -f storage/logs/laravel.log
```

### 2. Enable Debug Mode
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### 3. Community Resources
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Laravel Documentation](https://laravel.com/docs)
- [GitHub Issues](https://github.com/spatie/laravel-permission/issues)

## Related Documentation
- [Authentication Overview](README.md)
- [API Documentation](api.md)
- [Deployment Guide](deployment.md) 