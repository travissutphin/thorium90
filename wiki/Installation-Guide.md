# Installation Guide

## Overview

This guide will walk you through setting up the Thorium90 CMS from scratch. The system is built with Laravel 11, React 18, and Inertia.js, providing a modern full-stack development experience.

## Prerequisites

### System Requirements
- **PHP**: 8.2 or higher
- **Node.js**: 18.0 or higher
- **Composer**: 2.0 or higher
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Apache or Nginx
- **Git**: For version control

### Development Tools (Recommended)
- **IDE**: VS Code, PhpStorm, or similar
- **Database GUI**: phpMyAdmin, Sequel Pro, or TablePlus
- **API Testing**: Postman or Insomnia
- **Terminal**: Modern terminal with Git support

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/travissutphin/thorium90.git
cd thorium90
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Choose the appropriate environment configuration:

#### For Development
```bash
cp .env.example .env
```

#### For Testing
```bash
cp .env.testing.example .env.testing
```

#### For Production
```bash
cp .env.production.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Database Setup

#### Option A: SQLite (Development - Default)
No additional setup required. The database file will be created automatically.

#### Option B: MySQL/PostgreSQL
1. Create a new database:
```sql
CREATE DATABASE thorium90_dev;
```

2. Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90_dev
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

### 8. Seed the Database

```bash
php artisan db:seed
```

This will create:
- Default roles (Super Admin, Admin, Editor, Author, Subscriber)
- Permissions for all system features
- Role-permission assignments
- Default settings
- Sample content (optional)

### 9. Build Frontend Assets

#### For Development
```bash
npm run dev
```

#### For Production
```bash
npm run build
```

### 10. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Post-Installation Setup

### 1. Create Your First Admin User

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Your Name',
    'email' => 'your@email.com',
    'password' => bcrypt('your-secure-password'),
    'email_verified_at' => now(),
]);

$user->assignRole('Super Admin');
```

### 2. Configure Mail Settings

Update your `.env` file with mail configuration:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=your_resend_api_key
```

### 3. Set Up Social Login (Optional)

Configure OAuth providers in your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback

# GitHub OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URL=http://localhost:8000/auth/github/callback
```

## Verification

### 1. Check System Status

Visit `http://localhost:8000` and verify:
- ✅ Homepage loads correctly
- ✅ Login page is accessible
- ✅ Registration works (if enabled)
- ✅ Dashboard loads after login

### 2. Test Key Features

1. **Authentication**:
   - Login with your admin account
   - Test password reset functionality
   - Verify email verification (if configured)

2. **Role Management**:
   - Navigate to Admin → Users
   - Create a test user
   - Assign different roles
   - Test permission restrictions

3. **Content Management**:
   - Navigate to Content → Pages
   - Create a test page
   - Publish the page
   - View the page on frontend

### 3. Run Tests

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## Development Workflow

### 1. Frontend Development

Start the Vite development server:
```bash
npm run dev
```

This enables:
- Hot module replacement
- Automatic browser refresh
- TypeScript compilation
- CSS processing

### 2. Backend Development

Use Laravel's built-in server:
```bash
php artisan serve
```

For additional debugging:
```bash
# Enable query logging
php artisan tinker
>>> DB::enableQueryLog();

# View logs
tail -f storage/logs/laravel.log
```

### 3. Database Management

```bash
# Create new migration
php artisan make:migration create_example_table

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

## Troubleshooting

### Common Issues

#### 1. Permission Denied Errors
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Node.js Build Errors
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

#### 3. Database Connection Issues
- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall settings
- Test connection manually

#### 4. Composer Issues
```bash
# Update Composer
composer self-update

# Clear Composer cache
composer clear-cache

# Install with verbose output
composer install -vvv
```

### Debug Mode

Enable debug mode for detailed error messages:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

**⚠️ Never enable debug mode in production!**

## Environment-Specific Setup

### Development Environment

```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

### Testing Environment

```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

### Production Environment

```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
MAIL_MAILER=resend
SESSION_DRIVER=redis
```

## Performance Optimization

### 1. Caching

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear all caches
php artisan optimize:clear
```

### 2. Asset Optimization

```bash
# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Build optimized assets
npm run build
```

### 3. Database Optimization

```bash
# Optimize database
php artisan db:optimize

# Index optimization (run in production)
php artisan db:analyze
```

## Security Checklist

### Development Security
- [ ] Use strong application key
- [ ] Keep dependencies updated
- [ ] Use HTTPS in production
- [ ] Secure database credentials
- [ ] Enable CSRF protection
- [ ] Validate all inputs

### Production Security
- [ ] Disable debug mode
- [ ] Use environment variables for secrets
- [ ] Enable rate limiting
- [ ] Configure proper file permissions
- [ ] Set up SSL certificates
- [ ] Enable security headers

## Next Steps

After successful installation:

1. **Read the Documentation**:
   - [Developer Guide](Developer-Guide) - Technical implementation details
   - [User Guide](User-Guide) - How to use the system
   - [API Reference](API-Reference) - API documentation

2. **Explore Features**:
   - User and role management
   - Content management system
   - Settings configuration
   - API endpoints

3. **Customize**:
   - Modify themes and layouts
   - Add custom permissions
   - Extend functionality
   - Configure integrations

4. **Deploy**:
   - [Deployment Guide](Deployment-Guide) - Production deployment
   - [Performance Optimization](Performance-Optimization) - Performance tips

## Getting Help

If you encounter issues:

1. **Check Documentation**: Review relevant guides and troubleshooting sections
2. **Search Issues**: Look through [GitHub Issues](https://github.com/travissutphin/thorium90/issues)
3. **Ask Questions**: Use [GitHub Discussions](https://github.com/travissutphin/thorium90/discussions)
4. **Report Bugs**: Create a new issue with detailed information

## Contributing

Want to contribute? See our [Contributing Guide](Contributing-Guide) for:
- Code contribution guidelines
- Development setup
- Testing requirements
- Documentation standards

---

**Congratulations!** 🎉 You now have Thorium90 CMS up and running. Start building amazing web applications!
