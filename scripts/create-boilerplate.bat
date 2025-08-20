@echo off
echo ===========================================
echo    Thorium90 Boilerplate Creator
echo ===========================================
echo.

REM Get project name
set /p PROJECT_NAME="Enter project name: "
if "%PROJECT_NAME%"=="" set PROJECT_NAME=thorium90-project

echo.
echo Creating project: %PROJECT_NAME%
echo.

REM Create project using composer
echo [1/4] Creating project with Composer...
composer create-project thorium90/boilerplate %PROJECT_NAME% --prefer-dist

if errorlevel 1 (
    echo ERROR: Failed to create project. Make sure Composer is installed and thorium90/boilerplate package exists.
    pause
    exit /b 1
)

echo.
echo [2/4] Changing to project directory...
cd %PROJECT_NAME%

echo.
echo [3/4] Installing dependencies...
call composer install
call npm install

echo.
echo [4/4] Running setup wizard...
call php artisan thorium90:setup --interactive

echo.
echo ===========================================
echo    Setup Complete!
echo ===========================================
echo.
echo Your Thorium90 project "%PROJECT_NAME%" is ready!
echo.
echo Next steps:
echo   1. cd %PROJECT_NAME%
echo   2. php artisan serve
echo   3. Visit http://localhost:8000
echo.
pause