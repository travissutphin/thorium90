# Database Schema

## Overview

This document provides a complete reference of the database schema for the Thorium90 application, including all tables, relationships, and indexes.

## Entity Relationship Diagram

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     users       │     │      roles      │     │   permissions   │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ id (PK)         │────►│ id (PK)         │
│ name            │     │ name            │     │ name            │
│ email           │     │ guard_name      │     │ guard_name      │
│ password        │     │ created_at      │     │ created_at      │
│ provider        │     │ updated_at      │     │ updated_at      │
│ provider_id     │     └─────────────────┘     └─────────────────┘
│ avatar          │              │                        │
│ email_verified  │              │                        │
│ two_factor_*    │              ▼                        ▼
│ deleted_at      │     ┌─────────────────┐     ┌─────────────────┐
│ created_at      │     │ model_has_roles │     │ role_has_perms  │
│ updated_at      │     ├─────────────────┤     ├─────────────────┤
└─────────────────┘     │ role_id (FK)    │     │ permission_id   │
         │              │ model_type      │     │ role_id         │
         │              │ model_id (FK)   │     └─────────────────┘
         │              └─────────────────┘
         │                       │
         │              ┌─────────────────┐
         │              │ model_has_perms │
         │              ├─────────────────┤
         │              │ permission_id   │
         │              │ model_type      │
         │              │ model_id        │
         │              └─────────────────┘
         │
         ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│     pages       │     │    settings     │     │ personal_tokens │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)         │     │ id (PK)         │
│ title           │     │ key             │     │ tokenable_type  │
│ slug            │     │ value           │     │ tokenable_id    │
│ content         │     │ type            │     │ name            │
│ author_id (FK)  │     │ category        │     │ token           │
│ status          │     │ description     │     │ abilities       │
│ meta_*          │     │ is_public       │     │ last_used_at    │
│ og_*            │     │ created_at      │     │ expires_at      │
│ twitter_*       │     │ updated_at      │     │ created_at      │
│ schema_*        │     └─────────────────┘     │ updated_at      │
│ deleted_at      │                             └─────────────────┘
│ created_at      │
│ updated_at      │
└─────────────────┘
```

## Tables

### 1. Users Table

Stores user account information with support for social login and two-factor authentication.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NULL,
    
    -- Social Login Fields
    provider VARCHAR(50) NULL,
    provider_id VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    
    -- Two-Factor Authentication
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    
    -- Soft Deletes
    deleted_at TIMESTAMP NULL,
    
    -- Timestamps
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_provider (provider, provider_id),
    INDEX idx_deleted (deleted_at)
);
```

### 2. Pages Table

Content management system for pages with SEO optimization.

```sql
CREATE TABLE pages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    featured_image VARCHAR(255),
    author_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    
    -- SEO Fields
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    canonical_url VARCHAR(255),
    robots_meta VARCHAR(100) DEFAULT 'index,follow',
    
    -- Open Graph Fields
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(255),
    og_type VARCHAR(50) DEFAULT 'article',
    
    -- Twitter Card Fields
    twitter_card VARCHAR(50) DEFAULT 'summary_large_image',
    twitter_title VARCHAR(255),
    twitter_description TEXT,
    twitter_image VARCHAR(255),
    
    -- Schema Markup
    schema_type VARCHAR(50) DEFAULT 'Article',
    schema_data JSON,
    
    -- Additional Fields
    template VARCHAR(100) DEFAULT 'default',
    parent_id BIGINT UNSIGNED NULL,
    order_column INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    view_count BIGINT DEFAULT 0,
    
    -- Soft Deletes
    deleted_at TIMESTAMP NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at),
    INDEX idx_author (author_id),
    INDEX idx_parent (parent_id),
    INDEX idx_deleted (deleted_at),
    
    -- Foreign Keys
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
);
```

### 3. Roles Table

Defines system roles for access control.

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(125) NOT NULL,
    guard_name VARCHAR(125) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
);
```

**Default Roles:**
- Super Admin
- Admin
- Editor
- Author
- Subscriber

### 4. Permissions Table

Defines granular permissions for the system.

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(125) NOT NULL,
    guard_name VARCHAR(125) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
);
```

**Permission Categories:**
- User Management: view, create, edit, delete, restore, force delete users
- Role Management: manage user roles
- Settings: manage settings, view system stats
- Content: view, create, edit, delete, publish pages, manage SEO, manage schema
- Media: upload media

### 5. Model Has Roles Table

Junction table for assigning roles to users.

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    -- Indexes
    PRIMARY KEY (role_id, model_id, model_type),
    INDEX model_has_roles_model_id_model_type_index (model_id, model_type),
    
    -- Foreign Keys
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### 6. Model Has Permissions Table

Junction table for direct permission assignments.

```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    -- Indexes
    PRIMARY KEY (permission_id, model_id, model_type),
    INDEX model_has_permissions_model_id_model_type_index (model_id, model_type),
    
    -- Foreign Keys
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
```

### 7. Role Has Permissions Table

Junction table for assigning permissions to roles.

```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    
    -- Indexes
    PRIMARY KEY (permission_id, role_id),
    
    -- Foreign Keys
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### 8. Settings Table

Stores application configuration settings.

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    type ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    category VARCHAR(100) DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_key (key),
    INDEX idx_category (category),
    INDEX idx_public (is_public)
);
```

**Setting Categories:**
- general: Site name, description, timezone
- email: SMTP configuration
- security: Password policies, 2FA settings
- seo: Default meta tags, analytics
- social: Social media links and API keys
- media: Upload limits, storage paths

### 9. Personal Access Tokens Table

API authentication tokens for Sanctum.

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
);
```

### 10. Password Reset Tokens Table

Temporary tokens for password reset functionality.

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX password_reset_tokens_email_index (email)
);
```

### 11. Sessions Table

Active user sessions (if using database driver).

```sql
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    -- Indexes
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
);
```

### 12. Cache Table

Database cache storage (if using database driver).

```sql
CREATE TABLE cache (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    
    -- Indexes
    INDEX cache_expiration_index (expiration)
);

CREATE TABLE cache_locks (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    
    -- Indexes
    INDEX cache_locks_expiration_index (expiration)
);
```

### 13. Jobs Table

Queue jobs storage (if using database driver).

```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    
    -- Indexes
    INDEX jobs_queue_index (queue)
);

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT,
    options MEDIUMTEXT,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Relationships

### User Relationships
```php
// User Model
public function roles() { return $this->belongsToMany(Role::class); }
public function permissions() { return $this->belongsToMany(Permission::class); }
public function pages() { return $this->hasMany(Page::class, 'author_id'); }
public function tokens() { return $this->morphMany(PersonalAccessToken::class, 'tokenable'); }
```

### Page Relationships
```php
// Page Model
public function author() { return $this->belongsTo(User::class, 'author_id'); }
public function parent() { return $this->belongsTo(Page::class, 'parent_id'); }
public function children() { return $this->hasMany(Page::class, 'parent_id'); }
```

### Role & Permission Relationships
```php
// Role Model
public function permissions() { return $this->belongsToMany(Permission::class); }
public function users() { return $this->belongsToMany(User::class); }

// Permission Model
public function roles() { return $this->belongsToMany(Role::class); }
public function users() { return $this->belongsToMany(User::class); }
```

## Indexes Strategy

### Primary Indexes
- All tables use auto-incrementing BIGINT primary keys
- UUIDs used for tokens and unique identifiers

### Performance Indexes
- Email lookups: `idx_email` on users table
- Slug lookups: `idx_slug` on pages table
- Status filtering: `idx_status` on pages table
- Soft delete queries: `idx_deleted` on relevant tables
- Category filtering: `idx_category` on settings table

### Composite Indexes
- Social login: `(provider, provider_id)` on users
- Role assignments: `(role_id, model_id, model_type)`
- Permission assignments: `(permission_id, model_id, model_type)`

## Migration Order

Migrations must be run in this specific order due to foreign key dependencies:

1. `create_users_table`
2. `create_cache_table`
3. `create_jobs_table`
4. `create_permission_tables` (roles, permissions, junction tables)
5. `create_personal_access_tokens_table`
6. `add_social_login_fields_to_users_table`
7. `make_password_nullable_in_users_table`
8. `add_two_factor_columns_to_users_table`
9. `add_soft_deletes_to_users_table`
10. `create_settings_table`
11. `update_posts_to_pages_permissions`
12. `create_pages_table`

## Database Optimization Tips

### Query Optimization
1. Use eager loading to prevent N+1 queries
2. Add indexes for frequently queried columns
3. Use database transactions for bulk operations
4. Implement query caching for static data

### Storage Optimization
1. Use appropriate column types (VARCHAR vs TEXT)
2. Implement archiving for old data
3. Regular cleanup of soft-deleted records
4. Optimize JSON columns with virtual columns

### Performance Monitoring
1. Enable query logging in development
2. Use Laravel Debugbar for query analysis
3. Monitor slow query logs
4. Regular EXPLAIN analysis on complex queries

## Backup Strategy

### Backup Schedule
- **Daily**: Full database backup
- **Hourly**: Incremental backups
- **Weekly**: Off-site backup storage
- **Monthly**: Archive old backups

### Critical Tables
Priority backup for:
1. users
2. roles & permissions
3. pages
4. settings

### Recovery Procedures
1. Test restore procedures monthly
2. Document recovery time objectives (RTO)
3. Maintain backup verification logs
4. Store backups in multiple locations

## Security Considerations

### Data Protection
1. Encrypt sensitive columns (two_factor_secret)
2. Hash all passwords with bcrypt
3. Sanitize all user inputs
4. Use prepared statements for all queries

### Access Control
1. Database user with minimal privileges
2. Separate read/write connections
3. SSL/TLS for database connections
4. Regular security audits

### Compliance
1. GDPR compliance for user data
2. Soft deletes for audit trails
3. Data retention policies
4. User data export capabilities

## Maintenance Tasks

### Regular Tasks
```sql
-- Optimize tables (monthly)
OPTIMIZE TABLE users, pages, settings;

-- Analyze tables (weekly)
ANALYZE TABLE users, pages, model_has_roles;

-- Clean old sessions (daily)
DELETE FROM sessions WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY);

-- Clean expired tokens (daily)
DELETE FROM personal_access_tokens WHERE expires_at < NOW();

-- Archive soft-deleted records (monthly)
INSERT INTO archived_users SELECT * FROM users WHERE deleted_at < NOW() - INTERVAL 90 DAY;
DELETE FROM users WHERE deleted_at < NOW() - INTERVAL 90 DAY;
```

### Health Checks
```sql
-- Check table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'thorium90'
ORDER BY (data_length + index_length) DESC;

-- Check index usage
SELECT 
    table_name,
    index_name,
    cardinality
FROM information_schema.statistics
WHERE table_schema = 'thorium90'
ORDER BY cardinality DESC;
```

## Future Considerations

### Planned Schema Changes
1. Add versioning to pages table
2. Implement tagging system
3. Add media library table
4. Create audit log table
5. Add multi-language support

### Scalability Planning
1. Implement database sharding for users
2. Separate read/write databases
3. Add caching layer (Redis)
4. Consider NoSQL for certain data types
5. Implement event sourcing for audit trails
