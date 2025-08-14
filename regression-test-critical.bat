@echo off
REM Critical-Only Regression Testing Script (Windows)
REM Runs only Group 1: Foundation & Database tests for rapid validation
REM Version 2.0 - Critical infrastructure verification only

setlocal enabledelayedexpansion

REM Configuration
set SCRIPT_VERSION=2.0-Critical
set START_TIME=%time%
set LOG_FILE=regression-test-critical.log
set REPORT_FILE=regression-test-critical-report.html

REM Test counters
set TOTAL_TESTS=0
set PASSED_TESTS=0
set FAILED_TESTS=0

REM Initialize log file
echo Multi-Role Authentication System - Critical-Only Regression Test > %LOG_FILE%
echo ============================================================= >> %LOG_FILE%
echo Start Time: %date% %time% >> %LOG_FILE%
echo Mode: Critical Infrastructure Only >> %LOG_FILE%
echo. >> %LOG_FILE%

echo.
echo =====================================
echo 🧪 Multi-Role User Authentication System
echo    Critical-Only Testing v%SCRIPT_VERSION%
echo =====================================
echo.
echo 🚀 CRITICAL MODE - Foundation verification only
echo Duration: ~1-2 minutes
echo Purpose: Rapid infrastructure validation
echo.

REM Environment verification
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

REM Setup test environment
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

REM Run Group 1: Foundation & Database
set GROUP_START_TIME=%time%
echo =====================================
echo 🏗️ GROUP 1: FOUNDATION ^& DATABASE
echo =====================================
echo Group 1 started at: %GROUP_START_TIME% >> %LOG_FILE%
echo Testing: Database integrity and basic setup
echo.

REM Check roles
echo Verifying roles...
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::count();" >temp_output.txt 2>&1
set /p role_count=<temp_output.txt
if !role_count! GEQ 5 (
    echo ✅ Roles: !role_count! found
    echo [%time%] Role Verification: PASSED - !role_count! roles found >> %LOG_FILE%
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Roles: Only !role_count! found
    echo [%time%] Role Verification: FAILED - Only !role_count! roles found >> %LOG_FILE%
    set /a FAILED_TESTS+=1
)
set /a TOTAL_TESTS+=1

REM Check permissions
echo Verifying permissions...
php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" >temp_output.txt 2>&1
set /p permission_count=<temp_output.txt
if !permission_count! GEQ 20 (
    echo ✅ Permissions: !permission_count! found
    echo [%time%] Permission Verification: PASSED - !permission_count! permissions found >> %LOG_FILE%
    set /a PASSED_TESTS+=1
) else (
    echo ❌ Permissions: Only !permission_count! found
    echo [%time%] Permission Verification: FAILED - Only !permission_count! permissions found >> %LOG_FILE%
    set /a FAILED_TESTS+=1
)
set /a TOTAL_TESTS+=1

REM Unit tests
echo Running unit tests...
php artisan test tests/Unit/ --stop-on-failure >temp_test_output.txt 2>&1
if errorlevel 1 (
    echo ❌ Unit tests failed
    echo [%time%] Unit Tests: FAILED - See detailed output >> %LOG_FILE%
    set /a FAILED_TESTS+=1
) else (
    echo ✅ Unit tests passed
    echo [%time%] Unit Tests: PASSED - All unit tests successful >> %LOG_FILE%
    set /a PASSED_TESTS+=1
)
set /a TOTAL_TESTS+=1

set GROUP_END_TIME=%time%
echo Group 1 completed at: %GROUP_END_TIME% >> %LOG_FILE%
echo.

REM Generate final report
set END_TIME=%time%
echo.
echo =====================================
echo 📊 CRITICAL TEST RESULTS
echo =====================================

set /a SUCCESS_RATE=(!PASSED_TESTS! * 100) / !TOTAL_TESTS!

echo Total Tests Run: !TOTAL_TESTS!
echo Passed: !PASSED_TESTS!
echo Failed: !FAILED_TESTS!
echo Success Rate: !SUCCESS_RATE!%%
echo.

if !FAILED_TESTS! EQU 0 (
    echo 🎉 ALL CRITICAL TESTS PASSED!
    echo ✅ Foundation infrastructure is operational
    set EXIT_CODE=0
) else if !SUCCESS_RATE! GEQ 80 (
    echo ⚠️ Some critical tests failed - Infrastructure needs attention
    echo System may be unstable - fix before proceeding
    set EXIT_CODE=1
) else (
    echo ❌ Multiple critical failures - Infrastructure is compromised
    echo System requires immediate attention before any development
    set EXIT_CODE=1
)

REM Generate HTML report
(
echo ^<!DOCTYPE html^>
echo ^<html^>^<head^>^<title^>Critical Test Report^</title^>
echo ^<style^>
echo body { font-family: Arial, sans-serif; margin: 20px; }
echo .header { background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
echo .success { color: green; font-weight: bold; }
echo .failure { color: red; font-weight: bold; }
echo .critical { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; }
echo table { border-collapse: collapse; width: 100%%; margin: 20px 0; }
echo th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
echo th { background-color: #f2f2f2; font-weight: bold; }
echo ^</style^>^</head^>^<body^>
echo ^<div class="header"^>
echo ^<h1^>🏗️ Critical Infrastructure Test Report^</h1^>
echo ^<h2^>Foundation ^& Database Verification v%SCRIPT_VERSION%^</h2^>
echo ^<p^>^<strong^>Generated:^</strong^> %date% %time%^</p^>
echo ^<p^>^<strong^>Mode:^</strong^> Critical-Only ^(Group 1^)^</p^>
echo ^</div^>
echo ^<div class="critical"^>
echo ^<h3^>🎯 Critical Test Purpose^</h3^>
echo ^<p^>This test verifies the foundational infrastructure required for all other system functionality:^</p^>
echo ^<ul^>
echo ^<li^>Database connectivity and migrations^</li^>
echo ^<li^>Role and permission seeding^</li^>
echo ^<li^>Basic unit test functionality^</li^>
echo ^</ul^>
echo ^</div^>
echo ^<h2^>📊 Test Summary^</h2^>
echo ^<table^>
echo ^<tr^>^<th^>Metric^</th^>^<th^>Value^</th^>^</tr^>
echo ^<tr^>^<td^>Total Tests^</td^>^<td^>!TOTAL_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Passed^</td^>^<td class="success"^>!PASSED_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Failed^</td^>^<td class="failure"^>!FAILED_TESTS!^</td^>^</tr^>
echo ^<tr^>^<td^>Success Rate^</td^>^<td^>!SUCCESS_RATE!%%^</td^>^</tr^>
echo ^</table^>
echo ^<h2^>🔍 Infrastructure Status^</h2^>
echo ^<table^>
echo ^<tr^>^<th^>Component^</th^>^<th^>Expected^</th^>^<th^>Actual^</th^>^<th^>Status^</th^>^</tr^>
echo ^<tr^>^<td^>Roles^</td^>^<td^>≥5^</td^>^<td^>!role_count!^</td^>^<td^>
if !role_count! GEQ 5 (echo ✅ PASS) else (echo ❌ FAIL)
echo ^</td^>^</tr^>
echo ^<tr^>^<td^>Permissions^</td^>^<td^>≥20^</td^>^<td^>!permission_count!^</td^>^<td^>
if !permission_count! GEQ 20 (echo ✅ PASS) else (echo ❌ FAIL)
echo ^</td^>^</tr^>
echo ^</table^>
echo ^<footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;"^>
echo ^<p^>Generated by Critical-Only Regression Testing Script v%SCRIPT_VERSION%^</p^>
echo ^<p^>For comprehensive testing, use: regression-test-enhanced.bat^</p^>
echo ^</footer^>
echo ^</body^>^</html^>
) > %REPORT_FILE%

echo.
echo 📄 Reports generated:
echo   - Critical log: %LOG_FILE%
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

REM Cleanup
if exist temp_output.txt del temp_output.txt
if exist temp_test_output.txt del temp_test_output.txt

echo 💡 Next Steps:
if !FAILED_TESTS! EQU 0 (
    echo   - Foundation is solid - proceed with development
    echo   - Run 'regression-test-enhanced.bat --quick' for broader testing
    echo   - Run 'regression-test-enhanced.bat' for comprehensive testing
) else (
    echo   - Fix critical infrastructure issues before proceeding
    echo   - Check detailed log: %LOG_FILE%
    echo   - Verify database connection and seeding
)

echo.
echo Press any key to exit...
pause >nul

exit /b !EXIT_CODE!
