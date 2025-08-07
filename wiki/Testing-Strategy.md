# Testing Strategy

This document outlines the comprehensive testing strategy for the Thorium90 Multi-Role User Authentication system, including regression testing procedures, order of operations, and integration with development workflows.

## Table of Contents

1. [Testing Philosophy](#testing-philosophy)
2. [Test Categories](#test-categories)
3. [Regression Testing Workflow](#regression-testing-workflow)
4. [Order of Operations](#order-of-operations)
5. [When to Test](#when-to-test)
6. [Automated Testing Scripts](#automated-testing-scripts)
7. [CI/CD Integration](#cicd-integration)
8. [Performance Testing](#performance-testing)
9. [Security Testing](#security-testing)
10. [Test Data Management](#test-data-management)

## Testing Philosophy

Our testing strategy follows these core principles:

1. **Test Early, Test Often**: Catch issues before they reach production
2. **Automated First**: Automate repetitive tests to save time
3. **Comprehensive Coverage**: Test all authentication components
4. **Real-World Scenarios**: Test actual user workflows
5. **Performance Aware**: Monitor and test performance impacts
6. **Security Focused**: Prioritize security testing

## Test Categories

### 1. Unit Tests
**Purpose**: Test individual components in isolation

**Scope**:
- Model methods and relationships
- Helper functions
- Custom validation rules
- Action classes (Fortify actions)

**Location**: `tests/Unit/`

**Example**:
```php
public function test_user_can_have_multiple_roles()
{
    $user = User::factory()->create();
    $user->assignRole(['Admin', 'Editor']);
    
    $this->assertTrue($user->hasRole('Admin'));
    $this->assertTrue($user->hasRole('Editor'));
    $this->assertCount(2, $user->roles);
}
```

### 2. Feature Tests
**Purpose**: Test complete features and user workflows

**Scope**:
- Authentication flows (login, registration, 2FA)
- API endpoints
- Middleware functionality
- Role and permission management
- Social login integration

**Location**: `tests/Feature/`

**Structure**:
```
tests/Feature/
├── Auth/                    # Authentication tests
│   ├── LoginTest.php
│   ├── RegistrationTest.php
│   ├── PasswordResetTest.php
│   └── EmailVerificationTest.php
├── Admin/                   # Admin functionality
│   ├── UserManagementTest.php
│   └── RoleManagementTest.php
├── TwoFactorAuthenticationTest.php
├── SocialLoginTest.php
├── SanctumApiTest.php
├── MiddlewareTest.php
├── RoleBasedAccessTest.php
└── UIPermissionTest.php
```

### 3. Integration Tests
**Purpose**: Test how components work together

**Scope**:
- Fortify + Sanctum integration
- Socialite + Permission system
- Frontend + Backend data sharing
- Complete authentication workflows

**Example**:
```php
public function test_social_user_can_enable_2fa_and_use_api()
{
    // 1. Create user via social login
    $this->mockSocialiteUser();
    $this->get('/auth/google/callback');
    
    // 2. Enable 2FA
    $this->actingAs($user)->post('/user/two-factor-authentication');
    
    // 3. Create API token
    $response = $this->post('/api/tokens', ['name' => 'test-token']);
    
    // 4. Use token to access API
    $this->withToken($response->json('token'))
        ->get('/api/user')
        ->assertOk();
}
```

### 4. Browser Tests (Dusk)
**Purpose**: Test JavaScript interactions and full user experience

**Scope**:
- React component interactions
- Permission-based UI rendering
- 2FA QR code scanning simulation
- Social login flows

**Example**:
```php
public function test_admin_can_manage_roles_via_ui()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->createAdmin())
            ->visit('/admin/roles')
            ->click('@create-role-button')
            ->type('name', 'New Role')
            ->check('permissions[]', 'view dashboard')
            ->press('Create Role')
            ->assertSee('Role created successfully');
    });
}
```

## Regression Testing Workflow

### Overview
Regression testing ensures that new changes don't break existing functionality. Our automated regression test suite runs through all critical paths.

### Workflow Diagram
```
┌─────────────────┐
│ 1. Environment  │
│    Setup        │
└────────┬────────┘
         │
┌────────▼────────┐
│ 2. Database     │
│    Validation   │
└────────┬────────┘
         │
┌────────▼────────┐
│ 3. Core Auth    │
│    Tests        │
└────────┬────────┘
         │
┌────────▼────────┐
│ 4. API Tests    │
└────────┬────────┘
         │
┌────────▼────────┐
│ 5. Social Login │
│    Tests        │
└────────┬────────┘
         │
┌────────▼────────┐
│ 6. Permission   │
│    Tests        │
└────────┬────────┘
         │
┌────────▼────────┐
│ 7. Frontend     │
│    Integration  │
└────────┬────────┘
         │
┌────────▼────────┐
│ 8. Performance  │
│    Validation   │
└────────┬────────┘
         │
┌────────▼────────┐
│ 9. Report       │
│    Generation   │
└─────────────────┘
```

## Order of Operations

### Phase 1: Environment Setup (30 seconds)
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fresh database
php artisan migrate:fresh --seed

# Verify environment
php artisan about
```

**What to Check**:
- Laravel version (should be 12.x)
- PHP version (should be 8.2+)
- Database connection
- Cache drivers
- Queue configuration

### Phase 2: Database Integrity (1 minute)
```bash
# Run database tests
php artisan test tests/Feature/DatabaseIntegrityTest.php
```

**What to Verify**:
- All migrations run successfully
- Seeders create expected data:
  - 5 roles (Super Admin, Admin, Editor, Author, Subscriber)
  - 22+ permissions
  - Role-permission relationships
- Foreign key constraints are proper
- Indexes are created

### Phase 3: Core Authentication - Fortify (3-5 minutes)
```bash
# Run Fortify tests
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/TwoFactorAuthenticationTest.php
```

**Test Coverage**:
1. **Registration**:
   - User can register
   - Validation rules work
   - Default role assigned
   - Email verification sent

2. **Login**:
   - Valid credentials work
   - Invalid credentials rejected
   - Remember me functionality
   - Rate limiting active

3. **Two-Factor Authentication**:
   - 2FA can be enabled
   - QR code generated
   - TOTP codes work
   - Recovery codes function
   - Role-based 2FA enforcement

4. **Password Management**:
   - Password reset flow
   - Password complexity rules
   - Password update functionality

### Phase 4: API Authentication - Sanctum (2-3 minutes)
```bash
# Run Sanctum tests
php artisan test tests/Feature/SanctumApiTest.php
```

**Test Coverage**:
1. **Token Management**:
   - Token creation
   - Token authentication
   - Token revocation
   - Token abilities

2. **API Access**:
   - Authenticated endpoints
   - Role-based API access
   - Permission checking
   - CSRF protection

### Phase 5: Social Authentication - Socialite (2-3 minutes)
```bash
# Run Socialite tests
php artisan test tests/Feature/SocialLoginTest.php
```

**Test Coverage**:
1. **OAuth Flows**:
   - Provider redirects
   - Callback handling
   - User creation
   - Existing user linking

2. **Provider Testing**:
   - Google
   - GitHub
   - Facebook
   - LinkedIn
   - Twitter/X
   - GitLab

### Phase 6: Authorization - Permissions (2-3 minutes)
```bash
# Run permission tests
php artisan test tests/Feature/RoleBasedAccessTest.php
php artisan test tests/Feature/MiddlewareTest.php
```

**Test Coverage**:
1. **Role Management**:
   - Role assignment
   - Role removal
   - Multiple roles
   - Role hierarchy

2. **Permission Checking**:
   - Direct permissions
   - Role-based permissions
   - Permission inheritance
   - Wildcard permissions

3. **Middleware**:
   - Route protection
   - Unauthorized access
   - Multiple middleware

### Phase 7: Frontend Integration (1-2 minutes)
```bash
# Run frontend integration tests
php artisan test tests/Feature/UIPermissionTest.php
```

**Test Coverage**:
1. **Data Sharing**:
   - User data in props
   - Permission arrays
   - Computed properties
   - Helper functions

2. **Inertia.js**:
   - Page components receive data
   - Shared data structure
   - Authentication state

### Phase 8: Performance Validation (1-2 minutes)
```bash
# Run performance tests
php artisan test tests/Feature/PerformanceTest.php --filter=authentication
```

**Benchmarks**:
- Login response: < 200ms
- Permission check: < 10ms
- Role loading: < 100ms
- API token validation: < 50ms

### Phase 9: Security Testing (2-3 minutes)
```bash
# Run security tests
php artisan test tests/Feature/SecurityTest.php
```

**Test Coverage**:
- SQL injection prevention
- XSS protection
- CSRF validation
- Rate limiting
- Password security
- Token security

## When to Test

### 1. During Development

**Every Code Change**:
```bash
# Quick test for current feature
php artisan test --filter=FeatureName

# Run related tests
php artisan test tests/Feature/Auth/ --parallel
```

**Before Commits**:
```bash
# Run full test suite
php artisan test

# With coverage
php artisan test --coverage --min=80
```

### 2. Pre-Deployment

**Staging Deployment**:
```bash
# Full regression test
./regression-test.sh

# Performance tests
php artisan test tests/Feature/PerformanceTest.php

# Security audit
php artisan test tests/Feature/SecurityTest.php
```

**Production Deployment**:
```bash
# Smoke tests after deployment
php artisan test tests/Feature/SmokeTest.php

# Critical path validation
php artisan test --group=critical
```

### 3. Scheduled Testing

**Daily**:
- Run full test suite on CI/CD
- Check for deprecated features
- Validate external integrations

**Weekly**:
- Full regression testing
- Performance benchmarking
- Security scanning

**Monthly**:
- Dependency updates testing
- Browser compatibility testing
- Load testing

## Automated Testing Scripts

### Windows: `regression-test.bat`
```batch
@echo off
echo ========================================
echo Multi-Role Auth System - Regression Test
echo ========================================

REM Environment setup
call :setup_environment
if %errorlevel% neq 0 exit /b 1

REM Run test phases
call :run_database_tests
call :run_auth_tests
call :run_api_tests
call :run_permission_tests
call :run_integration_tests

REM Generate report
call :generate_report

exit /b 0
```

### Linux/Mac: `regression-test.sh`
```bash
#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Test execution with timing
run_test_suite() {
    local suite=$1
    local start=$(date +%s)
    
    echo -e "${YELLOW}Running $suite tests...${NC}"
    php artisan test $suite
    
    local end=$(date +%s)
    local duration=$((end - start))
    echo -e "${GREEN}$suite completed in ${duration}s${NC}"
}

# Main execution
main() {
    # Setup
    setup_environment
    
    # Run tests in order
    run_test_suite "tests/Feature/DatabaseIntegrityTest.php"
    run_test_suite "tests/Feature/Auth"
    run_test_suite "tests/Feature/TwoFactorAuthenticationTest.php"
    run_test_suite "tests/Feature/SanctumApiTest.php"
    run_test_suite "tests/Feature/SocialLoginTest.php"
    run_test_suite "tests/Feature/RoleBasedAccessTest.php"
    run_test_suite "tests/Feature/MiddlewareTest.php"
    run_test_suite "tests/Feature/UIPermissionTest.php"
    
    # Generate report
    generate_report
}

main "$@"
```

### Quick Validation Script
```bash
#!/bin/bash
# quick-test.sh - For rapid validation during development

php artisan test --parallel --stop-on-failure \
    tests/Feature/Auth/LoginTest.php \
    tests/Feature/RoleBasedAccessTest.php \
    tests/Feature/SanctumApiTest.php
```

## CI/CD Integration

### GitHub Actions
```yaml
name: Authentication System Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]
  schedule:
    - cron: '0 2 * * *'  # Daily at 2 AM

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, dom, fileinfo, mysql
        coverage: xdebug

    - name: Install Dependencies
      run: |
        composer install --no-interaction --prefer-dist
        npm ci
        npm run build

    - name: Setup Environment
      run: |
        cp .env.example .env
        php artisan key:generate
        php artisan migrate --seed

    - name: Run Tests
      run: |
        php artisan test --coverage --min=80
        
    - name: Run Security Tests
      run: php artisan test tests/Feature/SecurityTest.php
      
    - name: Performance Tests
      run: php artisan test tests/Feature/PerformanceTest.php
      
    - name: Upload Coverage
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        
    - name: Upload Test Results
      if: always()
      uses: actions/upload-artifact@v3
      with:
        name: test-results
        path: tests/_output
```

### GitLab CI
```yaml
stages:
  - build
  - test
  - security
  - deploy

variables:
  MYSQL_ROOT_PASSWORD: password
  MYSQL_DATABASE: testing
  DB_HOST: mysql

test:auth:
  stage: test
  image: php:8.2
  services:
    - mysql:8.0
  script:
    - composer install
    - php artisan migrate --seed
    - php artisan test tests/Feature/Auth/
  artifacts:
    reports:
      junit: tests/_output/junit.xml

test:api:
  stage: test
  script:
    - php artisan test tests/Feature/SanctumApiTest.php
    
test:permissions:
  stage: test
  script:
    - php artisan test tests/Feature/RoleBasedAccessTest.php

security:scan:
  stage: security
  script:
    - php artisan test tests/Feature/SecurityTest.php
    - ./vendor/bin/security-checker security:check
```

## Performance Testing

### Load Testing with Artillery
```yaml
# load-test.yml
config:
  target: "http://localhost:8000"
  phases:
    - duration: 60
      arrivalRate: 10
      name: "Warm up"
    - duration: 300
      arrivalRate: 50
      name: "Sustained load"

scenarios:
  - name: "User Login Flow"
    flow:
      - get:
          url: "/login"
      - post:
          url: "/login"
          json:
            email: "test@example.com"
            password: "password"
      - get:
          url: "/dashboard"
          
  - name: "API Token Usage"
    flow:
      - post:
          url: "/api/tokens"
          json:
            email: "api@example.com"
            password: "password"
          capture:
            - json: "$.token"
              as: "token"
      - get:
          url: "/api/user"
          headers:
            Authorization: "Bearer {{ token }}"
```

### Performance Benchmarks
```php
// tests/Feature/PerformanceTest.php
public function test_authentication_performance_benchmarks()
{
    $start = microtime(true);
    
    // Test login performance
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
    
    $loginTime = (microtime(true) - $start) * 1000;
    $this->assertLessThan(200, $loginTime, 'Login took too long');
    
    // Test permission check performance
    $user = User::find(1);
    $start = microtime(true);
    
    for ($i = 0; $i < 100; $i++) {
        $user->can('create posts');
    }
    
    $permissionTime = ((microtime(true) - $start) * 1000) / 100;
    $this->assertLessThan(10, $permissionTime, 'Permission check too slow');
}
```

## Security Testing

### Security Test Suite
```php
// tests/Feature/SecurityTest.php
class SecurityTest extends TestCase
{
    public function test_sql_injection_prevention()
    {
        $maliciousInput = "admin' OR '1'='1";
        
        $response = $this->post('/login', [
            'email' => $maliciousInput,
            'password' => 'password',
        ]);
        
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['email' => $maliciousInput]);
    }
    
    public function test_xss_prevention()
    {
        $user = User::factory()->create([
            'name' => '<script>alert("XSS")</script>',
        ]);
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;');
    }
    
    public function test_csrf_protection()
    {
        $user = User::factory()->create();
        
        // Without CSRF token
        $response = $this->actingAs($user)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/user/profile-information', [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);
            
        $response->assertStatus(419); // CSRF token mismatch
    }
    
    public function test_rate_limiting()
    {
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }
        
        $response->assertStatus(429); // Too Many Requests
    }
}
```

### Security Checklist
- [ ] Input validation on all forms
- [ ] SQL injection prevention via Eloquent
- [ ] XSS protection in Blade templates
- [ ] CSRF tokens on all POST requests
- [ ] Rate limiting on authentication endpoints
- [ ] Secure password hashing (bcrypt/argon2)
- [ ] HTTPS enforcement in production
- [ ] Security headers configured
- [ ] Session security settings
- [ ] API token expiration

## Test Data Management

### Factories
```php
// database/factories/UserFactory.php
public function withRole(string $role): static
{
    return $this->afterCreating(function (User $user) use ($role) {
        $user->assignRole($role);
    });
}

public function withPermissions(array $permissions): static
{
    return $this->afterCreating(function (User $user) use ($permissions) {
        $user->givePermissionTo($permissions);
    });
}

public function with2FA(): static
{
    return $this->state(function (array $attributes) {
        return [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ];
    });
}
```

### Test Helpers
```php
// tests/TestCase.php
protected function createAdmin(): User
{
    return User::factory()
        ->withRole('Admin')
        ->with2FA()
        ->create();
}

protected function createApiUser(): User
{
    $user = User::factory()->withRole('Subscriber')->create();
    $user->tokens()->create([
        'name' => 'test-token',
        'token' => hash('sha256', 'test-token'),
        'abilities' => ['*'],
    ]);
    
    return $user;
}

protected function mockSocialiteUser(array $attributes = []): void
{
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn($attributes['id'] ?? '123456');
    $socialiteUser->shouldReceive('getEmail')->andReturn($attributes['email'] ?? 'test@example.com');
    $socialiteUser->shouldReceive('getName')->andReturn($attributes['name'] ?? 'Test User');
    $socialiteUser->shouldReceive('getAvatar')->andReturn($attributes['avatar'] ?? 'https://example.com/avatar.jpg');
    
    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
}
```

### Database Seeders for Testing
```php
// database/seeders/TestingSeeder.php
class TestingSeeder extends Seeder
{
    public function run(): void
    {
        // Create users for each role
        $roles = ['Super Admin', 'Admin', 'Editor', 'Author', 'Subscriber'];
        
        foreach ($roles as $role) {
            User::factory()
                ->withRole($role)
                ->create([
                    'email' => strtolower(str_replace(' ', '', $role)) . '@example.com',
                    'password' => bcrypt('password'),
                ]);
        }
        
        // Create users with various permission combinations
        User::factory()
            ->withPermissions(['create posts', 'edit posts'])
            ->create(['email' => 'content.creator@example.com']);
            
        // Create social login users
        User::factory()->create([
            'email' => 'social@example.com',
            'provider' => 'google',
            'provider_id' => 'google123',
        ]);
        
        // Create API users with tokens
        $apiUser = User::factory()->create(['email' => 'api@example.com']);
        $apiUser->createToken('test-token', ['read', 'write']);
    }
}
```

## Troubleshooting Test Failures

### Common Issues and Solutions

1. **Database Connection Errors**
   ```bash
   # Check database configuration
   php artisan config:clear
   php artisan migrate:fresh --seed
   ```

2. **Permission Cache Issues**
   ```bash
   # Clear permission cache
   php artisan permission:cache-reset
   php artisan cache:clear
   ```

3. **Failed Assertions**
   ```php
   // Add debugging
   $response->dump(); // See full response
   $response->dumpHeaders(); // Check headers
   $response->dumpSession(); // Check session data
   ```

4. **Timing Issues**
   ```php
   // Add waits for async operations
   $this->artisan('queue:work --once');
   sleep(1); // Give time for background jobs
   ```

5. **Social Login Mock Failures**
   ```php
   // Ensure Socialite is properly mocked
   $this->beforeApplicationDestroyed(function () {
       Mockery::close();
   });
   ```

## Related Documentation

- [Authentication Architecture](Authentication-Architecture.md) - Component overview
- [Developer Guide](Developer-Guide.md) - Implementation details
- [TESTING.md](../TESTING.md) - Quick testing reference
- [Troubleshooting](Troubleshooting.md) - Common issues
