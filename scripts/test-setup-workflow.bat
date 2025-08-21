@echo off
echo Thorium90 SQLite Setup Validation Test
echo ======================================

cd /d "C:\xampp\htdocs\thorium90"

echo.
echo 1. Checking current database configuration...
php artisan config:show database.default
php artisan db:show --table=users

echo.
echo 2. Verifying SQLite database file...
if exist "database\database.sqlite" (
    echo SQLite database file: EXISTS
    for %%A in ("database\database.sqlite") do echo File size: %%~zA bytes
) else (
    echo SQLite database file: NOT FOUND
)

echo.
echo 3. Checking migration status...
php artisan migrate:status | findstr "Ran"

echo.
echo 4. Verifying admin user exists...
php artisan tinker --execute="echo 'Total users: ' . App\Models\User::count();"

echo.
echo 5. Testing database connection...
php artisan db:table users --count

echo.
echo ✅ SQLite setup validation complete!
echo.
echo Current .env configuration:
echo DB_CONNECTION=%DB_CONNECTION%
echo DB_DATABASE=%DB_DATABASE%

pause