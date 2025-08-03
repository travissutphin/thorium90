# Troubleshooting Guide

This guide helps you diagnose and resolve common issues with the Multi-Role User Authentication System.

## 🚨 Common Issues

### Authentication Issues

#### 1. User Cannot Login

**Symptoms:**
- User gets "Invalid credentials" error
- User is redirected back to login page
- No error message displayed

**Possible Causes:**
- Incorrect email/password
- User account is disabled
- Database connection issues
- Session configuration problems

**Solutions:**

1. **Check User Credentials**
   ```bash
   # Verify user exists and password is correct
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   use Illuminate\Support\Facades\Hash;
   
   $user = User::where('email', 'user@example.com')->first();
   if ($user) {
       echo "User exists: " . $user->name . "\n";
       echo "Password check: " . (Hash::check('password123', $user->password) ? 'Valid' : 'Invalid') . "\n";
   } else {
       echo "User not found\n";
   }
   ```

2. **Reset User Password**
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   use Illuminate\Support\Facades\Hash;
   
   $user = User::where('email', 'user@example.com')->first();
   $user->update(['password' => Hash::make('newpassword123')]);
   ```

3. **Check Session Configuration**
   ```env
   # In .env file
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   SESSION_SECURE_COOKIE=false
   ```

4. **Clear Application Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

#### 2. User Loses Permissions After Login

**Symptoms:**
- User can login but has no permissions
- UI shows no admin options
- Permission checks fail

**Possible Causes:**
- Roles not assigned to user
- Permission cache not cleared
- Database relationship issues

**Solutions:**

1. **Check User Roles**
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   
   $user = User::where('email', 'user@example.com')->first();
   echo "User roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
   echo "User permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
   ```

2. **Assign Role to User**
   ```php
   $user->assignRole('Admin');
   ```

3. **Clear Permission Cache**
   ```bash
   php artisan permission:cache-reset
   ```

4. **Re-run Seeders**
   ```bash
   php artisan db:seed --class=RoleSeeder
   php artisan db:seed --class=PermissionSeeder
   php artisan db:seed --class=RolePermissionSeeder
   ```

### Frontend Issues

#### 3. Permissions Not Showing in React

**Symptoms:**
- User data not available in frontend
- Permission checks return false
- UI elements not rendering based on permissions

**Possible Causes:**
- Inertia.js data not shared properly
- User data not loaded in middleware
- Frontend permission checks incorrect

**Solutions:**

1. **Check Inertia.js Data**
   ```javascript
   // In browser console
   console.log(window.Inertia.page.props.auth.user);
   ```

2. **Verify Middleware Configuration**
   ```php
   // Check HandleInertiaRequests middleware
   // Ensure user data is being shared
   ```

3. **Debug Frontend Permission Checks**
   ```tsx
   // Add debugging to React components
   console.log('User data:', auth.user);
   console.log('Can create posts:', auth.user.can('create-posts'));
   ```

4. **Check Network Requests**
   - Open browser developer tools
   - Check Network tab for failed requests
   - Verify response contains user data

#### 4. Route Protection Not Working

**Symptoms:**
- Users can access protected routes without permissions
- Middleware not blocking unauthorized access
- 403 errors not showing

**Possible Causes:**
- Middleware not registered
- Route definitions incorrect
- Permission names mismatch

**Solutions:**

1. **Check Middleware Registration**
   ```php
   // In bootstrap/app.php
   $middleware->alias([
       'role' => \App\Http\Middleware\EnsureUserHasRole::class,
       'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
   ]);
   ```

2. **Verify Route Definitions**
   ```php
   // In routes/web.php
   Route::middleware(['auth', 'role:Admin'])->group(function () {
       Route::get('/admin', [AdminController::class, 'index']);
   });
   ```

3. **Test Middleware Manually**
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   use App\Http\Middleware\EnsureUserHasRole;
   
   $user = User::first();
   $middleware = new EnsureUserHasRole();
   // Test middleware logic
   ```

### Database Issues

#### 5. Migration Errors

**Symptoms:**
- `php artisan migrate` fails
- Database tables missing
- Foreign key constraint errors

**Possible Causes:**
- Database connection issues
- Migration files corrupted
- Database permissions

**Solutions:**

1. **Check Database Connection**
   ```bash
   php artisan tinker
   ```
   ```php
   try {
       DB::connection()->getPdo();
       echo "Database connected successfully\n";
   } catch (\Exception $e) {
       echo "Database connection failed: " . $e->getMessage() . "\n";
   }
   ```

2. **Reset Migrations**
   ```bash
   php artisan migrate:reset
   php artisan migrate
   ```

3. **Check Database Permissions**
   ```sql
   -- Ensure database user has proper permissions
   GRANT ALL PRIVILEGES ON thorium90.* TO 'username'@'localhost';
   FLUSH PRIVILEGES;
   ```

#### 6. Seeder Errors

**Symptoms:**
- Roles and permissions not created
- Seeder fails with errors
- Inconsistent data

**Possible Causes:**
- Database constraints
- Duplicate data
- Missing dependencies

**Solutions:**

1. **Clear Database and Re-seed**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Run Seeders Individually**
   ```bash
   php artisan db:seed --class=RoleSeeder
   php artisan db:seed --class=PermissionSeeder
   php artisan db:seed --class=RolePermissionSeeder
   ```

3. **Check for Duplicates**
   ```bash
   php artisan tinker
   ```
   ```php
   use Spatie\Permission\Models\Role;
   use Spatie\Permission\Models\Permission;
   
   echo "Roles: " . Role::count() . "\n";
   echo "Permissions: " . Permission::count() . "\n";
   ```

### Performance Issues

#### 7. Slow Permission Checks

**Symptoms:**
- Page load times slow
- Permission checks taking time
- Database queries excessive

**Possible Causes:**
- Permission cache disabled
- N+1 query problems
- Missing database indexes

**Solutions:**

1. **Enable Permission Caching**
   ```php
   // In config/permission.php
   'cache' => [
       'expiration_time' => \DateInterval::createFromDateString('24 hours'),
       'key' => 'spatie.permission.cache',
       'store' => 'default',
   ],
   ```

2. **Add Database Indexes**
   ```sql
   CREATE INDEX idx_model_has_roles_model_id ON model_has_roles(model_id);
   CREATE INDEX idx_model_has_permissions_model_id ON model_has_permissions(model_id);
   CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);
   ```

3. **Optimize Queries**
   ```php
   // Eager load relationships
   $user = User::with(['roles.permissions', 'permissions'])->find($id);
   ```

#### 8. Memory Issues

**Symptoms:**
- PHP memory limit exceeded
- Application crashes
- Slow performance

**Possible Causes:**
- Large permission sets
- Memory leaks
- Inefficient queries

**Solutions:**

1. **Increase PHP Memory Limit**
   ```ini
   ; In php.ini
   memory_limit = 512M
   ```

2. **Optimize Permission Loading**
   ```php
   // Load only necessary permissions
   $user->load(['roles.permissions' => function ($query) {
       $query->select('id', 'name');
   }]);
   ```

3. **Use Pagination**
   ```php
   // For large user lists
   $users = User::with('roles')->paginate(50);
   ```

## 🔧 Debugging Tools

### Laravel Debugging

#### 1. Enable Debug Mode
```env
# In .env file
APP_DEBUG=true
APP_ENV=local
```

#### 2. Check Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Check error logs
tail -f storage/logs/error.log
```

#### 3. Use Laravel Telescope (Development)
```bash
# Install Telescope
composer require laravel/telescope --dev

# Publish configuration
php artisan telescope:install

# Access Telescope dashboard
# Visit /telescope in your browser
```

### Frontend Debugging

#### 1. Browser Developer Tools
```javascript
// Check Inertia.js data
console.log(window.Inertia.page.props);

// Debug permission checks
console.log('User permissions:', auth.user.permission_names);
console.log('Can create posts:', auth.user.can('create-posts'));
```

#### 2. React Developer Tools
- Install React Developer Tools browser extension
- Inspect component props and state
- Check for permission-related props

#### 3. Network Tab
- Check for failed API requests
- Verify response data structure
- Monitor authentication requests

### Database Debugging

#### 1. Check Database State
```bash
php artisan tinker
```
```php
// Check tables
DB::select('SHOW TABLES');

// Check user roles
$user = User::first();
echo "User: " . $user->name . "\n";
echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
```

#### 2. Database Queries
```php
// Enable query logging
DB::enableQueryLog();
// Perform action
DB::getQueryLog();
```

## 🚨 Emergency Procedures

### System Down - Quick Recovery

#### 1. Check Application Status
```bash
# Check if Laravel is running
php artisan about

# Check database connection
php artisan tinker
DB::connection()->getPdo();
```

#### 2. Restart Services
```bash
# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### 3. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan permission:cache-reset
```

### Data Recovery

#### 1. Backup Database
```bash
# Create backup
mysqldump -u username -p thorium90 > backup.sql

# Restore if needed
mysql -u username -p thorium90 < backup.sql
```

#### 2. Restore from Git
```bash
# Reset to last working commit
git reset --hard HEAD~1

# Reinstall dependencies
composer install
npm install

# Re-run migrations
php artisan migrate:fresh --seed
```

## 📞 Getting Help

### Before Contacting Support

1. **Document the Issue**
   - Note exact error messages
   - Record steps to reproduce
   - Include system information

2. **Check Existing Resources**
   - Review this troubleshooting guide
   - Check [FAQ](FAQ) page
   - Search [GitHub Issues](https://github.com/your-username/thorium90/issues)

3. **Gather Information**
   ```bash
   # System information
   php -v
   composer --version
   node --version
   npm --version
   
   # Laravel information
   php artisan about
   
   # Database information
   php artisan tinker
   DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION);
   ```

### Contacting Support

When contacting support, include:

1. **Issue Description**
   - What you were trying to do
   - What happened instead
   - Expected behavior

2. **Environment Details**
   - Operating system
   - PHP version
   - Database type and version
   - Laravel version

3. **Error Information**
   - Full error messages
   - Stack traces
   - Log files

4. **Steps to Reproduce**
   - Exact steps to trigger the issue
   - Any relevant configuration

### Support Channels

- **[GitHub Issues](https://github.com/your-username/thorium90/issues)** - Bug reports and feature requests
- **[GitHub Discussions](https://github.com/your-username/thorium90/discussions)** - Community support
- **[Documentation](https://github.com/your-username/thorium90/wiki)** - Comprehensive guides
- **[Email Support](mailto:support@example.com)** - Direct support (for premium users)

## 🔄 Prevention

### Best Practices

1. **Regular Backups**
   ```bash
   # Set up automated database backups
   # Use cron jobs for regular backups
   ```

2. **Monitoring**
   ```bash
   # Monitor application logs
   # Set up error tracking
   # Monitor performance metrics
   ```

3. **Testing**
   ```bash
   # Run tests regularly
   php artisan test
   
   # Test in staging environment
   # Validate before production deployment
   ```

4. **Documentation**
   - Keep configuration documented
   - Record custom modifications
   - Maintain change logs

---

**Still having issues?** Check out our [FAQ](FAQ) or [Contact Support](Support) for additional help. 