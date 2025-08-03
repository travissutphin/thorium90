# Installation Guide

This guide will walk you through the complete installation and setup of the Multi-Role User Authentication System.

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your system:

### Required Software
- **PHP 8.2 or higher**
- **Composer 2.0 or higher**
- **Node.js 18 or higher**
- **npm or yarn**
- **MySQL 8.0+ or PostgreSQL 13+**
- **Git**

### PHP Extensions
```bash
# Required PHP extensions
php -m | grep -E "(bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml)"
```

### System Requirements
- **Memory**: Minimum 512MB RAM (1GB recommended)
- **Storage**: At least 1GB free space
- **Network**: Internet connection for package downloads

## 🚀 Quick Installation

### Step 1: Clone the Repository

```bash
# Clone the repository
git clone https://github.com/your-username/thorium90.git
cd thorium90

# Or if you're starting from scratch
composer create-project laravel/laravel thorium90
cd thorium90
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Step 3: Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit the `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 4: Database Setup

```bash
# Run migrations
php artisan migrate

# Seed the database with roles and permissions
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RolePermissionSeeder
```

### Step 5: Build Assets

```bash
# Build frontend assets
npm run build

# Or for development
npm run dev
```

### Step 6: Create Admin User

```bash
# Create a super admin user
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Super Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
]);

$user->assignRole('Super Admin');
exit
```

### Step 7: Start the Application

```bash
# Start the development server
php artisan serve

# In another terminal, start Vite for hot reloading
npm run dev
```

Visit `http://localhost:8000` and log in with:
- **Email**: admin@example.com
- **Password**: password123

## 🔧 Detailed Installation

### Manual Installation Steps

If you prefer to install components manually or need to customize the installation:

#### 1. Install Spatie Laravel Permission

```bash
# Install the package
composer require spatie/laravel-permission

# Publish configuration
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate
```

#### 2. Configure the User Model

Add the `HasRoles` trait to your User model:

```php
<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    
    // ... rest of your model
}
```

#### 3. Set Up Middleware

Register the middleware in `bootstrap/app.php`:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
    'role.any' => \App\Http\Middleware\EnsureUserHasAnyRole::class,
    'permission.any' => \App\Http\Middleware\EnsureUserHasAnyPermission::class,
]);
```

#### 4. Configure Inertia.js

Install Inertia.js if not already installed:

```bash
composer require inertiajs/inertia-laravel
npm install @inertiajs/react
```

#### 5. Set Up Frontend

Install React and TypeScript dependencies:

```bash
npm install react react-dom @types/react @types/react-dom
npm install -D typescript @vitejs/plugin-react
```

## 🐳 Docker Installation

### Using Docker Compose

```bash
# Clone the repository
git clone https://github.com/your-username/thorium90.git
cd thorium90

# Copy Docker environment file
cp .env.docker .env

# Start containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Run migrations and seeders
docker-compose exec app php artisan migrate --seed

# Build assets
docker-compose exec app npm run build
```

### Docker Environment Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=thorium90
DB_USERNAME=root
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 🔒 Security Configuration

### 1. Update Default Passwords

```bash
# Change default admin password
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@example.com')->first();
$user->update(['password' => Hash::make('your-secure-password')]);
```

### 2. Configure Session Security

Update your `.env` file:

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### 3. Set Up HTTPS

For production, ensure HTTPS is properly configured:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

## 🧪 Testing Installation

### Run the Test Suite

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter=UIPermissionTest
php artisan test --filter=WithRoles
```

### Manual Testing

1. **Login Test**: Try logging in with the admin account
2. **Role Test**: Check if the admin user has Super Admin role
3. **Permission Test**: Verify permissions are working in the UI
4. **Route Protection**: Test protected routes with different user roles

## 🚨 Troubleshooting

### Common Issues

#### 1. Permission Denied Errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Database Connection Issues

```bash
# Test database connection
php artisan tinker
DB::connection()->getPdo();
```

#### 3. Composer Memory Issues

```bash
# Increase memory limit
COMPOSER_MEMORY_LIMIT=-1 composer install
```

#### 4. Node.js Build Issues

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
```

### Getting Help

If you encounter issues:

1. Check the [Troubleshooting](Troubleshooting) page
2. Review the [FAQ](FAQ)
3. Search existing [GitHub Issues](https://github.com/your-username/thorium90/issues)
4. Create a new issue with detailed information

## 📚 Next Steps

After successful installation:

1. **[Configuration](Configuration)** - Configure the system for your needs
2. **[User Guide](User-Guide)** - Learn how to use the system
3. **[Developer Guide](Developer-Guide)** - Understand the technical implementation
4. **[Deployment Guide](Deployment-Guide)** - Deploy to production

## 🔄 Updates

To update the system:

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild assets
npm run build
```

---

**Need help?** Check out our [Support](Support) page or open an [issue](https://github.com/your-username/thorium90/issues) on GitHub. 