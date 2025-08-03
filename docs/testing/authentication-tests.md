# Authentication Testing Guide

## Overview

This guide covers testing strategies and best practices for the Multi-Role User Authentication system.

## Test Structure

### Test Files
```
tests/
├── Feature/
│   ├── UIPermissionTest.php           # Main permission tests
│   ├── Admin/
│   │   ├── RoleManagementTest.php     # Role management tests
│   │   └── UserRoleManagementTest.php # User-role assignment tests
│   └── Auth/
│       ├── AuthenticationTest.php     # Basic auth tests
│       └── PasswordConfirmationTest.php
└── Traits/
    └── WithRoles.php                  # Test helpers and factories
```

### Test Coverage
- ✅ Role-based user creation
- ✅ Permission inheritance
- ✅ Frontend data sharing
- ✅ Inertia.js integration
- ✅ Computed properties validation
- ✅ Admin panel access control
- ✅ Role management functionality

## Running Tests

### Basic Commands
```bash
# Run all tests
php artisan test

# Run authentication tests only
php artisan test --filter=UIPermissionTest

# Run specific test method
php artisan test --filter=test_inertia_shares_user_data_correctly

# Run with coverage report
php artisan test --coverage --filter=UIPermissionTest
```

### Test Categories
```bash
# Run all authentication tests
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/Admin/
php artisan test tests/Feature/UIPermissionTest.php

# Run specific test class
php artisan test tests/Feature/UIPermissionTest.php
```

## Test Helpers and Traits

### WithRoles Trait
The `WithRoles` trait provides helper methods for creating test users with specific roles:

```php
use Tests\Traits\WithRoles;

class YourTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }
}
```

### Available Helper Methods

#### User Creation
```php
// Create users with specific roles
$superAdmin = $this->createSuperAdmin();
$admin = $this->createAdmin();
$editor = $this->createEditor();
$author = $this->createAuthor();
$subscriber = $this->createSubscriber();

// Create user with custom role
$user = $this->createUserWithRole('Custom Role');
```

#### Assertion Methods
```php
// Assert user has specific role
$this->assertUserHasRole($user, 'Admin');

// Assert user has specific permission
$this->assertUserHasPermission($user, 'create posts');

// Assert user lacks permission
$this->assertUserLacksPermission($user, 'delete users');
```

## Test Scenarios

### 1. Basic Permission Tests

#### Test User Data Sharing
```php
public function test_inertia_shares_user_data_correctly()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->has('auth.user.role_names')
            ->has('auth.user.permission_names')
            ->has('auth.user.is_admin')
            ->where('auth.user.is_admin', true)
            ->where('auth.user.role_names', fn ($roles) => in_array('Admin', $roles))
    );
}
```

#### Test Permission Inheritance
```php
public function test_subscriber_has_limited_permissions()
{
    $subscriber = $this->createSubscriber();

    $response = $this->actingAs($subscriber)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->where('auth.user.is_admin', false)
            ->where('auth.user.is_content_creator', false)
            ->where('auth.user.role_names', ['Subscriber'])
    );
}
```

### 2. Role Management Tests

#### Test Role Assignment
```php
public function test_can_assign_role_to_user()
{
    $user = User::factory()->create();
    $adminRole = Role::where('name', 'Admin')->first();

    $response = $this->actingAs($this->createSuperAdmin())
        ->post('/admin/users/' . $user->id . '/roles', [
            'role_id' => $adminRole->id
        ]);

    $response->assertRedirect();
    $this->assertUserHasRole($user, 'Admin');
}
```

#### Test Role Removal
```php
public function test_can_remove_role_from_user()
{
    $user = $this->createAdmin();

    $response = $this->actingAs($this->createSuperAdmin())
        ->delete('/admin/users/' . $user->id . '/roles/Admin');

    $response->assertRedirect();
    $this->assertFalse($user->fresh()->hasRole('Admin'));
}
```

### 3. Frontend Integration Tests

#### Test Permission Functions
```php
public function test_user_permission_functions_work()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->has('auth.user.can')
            ->has('auth.user.hasRole')
            ->has('auth.user.hasPermissionTo')
    );
}
```

#### Test Computed Properties
```php
public function test_computed_properties_are_correct()
{
    $superAdmin = $this->createSuperAdmin();

    $response = $this->actingAs($superAdmin)->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => 
        $page->where('auth.user.is_admin', true)
            ->where('auth.user.is_content_creator', true)
            ->where('auth.user.role_names', ['Super Admin'])
    );
}
```

### 4. Security Tests

#### Test Unauthorized Access
```php
public function test_unauthorized_users_cannot_access_admin_panel()
{
    $subscriber = $this->createSubscriber();

    $response = $this->actingAs($subscriber)->get('/admin');

    $response->assertForbidden();
}
```

#### Test Permission Bypass Prevention
```php
public function test_users_cannot_bypass_permissions()
{
    $subscriber = $this->createSubscriber();

    $response = $this->actingAs($subscriber)
        ->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

    $response->assertForbidden();
}
```

## Writing New Tests

### Test Template
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class YourFeatureTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_your_feature_works_correctly()
    {
        // Arrange
        $user = $this->createAdmin();

        // Act
        $response = $this->actingAs($user)->get('/your-route');

        // Assert
        $response->assertOk();
        // Add your specific assertions
    }
}
```

### Best Practices

#### 1. Use Descriptive Test Names
```php
// Good
public function test_admin_can_create_new_users()
public function test_subscriber_cannot_access_admin_panel()
public function test_permission_inheritance_works_correctly()

// Avoid
public function test_user_creation()
public function test_access_denied()
```

#### 2. Follow AAA Pattern
```php
public function test_admin_can_delete_users()
{
    // Arrange
    $admin = $this->createAdmin();
    $userToDelete = User::factory()->create();

    // Act
    $response = $this->actingAs($admin)
        ->delete('/admin/users/' . $userToDelete->id);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
}
```

#### 3. Test Edge Cases
```php
public function test_super_admin_can_manage_all_roles()
{
    $superAdmin = $this->createSuperAdmin();
    
    // Test role creation
    $response = $this->actingAs($superAdmin)
        ->post('/admin/roles', ['name' => 'New Role']);
    $response->assertRedirect();
    
    // Test role deletion
    $role = Role::where('name', 'New Role')->first();
    $response = $this->actingAs($superAdmin)
        ->delete('/admin/roles/' . $role->id);
    $response->assertRedirect();
}
```

## Performance Testing

### Database Query Testing
```php
public function test_permission_checks_are_optimized()
{
    $user = $this->createAdmin();
    
    DB::enableQueryLog();
    
    $this->actingAs($user)->get('/dashboard');
    
    $queries = DB::getQueryLog();
    
    // Assert minimal queries for permission loading
    $this->assertLessThan(10, count($queries));
}
```

### Memory Usage Testing
```php
public function test_no_memory_leaks_in_permission_checks()
{
    $user = $this->createSuperAdmin();
    
    $initialMemory = memory_get_usage();
    
    for ($i = 0; $i < 100; $i++) {
        $this->actingAs($user)->get('/dashboard');
    }
    
    $finalMemory = memory_get_usage();
    $memoryIncrease = $finalMemory - $initialMemory;
    
    // Assert reasonable memory usage
    $this->assertLessThan(1024 * 1024, $memoryIncrease); // 1MB
}
```

## Continuous Integration

### GitHub Actions Configuration
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: thorium90_test
          MYSQL_ROOT_PASSWORD: password
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, filter, gd, iconv, json, mbstring, pdo
        coverage: xdebug
    
    - name: Copy .env
      run: php -r "file_exists('.env') || copy('.env.example', '.env');"
    
    - name: Install Dependencies
      run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
    
    - name: Generate key
      run: php artisan key:generate
    
    - name: Configure Database
      run: |
        php artisan migrate --force
        php artisan db:seed --force
    
    - name: Execute tests (Unit and Feature tests) via PHPUnit
      run: vendor/bin/phpunit --coverage-clover coverage.xml
    
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        flags: unittests
        name: codecov-umbrella
        fail_ci_if_error: true
```

## Test Data Management

### Factories
```php
// database/factories/UserFactory.php
public function admin()
{
    return $this->afterCreating(function (User $user) {
        $user->assignRole('Admin');
    });
}

public function editor()
{
    return $this->afterCreating(function (User $user) {
        $user->assignRole('Editor');
    });
}
```

### Seeders
```php
// database/seeders/RoleSeeder.php
public function run()
{
    $this->createRolesAndPermissions();
}
```

## Debugging Tests

### Common Issues

#### 1. Test Database Not Migrated
```bash
php artisan migrate:fresh --env=testing
```

#### 2. Roles Not Created
```php
// Ensure WithRoles trait is used
use Tests\Traits\WithRoles;

protected function setUp(): void
{
    parent::setUp();
    $this->createRolesAndPermissions();
}
```

#### 3. Inertia Assertions Failing
```php
// Check if Inertia is properly configured
$response->assertInertia(fn ($page) => 
    $page->has('auth.user')
);
```

### Debug Commands
```bash
# Run tests with verbose output
php artisan test --verbose

# Run specific test with debug
php artisan test --filter=test_name --stop-on-failure

# Check test database
php artisan tinker --env=testing
```

## Related Documentation
- [Authentication Overview](../authentication/README.md)
- [API Documentation](../authentication/api.md)
- [Troubleshooting Guide](../authentication/troubleshooting.md) 