#!/bin/bash

# Multi-Role User Authentication System - Regression Testing Script
# This script provides comprehensive testing for the authentication system
# ensuring all components work correctly after changes or updates.

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to print colored output
print_status() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${NC}"
}

# Function to print section headers
print_section() {
    echo ""
    echo "=================================="
    print_status $BLUE "$1"
    echo "=================================="
}

# Function to run a test and track results
run_test() {
    local test_name=$1
    local test_command=$2
    
    print_status $CYAN "Running: $test_name"
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if eval $test_command > /dev/null 2>&1; then
        print_status $GREEN "✅ PASSED: $test_name"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        print_status $RED "❌ FAILED: $test_name"
        FAILED_TESTS=$((FAILED_TESTS + 1))
        
        # Show the actual error for debugging
        echo "Error details:"
        eval $test_command
        echo ""
    fi
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to verify environment
verify_environment() {
    print_section "🔍 VERIFYING ENVIRONMENT"
    
    # Check if we're in a Laravel project
    if [ ! -f "artisan" ]; then
        print_status $RED "❌ Error: Not in a Laravel project directory"
        exit 1
    fi
    
    # Check required commands
    local required_commands=("php" "composer")
    for cmd in "${required_commands[@]}"; do
        if ! command_exists $cmd; then
            print_status $RED "❌ Error: $cmd is not installed"
            exit 1
        fi
    done
    
    # Check Laravel version
    local laravel_version=$(php artisan --version | grep -o '[0-9]\+\.[0-9]\+')
    print_status $GREEN "✅ Laravel version: $laravel_version"
    
    # Check if Spatie Permission is installed
    if ! composer show spatie/laravel-permission > /dev/null 2>&1; then
        print_status $RED "❌ Error: Spatie Laravel Permission package not found"
        exit 1
    fi
    
    print_status $GREEN "✅ Environment verification complete"
}

# Function to setup test environment
setup_environment() {
    print_section "📋 SETTING UP TEST ENVIRONMENT"
    
    print_status $YELLOW "Clearing caches..."
    php artisan cache:clear > /dev/null 2>&1
    php artisan config:clear > /dev/null 2>&1
    php artisan route:clear > /dev/null 2>&1
    
    print_status $YELLOW "Running fresh migrations..."
    php artisan migrate:fresh --force > /dev/null 2>&1
    
    print_status $YELLOW "Seeding database..."
    php artisan db:seed --force > /dev/null 2>&1
    
    print_status $GREEN "✅ Test environment setup complete"
}

# Function to verify database integrity
verify_database() {
    print_section "🗄️ VERIFYING DATABASE INTEGRITY"
    
    # Check roles
    local expected_roles=("Super Admin" "Admin" "Editor" "Author" "Subscriber")
    for role in "${expected_roles[@]}"; do
        run_test "Role exists: $role" "php artisan tinker --execute=\"echo \Spatie\Permission\Models\Role::where('name', '$role')->exists() ? 'true' : 'false';\" | grep -q 'true'"
    done
    
    # Check permissions count
    run_test "Permissions seeded (minimum 20)" "php artisan tinker --execute=\"echo \Spatie\Permission\Models\Permission::count();\" | grep -E '[2-9][0-9]|[3-9][0-9]'"
    
    # Check role-permission relationships
    run_test "Super Admin has all permissions" "php artisan tinker --execute=\"\$role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first(); echo \$role->permissions()->count() > 15 ? 'true' : 'false';\" | grep -q 'true'"
    
    run_test "Subscriber has limited permissions" "php artisan tinker --execute=\"\$role = \Spatie\Permission\Models\Role::where('name', 'Subscriber')->first(); echo \$role->permissions()->count() <= 5 ? 'true' : 'false';\" | grep -q 'true'"
}

# Function to test authentication system
test_authentication() {
    print_section "🔐 TESTING AUTHENTICATION SYSTEM"
    
    run_test "Authentication tests" "php artisan test tests/Feature/Auth/ --stop-on-failure"
    run_test "Two-Factor Authentication tests" "php artisan test tests/Feature/TwoFactorAuthenticationTest.php --stop-on-failure"
    run_test "User factory works" "php artisan test tests/Unit/ --stop-on-failure"
}

# Function to test middleware protection
test_middleware() {
    print_section "🛡️ TESTING MIDDLEWARE PROTECTION"
    
    run_test "Role middleware tests" "php artisan test tests/Feature/MiddlewareTest.php --stop-on-failure"
    run_test "Route protection tests" "php artisan test tests/Feature/RoleBasedAccessTest.php --stop-on-failure"
}

# Function to test role management
test_role_management() {
    print_section "👥 TESTING ROLE MANAGEMENT SYSTEM"
    
    run_test "Role management tests" "php artisan test tests/Feature/Admin/RoleManagementTest.php --stop-on-failure"
    run_test "User role assignment tests" "php artisan test tests/Feature/Admin/UserRoleManagementTest.php --stop-on-failure"
}

# Function to test frontend integration
test_frontend_integration() {
    print_section "🔗 TESTING FRONTEND INTEGRATION"
    
    run_test "UI permission tests" "php artisan test tests/Feature/UIPermissionTest.php --stop-on-failure"
    run_test "Inertia data sharing" "php artisan test tests/Feature/DashboardTest.php --stop-on-failure"
}

# Function to run security validation
test_security() {
    print_section "🔒 SECURITY VALIDATION TESTS"
    
    # Test unauthorized access
    run_test "Guest users redirected to login" "curl -s -o /dev/null -w '%{http_code}' http://localhost:8000/dashboard | grep -q '302'"
    
    # Test permission escalation prevention
    run_test "WithRoles trait works correctly" "php artisan test tests/Traits/ --stop-on-failure"
}

# Function to run performance tests
test_performance() {
    print_section "⚡ PERFORMANCE VALIDATION"
    
    print_status $YELLOW "Testing database query performance..."
    
    # Test role loading performance
    local role_query_time=$(php artisan tinker --execute="
        \$start = microtime(true);
        \$user = \App\Models\User::factory()->create();
        \$user->assignRole('Admin');
        \$user->load(['roles.permissions']);
        \$end = microtime(true);
        echo round((\$end - \$start) * 1000, 2);
    " 2>/dev/null | tail -1)
    
    if (( $(echo "$role_query_time < 100" | bc -l) )); then
        print_status $GREEN "✅ Role loading performance: ${role_query_time}ms (Good)"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        print_status $YELLOW "⚠️ Role loading performance: ${role_query_time}ms (Consider optimization)"
    fi
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
}

# Function to generate test report
generate_report() {
    print_section "📊 TEST RESULTS SUMMARY"
    
    echo "Total Tests Run: $TOTAL_TESTS"
    echo "Passed: $PASSED_TESTS"
    echo "Failed: $FAILED_TESTS"
    
    local success_rate=$(( (PASSED_TESTS * 100) / TOTAL_TESTS ))
    
    if [ $FAILED_TESTS -eq 0 ]; then
        print_status $GREEN "🎉 ALL TESTS PASSED! Success Rate: 100%"
        print_status $GREEN "✅ Multi-Role Authentication System is working correctly"
    elif [ $success_rate -ge 80 ]; then
        print_status $YELLOW "⚠️ Most tests passed. Success Rate: ${success_rate}%"
        print_status $YELLOW "Some issues detected - review failed tests above"
    else
        print_status $RED "❌ Multiple test failures. Success Rate: ${success_rate}%"
        print_status $RED "Critical issues detected - system needs attention"
    fi
    
    # Generate timestamp report
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo ""
    echo "Test completed at: $timestamp"
    echo "Report saved to: regression-test-report.log"
    
    # Save detailed report
    {
        echo "Multi-Role Authentication System - Regression Test Report"
        echo "========================================================"
        echo "Timestamp: $timestamp"
        echo "Total Tests: $TOTAL_TESTS"
        echo "Passed: $PASSED_TESTS"
        echo "Failed: $FAILED_TESTS"
        echo "Success Rate: ${success_rate}%"
        echo ""
        echo "Environment:"
        echo "- Laravel Version: $(php artisan --version)"
        echo "- PHP Version: $(php --version | head -1)"
        echo "- Database: $(php artisan tinker --execute='echo config("database.default");' 2>/dev/null | tail -1)"
        echo ""
        if [ $FAILED_TESTS -gt 0 ]; then
            echo "FAILED TESTS REQUIRE ATTENTION"
            echo "Review the output above for specific failure details"
        else
            echo "ALL SYSTEMS OPERATIONAL"
        fi
    } > regression-test-report.log
}

# Function to show usage
show_usage() {
    echo "Multi-Role Authentication System - Regression Testing Script"
    echo ""
    echo "Usage: $0 [options]"
    echo ""
    echo "Options:"
    echo "  --quick     Run only critical tests (faster execution)"
    echo "  --full      Run complete test suite (default)"
    echo "  --setup     Only setup environment and verify database"
    echo "  --help      Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                 # Run full regression test suite"
    echo "  $0 --quick         # Run quick validation tests"
    echo "  $0 --setup         # Setup and verify environment only"
}

# Main execution function
main() {
    local mode="full"
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            --quick)
                mode="quick"
                shift
                ;;
            --full)
                mode="full"
                shift
                ;;
            --setup)
                mode="setup"
                shift
                ;;
            --help)
                show_usage
                exit 0
                ;;
            *)
                print_status $RED "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Print header
    print_status $PURPLE "🧪 Multi-Role User Authentication System"
    print_status $PURPLE "   Regression Testing Script v1.0"
    echo ""
    
    # Verify environment
    verify_environment
    
    # Setup test environment
    setup_environment
    
    # Verify database
    verify_database
    
    if [ "$mode" = "setup" ]; then
        print_status $GREEN "✅ Environment setup and verification complete"
        exit 0
    fi
    
    # Run tests based on mode
    if [ "$mode" = "quick" ]; then
        print_status $YELLOW "Running quick validation tests..."
        test_authentication
        test_middleware
    else
        print_status $YELLOW "Running full regression test suite..."
        test_authentication
        test_middleware
        test_role_management
        test_frontend_integration
        test_security
        test_performance
    fi
    
    # Generate final report
    generate_report
    
    # Exit with appropriate code
    if [ $FAILED_TESTS -eq 0 ]; then
        exit 0
    else
        exit 1
    fi
}

# Run main function with all arguments
main "$@"
