# Multi-Role Authentication System - Quick Testing Reference

## 🚀 Quick Commands

### Windows
```batch
# Full regression test
regression-test.bat

# Individual test suites
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/Admin/
php artisan test tests/Feature/MiddlewareTest.php
```

### Linux/Mac
```bash
# Full regression test
./regression-test.sh

# Quick validation
./regression-test.sh --quick

# Individual test suites
php artisan test tests/Feature/Auth/
php artisan test tests/Feature/Admin/
```

## 🔧 Environment Setup
```bash
# Reset environment
php artisan migrate:fresh --seed
php artisan cache:clear

# Verify setup
php artisan tinker --execute="\Spatie\Permission\Models\Role::count()"  # Should be 5
php artisan tinker --execute="\Spatie\Permission\Models\Permission::count()"  # Should be 22+
```

## 🧪 Test Categories

| Test Suite | Command | Duration | Purpose |
|------------|---------|----------|---------|
| **Authentication** | `php artisan test tests/Feature/Auth/` | 2-3 min | Login, registration, password reset |
| **Middleware** | `php artisan test tests/Feature/MiddlewareTest.php` | 1-2 min | Route protection, access control |
| **Role Management** | `php artisan test tests/Feature/Admin/` | 2-3 min | CRUD operations, user assignments |
| **UI Integration** | `php artisan test tests/Feature/UIPermissionTest.php` | 1-2 min | Frontend data sharing |
| **All Tests** | `php artisan test` | 5-8 min | Complete test suite |

## 🎯 Expected Results

### ✅ Success Indicators
- All 5 roles exist (Super Admin, Admin, Editor, Author, Subscriber)
- 22+ permissions seeded
- Middleware blocks unauthorized access
- Frontend receives user data correctly
- Role hierarchy works as expected

### ❌ Failure Indicators
- Missing roles or permissions
- 500 errors on protected routes
- Middleware allows unauthorized access
- Frontend missing user data
- Permission inheritance broken

## 🔍 Quick Debugging

### Check Database
```bash
# Verify roles
php artisan tinker --execute="\Spatie\Permission\Models\Role::all()->pluck('name')"

# Verify permissions
php artisan tinker --execute="\Spatie\Permission\Models\Permission::count()"

# Check user roles
php artisan tinker --execute="\App\Models\User::find(1)->roles->pluck('name')"
```

### Check Routes
```bash
# List protected routes
php artisan route:list --name=admin

# Test route access
curl -I http://localhost:8000/dashboard  # Should redirect if not logged in
```

### Check Frontend
```javascript
// In browser console
console.log(window.page.props.auth.user.role_names);
console.log(window.page.props.auth.user.permission_names);
console.log(window.page.props.auth.user.is_admin);
```

## 🛠️ Common Fixes

### Database Issues
```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
```

### Cache Issues
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Permission Issues
```bash
# Re-seed permissions
php artisan db:seed --class=PermissionSeeder

# Check permission names match exactly in tests
```

## 📊 Performance Benchmarks

| Metric | Expected | Good | Needs Attention |
|--------|----------|------|-----------------|
| Role Loading | < 100ms | < 50ms | > 200ms |
| Permission Check | < 10ms | < 5ms | > 20ms |
| Middleware Processing | < 20ms | < 10ms | > 50ms |
| Database Queries | < 50ms | < 25ms | > 100ms |

## 🔒 Security Checklist

- [ ] Guest users redirected to login
- [ ] Unauthorized access returns 403
- [ ] Super Admin role cannot be deleted
- [ ] Last Super Admin cannot lose role
- [ ] Permission escalation prevented
- [ ] Session security maintained

## 📈 Test Success Rates

| Success Rate | Status | Action Required |
|--------------|--------|-----------------|
| 100% | 🎉 Perfect | Continue development |
| 90-99% | ✅ Good | Review minor issues |
| 80-89% | ⚠️ Warning | Fix failing tests |
| < 80% | ❌ Critical | Stop and debug |

## 🆘 Emergency Debugging

### If All Tests Fail
1. Check Laravel installation: `php artisan --version`
2. Check database connection: `php artisan migrate:status`
3. Re-seed database: `php artisan migrate:fresh --seed`
4. Clear all caches: `php artisan optimize:clear`

### If Specific Tests Fail
1. Run individual test: `php artisan test tests/Feature/SpecificTest.php`
2. Check error messages in output
3. Verify test data setup
4. Check middleware registration

### If Frontend Tests Fail
1. Check Inertia middleware: `app/Http/Middleware/HandleInertiaRequests.php`
2. Verify user data structure
3. Clear view cache: `php artisan view:clear`
4. Check browser console for errors

## 📞 Support Workflow

1. **First**: Run automated regression test
2. **Second**: Check this quick reference
3. **Third**: Review full `TESTING.md` documentation
4. **Fourth**: Check generated log files
5. **Last**: Debug individual components

---

**Remember**: The authentication system is working correctly when all tests pass and users can only access features they have permissions for!
