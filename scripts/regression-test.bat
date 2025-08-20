@echo off
REM Multi-Role User Authentication System - Regression Testing Script (Windows)
REM This script provides comprehensive testing for the authentication system
REM ensuring all components work correctly after changes or updates.

setlocal enabledelayedexpansion

REM Test counters
set TOTAL_TESTS=0
set PASSED_TESTS=0
set FAILED_TESTS=0

REM Function to print colored output (Windows compatible)
echo.
echo =====================================
echo 🧪 Multi-Role User Authentication System
echo    Regression Testing Script v1.0 (Windows)
echo =====================================
echo.

REM Check if we're in a Laravel project
if not exist "artisan" (
    echo ❌ Error: Not in a Laravel project directory
    exit /b 1
)

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: PHP is not installed or not in PATH
    exit /b 1
)

REM Check if Composer is available
composer --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: Composer is not installed or not in PATH
    exit /b 1
)

echo ✅ Environment verification complete
echo.

echo =====================================
echo 📋 SETTING UP TEST ENVIRONMENT
echo =====================================

echo Clearing caches...
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1

echo Running fresh migrations...
php artisan migrate:fresh --force >nul 2>&1

echo Seeding database...
php artisan db:seed --force >nul 2>&1

echo ✅ Test environment setup complete
echo.

echo =====================================
echo 🗄️ VERIFYING DATABASE INTEGRITY
echo =====================================

REM Check if roles exist
echo Checking roles...
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::count();" >temp_output.txt 2>&1
set /p role_count=<temp_output.txt
if !role_count! GEQ 5 (
    echo ✅ Roles seeded correctly: !role_count! roles found
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Insufficient roles found: !role_count!
    set /a FAILED_TESTS+=1
)
set /a TOTAL_TESTS+=1

REM Check if permissions exist
echo Checking permissions...
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" >temp_output.txt 2>&1
set /p permission_count=<temp_output.txt
if !permission_count! GEQ 20 (
    echo ✅ Permissions seeded correctly: !permission_count! permissions found
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Insufficient permissions found: !permission_count!
    set /a FAILED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo.

echo =====================================
echo 🔐 TESTING AUTHENTICATION SYSTEM
echo =====================================

echo Running authentication tests...
php artisan test tests/Feature/Auth/ --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Authentication tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Authentication tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo Running unit tests...
php artisan test tests/Unit/ --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Unit tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Unit tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo.

echo =====================================
echo 🛡️ TESTING MIDDLEWARE PROTECTION
echo =====================================

echo Running middleware tests...
php artisan test tests/Feature/MiddlewareTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Middleware tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Middleware tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo Running route protection tests...
php artisan test tests/Feature/RoleBasedAccessTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Route protection tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Route protection tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo.

echo =====================================
echo 👥 TESTING ROLE MANAGEMENT SYSTEM
echo =====================================

echo Running role management tests...
php artisan test tests/Feature/Admin/RoleManagementTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Role management tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Role management tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo Running user role assignment tests...
php artisan test tests/Feature/Admin/UserRoleManagementTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ User role assignment tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ User role assignment tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo.

echo =====================================
echo 🔗 TESTING FRONTEND INTEGRATION
echo =====================================

echo Running UI permission tests...
php artisan test tests/Feature/UIPermissionTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ UI permission tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ UI permission tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo Running dashboard tests...
php artisan test tests/Feature/DashboardTest.php --stop-on-failure >nul 2>&1
if errorlevel 1 (
    echo ❌ Dashboard tests failed
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Dashboard tests passed
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

echo.

echo =====================================
echo 📊 TEST RESULTS SUMMARY
echo =====================================

echo Total Tests Run: !TOTAL_TESTS!
echo Passed: !PASSED_TESTS!
echo Failed: !FAILED_TESTS!

set /a SUCCESS_RATE=(!PASSED_TESTS! * 100) / !TOTAL_TESTS!

if !FAILED_TESTS! EQU 0 (
    echo.
    echo 🎉 ALL TESTS PASSED! Success Rate: 100%%
    echo ✅ Multi-Role Authentication System is working correctly
    set EXIT_CODE=0
) else if !SUCCESS_RATE! GEQ 80 (
    echo.
    echo ⚠️ Most tests passed. Success Rate: !SUCCESS_RATE!%%
    echo Some issues detected - review failed tests above
    set EXIT_CODE=1
) else (
    echo.
    echo ❌ Multiple test failures. Success Rate: !SUCCESS_RATE!%%
    echo Critical issues detected - system needs attention
    set EXIT_CODE=1
)

REM Generate report
echo.
echo Test completed at: %date% %time%
echo Report saved to: regression-test-report.log

REM Save detailed report
(
    echo Multi-Role Authentication System - Regression Test Report
    echo ========================================================
    echo Timestamp: %date% %time%
    echo Total Tests: !TOTAL_TESTS!
    echo Passed: !PASSED_TESTS!
    echo Failed: !FAILED_TESTS!
    echo Success Rate: !SUCCESS_RATE!%%
    echo.
    echo Environment:
    php artisan --version
    php --version | findstr /C:"PHP"
    echo.
    if !FAILED_TESTS! GTR 0 (
        echo FAILED TESTS REQUIRE ATTENTION
        echo Review the output above for specific failure details
    ) else (
        echo ALL SYSTEMS OPERATIONAL
    )
) > regression-test-report.log

REM Cleanup
if exist temp_output.txt del temp_output.txt

echo.
echo Press any key to exit...
pause >nul

exit /b !EXIT_CODE!
