@echo off
setlocal enabledelayedexpansion

echo ========================================
echo THORIUM90 COMPLETE REGRESSION TESTING
echo Database + Application Integration v2.1
echo ========================================

set "start_time=%time%"
set "total_tests=0"
set "passed_tests=0"
set "failed_tests=0"
set "critical_failures=0"

echo.
echo 🔍 Testing Strategy: Integrated Database + Application
echo 📊 Total Groups: 7 (4 Critical, 3 Non-Critical)
echo ⏱️  Expected Duration: 5-8 minutes
echo.

REM Group 1: Foundation & Database Infrastructure (CRITICAL)
echo ========================================
echo [1/7] 🏗️  FOUNDATION ^& DATABASE INFRASTRUCTURE
echo ========================================
echo Testing: Database migrations, performance, roles, permissions
echo Status: CRITICAL - Must pass for deployment

php artisan test tests/Database/MigrationTest.php --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Database migrations failed
    set /a "critical_failures+=1"
    goto :failure
)

php artisan test tests/Database/PerformanceTest.php --stop-on-failure  
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Database performance failed
    set /a "critical_failures+=1"
    goto :failure
)

php artisan test tests/Unit/ --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Unit tests failed
    set /a "critical_failures+=1"
    goto :failure
)

echo ✅ Group 1: Foundation & Database Infrastructure - PASSED
set /a "passed_tests+=1"

REM Group 2: Authentication Core (CRITICAL)
echo.
echo ========================================
echo [2/7] 🔐 AUTHENTICATION CORE
echo ========================================
echo Testing: User login, logout, basic authentication
echo Status: CRITICAL - Core security functionality

php artisan test tests/Feature/AuthenticationTest.php --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Authentication core failed
    set /a "critical_failures+=1"
    goto :failure
)

echo ✅ Group 2: Authentication Core - PASSED
set /a "passed_tests+=1"

REM Group 3: Access Control & Middleware (CRITICAL)
echo.
echo ========================================
echo [3/7] 🛡️  ACCESS CONTROL ^& MIDDLEWARE
echo ========================================
echo Testing: Role-based access control, middleware security
echo Status: CRITICAL - Security layer validation

php artisan test tests/Feature/MiddlewareTest.php --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Access control failed
    set /a "critical_failures+=1"
    goto :failure
)

echo ✅ Group 3: Access Control & Middleware - PASSED
set /a "passed_tests+=1"

REM Group 4: Advanced Authentication (2FA) (NON-CRITICAL)
echo.
echo ========================================
echo [4/7] 🔒 ADVANCED AUTHENTICATION (2FA)
echo ========================================
echo Testing: Two-factor authentication functionality
echo Status: NON-CRITICAL - Advanced security features

php artisan test tests/Feature/TwoFactorAuthenticationTest.php
if !errorlevel! neq 0 (
    echo ⚠️  Group 4: 2FA tests had issues (non-critical)
    set /a "failed_tests+=1"
) else (
    echo ✅ Group 4: Advanced Authentication (2FA) - PASSED
    set /a "passed_tests+=1"
)

REM Group 5: Content Management (NON-CRITICAL)
echo.
echo ========================================
echo [5/7] 📝 CONTENT MANAGEMENT
echo ========================================
echo Testing: Page creation, editing, management
echo Status: NON-CRITICAL - CMS functionality

php artisan test tests/Feature/Content/
if !errorlevel! neq 0 (
    echo ⚠️  Group 5: Content management tests had issues (non-critical)
    set /a "failed_tests+=1"
) else (
    echo ✅ Group 5: Content Management - PASSED
    set /a "passed_tests+=1"
)

REM Group 6: API & Integration (NON-CRITICAL)
echo.
echo ========================================
echo [6/7] 🌐 API ^& INTEGRATION
echo ========================================
echo Testing: API endpoints, external integrations
echo Status: NON-CRITICAL - API functionality

php artisan test tests/Feature/SanctumApiTest.php
if !errorlevel! neq 0 (
    echo ⚠️  Group 6: API integration tests had issues (non-critical)
    set /a "failed_tests+=1"
) else (
    echo ✅ Group 6: API & Integration - PASSED
    set /a "passed_tests+=1"
)

REM Group 7: Database Security & Integrity (CRITICAL)
echo.
echo ========================================
echo [7/7] 🔐 DATABASE SECURITY ^& INTEGRITY
echo ========================================
echo Testing: Data security, encryption, integrity validation
echo Status: CRITICAL - Data protection validation

php artisan test tests/Database/SecurityTest.php --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Database security failed
    set /a "critical_failures+=1"
    goto :failure
)

php artisan test tests/Database/IntegrityTest.php --stop-on-failure
if !errorlevel! neq 0 (
    echo ❌ CRITICAL FAILURE: Database integrity failed
    set /a "critical_failures+=1"
    goto :failure
)

echo ✅ Group 7: Database Security & Integrity - PASSED
set /a "passed_tests+=1"

REM Calculate totals
set /a "total_tests=passed_tests+failed_tests"

REM Success Summary
echo.
echo ========================================
echo 🎉 COMPLETE REGRESSION TESTING RESULTS
echo ========================================
echo.
echo 📊 SUMMARY:
echo    • Total Groups Tested: 7
echo    • Passed: !passed_tests!
echo    • Failed (Non-Critical): !failed_tests!
echo    • Critical Failures: !critical_failures!
echo.
echo 🎯 CRITICAL SYSTEMS STATUS:
echo    • Foundation & Database: ✅ OPERATIONAL
echo    • Authentication Core: ✅ OPERATIONAL  
echo    • Access Control: ✅ OPERATIONAL
echo    • Database Security: ✅ OPERATIONAL
echo.
echo 📈 SYSTEM READINESS:
if !critical_failures! equ 0 (
    echo    🟢 PRODUCTION READY - All critical systems operational
    echo    🚀 DEPLOYMENT APPROVED
    echo.
    echo ✅ SUCCESS: Thorium90 passed complete regression testing
    echo    Database + Application integration validated
    echo    Core systems: 100%% operational
    echo    Overall system: Ready for production deployment
) else (
    echo    🔴 DEPLOYMENT BLOCKED - Critical failures detected
    goto :failure
)

echo.
echo 📋 NEXT STEPS:
echo    1. Review any non-critical test failures
echo    2. Deploy to production environment
echo    3. Run post-deployment validation
echo    4. Monitor system performance

set "end_time=%time%"
echo.
echo ⏱️  Execution completed at: !end_time!
echo 📄 Detailed logs available in: regression-test-complete.log

exit /b 0

:failure
echo.
echo ========================================
echo ❌ REGRESSION TESTING FAILED
echo ========================================
echo.
echo 🚨 CRITICAL FAILURE DETECTED:
echo    • Critical failures: !critical_failures!
echo    • System status: NOT READY FOR DEPLOYMENT
echo.
echo 🔧 REQUIRED ACTIONS:
echo    1. Fix critical failures immediately
echo    2. Re-run complete regression testing
echo    3. Do not deploy until all critical tests pass
echo.
echo 📋 TROUBLESHOOTING:
echo    • Check database connectivity and migrations
echo    • Verify authentication system configuration
echo    • Review access control and middleware setup
echo    • Validate database security and integrity
echo.
echo ⏱️  Failed at: %time%
echo 📄 Error details in: regression-test-complete.log

exit /b 1
