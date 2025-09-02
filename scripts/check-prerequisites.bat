@echo off
REM Thorium90 Prerequisites Checker for Windows
REM Run this script to validate your development environment

echo.
echo Thorium90 Prerequisites Checker (Windows)
echo =========================================
echo.

REM Run the PHP prerequisites checker
php "%~dp0check-prerequisites.php"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Additional Windows-specific help:
    echo.
    echo PHP Extensions:
    echo - Download PHP from https://windows.php.net/download/
    echo - Enable extensions in php.ini: uncomment lines starting with extension=
    echo.
    echo Composer:
    echo - Download from https://getcomposer.org/download/
    echo.
    echo Node.js:
    echo - Download LTS version from https://nodejs.org/
    echo.
    pause
    exit /b 1
)

echo.
echo Ready to proceed with Thorium90 setup!
echo.
pause