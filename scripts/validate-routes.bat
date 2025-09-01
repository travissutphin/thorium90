@echo off
echo ================================
echo   ROUTE VALIDATION PROTOCOL
echo ================================
echo.

echo [1/3] Generating route list...
php artisan route:list --json > temp-routes.json

echo [2/3] Checking for common problematic routes...
php artisan route:list | findstr "admin.blog" > nul
if errorlevel 1 (
    echo WARNING: No admin blog routes found!
) else (
    echo ✓ Admin blog routes exist
)

php artisan route:list | findstr "admin.dashboard" > nul
if errorlevel 1 (
    echo WARNING: admin.dashboard route not found!
) else (
    echo ✓ Admin dashboard route exists
)

echo [3/3] Common route patterns check...
echo Available admin.blog routes:
php artisan route:list | findstr "admin.blog"

echo.
echo Available admin routes:
php artisan route:list | findstr "admin\." | head -10

echo.
echo ================================
echo   ROUTE VALIDATION COMPLETE
echo ================================

del temp-routes.json 2>nul
pause