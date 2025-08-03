# Authentication Deployment Guide

## Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Database connection configured
- [ ] Cache driver configured (Redis recommended)
- [ ] Queue driver configured (if using queues)
- [ ] Mail configuration set up
- [ ] Environment variables properly set

### 2. Database Preparation
- [ ] Run migrations: `php artisan migrate`
- [ ] Verify permission tables created
- [ ] Check database indexes
- [ ] Test database connection

### 3. Application Setup
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Generate application key: `php artisan key:generate`
- [ ] Optimize application: `php artisan optimize`
- [ ] Verify storage permissions

### 4. Testing
- [ ] Run authentication tests: `php artisan test --filter=UIPermissionTest`
- [ ] Test role assignments
- [ ] Verify permission inheritance
- [ ] Check frontend integration

## Deployment Steps

### Step 1: Server Preparation
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install required software
sudo apt install nginx php8.2-fpm php8.2-mysql php8.2-redis -y

# Configure PHP
sudo nano /etc/php/8.2/fpm/php.ini
# Set: memory_limit = 512M, max_execution_time = 300
```

### Step 2: Application Deployment
```bash
# Clone repository
git clone https://github.com/your-repo/thorium90.git
cd thorium90

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Step 3: Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Configure environment variables
nano .env

# Required variables:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Step 4: Database Setup
```bash
# Run migrations
php artisan migrate --force

# Verify tables created
php artisan tinker
>>> Schema::hasTable('roles')
>>> Schema::hasTable('permissions')
>>> Schema::hasTable('role_has_permissions')
>>> Schema::hasTable('model_has_roles')
```

### Step 5: Application Optimization
```bash
# Generate application key
php artisan key:generate

# Clear and optimize caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize application
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Web Server Configuration

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/thorium90/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### PHP-FPM Configuration
```ini
; /etc/php/8.2/fpm/php.ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

### Step 7: Security Configuration
```bash
# Set proper file permissions
sudo find /var/www/thorium90 -type f -exec chmod 644 {} \;
sudo find /var/www/thorium90 -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache

# Configure firewall
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable
```

## Post-Deployment Verification

### 1. Basic Functionality
- [ ] Application loads without errors
- [ ] Login/logout functionality works
- [ ] User registration works
- [ ] Password reset works

### 2. Authentication System
- [ ] Role assignments work correctly
- [ ] Permission inheritance functions
- [ ] Frontend permission checks work
- [ ] Admin panel accessible to admins

### 3. Performance Testing
- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Cache working properly
- [ ] No memory leaks

### 4. Security Testing
- [ ] HTTPS redirects working
- [ ] Security headers present
- [ ] Rate limiting functional
- [ ] Permission checks enforced

## Monitoring and Maintenance

### 1. Log Monitoring
```bash
# Monitor application logs
tail -f /var/www/thorium90/storage/logs/laravel.log

# Monitor nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Monitor PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### 2. Performance Monitoring
```bash
# Check database performance
php artisan tinker
>>> DB::select('SHOW STATUS LIKE "Slow_queries"');

# Monitor cache performance
redis-cli info memory
```

### 3. Regular Maintenance
```bash
# Daily tasks
php artisan cache:clear

# Weekly tasks
php artisan optimize

# Monthly tasks
composer update --no-dev
npm update
```

## Troubleshooting Deployment Issues

### 1. Permission Errors
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# Fix cache permissions
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 bootstrap/cache
```

### 2. Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database configuration
php artisan config:show database
```

### 3. Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart cache services
sudo systemctl restart redis
```

### 4. Web Server Issues
```bash
# Check nginx configuration
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
```

## Backup and Recovery

### 1. Database Backup
```bash
# Create backup script
#!/bin/bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Automated backup (crontab)
0 2 * * * /path/to/backup_script.sh
```

### 2. Application Backup
```bash
# Backup application files
tar -czf thorium90_backup_$(date +%Y%m%d).tar.gz /var/www/thorium90

# Backup environment configuration
cp /var/www/thorium90/.env /backup/env_backup_$(date +%Y%m%d)
```

### 3. Recovery Procedures
```bash
# Database recovery
mysql -u username -p database_name < backup_file.sql

# Application recovery
tar -xzf thorium90_backup_file.tar.gz -C /var/www/
cp /backup/env_backup_file /var/www/thorium90/.env
```

## SSL/HTTPS Configuration

### 1. Let's Encrypt Setup
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo crontab -e
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. SSL Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

## Related Documentation
- [Authentication Overview](README.md)
- [API Documentation](api.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Testing Guide](../testing/authentication-tests.md) 