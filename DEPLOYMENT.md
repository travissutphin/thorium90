# 🚀 Thorium90 Deployment Guide

**Complete deployment instructions for Local Development, Staging, and Production environments**

---

## 📋 Quick Reference

| Environment | Database | Setup Command | Primary Use |
|------------|----------|---------------|-------------|
| **Local** | SQLite | `git clone` + `composer install` | Development |
| **Staging** | MySQL | Manual setup + `.env.staging.example` | Testing |
| **Production** | MySQL | Manual setup + `.env.production.example` | Live site |

---

## 🏠 Local Development Setup

**Perfect for:** Development, testing, quick prototyping

### Prerequisites
- PHP 8.0+ (with extensions: mbstring, xml, ctype, json, bcmath, fileinfo, tokenizer, sqlite3)
- Composer 2.0+
- Node.js 16+ with NPM
- Git

### Quick Start (Recommended)
```bash
# Clone project
git clone https://github.com/[your-repo]/thorium90.git myproject
cd myproject

# Install dependencies
composer install
npm install

# Automatic setup (creates SQLite database, runs migrations, builds assets)
php artisan thorium90:setup --interactive

# Build frontend assets (required for admin panel)
npm run build

# Start development servers
npm run dev        # Vite dev server (for live reloading during development)
php artisan serve  # Laravel server (http://localhost:8000)
```

### Manual Setup
```bash
# Clone project
git clone https://github.com/[your-repo]/thorium90.git myproject
cd myproject

# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup (SQLite - no MySQL required)
touch database/database.sqlite
php artisan migrate --seed

# Build frontend assets (required for admin panel)
npm run build

# Start development servers
npm run dev        # Vite dev server (for live reloading during development)
php artisan serve  # Laravel server (http://localhost:8000)
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

### Common Issues

**Database Connection Errors**
- Verify database credentials in `.env`
- Ensure database server is running
- Check firewall/security group settings

**Permission Errors**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Asset Not Loading**
```bash
npm run build
php artisan view:cache
```

**Route Not Found Errors**
```bash
php artisan route:clear
php artisan config:clear
php scripts/check-ziggy-routes.php  # Thorium90 specific
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