# Testing Strategy

## Overview

This document outlines the comprehensive testing strategy for the Thorium90 application, including unit tests, feature tests, integration tests, and regression testing procedures.

## Testing Philosophy

Our testing approach follows these principles:
- **Test-Driven Development (TDD)** where appropriate
- **Comprehensive Coverage** for critical paths
- **Regression Prevention** through automated testing
- **Continuous Integration** with automated test runs
- **Performance Monitoring** for key operations

## Test Structure

```
tests/
├── Feature/
│   ├── Admin/
│   │   ├── UserRoleManagementTest.php
│   │   ├── SettingsManagementTest.php
│   │   └── RoleControllerTest.php
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── PasswordUpdateTest.php
│   │   └── RegistrationTest.php
│   ├── Content/
│   │   ├── PageManagementTest.php
│   │   ├── PageSEOTest.php
│   │   └── SitemapTest.php
│   ├── Settings/
│   │   └── SettingsAccessTest.php
│   ├── DashboardTest.php
│   ├── MiddlewareTest.php
│   ├── ResendEmailTest.php
│   ├── RoleBasedAccessTest.php
│   ├── SanctumApiTest.php
│   └── SocialLoginTest.php
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── PageTest.php
│   │   └── SettingTest.php
│   └── Services/
│       └── SEOServiceTest.php
└── Traits/
    └── WithRoles.php
```

## Test Categories

### 1. Unit Tests
Test individual components in isolation:
- Model methods and relationships
- Service classes
- Helper functions
- Data transformations

### 2. Feature Tests
Test complete features end-to-end:
- Authentication flows
- Role and permission management
- Content management (Pages CMS)
- Settings management
- API endpoints

### 3. Integration Tests
Test interactions between components:
- Database transactions
- External service integrations
- Cache operations
- Queue jobs

### 4. Browser Tests (Dusk)
Test JavaScript-heavy interactions:
- React component interactions
- Real-time updates
- Complex UI workflows
- File uploads

## Order of Operations for Testing

### Phase 1: Foundation Tests
Run these tests first as they verify core functionality:

```bash
# 1. Database and Migration Tests
php artisan test tests/Feature/DatabaseTest.php

# 2. Authentication Tests
php artisan test tests/Feature/Auth/

# 3. Middleware Tests
php artisan test tests/Feature/MiddlewareTest.php
```

### Phase 2: Permission System Tests
Verify the role and permission system:

```bash
# 4. Role-Based Access Tests
php artisan test tests/Feature/RoleBasedAccessTest.php

# 5. Admin Role Management Tests
php artisan test tests/Feature/Admin/UserRoleManagementTest.php

# 6. Permission Middleware Tests
php artisan test tests/Feature/PermissionMiddlewareTest.php
```

### Phase 3: Feature Tests
Test specific application features:

```bash
# 7. Dashboard Tests
php artisan test tests/Feature/DashboardTest.php

# 8. Settings Management Tests
php artisan test tests/Feature/Settings/

# 9. Content Management Tests
php artisan test tests/Feature/Content/
```

### Phase 4: API Tests
Test API endpoints and integrations:

```bash
# 10. Sanctum API Tests
php artisan test tests/Feature/SanctumApiTest.php

# 11. Social Login Tests
php artisan test tests/Feature/SocialLoginTest.php
```

### Phase 5: Full Regression Suite
Run complete test suite:

```bash
# Run all tests
php artisan test

# Or with coverage
php artisan test --coverage
```

## Regression Testing Procedures

### When to Run Regression Tests

1. **Before Every Deployment**
   - Run full test suite
   - Check code coverage (minimum 80%)
   - Verify no breaking changes

2. **After Major Changes**
   - Database schema changes
   - Authentication system updates
   - Permission structure modifications
   - Route changes

3. **During Development**
   - Run related tests after each change
   - Full suite before committing
   - Continuous integration on push

### Regression Test Checklist

#### Authentication System
- [ ] User registration works
- [ ] Login/logout functions properly
- [ ] Password reset flow completes
- [ ] Email verification sends and confirms
- [ ] Two-factor authentication enables/disables
- [ ] Social login integrations work

#### Permission System
- [ ] All roles have correct permissions
- [ ] Permission checks work in controllers
- [ ] Middleware blocks unauthorized access
- [ ] Frontend shows/hides based on permissions
- [ ] Role assignment works correctly

#### Content Management (Pages)
- [ ] Pages can be created with all fields
- [ ] SEO metadata saves correctly
- [ ] Schema markup generates properly
- [ ] Sitemap includes published pages
- [ ] Soft delete and restore work
- [ ] Publishing workflow functions

#### Settings Management
- [ ] Settings load correctly
- [ ] Updates persist to database
- [ ] Cache clears on update
- [ ] Import/export functions work
- [ ] Category filtering works

#### API Functionality
- [ ] API authentication works
- [ ] Rate limiting applies correctly
- [ ] CORS headers set properly
- [ ] JSON responses formatted correctly
- [ ] Error handling returns proper codes

## Test Data Management

### Factories
Use factories for consistent test data:

```php
// User Factory
User::factory()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);

// Page Factory
Page::factory()->published()->create();
Page::factory()->draft()->count(5)->create();
```

### Seeders for Testing
Special seeders for test environments:

```php
// TestDataSeeder.php
class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create test users with each role
        $this->createTestUsers();
        
        // Create sample pages
        $this->createSamplePages();
        
        // Set up test settings
        $this->createTestSettings();
    }
}
```

### Database Transactions
Use database transactions to keep tests isolated:

```php
use RefreshDatabase;  // Migrations run for each test
// or
use DatabaseTransactions;  // Rollback after each test
```

## Writing Effective Tests

### Test Structure Template

```php
class PageManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }
    
    /** @test */
    public function admin_can_create_page_with_seo_data()
    {
        // Arrange
        $admin = $this->createUserWithRole('Admin');
        $pageData = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'meta_title' => 'SEO Title',
        ];
        
        // Act
        $response = $this->actingAs($admin)
            ->post('/content/pages', $pageData);
        
        // Assert
        $response->assertRedirect('/content/pages');
        $this->assertDatabaseHas('pages', [
            'slug' => 'test-page',
        ]);
    }
}
```

### Testing Best Practices

1. **Test One Thing**: Each test should verify a single behavior
2. **Use Descriptive Names**: Test names should explain what they test
3. **Follow AAA Pattern**: Arrange, Act, Assert
4. **Keep Tests Independent**: No test should depend on another
5. **Use Factories**: Don't hardcode test data
6. **Mock External Services**: Don't make real API calls in tests
7. **Test Edge Cases**: Include boundary conditions and error paths

## Performance Testing

### Load Testing
Use Laravel's built-in tools or external services:

```bash
# Using Artillery for load testing
artillery quick --count 100 --num 10 http://localhost:8000/api/pages
```

### Query Optimization Tests
Monitor database queries in tests:

```php
/** @test */
public function pages_index_uses_eager_loading()
{
    DB::enableQueryLog();
    
    $this->get('/content/pages');
    
    $queries = DB::getQueryLog();
    $this->assertLessThan(5, count($queries), 'Too many queries executed');
}
```

## Continuous Integration

### GitHub Actions Workflow

```yaml
name: Tests

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
        
    - name: Install Dependencies
      run: composer install
      
    - name: Generate key
      run: php artisan key:generate
      
    - name: Run Tests
      run: php artisan test --parallel
      
    - name: Generate Coverage Report
      run: php artisan test --coverage-html coverage
      
    - name: Upload Coverage
      uses: actions/upload-artifact@v2
      with:
        name: coverage
        path: coverage
```

## Test Coverage Requirements

### Minimum Coverage Targets
- **Overall**: 80%
- **Controllers**: 90%
- **Models**: 85%
- **Services**: 95%
- **Middleware**: 100%

### Generating Coverage Reports

```bash
# HTML Report
php artisan test --coverage-html coverage

# Console Report
php artisan test --coverage

# Clover XML (for CI tools)
php artisan test --coverage-clover coverage.xml
```

## Debugging Failed Tests

### Common Issues and Solutions

#### 1. Permission Denied Errors
```php
// Solution: Ensure roles and permissions are seeded
$this->seed([RoleSeeder::class, PermissionSeeder::class]);
```

#### 2. Route Not Found
```php
// Solution: Check route definitions and middleware
$this->withoutMiddleware(); // Skip middleware for testing
```

#### 3. Database Connection Issues
```php
// Solution: Use separate testing database
// In .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

#### 4. Failed Assertions
```php
// Use dd() to debug
$response = $this->get('/pages');
dd($response->content()); // Inspect response
```

## Test Utilities

### Custom Test Helpers

```php
// tests/Traits/WithRoles.php
trait WithRoles
{
    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }
    
    protected function actingAsRole(string $role)
    {
        return $this->actingAs($this->createUserWithRole($role));
    }
}
```

### Test Commands

```bash
# Run specific test file
php artisan test tests/Feature/PageManagementTest.php

# Run specific test method
php artisan test --filter test_admin_can_create_page

# Run tests in parallel
php artisan test --parallel

# Stop on first failure
php artisan test --stop-on-failure

# Run only unit tests
php artisan test --testsuite=Unit

# Run with verbose output
php artisan test -v
```

## Monitoring Test Health

### Metrics to Track
1. **Test Execution Time**: Keep under 5 minutes for full suite
2. **Flaky Tests**: Identify and fix intermittent failures
3. **Coverage Trends**: Ensure coverage doesn't decrease
4. **Test Count**: Track growth of test suite
5. **Failure Rate**: Monitor test stability

### Test Quality Indicators
- Tests run quickly (< 100ms per test average)
- No flaky tests (100% consistent results)
- High coverage with meaningful assertions
- Clear test names and documentation
- Regular test maintenance and updates

## Quick Reference Commands

```bash
# Initial setup
composer install
cp .env.example .env.testing
php artisan key:generate --env=testing

# Database setup
php artisan migrate --env=testing
php artisan db:seed --env=testing

# Run tests
php artisan test                    # Run all tests
php artisan test --parallel         # Run in parallel
php artisan test --coverage         # With coverage
php artisan test --profile          # Show slow tests

# Specific suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Debugging
php artisan test --stop-on-failure  # Stop on first failure
php artisan test -v                 # Verbose output
php artisan test --filter=keyword   # Run matching tests

# Coverage reports
php artisan test --coverage-html reports/
php artisan test --coverage-text
```

## Troubleshooting Guide

### Problem: Tests fail locally but pass in CI
**Solution**: Check environment differences, ensure same PHP version, database type, and dependencies.

### Problem: Slow test execution
**Solution**: Use in-memory SQLite, run in parallel, mock external services, optimize factories.

### Problem: Inconsistent test results
**Solution**: Check for shared state, use RefreshDatabase trait, ensure proper tearDown, avoid time-dependent tests.

### Problem: Cannot reproduce production bugs
**Solution**: Add integration tests, use production-like data, test with same configurations, add logging.

## Next Steps

1. **Set up CI/CD pipeline** with automated testing
2. **Implement browser testing** with Laravel Dusk
3. **Add performance benchmarks** to tests
4. **Create test documentation** for new developers
5. **Establish code review process** including test review
