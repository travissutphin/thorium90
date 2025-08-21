@echo off
echo Testing SQLite Setup for Thorium90
echo =====================================

cd /d "C:\xampp\htdocs\thorium90"

echo.
echo Current database configuration:
php artisan config:show database.default
php artisan config:show database.connections.sqlite.database

echo.
echo Testing SQLite database connection...
php artisan tinker --execute="try { DB::connection('sqlite')->getPdo(); echo 'SQLite connection: SUCCESS'; } catch (Exception \$e) { echo 'SQLite connection failed: ' . \$e->getMessage(); }"

echo.
echo Checking if migrations table exists...
php artisan tinker --execute="try { echo 'Migrations table exists: ' . (Schema::hasTable('migrations') ? 'YES' : 'NO'); } catch (Exception \$e) { echo 'Cannot check migrations table: ' . \$e->getMessage(); }"

echo.
echo Testing complete.
pause