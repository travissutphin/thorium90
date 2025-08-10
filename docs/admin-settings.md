# Admin Settings System

## Overview

The Admin Settings system provides comprehensive configuration management for the Multi-Role User Authentication system. It allows administrators to configure system-wide settings across multiple categories with proper permission controls, validation, caching, and audit logging.

## Key Features

- **Category-based Organization**: Settings are organized into logical categories (Application, Authentication, User Management, Security, Email, Features, System)
- **Type-safe Value Handling**: Support for string, integer, boolean, JSON, and array data types with automatic casting
- **Permission-based Access Control**: Different permission levels for general settings vs. security settings
- **Caching for Performance**: Automatic caching with cache invalidation on updates
- **Import/Export Functionality**: Backup and restore settings configurations
- **System Statistics**: Real-time monitoring of system health and usage
- **Audit Logging**: Track all setting changes for security and compliance
- **Validation**: Comprehensive validation for different setting types and specific business rules

## Architecture

### Database Schema

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    type ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    category VARCHAR(100),
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX(category),
    INDEX(is_public)
);
```

### Model Structure

The `Setting` model provides a comprehensive API for managing configuration values:

```php
// Basic operations
Setting::get('app.name', 'Default Name');
Setting::set('app.name', 'My App', 'string', 'application', 'App name', true);
Setting::has('app.name');
Setting::forget('app.name');

// Category operations
Setting::getByCategory('application');
Setting::getGroupedByCategory();

// Filtering
Setting::getAll(true); // Public settings only
Setting::getAll(false); // All settings
```

## Settings Categories

### 1. Application Settings
- **app.name**: Application name displayed throughout the system
- **app.description**: Brief description of the application
- **app.version**: Current application version
- **app.timezone**: Default timezone
- **app.locale**: Default language locale
- **app.maintenance_mode**: Enable/disable maintenance mode
- **app.maintenance_message**: Message displayed during maintenance

### 2. Authentication Settings
- **auth.registration_enabled**: Allow new user registration
- **auth.email_verification_required**: Require email verification
- **auth.default_role**: Default role for new users
- **auth.login_attempts_limit**: Maximum login attempts before lockout
- **auth.lockout_duration**: Account lockout duration in minutes
- **auth.session_lifetime**: Session lifetime in minutes
- **auth.remember_me_duration**: Remember me duration in minutes
- **auth.social_login_enabled**: Enable social login providers
- **auth.social_providers**: Array of enabled social providers

### 3. User Management Settings
- **users.profile_photo_enabled**: Allow profile photo uploads
- **users.profile_photo_max_size**: Maximum photo size in KB
- **users.soft_delete_enabled**: Use soft delete for user accounts
- **users.auto_cleanup_deleted**: Automatically cleanup deleted users
- **users.cleanup_after_days**: Days before permanent deletion
- **users.bulk_operations_enabled**: Enable bulk user operations
- **users.export_enabled**: Allow user data export

### 4. Security Settings
- **security.password_min_length**: Minimum password length
- **security.password_require_uppercase**: Require uppercase letters
- **security.password_require_lowercase**: Require lowercase letters
- **security.password_require_numbers**: Require numbers
- **security.password_require_symbols**: Require special characters
- **security.password_history_limit**: Number of previous passwords to remember
- **security.two_factor_required_roles**: Roles requiring 2FA (array)
- **security.two_factor_grace_period**: Days to set up 2FA
- **security.ip_whitelist_enabled**: Enable IP whitelisting
- **security.ip_whitelist**: Whitelisted IP addresses (array)
- **security.audit_log_enabled**: Enable audit logging
- **security.audit_log_retention_days**: Days to retain audit logs

### 5. Email Settings
- **email.from_name**: Default sender name
- **email.from_address**: Default sender email
- **email.welcome_enabled**: Send welcome emails
- **email.password_reset_expiry**: Password reset link expiry (minutes)
- **email.verification_expiry**: Email verification expiry (minutes)
- **email.notification_preferences**: Admin notification settings (JSON)

### 6. Feature Settings
- **features.api_enabled**: Enable API endpoints
- **features.api_rate_limiting**: Enable API rate limiting
- **features.api_rate_limit**: Requests per minute limit
- **features.content_management_enabled**: Enable content features
- **features.media_uploads_enabled**: Enable media uploads
- **features.comments_enabled**: Enable comment system
- **features.notifications_enabled**: Enable notifications
- **features.real_time_updates**: Enable WebSocket updates
- **features.dark_mode_enabled**: Enable dark mode option

### 7. System Settings
- **system.debug_mode**: Enable debug mode
- **system.log_level**: System logging level
- **system.cache_enabled**: Enable application caching
- **system.cache_duration**: Default cache duration (seconds)
- **system.queue_enabled**: Enable background jobs
- **system.backup_enabled**: Enable automatic backups
- **system.backup_frequency**: Backup frequency
- **system.backup_retention_days**: Days to retain backups
- **system.health_check_enabled**: Enable health monitoring
- **system.performance_monitoring**: Enable performance metrics
- **system.error_reporting_enabled**: Enable error tracking

## Permissions

The system uses granular permissions for access control:

### Core Permissions
- **manage settings**: Basic settings management (Admin+)
- **view system stats**: View system statistics (Admin+)
- **manage security settings**: Manage security-related settings (Super Admin only)
- **view audit logs**: View audit logs (Admin+)

### Permission Hierarchy
- **Super Admin**: All permissions including security settings and force delete
- **Admin**: Most settings except security settings
- **Editor/Author/Subscriber**: No settings access

## API Endpoints

### Settings Management
```php
GET    /admin/settings                    # View settings dashboard
PUT    /admin/settings                    # Update multiple settings
PUT    /admin/settings/{key}              # Update single setting
POST   /admin/settings/reset              # Reset settings to defaults
GET    /admin/settings/category/{category} # Get settings by category
```

### Import/Export
```php
GET    /admin/settings/export             # Export settings as JSON
POST   /admin/settings/import             # Import settings from file
```

### Statistics
```php
GET    /admin/settings/stats              # Get system statistics
```

## Usage Examples

### Basic Setting Operations

```php
// Get a setting with default
$appName = Setting::get('app.name', 'Default App');

// Set a setting
Setting::set('app.name', 'My Application', 'string', 'application', 'App name', true);

// Check if setting exists
if (Setting::has('feature.enabled')) {
    // Setting exists
}

// Get all settings in a category
$authSettings = Setting::getByCategory('authentication');

// Get public settings only
$publicSettings = Setting::getAll(true);
```

### Controller Usage

```php
// In a controller
public function updateSettings(Request $request)
{
    $settings = $request->input('settings', []);
    
    foreach ($settings as $key => $data) {
        Setting::set(
            $key,
            $data['value'],
            $data['type'],
            $data['category'],
            $data['description'],
            $data['is_public']
        );
    }
    
    return redirect()->back()->with('success', 'Settings updated successfully.');
}
```

### Frontend Usage

```tsx
// In React components
export default function SettingsPage({ settings, categories }) {
    const [formData, setFormData] = useState(settings);
    
    const handleSubmit = (e) => {
        e.preventDefault();
        router.put('/admin/settings', { settings: formData });
    };
    
    return (
        <form onSubmit={handleSubmit}>
            {/* Settings form */}
        </form>
    );
}
```

## Validation Rules

### Type-specific Validation
- **boolean**: Must be true/false
- **integer**: Must be numeric
- **array**: Must be array format
- **json**: Must be valid JSON

### Business Rule Validation
- **auth.default_role**: Must be existing role name
- **email addresses**: Must be valid email format
- **URLs**: Must be valid URL format
- **IP addresses**: Must be valid IP format

### Custom Validation Example

```php
// In AdminSettingsController
protected function validateSettings(array $settings)
{
    $rules = [];
    
    foreach ($settings as $key => $data) {
        if ($key === 'auth.default_role') {
            $roles = Role::pluck('name')->toArray();
            $rules["settings.{$key}.value"] = ['required', 'string', Rule::in($roles)];
        }
        
        if (str_contains($key, 'email') && str_contains($key, 'address')) {
            $rules["settings.{$key}.value"] = 'required|email';
        }
    }
    
    return Validator::make(['settings' => $settings], $rules);
}
```

## Caching Strategy

### Automatic Caching
- Settings are automatically cached for 24 hours
- Cache keys use the format: `setting:{key}`
- Category caches use: `settings:category:{category}`

### Cache Invalidation
- Cache is cleared when settings are updated
- Model events handle automatic cache clearing
- Manual cache clearing available

```php
// Manual cache operations
Setting::clearCache(); // Clear all settings cache
Cache::forget('setting:app.name'); // Clear specific setting
```

## Security Considerations

### Permission-based Access
- Security settings require special `manage security settings` permission
- Only Super Admins can manage security settings by default
- Regular admins cannot modify security-critical settings

### Audit Logging
- All setting changes are logged with user information
- Includes old and new values for tracking
- Timestamps and IP addresses recorded

### Input Validation
- All inputs validated based on type and business rules
- XSS protection through proper escaping
- SQL injection prevention through Eloquent ORM

## Testing

### Test Coverage
The system includes comprehensive tests covering:

- **Permission-based access control**
- **CRUD operations for all setting types**
- **Validation for different data types**
- **Caching behavior and invalidation**
- **Import/export functionality**
- **Security settings protection**
- **System statistics accuracy**

### Running Tests

```bash
# Run all admin settings tests
php artisan test tests/Feature/Admin/AdminSettingsTest.php

# Run specific test method
php artisan test --filter=test_admin_can_update_settings

# Run with coverage
php artisan test --coverage tests/Feature/Admin/AdminSettingsTest.php
```

### Test Examples

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
```

## Performance Optimization

### Database Optimization
- Indexed columns for faster queries
- Efficient query patterns in model methods
- Minimal database hits through caching

### Frontend Optimization
- Lazy loading of setting categories
- Debounced input handling
- Optimistic UI updates

### Caching Strategy
- Long-term caching for stable settings
- Automatic cache invalidation
- Category-based cache organization

## Troubleshooting

### Common Issues

#### Settings Not Updating
1. Check user permissions
2. Verify validation rules
3. Check cache invalidation
4. Review error logs

#### Cache Issues
```php
// Clear all caches
php artisan cache:clear
Setting::clearCache();
```

#### Permission Errors
```php
// Check user permissions
$user->can('manage settings');
$user->can('manage security settings');
```

#### Validation Failures
- Check setting type matches expected format
- Verify business rule compliance
- Review validation error messages

### Debug Commands

```bash
# Check setting values
php artisan tinker
>>> Setting::get('app.name')
>>> Setting::getByCategory('application')

# Clear caches
php artisan cache:clear
php artisan config:clear

# Run migrations
php artisan migrate
php artisan db:seed --class=SettingsSeeder
```

## Best Practices

### Setting Management
1. **Use descriptive keys**: `app.name` instead of `name`
2. **Organize by category**: Group related settings
3. **Provide descriptions**: Help administrators understand settings
4. **Set appropriate visibility**: Mark public settings correctly

### Performance
1. **Cache frequently accessed settings**
2. **Use appropriate data types**
3. **Minimize database queries**
4. **Implement proper indexing**

### Security
1. **Validate all inputs**
2. **Use permission-based access**
3. **Log setting changes**
4. **Protect sensitive settings**

### Development
1. **Write comprehensive tests**
2. **Document new settings**
3. **Follow naming conventions**
4. **Handle errors gracefully**

## Migration and Deployment

### Initial Setup
```bash
# Run migrations
php artisan migrate

# Seed default settings
php artisan db:seed --class=SettingsSeeder

# Clear caches
php artisan cache:clear
```

### Production Deployment
1. **Backup existing settings** before deployment
2. **Run migrations** to update schema
3. **Seed new settings** if added
4. **Clear caches** after deployment
5. **Verify critical settings** are correct

### Rollback Procedures
1. **Export current settings** before changes
2. **Keep backup of previous settings**
3. **Use import functionality** to restore if needed
4. **Test thoroughly** after rollback

## Related Documentation

- [Authentication Architecture](../wiki/Authentication-Architecture.md)
- [Developer Guide](../wiki/Developer-Guide.md)
- [Testing Strategy](../wiki/Testing-Strategy.md)
- [API Reference](../wiki/API-Reference.md)
- [Troubleshooting](../wiki/Troubleshooting.md)
