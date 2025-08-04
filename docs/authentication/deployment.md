# Laravel Sanctum Deployment Guide

## Overview

This guide covers the deployment considerations and configuration steps for the Laravel Sanctum API authentication system integrated with the Multi-Role User Authentication system.

## Pre-Deployment Checklist

### 1. Environment Configuration

#### Required Environment Variables
```env
# Application
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com
SANCTUM_TOKEN_PREFIX=

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Optional Environment Variables
```env
# Token Expiration (in minutes, null = no expiration)
SANCTUM_EXPIRATION=null

# Token Prefix for Security Scanning
SANCTUM_TOKEN_PREFIX=myapp_
```

### 2. Database Migration

Ensure all migrations are run in production:

```bash
php artisan migrate --force
```

Required tables:
- `personal_access_tokens` (Sanctum tokens)
- `users` (User accounts)
- `roles` (User roles)
- `permissions` (System permissions)
- `role_has_permissions` (Role-permission relationships)
- `model_has_roles` (User-role assignments)

### 3. Configuration Files

#### Sanctum Configuration (`config/sanctum.php`)
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1')),
'expiration' => env('SANCTUM_EXPIRATION'),
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

#### Authentication Guards (`config/auth.php`)
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'sanctum' => [
        'driver' => 'sanctum',
        'provider' => null,
    ],
],
```

## Production Deployment Steps

### 1. Server Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL 8.0+ or PostgreSQL 13+
- Redis (recommended for sessions and caching)
- SSL Certificate (required for production)

### 2. Web Server Configuration

#### Nginx Configuration
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    root /var/www/html/public;
    index index.php;
    
    # Handle API routes
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
        
        # CORS headers for API
        add_header 'Access-Control-Allow-Origin' 'https://yourdomain.com' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN' always;
        add_header 'Access-Control-Allow-Credentials' 'true' always;
        
        if ($request_method = 'OPTIONS') {
            return 204;
        }
    }
    
    # Handle Sanctum CSRF cookie
    location /sanctum/csrf-cookie {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Apache Configuration (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle CORS preflight requests
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
    
    # Redirect to index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# CORS Headers
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "https://yourdomain.com"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Authorization, Content-Type, X-Requested-With, X-CSRF-TOKEN"
    Header always set Access-Control-Allow-Credentials "true"
</IfModule>
```

### 3. SSL/TLS Configuration

#### Requirements
- Valid SSL certificate
- HTTPS enforced for all API endpoints
- Secure cookie settings

#### Laravel Configuration
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',

// config/sanctum.php
'middleware' => [
    'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

### 4. Performance Optimization

#### Caching Configuration
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

#### Database Optimization
```sql
-- Index for personal_access_tokens table
CREATE INDEX idx_personal_access_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);
CREATE INDEX idx_personal_access_tokens_token ON personal_access_tokens(token);

-- Index for performance
CREATE INDEX idx_model_has_roles_model ON model_has_roles(model_type, model_id);
CREATE INDEX idx_role_has_permissions_role ON role_has_permissions(role_id);
```

## Security Considerations

### 1. Token Security

#### Token Expiration
```php
// Set token expiration in production
'expiration' => 60 * 24, // 24 hours
```

#### Token Abilities
```php
// Use specific abilities instead of wildcard
$token = $user->createToken('API Token', ['read', 'write']);
```

### 2. Rate Limiting

#### API Rate Limiting
```php
// routes/api.php
Route::middleware(['throttle:api'])->group(function () {
    // API routes
});

// config/cache.php - Configure rate limiting store
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
],
```

#### Custom Rate Limiting
```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('api-tokens', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
});
```

### 3. CORS Configuration

#### Production CORS Settings
```php
// config/cors.php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://yourdomain.com',
        'https://api.yourdomain.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

## Monitoring and Logging

### 1. API Monitoring

#### Health Check Endpoint
```php
// Monitor API health
GET /api/health

// Expected response
{
    "status": "ok",
    "timestamp": "2025-08-03T20:29:26.282714Z",
    "version": "1.0.0"
}
```

#### Custom Health Checks
```php
// Add database connectivity check
Route::get('/api/health/detailed', function () {
    return response()->json([
        'status' => 'ok',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::store()->getStore() ? 'connected' : 'disconnected',
        'timestamp' => now()->toISOString(),
    ]);
});
```

### 2. Logging Configuration

#### API Request Logging
```php
// config/logging.php
'channels' => [
    'api' => [
        'driver' => 'daily',
        'path' => storage_path('logs/api.log'),
        'level' => 'info',
        'days' => 14,
    ],
],
```

#### Security Event Logging
```php
// Log authentication events
Event::listen('Illuminate\Auth\Events\Login', function ($event) {
    Log::channel('api')->info('User logged in', [
        'user_id' => $event->user->id,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
});

// Log token creation
Event::listen('Laravel\Sanctum\Events\TokenAuthenticated', function ($event) {
    Log::channel('api')->info('API token used', [
        'token_id' => $event->token->id,
        'user_id' => $event->token->tokenable_id,
        'ip' => request()->ip(),
    ]);
});
```

## Backup and Recovery

### 1. Database Backup

#### Token Table Backup
```bash
# Backup personal access tokens
mysqldump -u username -p database_name personal_access_tokens > tokens_backup.sql

# Full database backup
mysqldump -u username -p database_name > full_backup.sql
```

### 2. Configuration Backup

#### Environment Files
```bash
# Backup environment configuration
cp .env .env.backup.$(date +%Y%m%d)

# Backup configuration cache
cp bootstrap/cache/config.php config_backup.php
```

## Troubleshooting

### Common Issues

#### 1. CORS Errors
```bash
# Check CORS configuration
php artisan config:show cors

# Clear configuration cache
php artisan config:clear
```

#### 2. Token Authentication Issues
```bash
# Check Sanctum configuration
php artisan config:show sanctum

# Verify database tables
php artisan migrate:status
```

#### 3. Session Issues
```bash
# Clear sessions
php artisan session:table
php artisan migrate

# Check session configuration
php artisan config:show session
```

### Debug Commands

```bash
# Check routes
php artisan route:list --path=api

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check permissions
php artisan permission:show

# Verify user roles
php artisan tinker
>>> User::with('roles', 'permissions')->find(1)
```

## Performance Benchmarks

### Expected Performance Metrics

- API Health Check: < 50ms
- Token Authentication: < 100ms
- Role/Permission Check: < 150ms
- Token Creation: < 200ms

### Optimization Tips

1. **Database Indexing**: Ensure proper indexes on frequently queried columns
2. **Caching**: Use Redis for session and permission caching
3. **Connection Pooling**: Configure database connection pooling
4. **CDN**: Use CDN for static assets
5. **Load Balancing**: Implement load balancing for high traffic

## Related Documentation

- [Authentication Overview](README.md)
- [API Documentation](api.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Testing Guide](../testing/authentication-tests.md)
