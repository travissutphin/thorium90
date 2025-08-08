# Thorium90 Multi-Role Authentication System - Development Rules and Workflows

## 🎯 Core Development Principles

### 1. Authentication-First Architecture
- **Rule**: Every feature must consider authentication and authorization implications
- **Workflow**: 
  - Start by defining required roles/permissions for new features
  - Implement server-side authorization before frontend UI
  - Test with multiple user roles to ensure proper access control
  - Document permission requirements in feature specifications

### 2. Component Isolation
- **Rule**: Each authentication component (Fortify, Sanctum, Socialite) has specific responsibilities
- **Workflow**:
  - Use Fortify for user registration, login, 2FA, password management
  - Use Sanctum for API authentication and SPA sessions
  - Use Socialite for OAuth/social login flows
  - Use Spatie Permission for all authorization decisions
  - Never mix concerns between components

### 3. Security by Default
- **Rule**: All new features must follow security best practices
- **Workflow**:
  - Always validate permissions on server-side
  - Use middleware for route protection
  - Implement proper input validation
  - Follow Laravel security conventions
  - Test for common vulnerabilities (SQL injection, XSS, CSRF)

## 🔧 Development Workflows

### Feature Development Workflow

#### Phase 1: Planning & Design
```bash
# 1. Define feature requirements
- Identify required roles and permissions
- Document user stories for each role
- Plan database schema changes
- Design API endpoints

# 2. Create feature branch
git checkout -b feature/your-feature-name

# 3. Update documentation
- Add to wiki/Developer-Guide.md if needed
- Update API documentation
- Create test specifications
```

#### Phase 2: Backend Implementation
```bash
# 1. Database changes
php artisan make:migration add_feature_table
php artisan migrate

# 2. Model creation
php artisan make:model FeatureModel
# Add relationships and Spatie Permission traits

# 3. Controller implementation
php artisan make:controller FeatureController
# Implement with proper authorization checks

# 4. Route protection
# Add middleware to routes/web.php or routes/api.php
Route::middleware(['auth', 'permission:manage-feature'])->group(function () {
    // Feature routes
});
```

#### Phase 3: Frontend Integration
```bash
# 1. Create React components
# Use permission checking patterns from docs/development/frontend-integration.md

# 2. Implement conditional rendering
{user.can('manage-feature') && <FeatureComponent />}

# 3. Add to navigation
{user.hasRole('Admin') && <Link href="/feature">Feature</Link>}
```

#### Phase 4: Testing
```bash
# 1. Create test files
php artisan make:test FeatureTest
# Use WithRoles trait and follow testing patterns

# 2. Run tests
php artisan test tests/Feature/FeatureTest.php

# 3. Run regression tests
./regression-test.sh
```

### Bug Fix Workflow

#### Phase 1: Issue Analysis
```bash
# 1. Reproduce the issue
- Test with different user roles
- Check browser console for errors
- Verify database state

# 2. Identify root cause
- Check authentication/authorization logic
- Verify middleware configuration
- Test permission inheritance
```

#### Phase 2: Fix Implementation
```bash
# 1. Create fix branch
git checkout -b fix/issue-description

# 2. Implement fix
# Follow existing patterns and conventions

# 3. Add regression test
# Ensure the bug cannot happen again
```

#### Phase 3: Validation
```bash
# 1. Test with all user roles
php artisan test --filter=RelatedTest

# 2. Run full regression suite
./regression-test.sh

# 3. Manual testing
# Test the specific scenario that was broken
```

## �� Coding Standards

### PHP/Laravel Standards

#### Authentication Code Patterns
```php
// ✅ Correct: Use middleware for route protection
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// ✅ Correct: Check permissions in controllers
public function store(Request $request)
{
    if (!$request->user()->can('create posts')) {
        abort(403);
    }
    // Implementation
}

// ✅ Correct: Use Gates for complex authorization
Gate::define('edit-post', function (User $user, Post $post) {
    return $user->hasPermissionTo('edit posts') || 
           ($user->hasPermissionTo('edit own posts') && $post->user_id === $user->id);
});
```

#### Model Patterns
```php
// ✅ Correct: Use Spatie Permission traits
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    
    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// ✅ Correct: Add permission scopes
class Post extends Model
{
    public function scopeForUser($query, User $user)
    {
        if ($user->hasPermissionTo('view posts')) {
            return $query;
        }
        
        return $query->where('user_id', $user->id);
    }
}
```

### Frontend/React Standards

#### Permission Checking Patterns
```tsx
// ✅ Correct: Use custom hooks for permissions
function usePermissions() {
  const { auth } = usePage().props;
  const user = auth.user;
  
  return {
    can: (permission: string) => user?.permission_names.includes(permission) ?? false,
    hasRole: (role: string) => user?.role_names.includes(role) ?? false,
    isAdmin: user?.is_admin ?? false,
  };
}

// ✅ Correct: Conditional rendering
function AdminPanel() {
  const { can, isAdmin } = usePermissions();
  
  if (!isAdmin) return null;
  
  return (
    <div>
      {can('manage users') && <UserManagement />}
      {can('manage settings') && <SystemSettings />}
    </div>
  );
}
```

#### Component Structure
```tsx
// ✅ Correct: Protected components
interface ProtectedComponentProps {
  permission?: string;
  role?: string;
  fallback?: React.ReactNode;
  children: React.ReactNode;
}

function ProtectedComponent({ permission, role, fallback, children }: ProtectedComponentProps) {
  const { can, hasRole } = usePermissions();
  
  if (permission && !can(permission)) return <>{fallback}</>;
  if (role && !hasRole(role)) return <>{fallback}</>;
  
  return <>{children}</>;
}
```

## 🧪 Testing Requirements

### Test Structure Standards
```php
// ✅ Correct: Use WithRoles trait
class FeatureTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }
}
```

### Test Coverage Requirements
- **Unit Tests**: 80% minimum coverage
- **Feature Tests**: All authentication flows
- **Integration Tests**: Cross-component functionality
- **Security Tests**: Authorization bypass attempts

### Test Patterns
```php
// ✅ Correct: Test with multiple roles
public function test_feature_requires_admin_permission()
{
    $admin = $this->createAdmin();
    $subscriber = $this->createSubscriber();
    
    // Admin can access
    $this->actingAs($admin)->get('/feature')->assertOk();
    
    // Subscriber cannot access
    $this->actingAs($subscriber)->get('/feature')->assertForbidden();
}

// ✅ Correct: Test permission inheritance
public function test_super_admin_has_all_permissions()
{
    $superAdmin = $this->createSuperAdmin();
    
    $this->assertTrue($superAdmin->can('manage users'));
    $this->assertTrue($superAdmin->can('create posts'));
    $this->assertTrue($superAdmin->hasRole('Super Admin'));
}
```

## 🔒 Security Rules

### Authentication Security
- **Rule**: Never store sensitive data in frontend state
- **Rule**: Always validate permissions server-side
- **Rule**: Use HTTPS in production
- **Rule**: Implement rate limiting on auth endpoints

### Authorization Security
- **Rule**: Check permissions at every entry point
- **Rule**: Use middleware for route protection
- **Rule**: Validate user ownership for resource access
- **Rule**: Log all authorization decisions

### Data Security
- **Rule**: Sanitize all user inputs
- **Rule**: Use prepared statements (Eloquent handles this)
- **Rule**: Validate file uploads
- **Rule**: Implement proper session management

## 📚 Documentation Standards

### Code Documentation
```php
/**
 * User Management Controller
 * 
 * Handles user CRUD operations with proper authorization.
 * Requires 'manage users' permission for all operations.
 * 
 * @see https://spatie.be/docs/laravel-permission
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view users');
        
        $users = User::with('roles')->paginate(15);
        
        return response()->json($users);
    }
}
```

### API Documentation
```markdown
## User Management API

### GET /api/users
List all users (requires 'view users' permission)

**Headers:**
- Authorization: Bearer {token}

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roles": ["Admin"]
    }
  ]
}
```
```

## 🚀 Deployment Workflow

### Pre-Deployment Checklist
- [ ] All tests pass (`./regression-test.sh`)
- [ ] Security scan completed
- [ ] Performance benchmarks met
- [ ] Documentation updated
- [ ] Database migrations tested
- [ ] Environment variables configured

### Deployment Steps
```bash
# 1. Run full regression test
./regression-test.sh

# 2. Deploy to staging
git push origin main

# 3. Run staging tests
# Verify all authentication flows work

# 4. Deploy to production
# Use deployment script with rollback capability

# 5. Post-deployment verification
# Run smoke tests on production
```

## 🔄 Maintenance Workflow

### Regular Maintenance Tasks
```bash
# Weekly
- Update dependencies
- Review security advisories
- Check performance metrics
- Run full test suite

# Monthly
- Audit user permissions
- Review access logs
- Update documentation
- Performance optimization
```

### Monitoring Requirements
- Authentication failure rates
- Permission check performance
- API response times
- Error rates by user role
- Security event logs

## 🎯 Quality Assurance

### Code Review Checklist
- [ ] Authentication/authorization implemented correctly
- [ ] Tests cover all user roles
- [ ] Security best practices followed
- [ ] Documentation updated
- [ ] Performance impact considered
- [ ] Error handling implemented

### Performance Standards
- Authentication response time: < 200ms
- Permission check: < 10ms
- Database queries: < 50ms
- Frontend render time: < 100ms

## 📞 Support and Troubleshooting

### Common Issues and Solutions
```bash
# Permission cache issues
php artisan permission:cache-reset
php artisan cache:clear

# Database seeding problems
php artisan migrate:fresh --seed

# Frontend permission issues
# Check HandleInertiaRequests middleware
# Verify user data structure
```

### Debug Commands
```bash
# Check user permissions
php artisan tinker --execute="User::find(1)->getAllPermissions()->pluck('name')"

# Verify roles
php artisan tinker --execute="Role::all()->pluck('name')"

# Test permission inheritance
php artisan tinker --execute="User::find(1)->hasRole('Admin')"
```

## �� Regression Testing Workflow

### Automated Testing Scripts

#### Windows: `regression-test.bat`
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

#### Linux/Mac: `regression-test.sh`
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

### Test Execution Order

#### Phase 1: Environment Setup (30 seconds)
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

#### Phase 2: Database Integrity (1 minute)
```bash
# Run database tests
php artisan test tests/Feature/DatabaseIntegrityTest.php
```

#### Phase 3: Core Authentication - Fortify (3-5 minutes)
```bash
# Run Fortify tests
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/TwoFactorAuthenticationTest.php
```

#### Phase 4: API Authentication - Sanctum (2-3 minutes)
```bash
# Run Sanctum tests
php artisan test tests/Feature/SanctumApiTest.php
```

#### Phase 5: Social Authentication - Socialite (2-3 minutes)
```bash
# Run Socialite tests
php artisan test tests/Feature/SocialLoginTest.php
```

#### Phase 6: Authorization - Permissions (2-3 minutes)
```bash
# Run permission tests
php artisan test tests/Feature/RoleBasedAccessTest.php
php artisan test tests/Feature/MiddlewareTest.php
```

#### Phase 7: Frontend Integration (1-2 minutes)
```bash
# Run frontend integration tests
php artisan test tests/Feature/UIPermissionTest.php
```

#### Phase 8: Performance Validation (1-2 minutes)
```bash
# Run performance tests
php artisan test tests/Feature/PerformanceTest.php --filter=authentication
```

#### Phase 9: Security Testing (2-3 minutes)
```bash
# Run security tests
php artisan test tests/Feature/SecurityTest.php
```

## 🏗️ Architecture Patterns

### Authentication Components Overview

The authentication system leverages multiple Laravel packages, each serving a specific purpose:

#### Laravel 12 Core
- **Purpose**: Foundation for all authentication features
- **Features**: Sessions, guards, providers, middleware
- **Usage**: Always active, provides base authentication functionality

#### Laravel Fortify
- **Purpose**: Headless authentication backend
- **Features**: Registration, login, 2FA, password reset, email verification
- **Usage**: All user authentication flows except social login

#### Laravel Sanctum
- **Purpose**: API authentication and SPA sessions
- **Features**: Personal access tokens, SPA authentication, CSRF protection
- **Usage**: API endpoints, mobile apps, React SPA authentication

#### Laravel Socialite
- **Purpose**: OAuth/social login integration
- **Features**: Multiple provider support (Google, GitHub, Facebook, etc.)
- **Usage**: Social login buttons and OAuth flows

#### Spatie Laravel Permission
- **Purpose**: Role-based access control (RBAC)
- **Features**: Roles, permissions, middleware, caching
- **Usage**: All authorization decisions throughout the application

### Frontend Integration Patterns

#### Inertia.js Data Sharing
```php
// HandleInertiaRequests middleware shares user data with frontend
public function share(Request $request): array
{
    $user = $request->user();
    $authUser = null;

    if ($user) {
        $user->load(['roles.permissions', 'permissions']);
        
        $authUser = [
            ...$user->toArray(),
            
            // Arrays for easy access
            'role_names' => $user->roles->pluck('name')->toArray(),
            'permission_names' => $user->getAllPermissions()->pluck('name')->toArray(),
            
            // Computed properties
            'is_admin' => $user->hasAnyRole(['Super Admin', 'Admin']),
            'is_content_creator' => $user->hasAnyRole(['Super Admin', 'Admin', 'Editor', 'Author']),
        ];
    }

    return [
        'auth' => ['user' => $authUser],
        // ... other shared data
    ];
}
```

#### React Permission Checking
```tsx
// Use custom hooks for consistent permission checking
export function usePermissions() {
  const { auth } = usePage().props;
  const user = auth.user as User;

  return {
    can: (permission: string) => user.permission_names.includes(permission),
    hasRole: (role: string) => user.role_names.includes(role),
    hasAnyRole: (roles: string[]) => user.role_names.some(role => roles.includes(role)),
    isAdmin: user.is_admin,
    isContentCreator: user.is_content_creator,
  };
}
```

## 📋 Role Hierarchy

### Role Definitions
1. **Super Admin**: Complete system access, can manage all roles and permissions
2. **Admin**: High-level administrative access, can manage users and content
3. **Editor**: Content management access, can edit all content
4. **Author**: Limited content creation, can manage own content
5. **Subscriber**: Basic read-only access

### Permission Inheritance
- Each role inherits permissions from lower roles
- Super Admin has all permissions
- Admin has user management + content permissions
- Editor has content management permissions
- Author has content creation permissions
- Subscriber has basic dashboard access

## 🔧 Configuration Standards

### Environment Variables
```env
# Application
APP_NAME="Multi-Role Auth System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Permission Configuration
```php
// config/permission.php
return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
```

---

This comprehensive set of rules and workflows ensures consistent, secure, and maintainable development of the Thorium90 Multi-Role Authentication System. All developers and AI assistants should follow these guidelines to maintain the integrity and security of the authentication system.
```

The comprehensive rules and workflows have been added to the `rules_and_workflows.md` file. This document now contains:

1. **Core Development Principles** - Authentication-first architecture, component isolation, and security by default
2. **Development Workflows** - Feature development and bug fix processes
3. **Coding Standards** - PHP/Laravel and React patterns for authentication
4. **Testing Requirements** - Test structure, coverage, and patterns
5. **Security Rules** - Authentication, authorization, and data security guidelines
6. **Documentation Standards** - Code and API documentation requirements
7. **Deployment Workflow** - Pre-deployment checklist and deployment steps
8. **Maintenance Workflow** - Regular tasks and monitoring requirements
9. **Quality Assurance** - Code review checklist and performance standards
10. **Support and Troubleshooting** - Common issues and debug commands
11. **Regression Testing Workflow** - Automated testing scripts and execution order
12. **Architecture Patterns** - Component overview and frontend integration
13. **Role Hierarchy** - Role definitions and permission inheritance
14. **Configuration Standards** - Environment variables and permission configuration

This document serves as a comprehensive guide for both developers and AI assistants to ensure consistency, security, and maintainability in the Thorium90 Multi-Role Authentication System.