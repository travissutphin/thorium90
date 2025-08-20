@echo off
REM Enhanced Multi-Role User Authentication System - Regression Testing Script (Windows)
REM Groups tests logically for maximum efficiency and early bug detection
REM Version 2.0 - Enhanced with performance metrics and detailed reporting

setlocal enabledelayedexpansion

REM Configuration
set SCRIPT_VERSION=2.0
set START_TIME=%time%
set LOG_FILE=regression-test-detailed.log
set REPORT_FILE=regression-test-report.html
set QUICK_MODE=false

REM Parse command line arguments
:parse_args
if "%1"=="--quick" (
    set QUICK_MODE=true
    shift
    goto parse_args
)
if "%1"=="--help" (
    goto show_help
)
if "%1"=="/?" (
    goto show_help
)

REM Test counters
set TOTAL_GROUPS=6
set CURRENT_GROUP=0
set TOTAL_TESTS=0
set PASSED_TESTS=0
set FAILED_TESTS=0
set GROUP_FAILURES=0

REM Performance tracking
set GROUP_START_TIME=
set GROUP_END_TIME=

REM Initialize log file
echo Multi-Role Authentication System - Enhanced Regression Test > %LOG_FILE%
echo ============================================================= >> %LOG_FILE%
echo Start Time: %date% %time% >> %LOG_FILE%
echo Quick Mode: %QUICK_MODE% >> %LOG_FILE%
echo. >> %LOG_FILE%

echo.
echo =====================================
echo 🧪 Multi-Role User Authentication System
echo    Enhanced Regression Testing v%SCRIPT_VERSION%
echo =====================================
echo.
if "%QUICK_MODE%"=="true" (
    echo 🚀 QUICK MODE ENABLED - Essential tests only
    echo.
)

REM Environment verification
call :verify_environment
if errorlevel 1 exit /b 1

REM Setup test environment
call :setup_environment
if errorlevel 1 exit /b 1

REM Execute test groups
call :run_group_1_foundation
if !GROUP_FAILURES! GTR 0 goto group_failure_exit

call :run_group_2_authentication
if !GROUP_FAILURES! GTR 0 goto group_failure_exit

call :run_group_3_access_control
if !GROUP_FAILURES! GTR 0 goto group_failure_exit

if "%QUICK_MODE%"=="false" (
    call :run_group_4_advanced_auth
    if !GROUP_FAILURES! GTR 0 goto group_failure_exit

    call :run_group_5_admin_management
    if !GROUP_FAILURES! GTR 0 goto group_failure_exit

    call :run_group_6_content_frontend
    if !GROUP_FAILURES! GTR 0 goto group_failure_exit
)

REM Generate final report
call :generate_final_report
goto end

:group_failure_exit
echo.
echo ❌ CRITICAL FAILURE IN GROUP !CURRENT_GROUP!
echo Testing stopped to allow for immediate bug fixing.
echo Review the detailed log: %LOG_FILE%
echo.
call :generate_failure_report
exit /b 1

:verify_environment
echo =====================================
echo 🔍 ENVIRONMENT VERIFICATION
echo =====================================

if not exist "artisan" (
    echo ❌ Error: Not in a Laravel project directory
    echo Not in Laravel project directory >> %LOG_FILE%
    exit /b 1
)

php --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: PHP is not installed or not in PATH
    echo PHP not available >> %LOG_FILE%
    exit /b 1
)

composer --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Error: Composer is not installed or not in PATH
    echo Composer not available >> %LOG_FILE%
    exit /b 1
)

echo ✅ Environment verification complete
echo Environment verification: PASSED >> %LOG_FILE%
echo.
exit /b 0

:setup_environment
echo =====================================
echo 📋 SETTING UP TEST ENVIRONMENT
echo =====================================

echo Clearing caches...
php artisan cache:clear >nul 2>&1
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan view:clear >nul 2>&1

echo Running fresh migrations...
php artisan migrate:fresh --force >nul 2>&1
if errorlevel 1 (
    echo ❌ Migration failed
    echo Migration failed >> %LOG_FILE%
    exit /b 1
)

echo Seeding database...
php artisan db:seed --force >nul 2>&1
if errorlevel 1 (
    echo ❌ Database seeding failed
    echo Database seeding failed >> %LOG_FILE%
    exit /b 1
)

echo ✅ Test environment setup complete
echo Test environment setup: PASSED >> %LOG_FILE%
echo.
exit /b 0

:run_group_1_foundation
set /a CURRENT_GROUP=1
set GROUP_START_TIME=%time%
echo =====================================
echo 🏗️ GROUP 1: FOUNDATION ^& DATABASE
echo =====================================
echo Group 1 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Foundation & Database" "Database integrity and basic setup"

REM Check roles
echo Verifying roles...
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::count();" >temp_output.txt 2>&1
set /p role_count=<temp_output.txt
if !role_count! GEQ 5 (
    echo ✅ Roles: !role_count! found
    call :log_test_result "Role Verification" "PASSED" "!role_count! roles found"
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Roles: Only !role_count! found
    call :log_test_result "Role Verification" "FAILED" "Only !role_count! roles found"
    set /a FAILED_TESTS+=1
    set /a GROUP_FAILURES+=1
)
set /a TOTAL_TESTS+=1

REM Check permissions
echo Verifying permissions...
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" >temp_output.txt 2>&1
set /p permission_count=<temp_output.txt
if !permission_count! GEQ 20 (
    echo ✅ Permissions: !permission_count! found
    call :log_test_result "Permission Verification" "PASSED" "!permission_count! permissions found"
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Permissions: Only !permission_count! found
    call :log_test_result "Permission Verification" "FAILED" "Only !permission_count! permissions found"
    set /a FAILED_TESTS+=1
    set /a GROUP_FAILURES+=1
)
set /a TOTAL_TESTS+=1

REM Unit tests
echo Running unit tests...
php artisan test tests/Unit/ --stop-on-failure >temp_test_output.txt 2>&1
if errorlevel 1 (
    echo ❌ Unit tests failed
    call :log_test_result "Unit Tests" "FAILED" "See detailed output"
    set /a FAILED_TESTS+=1
    set /a GROUP_FAILURES+=1
) else (
    echo ✅ Unit tests passed
    call :log_test_result "Unit Tests" "PASSED" "All unit tests successful"
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

call :end_group_timing
exit /b 0

:run_group_2_authentication
set /a CURRENT_GROUP=2
set GROUP_START_TIME=%time%
echo =====================================
echo 🔐 GROUP 2: AUTHENTICATION CORE
echo =====================================
echo Group 2 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Authentication Core" "Login, registration, password management"

call :run_single_test "Registration" "tests/Feature/Auth/RegistrationTest.php"
call :run_single_test "Authentication" "tests/Feature/Auth/AuthenticationTest.php"
call :run_single_test "Password Reset" "tests/Feature/Auth/PasswordResetTest.php"
call :run_single_test "Email Verification" "tests/Feature/Auth/EmailVerificationTest.php"
call :run_single_test "Password Confirmation" "tests/Feature/Auth/PasswordConfirmationTest.php"

call :end_group_timing
exit /b 0

:run_group_3_access_control
set /a CURRENT_GROUP=3
set GROUP_START_TIME=%time%
echo =====================================
echo 🛡️ GROUP 3: ACCESS CONTROL ^& MIDDLEWARE
echo =====================================
echo Group 3 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Access Control & Middleware" "Security boundaries and route protection"

call :run_single_test "Middleware Protection" "tests/Feature/MiddlewareTest.php"
call :run_single_test "Role-Based Access" "tests/Feature/RoleBasedAccessTest.php"
call :run_single_test "Dashboard Access" "tests/Feature/DashboardTest.php"

call :end_group_timing
exit /b 0

:run_group_4_advanced_auth
set /a CURRENT_GROUP=4
set GROUP_START_TIME=%time%
echo =====================================
echo 🔒 GROUP 4: ADVANCED AUTHENTICATION
echo =====================================
echo Group 4 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Advanced Authentication" "2FA, social login, API auth"

call :run_single_test "Two-Factor Authentication" "tests/Feature/TwoFactorAuthenticationTest.php"
call :run_single_test "Social Login" "tests/Feature/SocialLoginTest.php"
call :run_single_test "API Authentication" "tests/Feature/SanctumApiTest.php"
call :run_single_test "Email Resending" "tests/Feature/ResendEmailTest.php"

call :end_group_timing
exit /b 0

:run_group_5_admin_management
set /a CURRENT_GROUP=5
set GROUP_START_TIME=%time%
echo =====================================
echo 👥 GROUP 5: ADMIN ^& USER MANAGEMENT
echo =====================================
echo Group 5 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Admin & User Management" "User and role administration"

call :run_single_test "User Management" "tests/Feature/Admin/UserManagementTest.php"
call :run_single_test "Role Management" "tests/Feature/Admin/RoleManagementTest.php"
call :run_single_test "Role CRUD Operations" "tests/Feature/Admin/RoleManagementCrudTest.php"
call :run_single_test "User Role Assignments" "tests/Feature/Admin/UserRoleManagementTest.php"
call :run_single_test "Admin Settings" "tests/Feature/Admin/AdminSettingsTest.php"

call :end_group_timing
exit /b 0

:run_group_6_content_frontend
set /a CURRENT_GROUP=6
set GROUP_START_TIME=%time%
echo =====================================
echo 🎨 GROUP 6: CONTENT ^& FRONTEND
echo =====================================
echo Group 6 started at: %GROUP_START_TIME% >> %LOG_FILE%

call :run_test_group "Content & Frontend" "CMS features and UI integration"

call :run_single_test "Page Management" "tests/Feature/Content/PageManagementTest.php"
call :run_single_test "Page SEO" "tests/Feature/Content/PageSEOTest.php"
call :run_single_test "Sitemap Generation" "tests/Feature/Content/SitemapTest.php"
call :run_single_test "UI Permissions" "tests/Feature/UIPermissionTest.php"
call :run_single_test "Profile Updates" "tests/Feature/Settings/ProfileUpdateTest.php"
call :run_single_test "Password Updates" "tests/Feature/Settings/PasswordUpdateTest.php"

call :end_group_timing
exit /b 0

:run_test_group
echo Testing: %~2
echo.
exit /b 0

:run_single_test
echo Running %~1...
php artisan test "%~2" --stop-on-failure >temp_test_output.txt 2>&1
if errorlevel 1 (
    echo ❌ %~1 failed
    call :log_test_result "%~1" "FAILED" "See detailed output in temp_test_output.txt"
    set /a FAILED_TESTS+=1
    set /a GROUP_FAILURES+=1
) else (
    echo ✅ %~1 passed
    call :log_test_result "%~1" "PASSED" "All tests successful"
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1
exit /b 0

:log_test_result
echo [%time%] %~1: %~2 - %~3 >> %LOG_FILE%
exit /b 0

:end_group_timing
set GROUP_END_TIME=%time%
echo Group !CURRENT_GROUP! completed at: !GROUP_END_TIME! >> %LOG_FILE%
echo Group !CURRENT_GROUP! failures: !GROUP_FAILURES! >> %LOG_FILE%
echo. >> %LOG_FILE%
set GROUP_FAILURES=0
exit /b 0

:generate_final_report
set END_TIME=%time%
echo.
echo =====================================
echo 📊 FINAL TEST RESULTS
echo =====================================

set /a SUCCESS_RATE=(!PASSED_TESTS! * 100) / !TOTAL_TESTS!

echo Total Tests Run: !TOTAL_TESTS!
echo Passed: !PASSED_TESTS!
echo Failed: !FAILED_TESTS!
echo Success Rate: !SUCCESS_RATE!%%
echo.

if !FAILED_TESTS! EQU 0 (
    echo 🎉 ALL TESTS PASSED! 
    echo ✅ Multi-Role Authentication System is fully operational
    set EXIT_CODE=0
) else if !SUCCESS_RATE! GEQ 90 (
    echo ⚠️ Most tests passed - Minor issues detected
    echo Review failed tests for optimization opportunities
    set EXIT_CODE=1
) else if !SUCCESS_RATE! GEQ 80 (
    echo ⚠️ Some tests failed - Moderate issues detected
    echo System functional but needs attention
    set EXIT_CODE=1
) else (
    echo ❌ Multiple test failures - Critical issues detected
    echo System requires immediate attention
    set EXIT_CODE=1
)

REM Generate HTML report
call :generate_html_report

echo.
echo 📄 Reports generated:
echo   - Detailed log: %LOG_FILE%
echo   - HTML report: %REPORT_FILE%
echo.

REM Final log entry
echo ============================================================= >> %LOG_FILE%
echo End Time: %date% %time% >> %LOG_FILE%
echo Total Tests: !TOTAL_TESTS! >> %LOG_FILE%
echo Passed: !PASSED_TESTS! >> %LOG_FILE%
echo Failed: !FAILED_TESTS! >> %LOG_FILE%
echo Success Rate: !SUCCESS_RATE!%% >> %LOG_FILE%
echo Exit Code: !EXIT_CODE! >> %LOG_FILE%

exit /b !EXIT_CODE!

:generate_failure_report
echo.
echo 🚨 FAILURE ANALYSIS
echo =====================================
echo Group !CURRENT_GROUP! failed with !GROUP_FAILURES! failures
echo.
echo 💡 RECOMMENDED ACTIONS:
echo 1. Review the detailed log: %LOG_FILE%
echo 2. Check the last test output: temp_test_output.txt
echo 3. Verify database seeding completed successfully
echo 4. Ensure all migrations ran without errors
echo 5. Check for missing dependencies or configuration issues
echo.
exit /b 0

:generate_html_report
(
echo ^<!DOCTYPE html^>
echo ^<html^>^<head^>^<title^>Regression Test Report^</title^>
echo ^<style^>
echo body { font-family: Arial, sans-serif; margin: 20px; }
echo .header { background: #f0f0f0; padding: 20px; border-radius: 5px; }
echo .success { color: green; }
echo .failure { color: red; }
echo .warning { color: orange; }
echo table { border-collapse: collapse; width: 100%%; margin: 20px 0; }
echo th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
echo th { background-color: #f2f2f2; }
echo ^</style^>^</head^>^<body^>
echo ^<div class="header"^>
echo ^<h1^>Multi-Role Authentication System - Regression Test Report^</h1^>
echo ^<p^>Generated: %date% %time%^</p^>
echo ^<p^>Version: %SCRIPT_VERSION%^</p^>
echo ^</div^>
echo ^<h2^>Summary^</h2^>
echo ^<table^>
echo ^<tr^>^<th^>Metric^</th^>^<th^>Value^</th^>^</tr^>
echo ^<tr^>^<td^>Total Tests^</td^>^<td^>!TOTAL_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Passed^</td^>^<td class="success"^>!PASSED_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Failed^</td^>^<td class="failure"^>!FAILED_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Success Rate^</td^>^<td^>!SUCCESS_RATE!%%^</td^>^</tr^>
echo ^</table^>
echo ^</body^>^</html^>
) > %REPORT_FILE%
exit /b 0

:show_help
echo.
echo Enhanced Regression Testing Script v%SCRIPT_VERSION%
echo.
echo Usage: regression-test-enhanced.bat [options]
echo.
echo Options:
echo   --quick     Run essential tests only (Groups 1-3)
echo   --help      Show this help message
echo.
echo Test Groups:
echo   Group 1: Foundation ^& Database (Critical)
echo   Group 2: Authentication Core (Essential)
echo   Group 3: Access Control ^& Middleware (Security)
echo   Group 4: Advanced Authentication (Extended)
echo   Group 5: Admin ^& User Management (Features)
echo   Group 6: Content ^& Frontend (Integration)
echo.
echo Quick mode runs Groups 1-3 only for rapid validation.
echo Full mode runs all 6 groups for comprehensive testing.
echo.
exit /b 0

:end
REM Cleanup
if exist temp_output.txt del temp_output.txt
if exist temp_test_output.txt del temp_test_output.txt

echo Press any key to exit...
pause >nul
exit /b !EXIT_CODE!
