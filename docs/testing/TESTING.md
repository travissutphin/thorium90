# Multi-Role User Authentication System - Testing Guide

This document provides comprehensive guidance for testing the Multi-Role User Authentication system, including regression testing procedures, test execution order, and troubleshooting common issues.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Test Environment Setup](#test-environment-setup)
3. [Testing Workflow](#testing-workflow)
4. [Automated Testing Scripts](#automated-testing-scripts)
5. [Manual Testing Procedures](#manual-testing-procedures)
6. [Test Categories](#test-categories)
7. [Expected Results](#expected-results)
8. [Troubleshooting](#troubleshooting)
9. [Performance Benchmarks](#performance-benchmarks)
10. [Security Testing](#security-testing)

## Quick Start

### For Windows Users
```bash
# Run the automated regression test
scripts/regression-test.bat

# Or run specific test suites
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/Admin/
```

### For Linux/Mac Users
```bash
# Make script executable (first time only)
chmod +x regression-test.sh

# Run the automated regression test
./regression-test.sh

# Run quick validation only
./regression-test.sh --quick

# Setup environment only
./regression-test.sh --setup
```

## Test Environment Setup

### Prerequisites
- PHP 8.1+
- Laravel 11+
- Composer
- SQLite/MySQL database
- Spatie Laravel Permission package

### Environment Preparation
```bash
# 1. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 2. Fresh database setup
php artisan migrate:fresh --seed

# 3. Verify seeding
php artisan tinker
>>> \Spatie\Permission\Models\Role::count()  # Should return 5
>>> \Spatie\Permission\Models\Permission::count()  # Should return 22+
>>> exit
```

## Testing Workflow

### Phase 1: Environment Validation
**Purpose**: Ensure the testing environment is properly configured

**Tests**:
- Laravel installation verification
- Database connectivity
- Package dependencies
- Configuration validation

**Expected Duration**: 30 seconds

### Phase 2: Database Integrity
**Purpose**: Verify roles and permissions are properly seeded

**Tests**:
- Role existence (Super Admin, Admin, Editor, Author, Subscriber)
- Permission count validation (minimum 20 permissions)
- Role-permission relationships
- Database constraints

**Expected Duration**: 1 minute

### Phase 3: Authentication System
**Purpose**: Test core authentication functionality including Laravel Fortify features

**Tests**:
- User registration and login
- Password reset functionality
- Email verification (enhanced with Fortify)
- Session management
- Two-Factor Authentication (2FA)
- Role-based 2FA requirements
- Password complexity validation
- Recovery code management

**Commands**: 
- `php artisan test tests/Feature/Auth/`
- `php artisan test tests/Feature/TwoFactorAuthenticationTest.php`

**Expected Duration**: 3-5 minutes

### Phase 4: Middleware Protection
**Purpose**: Validate route protection and access control

**Tests**:
- Role-based middleware (`EnsureUserHasRole`)
- Permission-based middleware (`EnsureUserHasPermission`)
- Multiple role validation (`EnsureUserHasAnyRole`)
- Guest redirection
- Unauthorized access prevention

**Command**: `php artisan test tests/Feature/MiddlewareTest.php`

**Expected Duration**: 1-2 minutes

### Phase 5: Role Management
**Purpose**: Test role CRUD operations and user assignments

**Tests**:
- Role creation, editing, deletion
- Permission assignment to roles
- User role assignment/removal
- Bulk operations
- Super Admin protection

**Command**: `php artisan test tests/Feature/Admin/`

**Expected Duration**: 2-3 minutes

### Phase 6: Frontend Integration
**Purpose**: Validate UI permission sharing and component rendering

**Tests**:
- Inertia.js data sharing
- User role/permission data structure
- Computed properties
- Permission-based UI rendering

**Command**: `php artisan test tests/Feature/UIPermissionTest.php`

**Expected Duration**: 1-2 minutes

## Automated Testing Scripts

### Windows Script (`scripts/regression-test.bat`)
```batch
# Full regression test
scripts/regression-test.bat

# Features:
- Environment verification
- Database integrity checks
- Complete test suite execution
- Detailed reporting
- Error logging
```

### Linux/Mac Script (`regression-test.sh`)
```bash
# Full regression test
./regression-test.sh

# Quick validation
./regression-test.sh --quick

# Environment setup only
./regression-test.sh --setup

# Help
./regression-test.sh --help
```

### Script Features
- ✅ Colored output for easy reading
- ✅ Progress tracking with counters
- ✅ Detailed error reporting
- ✅ Performance monitoring
- ✅ Automated report generation
- ✅ Exit codes for CI/CD integration

## Manual Testing Procedures

### 1. Role Hierarchy Validation
```php
// In php artisan tinker
$subscriber = \App\Models\User::factory()->create();
$subscriber->assignRole('Subscriber');

$author = \App\Models\User::factory()->create();
$author->assignRole('Author');

$editor = \App\Models\User::factory()->create();
$editor->assignRole('Editor');

$admin = \App\Models\User::factory()->create();
$admin->assignRole('Admin');

$superAdmin = \App\Models\User::factory()->create();
$superAdmin->assignRole('Super Admin');

// Test permission hierarchy
$subscriber->can('view dashboard');  // Should be true
$subscriber->can('create posts');    // Should be false

$author->can('create posts');        // Should be true
$author->can('edit posts');          // Should be false

$editor->can('edit posts');          // Should be true
$editor->can('view users');          // Should be false

$admin->can('view users');           // Should be true
$admin->can('manage roles');         // Should be false

$superAdmin->can('manage roles');    // Should be true
```

### 2. Route Access Testing
```bash
# Test guest access (should redirect to login)
curl -I http://localhost:8000/dashboard

# Test authenticated access
# Login first, then test protected routes
curl -I http://localhost:8000/admin/users
curl -I http://localhost:8000/admin/roles
curl -I http://localhost:8000/content/posts
```

### 3. Frontend Integration Testing
```javascript
// Check browser console for user data
console.log(window.page.props.auth.user);

// Verify role data
console.log(window.page.props.auth.user.role_names);

// Verify permission data
console.log(window.page.props.auth.user.permission_names);

// Verify computed properties
console.log(window.page.props.auth.user.is_admin);
console.log(window.page.props.auth.user.is_content_creator);
```

## Test Categories

### Unit Tests (`tests/Unit/`)
- Model relationships
- Helper functions
- Utility classes
- Basic functionality

### Feature Tests (`tests/Feature/`)
- **Auth/**: Authentication flows
- **Admin/**: Role management operations
- **Settings/**: User settings functionality
- **MiddlewareTest.php**: Route protection
- **RoleBasedAccessTest.php**: Access control
- **UIPermissionTest.php**: Frontend integration

### Integration Tests
- Complete user workflows
- Cross-component functionality
- Database transactions
- API endpoints

## Expected Results

### Successful Test Run
```
🧪 Multi-Role User Authentication System
   Regression Testing Script v1.0

✅ Environment verification complete
✅ Test environment setup complete
✅ Database integrity verified
✅ Authentication tests passed
✅ Middleware tests passed
✅ Role management tests passed
✅ Frontend integration tests passed

📊 TEST RESULTS SUMMARY
Total Tests Run: 25
Passed: 25
Failed: 0

🎉 ALL TESTS PASSED! Success Rate: 100%
✅ Multi-Role Authentication System is working correctly
```

### Test Failure Indicators
- ❌ Database seeding issues
- ❌ Missing permissions or roles
- ❌ Middleware not blocking unauthorized access
- ❌ Frontend not receiving user data
- ❌ Role assignment failures

## Troubleshooting

### Common Issues

#### 1. Database Not Seeded
**Symptoms**: Role/permission count tests fail
**Solution**:
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

#### 2. Middleware Tests Failing
**Symptoms**: 500 errors instead of 403/302
**Solution**:
```bash
# Check middleware registration
php artisan route:list
# Verify middleware aliases in bootstrap/app.php
```

#### 3. Frontend Integration Issues
**Symptoms**: UI tests fail, missing user data
**Solution**:
```bash
# Check Inertia middleware
# Verify HandleInertiaRequests.php
# Clear view cache
php artisan view:clear
```

#### 4. Permission Errors
**Symptoms**: "Permission does not exist" errors
**Solution**:
```bash
# Re-seed permissions
php artisan db:seed --class=PermissionSeeder
# Check permission names match exactly
```

### Debug Commands
```bash
# Check current roles
php artisan tinker --execute="\Spatie\Permission\Models\Role::all()->pluck('name')"

# Check current permissions
php artisan tinker --execute="\Spatie\Permission\Models\Permission::all()->pluck('name')"

# Check user roles
php artisan tinker --execute="\App\Models\User::find(1)->roles->pluck('name')"

# Check user permissions
php artisan tinker --execute="\App\Models\User::find(1)->getAllPermissions()->pluck('name')"
```

## Performance Benchmarks

### Expected Performance Metrics
- **Role Loading**: < 100ms
- **Permission Check**: < 10ms
- **User Authentication**: < 200ms
- **Database Queries**: < 50ms per query
- **Middleware Processing**: < 20ms

### Performance Testing
```bash
# Test role loading performance
php artisan tinker --execute="
\$start = microtime(true);
\$user = \App\Models\User::factory()->create();
\$user->assignRole('Admin');
\$user->load(['roles.permissions']);
\$end = microtime(true);
echo 'Role loading time: ' . round((\$end - \$start) * 1000, 2) . 'ms';
"
```

## Security Testing

### Security Checklist
- [ ] Unauthorized route access blocked
- [ ] Permission escalation prevented
- [ ] Super Admin role protected
- [ ] Session security maintained
- [ ] CSRF protection active
- [ ] Input validation working
- [ ] SQL injection prevention
- [ ] XSS protection enabled

### Security Test Commands
```bash
# Test unauthorized access
curl -I http://localhost:8000/admin/roles  # Should redirect

# Test permission escalation
# Create user with Subscriber role, try to access admin routes

# Test Super Admin protection
# Try to delete Super Admin role or remove last Super Admin
```

## Continuous Integration

### CI/CD Integration
```yaml
# Example GitHub Actions workflow
name: Authentication System Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run regression tests
        run: ./regression-test.sh
```

### Exit Codes
- `0`: All tests passed
- `1`: Some tests failed
- `2`: Environment setup failed
- `3`: Critical system error

## Reporting

### Automated Reports
- **Console Output**: Real-time test results
- **Log File**: `regression-test-report.log`
- **Timestamps**: All test runs timestamped
- **Environment Info**: PHP, Laravel, database versions
- **Performance Metrics**: Query times, loading speeds

### Report Analysis
- **Success Rate**: Percentage of passed tests
- **Failure Patterns**: Common failure points
- **Performance Trends**: Speed improvements/degradations
- **Coverage Metrics**: Test coverage percentages

---

## Support

For issues with the testing system:
1. Check this documentation first
2. Review the automated test output
3. Check the generated log files
4. Verify environment setup
5. Run individual test suites to isolate issues

**Remember**: The authentication system is complex, but these tests ensure it works correctly and securely in all scenarios.
