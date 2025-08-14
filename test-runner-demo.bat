@echo off
REM Demo script to showcase the enhanced regression testing system
REM This script demonstrates the different modes and features

echo.
echo =====================================
echo 🎬 Enhanced Regression Testing Demo
echo =====================================
echo.

echo This demo will showcase the enhanced regression testing system
echo with its grouped approach and detailed reporting features.
echo.

:menu
echo Please select a demo option:
echo.
echo 1. Quick Mode Demo (Groups 1-3, ~3-5 minutes)
echo 2. Full Mode Demo (All 6 groups, ~8-12 minutes)
echo 3. Show Help Information
echo 4. View Configuration
echo 5. Exit Demo
echo.
set /p choice="Enter your choice (1-5): "

if "%choice%"=="1" goto quick_demo
if "%choice%"=="2" goto full_demo
if "%choice%"=="3" goto show_help
if "%choice%"=="4" goto show_config
if "%choice%"=="5" goto exit_demo
echo Invalid choice. Please try again.
goto menu

:quick_demo
echo.
echo =====================================
echo 🚀 QUICK MODE DEMO
echo =====================================
echo.
echo Running essential tests only (Groups 1-3):
echo - Group 1: Foundation & Database (Critical)
echo - Group 2: Authentication Core (Essential)  
echo - Group 3: Access Control & Middleware (Security)
echo.
echo This mode is perfect for:
echo - Rapid validation during development
echo - CI/CD pipeline integration
echo - Quick health checks
echo.
pause
echo.
echo Starting Quick Mode...
regression-test-enhanced.bat --quick
echo.
echo Quick Mode Demo Complete!
echo Check the generated reports:
echo - regression-test-detailed.log
echo - regression-test-report.html
echo.
pause
goto menu

:full_demo
echo.
echo =====================================
echo 🔬 FULL MODE DEMO
echo =====================================
echo.
echo Running comprehensive tests (All 6 groups):
echo - Group 1: Foundation & Database (Critical)
echo - Group 2: Authentication Core (Essential)
echo - Group 3: Access Control & Middleware (Security)
echo - Group 4: Advanced Authentication (Extended)
echo - Group 5: Admin & User Management (Features)
echo - Group 6: Content & Frontend (Integration)
echo.
echo This mode is perfect for:
echo - Pre-deployment validation
echo - Comprehensive system testing
echo - Weekly regression testing
echo.
pause
echo.
echo Starting Full Mode...
regression-test-enhanced.bat
echo.
echo Full Mode Demo Complete!
echo Check the generated reports:
echo - regression-test-detailed.log
echo - regression-test-report.html
echo.
pause
goto menu

:show_help
echo.
echo =====================================
echo 📖 HELP INFORMATION
echo =====================================
echo.
regression-test-enhanced.bat --help
echo.
pause
goto menu

:show_config
echo.
echo =====================================
echo ⚙️ CONFIGURATION OVERVIEW
echo =====================================
echo.
echo Configuration file: regression-test-config.json
echo.
if exist regression-test-config.json (
    echo Configuration file found! Key settings:
    echo.
    echo Test Groups: 6 logical groups
    echo Execution Modes: Quick, Full, Critical-only
    echo Reporting: HTML, Log, JSON ^(planned^)
    echo Performance Tracking: Enabled
    echo Failure Handling: Stop on group failure
    echo.
    echo For detailed configuration, open: regression-test-config.json
) else (
    echo ❌ Configuration file not found!
    echo Expected: regression-test-config.json
)
echo.
pause
goto menu

:exit_demo
echo.
echo =====================================
echo 👋 DEMO COMPLETE
echo =====================================
echo.
echo Thank you for trying the Enhanced Regression Testing System!
echo.
echo Key Benefits Demonstrated:
echo ✅ Grouped test execution for logical organization
echo ✅ Multiple execution modes for different needs
echo ✅ Detailed reporting with HTML and log files
echo ✅ Performance tracking and benchmarking
echo ✅ Early failure detection with group-based stopping
echo ✅ Comprehensive documentation and help system
echo.
echo Next Steps:
echo 1. Use 'regression-test-enhanced.bat --quick' for daily testing
echo 2. Use 'regression-test-enhanced.bat' for comprehensive testing
echo 3. Review generated reports for insights
echo 4. Integrate into your CI/CD pipeline
echo.
echo Documentation:
echo - Quick Card: REGRESSION-TESTING-QUICK-CARD.md
echo - Full Guide: docs/testing/ENHANCED-REGRESSION-TESTING.md
echo - Legacy Guide: docs/testing/TESTING-QUICK-REFERENCE.md
echo.
pause
exit /b 0

REM Error handling
:error
echo.
echo ❌ An error occurred during the demo.
echo Please check that you're in the correct Laravel project directory
echo and that all required files are present.
echo.
pause
exit /b 1
