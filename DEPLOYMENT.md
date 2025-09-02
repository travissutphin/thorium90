# 🚀 Thorium90 Deployment Guide

**Complete deployment instructions for Local Development, Staging, and Production environments**

---

## 📋 Quick Reference

| Environment | Database | Setup Command | Primary Use |
|------------|----------|---------------|-------------|
| **Local** | SQLite | `composer create-project thorium90/boilerplate myproject` | Development |
| **Staging** | MySQL | Manual setup + `.env.staging.example` | Testing |
| **Production** | MySQL | Manual setup + `.env.production.example` | Live site |

---

## 🏠 Local Development Setup

**Perfect for:** Development, testing, quick prototyping

### Prerequisites
- PHP 8.0+ with extensions: mbstring, xml, ctype, json, bcmath, fileinfo, tokenizer, sqlite3, openssl, pdo
- Composer 2.0+
- Node.js 16+ with NPM
- Git (recommended)

#### 🔍 Prerequisites Verification
**Before starting, validate your environment:**

**Windows:**
```bash
# Quick check
scripts\check-prerequisites.bat

# Manual verification
php --version && composer --version && node --version
php -m | findstr /C:"mbstring" /C:"sqlite"
```

**macOS/Linux:**
```bash
# Quick check
./scripts/check-prerequisites.sh

# Manual verification
php --version && composer --version && node --version
php -m | grep -E "(mbstring|xml|sqlite)"
```

**⚠️ Common Issues:**
- **Missing PHP extensions**: Enable in `php.ini` by uncommenting `extension=` lines
- **Composer not found**: Download from [getcomposer.org](https://getcomposer.org/)
- **Node.js outdated**: Install LTS version from [nodejs.org](https://nodejs.org/)
- **Port 8000 in use**: Change `APP_URL` in `.env` to use different port (e.g., `:8080`)

### Quick Start (Recommended)

#### Step 1: Verify Prerequisites
```bash
# Windows
scripts\check-prerequisites.bat

# macOS/Linux  
./scripts/check-prerequisites.sh
```

#### Step 2: Create New Project
```bash
# Create new project
composer create-project thorium90/boilerplate myproject
cd myproject

# Verify installation
php scripts/check-prerequisites.php
```

#### Step 3: Automatic Setup
```bash
# Interactive setup (recommended for first-time users)
php artisan thorium90:setup --interactive

# Silent setup (for experienced users)
php artisan thorium90:setup --silent
```

#### Step 4: Start Development
```bash
# Start development servers
npm run dev        # Vite dev server (frontend assets)
php artisan serve  # Laravel server (http://localhost:8000)

# Alternative: All-in-one development command
composer run dev   # Starts server, queue, logs, and vite concurrently
```

#### Step 5: Verify Installation
```bash
# Test basic functionality
php artisan test tests/Unit/ExampleTest.php

# Visit in browser
# http://localhost:8000 (public site)
# http://localhost:8000/admin (admin login)
```

### Manual Setup (Alternative Approach)

#### Step 1: Get Project Files
```bash
# Clone repository
git clone https://github.com/yourrepo/thorium90.git
cd thorium90

# OR download and extract ZIP file
```

#### Step 2: Validate Prerequisites
```bash
# Check system requirements
php scripts/check-prerequisites.php
```

#### Step 3: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies  
npm install

# If npm fails, try clearing cache:
# npm cache clean --force && npm install
```

#### Step 4: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit .env if needed (change APP_URL, etc.)
```

#### Step 5: Database Setup
```bash
# Create SQLite database (cross-platform)
# Windows (automatic)
php artisan migrate --seed

# macOS/Linux (manual creation)
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate --seed
```

#### Step 6: Build Assets & Start
```bash
# Build frontend assets
npm run build

# Start development servers
npm run dev        # Terminal 1: Vite dev server
php artisan serve  # Terminal 2: Laravel server

# OR use all-in-one command
composer run dev   # Single terminal with all services
```

#### Step 7: Final Verification
```bash
# Run basic tests
php artisan test tests/Unit/ExampleTest.php

# Check application status
php artisan about

# Visit: http://localhost:8000
```

### Local Environment Details
- **Database**: SQLite (`database/database.sqlite`)
- **Debug**: Enabled
- **Caching**: File/Database (simple)
- **Mail**: Log driver (emails logged, not sent)
- **Storage**: Local filesystem
- **Sessions**: File-based

### Development Commands
```bash
# Testing
php artisan test                    # Run tests
scripts\test-regression.bat         # Run regression tests

# Cache management  
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Development with live reload
composer run dev                    # All services (server, queue, logs, vite)
```

---

## 🧪 Staging Environment Setup

**Perfect for:** Client testing, QA, pre-production validation

### Prerequisites
- PHP 8.0+
- MySQL 8.0+
- Composer
- Node.js 16+
- Web server (Apache/Nginx)

### Server Setup

#### 1. Prepare MySQL Database
```sql
CREATE DATABASE thorium90_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'staging_user'@'localhost' IDENTIFIED BY 'secure_staging_password';
GRANT ALL PRIVILEGES ON thorium90_staging.* TO 'staging_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 2. Upload & Configure Files
```bash
# Upload project files to server
# Navigate to project directory
cd /path/to/your/staging/site

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install

# Environment setup
cp .env.staging.example .env
nano .env  # Edit configuration (see below)

# Generate application key
php artisan key:generate
```

#### 3. Configure .env for Staging
Edit your `.env` file with these key settings:
```bash
APP_NAME="Your App - Staging"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.yoursite.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90_staging
DB_USERNAME=staging_user
DB_PASSWORD=secure_staging_password

# Mail (use Mailtrap or similar for testing)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password

# Security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
```

#### 4. Deploy Application
```bash
# Database migration
php artisan migrate --seed

# Build frontend assets
npm run build

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Staging Environment Details
- **Database**: MySQL (production-like)
- **Debug**: Disabled
- **Caching**: Database/Redis
- **Mail**: Testing service (Mailtrap)
- **Storage**: Local/S3 (configurable)
- **Sessions**: Database, encrypted

---

## 🏭 Production Environment Setup

**Perfect for:** Live websites, production applications

### Prerequisites
- PHP 8.0+ with required extensions
- MySQL 8.0+ or MariaDB
- Redis (recommended)
- Web server (Apache/Nginx) with SSL
- Process manager (Supervisor for queues)

### Server Setup

#### 1. Prepare Production Database
```sql
CREATE DATABASE thorium90_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'prod_user'@'localhost' IDENTIFIED BY 'very_secure_production_password';
GRANT ALL PRIVILEGES ON thorium90_production.* TO 'prod_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 2. Upload & Configure Files
```bash
# Upload to production server
cd /var/www/yoursite.com

# Install production dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Environment setup
cp .env.production.example .env
nano .env  # Configure production settings
php artisan key:generate
```

#### 3. Configure .env for Production
```bash
APP_NAME="Your Production App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=thorium90_production
DB_USERNAME=prod_user
DB_PASSWORD=very_secure_production_password

# Performance
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database

# Security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Mail (production SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yoursite.com
MAIL_PASSWORD=secure_email_password
MAIL_FROM_ADDRESS=noreply@yoursite.com

# File Storage (S3 recommended)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_BUCKET=your-production-bucket
```

#### 4. Deploy & Optimize
```bash
# Database setup
php artisan migrate

# Build optimized assets
npm run build

# Cache everything for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set secure permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
find storage -type f -exec chmod 644 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
```

### Production Environment Details
- **Database**: MySQL with optimized configuration
- **Debug**: Disabled (security)
- **Caching**: Redis (performance)
- **Mail**: Production SMTP service
- **Storage**: S3 (scalability)
- **Sessions**: Database, fully encrypted
- **Queues**: Redis with Supervisor
- **Logging**: Structured logging to files

---

## ☁️ Cloudways Deployment

**Cloudways-specific deployment instructions**

### 1. Server Creation
1. Login to Cloudways dashboard
2. Create new server: PHP 8.0+, MySQL 8.0+
3. Create new application: "Custom App via Git"
4. Configure Git repository details

### 2. Post-Deployment Setup
```bash
# SSH into your Cloudways server
ssh master@yourserver.cloudwaysapps.com

# Navigate to application directory
cd /home/master/applications/yourapp/public_html

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Environment setup
cp .env.production.example .env
# Edit .env with Cloudways database credentials (check Application Access Details)

# Generate key and setup database
php artisan key:generate
php artisan migrate --seed

# Build assets
npm run build

# Cache optimization
php artisan config:cache
php artisan route:cache
```

### 3. Cloudways Environment Configuration
Use your Cloudways database credentials:
```bash
# Get these from Cloudways Application > Access Details
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_cloudways_db_name
DB_USERNAME=your_cloudways_db_user
DB_PASSWORD=your_cloudways_db_password

# Cloudways Redis (if available)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379

# Use Cloudways domain or custom domain
APP_URL=https://your-app.cloudwaysapps.com
```

### 4. Cloudways-Specific Optimizations
- Enable Redis from Cloudways dashboard
- Configure SSL certificate
- Set up automated backups
- Configure monitoring and alerts

---

## 🔧 Generic Server Deployment

**For any VPS, dedicated server, or shared hosting**

### Apache Configuration
Create `.htaccess` in your public directory:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yoursite.com;
    root /var/www/yoursite.com/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔒 Security Checklist

### Before Going Live
- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials are secure
- [ ] SSL certificate installed and working
- [ ] File permissions properly set (755/644)
- [ ] Remove unnecessary files (.env.example, README.md, etc.)
- [ ] Configure proper error pages
- [ ] Set up monitoring and logging
- [ ] Enable automated backups
- [ ] Test all functionality thoroughly

### Environment Variables Security
- **Never commit** `.env` files to version control
- **Use strong passwords** for all database and service credentials  
- **Rotate credentials regularly** especially for production
- **Use environment-specific secrets** (different passwords per environment)
- **Store sensitive data securely** (use services like AWS Secrets Manager)

---

## 🔄 Maintenance & Updates

### Regular Update Process
```bash
# 1. Backup database and files
php artisan backup:run  # If backup package installed

# 2. Put application in maintenance mode  
php artisan down

# 3. Pull latest code
git pull origin main

# 4. Update dependencies
composer install --no-dev --optimize-autoloader

# 5. Run migrations
php artisan migrate

# 6. Clear and rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Rebuild frontend assets
npm run build

# 8. Bring application back online
php artisan up
```

### Performance Optimization Commands
```bash
# Cache optimization
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan event:cache

# Autoloader optimization
composer dump-autoload --optimize

# Database optimization
php artisan model:cache
```

---

## 🆘 Troubleshooting

### Local Development Issues

#### Prerequisites & Installation

**❌ PHP Extensions Missing**
```bash
# Check what's missing
php scripts/check-prerequisites.php

# Windows (XAMPP/WAMP)
# Enable in C:\xampp\php\php.ini:
# extension=mbstring
# extension=sqlite3
# extension=pdo_sqlite

# macOS (Homebrew)
brew install php
# Edit /opt/homebrew/etc/php/8.x/php.ini

# Ubuntu/Debian
sudo apt install php8.2-mbstring php8.2-sqlite3 php8.2-xml php8.2-curl

# CentOS/RHEL
sudo dnf install php-mbstring php-sqlite3 php-xml
```

**❌ Composer Install Fails**
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Install with more memory
php -d memory_limit=512M /usr/local/bin/composer install

# Skip platform requirements (last resort)
composer install --ignore-platform-reqs
```

**❌ NPM Install Fails**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and retry
rm -rf node_modules package-lock.json
npm install

# Use different registry
npm install --registry https://registry.npmjs.org/

# Check Node.js version
node --version  # Should be 16+
```

#### Database Issues

**❌ SQLite Database Errors**
```bash
# Windows: Usually auto-created, but check permissions
# macOS/Linux: Manual creation required
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# If migrations fail
php artisan migrate:fresh --seed

# Check SQLite is working
php artisan tinker
>>> DB::connection()->getPdo();
```

**❌ Migration Errors**
```bash
# Clear and retry
php artisan migrate:fresh --seed

# Check database connection
php artisan config:show database.default
php artisan config:show database.connections.sqlite

# Manual SQLite fix
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```

#### Server & Asset Issues

**❌ Port 8000 Already in Use**
```bash
# Find what's using port 8000
# Windows
netstat -ano | findstr :8000

# macOS/Linux  
lsof -i :8000

# Use different port
php artisan serve --port=8080
# Update APP_URL in .env: http://localhost:8080
```

**❌ Vite/Asset Building Fails**
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild from scratch
npm run build

# Check for TypeScript errors
npm run types

# Development mode issues
npm run dev
# If fails, check package.json scripts
```

**❌ Permission Errors (macOS/Linux)**
```bash
# Fix storage permissions
chmod -R 755 storage bootstrap/cache
sudo chown -R $USER:$USER storage bootstrap/cache

# Fix database permissions
chmod 664 database/database.sqlite
chmod 755 database

# SELinux issues (CentOS/RHEL)
sudo setsebool -P httpd_can_network_connect 1
```

#### Application Errors

**❌ Key Not Generated**
```bash
# Generate application key
php artisan key:generate

# Force generation
php artisan key:generate --force

# Verify key exists
php artisan config:show app.key
```

**❌ Cache Issues**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# If still issues, clear compiled files
php artisan clear-compiled
composer dump-autoload
```

**❌ Route Errors**
```bash
# Thorium90-specific route validation
php scripts/check-ziggy-routes.php

# Clear route cache
php artisan route:clear

# List all routes
php artisan route:list | grep -i admin
```

### Production/Staging Issues

**Database Connection Errors**
- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall/security group settings

**Asset Not Loading**
```bash
npm run build
php artisan view:cache
```

**General Server Errors**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Enable debug temporarily (staging only)
APP_DEBUG=true in .env

# Check server status
php artisan about
```

### Getting Help

**Thorium90 Specific:**
- Check documentation: `/docs/` directory
- Run regression tests: `scripts\test-regression.bat`
- Validate setup: `php artisan thorium90:setup --interactive`
- Test database: `php artisan thorium90:validate-database`

**Laravel General:**
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Community](https://laravel.io)

## 🔄 Installation Rollback & Recovery

### Automated Rollback (Recommended)

**If installation fails or becomes corrupted:**

```bash
# Safe, guided rollback
php scripts/rollback-installation.php

# Follow the prompts, then restart installation:
php scripts/check-prerequisites.php
cp .env.example .env
php artisan key:generate
composer install
npm install
php artisan thorium90:setup --interactive
```

### Manual Emergency Reset (Advanced)

**Complete nuclear option for severely broken installations:**

```bash
# ⚠️  WARNING: This removes EVERYTHING including uncommitted changes
git clean -fd
git reset --hard HEAD

# Rebuild from scratch
composer install
npm install
cp .env.example .env
php artisan key:generate

# Recreate database
rm -f database/database.sqlite
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Complete setup
php artisan migrate --seed
npm run build
php artisan serve
```

### Partial Recovery Options

**Fix Specific Issues Without Full Reset:**

```bash
# Reset only database
php artisan migrate:fresh --seed

# Reset only caches
php artisan cache:clear && php artisan config:clear && php artisan route:clear

# Reset only Node.js dependencies
rm -rf node_modules package-lock.json
npm cache clean --force
npm install

# Reset only Composer dependencies
rm -rf vendor composer.lock
composer clear-cache
composer install

# Reset only environment
cp .env.example .env
php artisan key:generate
```

### Backup & Recovery Best Practices

**Before making major changes:**

```bash
# Backup current working state
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
cp database/database.sqlite database/database.backup.$(date +%Y%m%d_%H%M%S)

# Create git stash
git add -A
git stash push -m "Backup before changes"

# Later restore if needed
git stash pop
```

### Getting Help
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode temporarily: `APP_DEBUG=true`
- Run health check: `php artisan about`
- Test database: `php artisan tinker` then `DB::connection()->getPdo()`

---

## 📞 Support

For Thorium90-specific issues:
- Check documentation in `/docs/` directory
- Run regression tests: `scripts\test-regression.bat`
- Validate setup: `php artisan thorium90:setup --interactive`

For Laravel issues:
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Community](https://laravel.io)

---

*Last updated: [DATE] | Version: 2.0.1*