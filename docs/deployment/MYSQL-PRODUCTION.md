# MySQL Production Deployment Guide

## Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: 8.2+ with extensions: `php-mysql`, `php-mbstring`, `php-xml`, `php-curl`
- **MySQL**: 8.0+ with InnoDB engine
- **Web Server**: Nginx or Apache with SSL
- **Node.js**: 18+ (for asset compilation)

### MySQL Server Setup

#### 1. Install MySQL 8.0
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install mysql-server-8.0

# CentOS/RHEL
sudo dnf install mysql-server
sudo systemctl enable --now mysqld
```

#### 2. Secure MySQL Installation
```bash
sudo mysql_secure_installation
```

#### 3. Create Database and User
```sql
-- Connect as root
mysql -u root -p

-- Create database
CREATE DATABASE thorium90_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'thorium90_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON thorium90_production.* TO 'thorium90_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
EXIT;
```

## Application Deployment

### 1. Server Preparation
```bash
# Clone repository
git clone https://github.com/thorium90/boilerplate.git /var/www/thorium90
cd /var/www/thorium90

# Set permissions
sudo chown -R www-data:www-data /var/www/thorium90
sudo chmod -R 755 /var/www/thorium90
sudo chmod -R 777 storage bootstrap/cache
```

### 2. Dependencies Installation
```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Node.js dependencies
npm install --production
npm run build
```

### 3. Environment Configuration
```bash
# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment file
nano .env
```

**Production .env Configuration:**
```env
APP_NAME="Your Production App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thorium90_production
DB_USERNAME=thorium90_user
DB_PASSWORD=your_secure_password_here

# Cache Configuration
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

### 4. Database Migration
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Create admin user
php artisan thorium90:admin
```

### 5. Cache Optimization
```bash
# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear any existing caches
php artisan cache:clear
```

## Web Server Configuration

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/thorium90/public;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.pem;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256;

    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Asset caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/thorium90/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.pem
    SSLCertificateKeyFile /path/to/your/private.key
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    <Directory /var/www/thorium90/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Asset caching
    <LocationMatch "\.(js|css|png|jpg|jpeg|gif|ico|svg)$">
        ExpiresActive On
        ExpiresDefault "access plus 1 year"
        Header set Cache-Control "public, immutable"
    </LocationMatch>
</VirtualHost>
```

## Performance Optimization

### MySQL Configuration
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1

# Connection settings
max_connections = 200
max_connect_errors = 10

# Query cache (MySQL 5.7 only)
query_cache_type = 1
query_cache_size = 32M

# Binary logging for replication
log_bin = mysql-bin
binlog_format = ROW
expire_logs_days = 7
```

### PHP-FPM Configuration
```ini
# /etc/php/8.2/fpm/pool.d/thorium90.conf

[thorium90]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm-thorium90.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 32M
php_admin_value[post_max_size] = 32M
```

## Monitoring and Maintenance

### 1. Database Backup Script
```bash
#!/bin/bash
# /opt/scripts/thorium90-backup.sh

BACKUP_DIR="/opt/backups/thorium90"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="thorium90_production"
DB_USER="thorium90_user"
DB_PASSWORD="your_secure_password_here"

mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/thorium90_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/thorium90_$DATE.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete

echo "Backup completed: thorium90_$DATE.sql.gz"
```

### 2. Log Rotation
```ini
# /etc/logrotate.d/thorium90
/var/www/thorium90/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
```

### 3. Cron Jobs
```bash
# crontab -e (as www-data user)

# Database backup every day at 2 AM
0 2 * * * /opt/scripts/thorium90-backup.sh

# Queue processing
* * * * * cd /var/www/thorium90 && php artisan queue:work --stop-when-empty

# Clear expired sessions weekly
0 3 * * 0 cd /var/www/thorium90 && php artisan session:gc
```

## Security Considerations

### 1. Firewall Rules
```bash
# UFW configuration
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw deny 3306/tcp  # MySQL should not be accessible externally
sudo ufw enable
```

### 2. MySQL Security
```sql
-- Disable remote root access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';

-- Reload privileges
FLUSH PRIVILEGES;
```

### 3. File Permissions
```bash
# Set secure permissions
sudo chown -R www-data:www-data /var/www/thorium90
sudo chmod -R 755 /var/www/thorium90
sudo chmod -R 777 storage bootstrap/cache
sudo chmod 600 .env

# Prevent access to sensitive files
sudo chmod 600 composer.json composer.lock package.json
```

## Troubleshooting

### Common Issues

**Connection refused:**
```bash
# Check MySQL status
sudo systemctl status mysql
sudo systemctl restart mysql

# Check connection
mysql -u thorium90_user -p -h 127.0.0.1
```

**Permission errors:**
```bash
# Fix storage permissions
sudo chmod -R 777 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

**Migration errors:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Reset migrations if needed
php artisan migrate:rollback --step=5
php artisan migrate
```

### Performance Monitoring
```bash
# Check MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G"

# Monitor slow queries
tail -f /var/log/mysql/mysql-slow.log

# Check PHP-FPM status
sudo systemctl status php8.2-fpm
curl http://localhost/fpm-status
```

---

**Support**: For deployment issues, contact [Thorium90 Support](https://thorium90.com/support)