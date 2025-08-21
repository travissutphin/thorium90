#!/bin/bash
# Enhanced Multi-Role User Authentication System - Regression Testing Script (Linux/Mac)
# Groups tests logically for maximum efficiency and early bug detection
# Version 2.0 - Enhanced with performance metrics and detailed reporting

set -e  # Exit on any error

# Configuration
SCRIPT_VERSION="2.0"
START_TIME=$(date +%s)
LOG_FILE="regression-test-detailed.log"
REPORT_FILE="regression-test-report.html"
QUICK_MODE=false
VERBOSE=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Test counters
TOTAL_GROUPS=6
CURRENT_GROUP=0
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
GROUP_FAILURES=0

# Performance tracking
GROUP_START_TIME=0
GROUP_END_TIME=0

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --quick)
            QUICK_MODE=true
            shift
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        --help)
            show_help
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Initialize log file
cat > "$LOG_FILE" << EOF
Multi-Role Authentication System - Enhanced Regression Test
=============================================================
Start Time: $(date)
Quick Mode: $QUICK_MODE
Verbose Mode: $VERBOSE

EOF

# Function to print colored output
print_header() {
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${CYAN}🧪 Multi-Role User Authentication System${NC}"
    echo -e "${CYAN}   Enhanced Regression Testing v$SCRIPT_VERSION${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo
    if [ "$QUICK_MODE" = true ]; then
        echo -e "${YELLOW}🚀 QUICK MODE ENABLED - Essential tests only${NC}"
        echo
    fi
}

# Function to log test results
log_test_result() {
    local test_name="$1"
    local result="$2"
    local details="$3"
    echo "[$(date '+%H:%M:%S')] $test_name: $result - $details" >> "$LOG_FILE"
}

# Function to verify environment
verify_environment() {
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${CYAN}🔍 ENVIRONMENT VERIFICATION${NC}"
    echo -e "${BLUE}=====================================${NC}"

    if [ ! -f "artisan" ]; then
        echo -e "${RED}❌ Error: Not in a Laravel project directory${NC}"
        echo "Not in Laravel project directory" >> "$LOG_FILE"
        exit 1
    fi

    if ! command -v php &> /dev/null; then
        echo -e "${RED}❌ Error: PHP is not installed or not in PATH${NC}"
        echo "PHP not available" >> "$LOG_FILE"
        exit 1
    fi

    if ! command -v composer &> /dev/null; then
        echo -e "${RED}❌ Error: Composer is not installed or not in PATH${NC}"
        echo "Composer not available" >> "$LOG_FILE"
        exit 1
    fi

    echo -e "${GREEN}✅ Environment verification complete${NC}"
    echo "Environment verification: PASSED" >> "$LOG_FILE"
    echo
}

# Function to setup test environment
setup_environment() {
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${CYAN}📋 SETTING UP TEST ENVIRONMENT${NC}"
    echo -e "${BLUE}=====================================${NC}"

    echo "Clearing caches..."
    php artisan cache:clear > /dev/null 2>&1
    php artisan config:clear > /dev/null 2>&1
    php artisan route:clear > /dev/null 2>&1
    php artisan view:clear > /dev/null 2>&1

    echo "Running fresh migrations..."
    if ! php artisan migrate:fresh --force > /dev/null 2>&1; then
        echo -e "${RED}❌ Migration failed${NC}"
        echo "Migration failed" >> "$LOG_FILE"
        exit 1
    fi

    echo "Seeding database..."
    if ! php artisan db:seed --force > /dev/null 2>&1; then
        echo -e "${RED}❌ Database seeding failed${NC}"
        echo "Database seeding failed" >> "$LOG_FILE"
        exit 1
    fi

    echo -e "${GREEN}✅ Test environment setup complete${NC}"
    echo "Test environment setup: PASSED" >> "$LOG_FILE"
    echo
}

# Function to run a single test
run_single_test() {
    local test_name="$1"
    local test_path="$2"
    
    echo "Running $test_name..."
    if php artisan test "$test_path" --stop-on-failure > temp_test_output.txt 2>&1; then
        echo -e "${GREEN}✅ $test_name passed${NC}"
        log_test_result "$test_name" "PASSED" "All tests successful"
        ((PASSED_TESTS++))
    else
        echo -e "${RED}❌ $test_name failed${NC}"
        log_test_result "$test_name" "FAILED" "See detailed output in temp_test_output.txt"
        ((FAILED_TESTS++))
        ((GROUP_FAILURES++))
        
        if [ "$VERBOSE" = true ]; then
            echo -e "${YELLOW}Failure details:${NC}"
            tail -10 temp_test_output.txt
        fi
    fi
    ((TOTAL_TESTS++))
}

# Function to start group timing
start_group_timing() {
    GROUP_START_TIME=$(date +%s)
    echo "Group $CURRENT_GROUP started at: $(date)" >> "$LOG_FILE"
}

# Function to end group timing
end_group_timing() {
    GROUP_END_TIME=$(date +%s)
    local duration=$((GROUP_END_TIME - GROUP_START_TIME))
    echo "Group $CURRENT_GROUP completed at: $(date)" >> "$LOG_FILE"
    echo "Group $CURRENT_GROUP duration: ${duration}s" >> "$LOG_FILE"
    echo "Group $CURRENT_GROUP failures: $GROUP_FAILURES" >> "$LOG_FILE"
    echo >> "$LOG_FILE"
    GROUP_FAILURES=0
}

# Function to run Group 1: Foundation & Database
run_group_1_foundation() {
    CURRENT_GROUP=1
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}🏗️ GROUP 1: FOUNDATION & DATABASE${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: Database integrity and basic setup"
    echo

    # Check roles
    echo "Verifying roles..."
    role_count=$(php artisan tinker --execute="echo \Spatie\Permission\Models\Role::count();" 2>/dev/null | tail -1)
    if [ "$role_count" -ge 5 ]; then
        echo -e "${GREEN}✅ Roles: $role_count found${NC}"
        log_test_result "Role Verification" "PASSED" "$role_count roles found"
        ((PASSED_TESTS++))
    else
        echo -e "${RED}❌ Roles: Only $role_count found${NC}"
        log_test_result "Role Verification" "FAILED" "Only $role_count roles found"
        ((FAILED_TESTS++))
        ((GROUP_FAILURES++))
    fi
    ((TOTAL_TESTS++))

    # Check permissions
    echo "Verifying permissions..."
    permission_count=$(php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" 2>/dev/null | tail -1)
    if [ "$permission_count" -ge 20 ]; then
        echo -e "${GREEN}✅ Permissions: $permission_count found${NC}"
        log_test_result "Permission Verification" "PASSED" "$permission_count permissions found"
        ((PASSED_TESTS++))
    else
        echo -e "${RED}❌ Permissions: Only $permission_count found${NC}"
        log_test_result "Permission Verification" "FAILED" "Only $permission_count permissions found"
        ((FAILED_TESTS++))
        ((GROUP_FAILURES++))
    fi
    ((TOTAL_TESTS++))

    # Unit tests
    echo "Running unit tests..."
    if php artisan test tests/Unit/ --stop-on-failure > temp_test_output.txt 2>&1; then
        echo -e "${GREEN}✅ Unit tests passed${NC}"
        log_test_result "Unit Tests" "PASSED" "All unit tests successful"
        ((PASSED_TESTS++))
    else
        echo -e "${RED}❌ Unit tests failed${NC}"
        log_test_result "Unit Tests" "FAILED" "See detailed output"
        ((FAILED_TESTS++))
        ((GROUP_FAILURES++))
    fi
    ((TOTAL_TESTS++))

    end_group_timing
}

# Function to run Group 2: Authentication Core
run_group_2_authentication() {
    CURRENT_GROUP=2
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}🔐 GROUP 2: AUTHENTICATION CORE${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: Login, registration, password management"
    echo

    run_single_test "Registration" "tests/Feature/Auth/RegistrationTest.php"
    run_single_test "Authentication" "tests/Feature/Auth/AuthenticationTest.php"
    run_single_test "Password Reset" "tests/Feature/Auth/PasswordResetTest.php"
    run_single_test "Email Verification" "tests/Feature/Auth/EmailVerificationTest.php"
    run_single_test "Password Confirmation" "tests/Feature/Auth/PasswordConfirmationTest.php"

    end_group_timing
}

# Function to run Group 3: Access Control & Middleware
run_group_3_access_control() {
    CURRENT_GROUP=3
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}🛡️ GROUP 3: ACCESS CONTROL & MIDDLEWARE${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: Security boundaries and route protection"
    echo

    run_single_test "Middleware Protection" "tests/Feature/MiddlewareTest.php"
    run_single_test "Role-Based Access" "tests/Feature/RoleBasedAccessTest.php"
    run_single_test "Dashboard Access" "tests/Feature/DashboardTest.php"

    end_group_timing
}

# Function to run Group 4: Advanced Authentication
run_group_4_advanced_auth() {
    CURRENT_GROUP=4
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}🔒 GROUP 4: ADVANCED AUTHENTICATION${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: 2FA, social login, API auth"
    echo

    run_single_test "Two-Factor Authentication" "tests/Feature/TwoFactorAuthenticationTest.php"
    run_single_test "Social Login" "tests/Feature/SocialLoginTest.php"
    run_single_test "API Authentication" "tests/Feature/SanctumApiTest.php"
    run_single_test "Email Resending" "tests/Feature/ResendEmailTest.php"

    end_group_timing
}

# Function to run Group 5: Admin & User Management
run_group_5_admin_management() {
    CURRENT_GROUP=5
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}👥 GROUP 5: ADMIN & USER MANAGEMENT${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: User and role administration"
    echo

    run_single_test "User Management" "tests/Feature/Admin/UserManagementTest.php"
    run_single_test "Role Management" "tests/Feature/Admin/RoleManagementTest.php"
    run_single_test "Role CRUD Operations" "tests/Feature/Admin/RoleManagementCrudTest.php"
    run_single_test "User Role Assignments" "tests/Feature/Admin/UserRoleManagementTest.php"
    run_single_test "Admin Settings" "tests/Feature/Admin/AdminSettingsTest.php"

    end_group_timing
}

# Function to run Group 6: Content & Frontend
run_group_6_content_frontend() {
    CURRENT_GROUP=6
    start_group_timing
    
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${PURPLE}🎨 GROUP 6: CONTENT & FRONTEND${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo "Testing: CMS features and UI integration"
    echo

    run_single_test "Page Management" "tests/Feature/Content/PageManagementTest.php"
    run_single_test "Page SEO" "tests/Feature/Content/PageSEOTest.php"
    run_single_test "Sitemap Generation" "tests/Feature/Content/SitemapTest.php"
    run_single_test "UI Permissions" "tests/Feature/UIPermissionTest.php"
    run_single_test "Profile Updates" "tests/Feature/Settings/ProfileUpdateTest.php"
    run_single_test "Password Updates" "tests/Feature/Settings/PasswordUpdateTest.php"

    end_group_timing
}

# Function to generate failure report
generate_failure_report() {
    echo
    echo -e "${RED}🚨 FAILURE ANALYSIS${NC}"
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${YELLOW}Group $CURRENT_GROUP failed with $GROUP_FAILURES failures${NC}"
    echo
    echo -e "${CYAN}💡 RECOMMENDED ACTIONS:${NC}"
    echo "1. Review the detailed log: $LOG_FILE"
    echo "2. Check the last test output: temp_test_output.txt"
    echo "3. Verify database seeding completed successfully"
    echo "4. Ensure all migrations ran without errors"
    echo "5. Check for missing dependencies or configuration issues"
    echo
}

# Function to generate HTML report
generate_html_report() {
    local success_rate=$((PASSED_TESTS * 100 / TOTAL_TESTS))
    
    cat > "$REPORT_FILE" << EOF
<!DOCTYPE html>
<html>
<head>
    <title>Regression Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .success { color: green; font-weight: bold; }
        .failure { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .metric-table { max-width: 500px; }
        .status-good { background-color: #d4edda; }
        .status-warning { background-color: #fff3cd; }
        .status-error { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪 Multi-Role Authentication System</h1>
        <h2>Enhanced Regression Test Report v$SCRIPT_VERSION</h2>
        <p><strong>Generated:</strong> $(date)</p>
        <p><strong>Quick Mode:</strong> $QUICK_MODE</p>
        <p><strong>Verbose Mode:</strong> $VERBOSE</p>
    </div>
    
    <h2>📊 Test Summary</h2>
    <table class="metric-table">
        <tr><th>Metric</th><th>Value</th></tr>
        <tr><td>Total Tests</td><td>$TOTAL_TESTS</td></tr>
        <tr><td>Passed</td><td class="success">$PASSED_TESTS</td></tr>
        <tr><td>Failed</td><td class="failure">$FAILED_TESTS</td></tr>
        <tr><td>Success Rate</td><td>$success_rate%</td></tr>
        <tr><td>Duration</td><td>$(($(date +%s) - START_TIME))s</td></tr>
    </table>
    
    <h2>🎯 Test Groups</h2>
    <table>
        <tr><th>Group</th><th>Name</th><th>Status</th></tr>
        <tr><td>1</td><td>Foundation & Database</td><td class="status-good">✅ Critical</td></tr>
        <tr><td>2</td><td>Authentication Core</td><td class="status-good">✅ Essential</td></tr>
        <tr><td>3</td><td>Access Control & Middleware</td><td class="status-good">✅ Security</td></tr>
EOF

    if [ "$QUICK_MODE" = false ]; then
        cat >> "$REPORT_FILE" << EOF
        <tr><td>4</td><td>Advanced Authentication</td><td class="status-good">✅ Extended</td></tr>
        <tr><td>5</td><td>Admin & User Management</td><td class="status-good">✅ Features</td></tr>
        <tr><td>6</td><td>Content & Frontend</td><td class="status-good">✅ Integration</td></tr>
EOF
    fi

    cat >> "$REPORT_FILE" << EOF
    </table>
    
    <h2>📋 Detailed Log</h2>
    <p>For detailed test output, see: <code>$LOG_FILE</code></p>
    
    <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666;">
        <p>Generated by Enhanced Regression Testing Script v$SCRIPT_VERSION</p>
    </footer>
</body>
</html>
EOF
}

# Function to generate final report
generate_final_report() {
    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))
    local success_rate=$((PASSED_TESTS * 100 / TOTAL_TESTS))
    
    echo
    echo -e "${BLUE}=====================================${NC}"
    echo -e "${CYAN}📊 FINAL TEST RESULTS${NC}"
    echo -e "${BLUE}=====================================${NC}"

    echo "Total Tests Run: $TOTAL_TESTS"
    echo "Passed: $PASSED_TESTS"
    echo "Failed: $FAILED_TESTS"
    echo "Success Rate: $success_rate%"
    echo "Total Duration: ${duration}s"
    echo

    if [ $FAILED_TESTS -eq 0 ]; then
        echo -e "${GREEN}🎉 ALL TESTS PASSED!${NC}"
        echo -e "${GREEN}✅ Multi-Role Authentication System is fully operational${NC}"
        EXIT_CODE=0
    elif [ $success_rate -ge 90 ]; then
        echo -e "${YELLOW}⚠️ Most tests passed - Minor issues detected${NC}"
        echo "Review failed tests for optimization opportunities"
        EXIT_CODE=1
    elif [ $success_rate -ge 80 ]; then
        echo -e "${YELLOW}⚠️ Some tests failed - Moderate issues detected${NC}"
        echo "System functional but needs attention"
        EXIT_CODE=1
    else
        echo -e "${RED}❌ Multiple test failures - Critical issues detected${NC}"
        echo "System requires immediate attention"
        EXIT_CODE=1
    fi

    # Generate HTML report
    generate_html_report

    echo
    echo -e "${CYAN}📄 Reports generated:${NC}"
    echo "  - Detailed log: $LOG_FILE"
    echo "  - HTML report: $REPORT_FILE"
    echo

    # Final log entry
    cat >> "$LOG_FILE" << EOF
=============================================================
End Time: $(date)
Total Tests: $TOTAL_TESTS
Passed: $PASSED_TESTS
Failed: $FAILED_TESTS
Success Rate: $success_rate%
Duration: ${duration}s
Exit Code: $EXIT_CODE
EOF
}

# Function to show help
show_help() {
    echo
    echo "Enhanced Regression Testing Script v$SCRIPT_VERSION"
    echo
    echo "Usage: $0 [options]"
    echo
    echo "Options:"
    echo "  --quick     Run essential tests only (Groups 1-3)"
    echo "  --verbose   Show detailed failure output"
    echo "  --help      Show this help message"
    echo
    echo "Test Groups:"
    echo "  Group 1: Foundation & Database (Critical)"
    echo "  Group 2: Authentication Core (Essential)"
    echo "  Group 3: Access Control & Middleware (Security)"
    echo "  Group 4: Advanced Authentication (Extended)"
    echo "  Group 5: Admin & User Management (Features)"
    echo "  Group 6: Content & Frontend (Integration)"
    echo
    echo "Quick mode runs Groups 1-3 only for rapid validation."
    echo "Full mode runs all 6 groups for comprehensive testing."
    echo
}

# Main execution
main() {
    print_header
    
    # Environment verification
    verify_environment
    
    # Setup test environment
    setup_environment
    
    # Execute test groups
    run_group_1_foundation
    if [ $GROUP_FAILURES -gt 0 ]; then
        echo
        echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
        echo "Testing stopped to allow for immediate bug fixing."
        echo "Review the detailed log: $LOG_FILE"
        echo
        generate_failure_report
        exit 1
    fi

    run_group_2_authentication
    if [ $GROUP_FAILURES -gt 0 ]; then
        echo
        echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
        echo "Testing stopped to allow for immediate bug fixing."
        echo "Review the detailed log: $LOG_FILE"
        echo
        generate_failure_report
        exit 1
    fi

    run_group_3_access_control
    if [ $GROUP_FAILURES -gt 0 ]; then
        echo
        echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
        echo "Testing stopped to allow for immediate bug fixing."
        echo "Review the detailed log: $LOG_FILE"
        echo
        generate_failure_report
        exit 1
    fi

    if [ "$QUICK_MODE" = false ]; then
        run_group_4_advanced_auth
        if [ $GROUP_FAILURES -gt 0 ]; then
            echo
            echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
            echo "Testing stopped to allow for immediate bug fixing."
            echo "Review the detailed log: $LOG_FILE"
            echo
            generate_failure_report
            exit 1
        fi

        run_group_5_admin_management
        if [ $GROUP_FAILURES -gt 0 ]; then
            echo
            echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
            echo "Testing stopped to allow for immediate bug fixing."
            echo "Review the detailed log: $LOG_FILE"
            echo
            generate_failure_report
            exit 1
        fi

        run_group_6_content_frontend
        if [ $GROUP_FAILURES -gt 0 ]; then
            echo
            echo -e "${RED}❌ CRITICAL FAILURE IN GROUP $CURRENT_GROUP${NC}"
            echo "Testing stopped to allow for immediate bug fixing."
            echo "Review the detailed log: $LOG_FILE"
            echo
            generate_failure_report
            exit 1
        fi
    fi

    # Generate final report
    generate_final_report
    
    # Cleanup
    rm -f temp_test_output.txt
    
    exit $EXIT_CODE
}

# Run main function
main "$@"
