# Frequently Asked Questions (FAQ)

This page answers the most common questions about the Multi-Role User Authentication System.

## 🔐 Authentication & Login

### Q: How do I create a new user account?

**A:** There are several ways to create user accounts:

1. **Via Admin Panel** (if you have admin permissions):
   - Navigate to Users section
   - Click "Add User"
   - Fill in the required information
   - Assign appropriate role
   - Click "Create User"

2. **Via Registration** (if enabled):
   - Visit `/register` page
   - Fill in your details
   - Complete email verification (if required)
   - You'll be assigned the default role (usually Subscriber)

3. **Via Command Line**:
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   use Illuminate\Support\Facades\Hash;
   
   $user = User::create([
       'name' => 'John Doe',
       'email' => 'john@example.com',
       'password' => Hash::make('password123'),
   ]);
   $user->assignRole('Author');
   ```

### Q: I forgot my password. How do I reset it?

**A:** You can reset your password in several ways:

1. **Password Reset Link**:
   - Go to the login page
   - Click "Forgot Password?"
   - Enter your email address
   - Check your email for reset link
   - Follow the link to set a new password

2. **Admin Reset** (if you're an admin):
   - Go to Users section
   - Find the user
   - Click "Edit"
   - Set a new password
   - Save changes

3. **Command Line Reset**:
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   use Illuminate\Support\Facades\Hash;
   
   $user = User::where('email', 'user@example.com')->first();
   $user->update(['password' => Hash::make('newpassword123')]);
   ```

### Q: Why can't I log in even with correct credentials?

**A:** This could be due to several reasons:

1. **Account Disabled**: Your account might be disabled by an admin
2. **Email Not Verified**: If email verification is required, verify your email first
3. **Session Issues**: Clear your browser cookies and try again
4. **Database Issues**: Check if the database is accessible

**Troubleshooting Steps**:
```bash
# Check if user exists and password is correct
php artisan tinker
```
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'your-email@example.com')->first();
if ($user) {
    echo "User exists\n";
    echo "Password valid: " . (Hash::check('your-password', $user->password) ? 'Yes' : 'No') . "\n";
} else {
    echo "User not found\n";
}
```

## 👥 Roles & Permissions

### Q: What are the different user roles and what can they do?

**A:** The system includes 5 roles with different capabilities:

1. **Super Admin**:
   - Full system access
   - Manage all users, roles, and permissions
   - Configure system settings
   - All permissions

2. **Admin**:
   - Manage users and their roles
   - Oversee content and media
   - Moderate comments
   - Most permissions (except role/permission management)

3. **Editor**:
   - Create and edit content
   - Moderate comments
   - Manage media files
   - Publish content

4. **Author**:
   - Create content
   - Edit and delete own posts
   - Upload media for own content
   - View comments

5. **Subscriber**:
   - View published content
   - Access dashboard
   - View comments
   - Read-only access

### Q: How do I change a user's role?

**A:** You can change a user's role in several ways:

1. **Via Admin Panel**:
   - Go to Users section
   - Find the user
   - Click "Edit"
   - Select new role(s)
   - Save changes

2. **Via Command Line**:
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   
   $user = User::where('email', 'user@example.com')->first();
   $user->syncRoles(['Editor']); // Replace all roles
   // OR
   $user->assignRole('Editor'); // Add role
   $user->removeRole('Author'); // Remove role
   ```

### Q: Why can't I access certain features even though I have the right role?

**A:** This could be due to:

1. **Permission Cache**: Clear the permission cache
   ```bash
   php artisan permission:cache-reset
   ```

2. **Missing Permissions**: Check if your role has the required permissions
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\User;
   
   $user = User::where('email', 'your-email@example.com')->first();
   echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
   echo "Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";
   ```

3. **Frontend Cache**: Clear browser cache and refresh the page

4. **Session Issues**: Log out and log back in

### Q: How do I add a new permission to the system?

**A:** To add a new permission:

1. **Create Migration**:
   ```bash
   php artisan make:migration add_new_permissions
   ```

2. **Add Permission in Migration**:
   ```php
   public function up()
   {
       Permission::create(['name' => 'manage-reports']);
   }
   ```

3. **Add to Seeder** (for future installations):
   ```php
   // In PermissionSeeder.php
   $permissions = [
       // ... existing permissions
       'manage-reports',
   ];
   ```

4. **Add Gate** (optional):
   ```php
   // In AppServiceProvider.php
   Gate::define('manage-reports', function (User $user) {
       return $user->hasPermissionTo('manage-reports');
   });
   ```

5. **Run Migration**:
   ```bash
   php artisan migrate
   ```

## 🛠️ Technical Issues

### Q: How do I check if the system is working correctly?

**A:** Run the test suite to verify everything is working:

```bash
# Run all tests
php artisan test

# Run specific authentication tests
php artisan test --filter=UIPermissionTest

# Run with coverage report
php artisan test --coverage
```

### Q: The system is slow. How can I improve performance?

**A:** Here are several optimization strategies:

1. **Enable Permission Caching**:
   ```php
   // In config/permission.php
   'cache' => [
       'expiration_time' => \DateInterval::createFromDateString('24 hours'),
       'key' => 'spatie.permission.cache',
       'store' => 'default',
   ],
   ```

2. **Add Database Indexes**:
   ```sql
   CREATE INDEX idx_model_has_roles_model_id ON model_has_roles(model_id);
   CREATE INDEX idx_model_has_permissions_model_id ON model_has_permissions(model_id);
   CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);
   ```

3. **Optimize Queries**:
   ```php
   // Eager load relationships
   $user = User::with(['roles.permissions', 'permissions'])->find($id);
   ```

4. **Use Redis for Caching**:
   ```env
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   ```

### Q: How do I backup and restore the system?

**A:** Here's how to backup and restore:

1. **Database Backup**:
   ```bash
   # Create backup
   mysqldump -u username -p thorium90 > backup.sql
   
   # Restore
   mysql -u username -p thorium90 < backup.sql
   ```

2. **Application Backup**:
   ```bash
   # Backup code
   tar -czf thorium90-backup.tar.gz /path/to/thorium90
   
   # Backup environment
   cp .env .env.backup
   ```

3. **Full System Restore**:
   ```bash
   # Restore code
   tar -xzf thorium90-backup.tar.gz
   
   # Restore environment
   cp .env.backup .env
   
   # Install dependencies
   composer install
   npm install
   
   # Restore database
   mysql -u username -p thorium90 < backup.sql
   
   # Clear caches
   php artisan config:clear
   php artisan cache:clear
   ```

### Q: How do I update the system to a new version?

**A:** Follow these steps to update:

1. **Backup First**:
   ```bash
   # Backup database
   mysqldump -u username -p thorium90 > backup.sql
   
   # Backup code
   git stash
   ```

2. **Update Code**:
   ```bash
   # Pull latest changes
   git pull origin main
   
   # Update dependencies
   composer install
   npm install
   ```

3. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

4. **Clear Caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan permission:cache-reset
   ```

5. **Build Assets**:
   ```bash
   npm run build
   ```

6. **Test the System**:
   ```bash
   php artisan test
   ```

## 🔒 Security

### Q: How secure is the authentication system?

**A:** The system implements several security measures:

1. **Password Security**:
   - Passwords are hashed using Laravel's bcrypt
   - Minimum password requirements enforced
   - Password reset functionality

2. **Session Security**:
   - Secure session handling
   - CSRF protection
   - Session timeout

3. **Permission Security**:
   - Role-based access control
   - Granular permissions
   - Route-level protection

4. **Database Security**:
   - SQL injection prevention
   - Parameterized queries
   - Foreign key constraints

### Q: How do I enable two-factor authentication?

**A:** To enable 2FA:

1. **Install Laravel Fortify** (if not already installed):
   ```bash
   composer require laravel/fortify
   php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
   ```

2. **Configure 2FA**:
   ```php
   // In config/fortify.php
   'features' => [
       Features::registration(),
       Features::resetPasswords(),
       Features::emailVerification(),
       Features::updateProfileInformation(),
       Features::updatePasswords(),
       Features::twoFactorAuthentication([
           'confirm' => true,
           'confirmPassword' => true,
       ]),
   ],
   ```

3. **Enable in User Model**:
   ```php
   use Laravel\Fortify\TwoFactorAuthenticatable;
   
   class User extends Authenticatable
   {
       use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;
   }
   ```

### Q: How do I audit user actions for security?

**A:** You can implement audit logging:

1. **Install Audit Package**:
   ```bash
   composer require owen-it/laravel-auditing
   ```

2. **Configure Auditing**:
   ```php
   // In User model
   use OwenIt\Auditing\Contracts\Auditable;
   
   class User extends Authenticatable implements Auditable
   {
       use \OwenIt\Auditing\Auditable;
   }
   ```

3. **View Audit Logs**:
   ```php
   // Get user's audit trail
   $user = User::find(1);
   $audits = $user->audits;
   ```

## 📱 Frontend & UI

### Q: How do I customize the frontend interface?

**A:** You can customize the frontend in several ways:

1. **Modify React Components**:
   - Edit components in `resources/js/components/`
   - Update styles in `resources/css/`
   - Modify layouts in `resources/js/layouts/`

2. **Customize Permission UI**:
   ```tsx
   // Create custom permission components
   const PermissionGuard = ({ permission, children }) => {
     const { can } = usePermissions();
     return can(permission) ? children : null;
   };
   ```

3. **Add Custom Routes**:
   ```tsx
   // In your React router
   <Route 
     path="/custom" 
     element={
       <PermissionGuard permission="view-custom">
         <CustomComponent />
       </PermissionGuard>
     } 
   />
   ```

### Q: How do I add new features to the dashboard?

**A:** To add new dashboard features:

1. **Create Backend API**:
   ```php
   // Create controller
   php artisan make:controller DashboardController
   
   // Add routes
   Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
   ```

2. **Create Frontend Component**:
   ```tsx
   // Create new component
   const DashboardStats = () => {
     const [stats, setStats] = useState({});
     
     useEffect(() => {
       // Fetch stats from API
     }, []);
     
     return <div>Dashboard Stats</div>;
   };
   ```

3. **Add to Dashboard**:
   ```tsx
   // Include in dashboard
   {user.is_admin && <DashboardStats />}
   ```

## 🚀 Deployment

### Q: How do I deploy the system to production?

**A:** Follow these deployment steps:

1. **Prepare Server**:
   ```bash
   # Install required software
   sudo apt update
   sudo apt install nginx mysql-server php8.2-fpm
   ```

2. **Deploy Application**:
   ```bash
   # Clone repository
   git clone https://github.com/your-username/thorium90.git
   cd thorium90
   
   # Install dependencies
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

3. **Configure Environment**:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=thorium90
   DB_USERNAME=username
   DB_PASSWORD=password
   ```

4. **Set Permissions**:
   ```bash
   sudo chown -R www-data:www-data /var/www/thorium90
   sudo chmod -R 755 /var/www/thorium90
   sudo chmod -R 775 storage bootstrap/cache
   ```

5. **Configure Web Server**:
   ```nginx
   # Nginx configuration
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/thorium90/public;
       
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

### Q: How do I set up SSL/HTTPS?

**A:** To enable HTTPS:

1. **Install Certbot**:
   ```bash
   sudo apt install certbot python3-certbot-nginx
   ```

2. **Obtain SSL Certificate**:
   ```bash
   sudo certbot --nginx -d yourdomain.com
   ```

3. **Auto-renewal**:
   ```bash
   sudo crontab -e
   # Add: 0 12 * * * /usr/bin/certbot renew --quiet
   ```

## 🔧 Configuration

### Q: How do I change the default role for new users?

**A:** You can change the default role in several ways:

1. **In Registration Controller**:
   ```php
   // In RegisteredUserController
   protected function create(array $data)
   {
       $user = User::create([
           'name' => $data['name'],
           'email' => $data['email'],
           'password' => Hash::make($data['password']),
       ]);
       
       $user->assignRole('Author'); // Change default role here
       
       return $user;
   }
   ```

2. **In User Factory** (for testing):
   ```php
   // In UserFactory
   public function definition()
   {
       return [
           'name' => $this->faker->name(),
           'email' => $this->faker->unique()->safeEmail(),
           'password' => Hash::make('password'),
       ];
   }
   
   public function configure()
   {
       return $this->afterCreating(function (User $user) {
           $user->assignRole('Author'); // Default role
       });
   }
   ```

### Q: How do I disable user registration?

**A:** To disable registration:

1. **Remove Registration Routes**:
   ```php
   // In routes/auth.php, comment out or remove:
   // Route::get('register', [RegisteredUserController::class, 'create']);
   // Route::post('register', [RegisteredUserController::class, 'store']);
   ```

2. **Hide Registration Links**:
   ```tsx
   // In your React components, conditionally show registration
   {config.allowRegistration && <RegisterLink />}
   ```

3. **Add Configuration**:
   ```env
   # In .env
   ALLOW_REGISTRATION=false
   ```

### Q: How do I customize the permission names?

**A:** You can customize permission names:

1. **Update Permission Seeder**:
   ```php
   // In PermissionSeeder.php
   $permissions = [
       'view_dashboard', // Instead of 'view dashboard'
       'create_posts',   // Instead of 'create posts'
       // ... other permissions
   ];
   ```

2. **Update Gates**:
   ```php
   // In AppServiceProvider.php
   Gate::define('view-dashboard', function (User $user) {
       return $user->hasPermissionTo('view_dashboard');
   });
   ```

3. **Update Frontend**:
   ```tsx
   // In React components
   user.can('view_dashboard') // Use new permission names
   ```

## 📊 Monitoring & Maintenance

### Q: How do I monitor system performance?

**A:** You can monitor performance using:

1. **Laravel Telescope** (Development):
   ```bash
   composer require laravel/telescope --dev
   php artisan telescope:install
   # Visit /telescope in browser
   ```

2. **Application Logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Database Monitoring**:
   ```sql
   -- Check slow queries
   SHOW VARIABLES LIKE 'slow_query_log';
   SHOW VARIABLES LIKE 'long_query_time';
   ```

4. **Server Monitoring**:
   ```bash
   # Monitor system resources
   htop
   df -h
   free -h
   ```

### Q: How often should I update the system?

**A:** Recommended update schedule:

1. **Security Updates**: Immediately when available
2. **Bug Fixes**: Within 1-2 weeks
3. **Feature Updates**: Monthly or as needed
4. **Major Versions**: After testing in staging

**Update Checklist**:
- [ ] Backup database and code
- [ ] Test in staging environment
- [ ] Review changelog
- [ ] Update dependencies
- [ ] Run migrations
- [ ] Clear caches
- [ ] Test functionality
- [ ] Deploy to production

---

**Still have questions?** Check out our [Troubleshooting](Troubleshooting) guide or [Contact Support](Support) for additional help. 