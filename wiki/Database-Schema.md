# Complete Database Schema

This document provides the complete database schema for the Thorium90 Multi-Role User Authentication system, including all tables created by Laravel Core, Fortify, Sanctum, Socialite, and Spatie Laravel Permission.

## Table of Contents

1. [Core Authentication Tables](#core-authentication-tables)
2. [Fortify Tables](#fortify-tables)
3. [Sanctum Tables](#sanctum-tables)
4. [Socialite Fields](#socialite-fields)
5. [Spatie Permission Tables](#spatie-permission-tables)
6. [Laravel Framework Tables](#laravel-framework-tables)
7. [Relationships](#relationships)
8. [Indexes](#indexes)
9. [Migration Order](#migration-order)

## Core Authentication Tables

### Users Table
The main users table with authentication fields and extensions for Fortify, Sanctum, and Socialite.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NULL, -- Nullable for social-only users
    remember_token VARCHAR(100) NULL,
    
    -- Socialite fields
    provider VARCHAR(255) NULL,
    provider_id VARCHAR(255) NULL,
    avatar VARCHAR(255) NULL,
    
    -- Two-factor authentication fields (Fortify)
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    two_factor_confirmed_at TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE KEY unique_provider_id (provider, provider_id),
    INDEX idx_email (email),
    INDEX idx_provider (provider),
    INDEX idx_two_factor_confirmed (two_factor_confirmed_at)
);
```

## Fortify Tables

### Password Reset Tokens
Stores password reset tokens for secure password recovery.

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    
    INDEX idx_token (token),
    INDEX idx_created_at (created_at)
);
```

### Sessions Table
Stores user sessions when using database session driver.

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Sanctum Tables

### Personal Access Tokens
Stores API tokens for Sanctum authentication.

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_token (token),
    INDEX idx_last_used_at (last_used_at),
    INDEX idx_expires_at (expires_at)
);
```

## Socialite Fields

The Socialite integration adds fields directly to the users table rather than creating separate tables:

```sql
-- Added to users table
ALTER TABLE users ADD COLUMN provider VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN provider_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users ADD UNIQUE KEY unique_provider_id (provider, provider_id);
```

### Supported Providers
- Google (`google`)
- GitHub (`github`)
- Facebook (`facebook`)
- LinkedIn (`linkedin`)
- Twitter/X (`twitter`)
- GitLab (`gitlab`)

## Spatie Permission Tables

### Roles Table
Defines available roles in the system.

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_role_guard (name, guard_name),
    INDEX idx_guard_name (guard_name)
);
```

### Permissions Table
Defines available permissions in the system.

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_permission_guard (name, guard_name),
    INDEX idx_guard_name (guard_name)
);
```

### Model Has Roles (User-Role Assignments)
Links users to their assigned roles.

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (role_id, model_id, model_type),
    INDEX idx_model_id (model_id),
    INDEX idx_model_type (model_type),
    
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

### Model Has Permissions (Direct User Permissions)
Links users to directly assigned permissions (bypassing roles).

```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (permission_id, model_id, model_type),
    INDEX idx_model_id (model_id),
    INDEX idx_model_type (model_type),
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
```

### Role Has Permissions (Role-Permission Assignments)
Links roles to their assigned permissions.

```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (permission_id, role_id),
    INDEX idx_role_id (role_id),
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

## Laravel Framework Tables

### Cache Table
Stores cache data when using database cache driver.

```sql
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    
    INDEX idx_expiration (expiration)
);
```

### Cache Locks Table
Stores cache locks for atomic operations.

```sql
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    
    INDEX idx_expiration (expiration)
);
```

### Jobs Table
Stores queued jobs when using database queue driver.

```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    
    INDEX idx_queue (queue),
    INDEX idx_reserved_at (reserved_at),
    INDEX idx_available_at (available_at)
);
```

### Job Batches Table
Stores job batch information for batch processing.

```sql
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL,
    
    INDEX idx_created_at (created_at),
    INDEX idx_finished_at (finished_at)
);
```

### Failed Jobs Table
Stores information about failed jobs.

```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_uuid (uuid),
    INDEX idx_failed_at (failed_at)
);
```

## Relationships

### User Model Relationships

```php
// User.php
class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, HasApiTokens, TwoFactorAuthenticatable;
    
    // Spatie Permission relationships
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles');
    }
    
    public function permissions()
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
    }
    
    // Sanctum relationship
    public function tokens()
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }
    
    // Session relationship
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}
```

### Role Model Relationships

```php
// Role.php (Spatie Permission)
class Role extends Model
{
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }
    
    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }
}
```

### Permission Model Relationships

```php
// Permission.php (Spatie Permission)
class Permission extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }
    
    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_permissions');
    }
}
```

## Indexes

### Performance Optimization Indexes

```sql
-- Users table indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_provider ON users(provider);
CREATE INDEX idx_users_two_factor_confirmed ON users(two_factor_confirmed_at);
CREATE UNIQUE INDEX idx_users_provider_id ON users(provider, provider_id);

-- Personal access tokens indexes
CREATE INDEX idx_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);
CREATE INDEX idx_tokens_last_used ON personal_access_tokens(last_used_at);
CREATE INDEX idx_tokens_expires ON personal_access_tokens(expires_at);

-- Permission system indexes
CREATE INDEX idx_model_has_roles_model_id ON model_has_roles(model_id);
CREATE INDEX idx_model_has_permissions_model_id ON model_has_permissions(model_id);
CREATE INDEX idx_role_has_permissions_role_id ON role_has_permissions(role_id);

-- Session indexes
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);

-- Password reset indexes
CREATE INDEX idx_password_resets_token ON password_reset_tokens(token);
CREATE INDEX idx_password_resets_created ON password_reset_tokens(created_at);
```

## Migration Order

The migrations should be run in this order to ensure proper foreign key relationships:

```bash
# 1. Laravel core tables
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php

# 2. Spatie Permission tables
2025_08_03_144936_create_permission_tables.php

# 3. Sanctum tables
2025_08_03_200836_create_personal_access_tokens_table.php

# 4. Socialite fields
2025_08_04_012041_add_social_login_fields_to_users_table.php
2025_08_04_012349_make_password_nullable_in_users_table.php

# 5. Fortify two-factor fields
2025_08_06_174746_add_two_factor_columns_to_users_table.php
```

## Sample Data

### Default Roles

```sql
INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('Super Admin', 'web', NOW(), NOW()),
('Admin', 'web', NOW(), NOW()),
('Editor', 'web', NOW(), NOW()),
('Author', 'web', NOW(), NOW()),
('Subscriber', 'web', NOW(), NOW());
```

### Default Permissions

```sql
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
-- Dashboard
('view dashboard', 'web', NOW(), NOW()),

-- User Management
('view users', 'web', NOW(), NOW()),
('create users', 'web', NOW(), NOW()),
('edit users', 'web', NOW(), NOW()),
('delete users', 'web', NOW(), NOW()),
('manage user roles', 'web', NOW(), NOW()),

-- Content Management
('view posts', 'web', NOW(), NOW()),
('create posts', 'web', NOW(), NOW()),
('edit posts', 'web', NOW(), NOW()),
('delete posts', 'web', NOW(), NOW()),
('publish posts', 'web', NOW(), NOW()),
('edit own posts', 'web', NOW(), NOW()),
('delete own posts', 'web', NOW(), NOW()),

-- System Administration
('manage settings', 'web', NOW(), NOW()),
('manage roles', 'web', NOW(), NOW()),
('manage permissions', 'web', NOW(), NOW()),

-- Media Management
('upload media', 'web', NOW(), NOW()),
('manage media', 'web', NOW(), NOW()),
('delete media', 'web', NOW(), NOW()),

-- Comment Management
('view comments', 'web', NOW(), NOW()),
('moderate comments', 'web', NOW(), NOW()),
('delete comments', 'web', NOW(), NOW());
```

## Database Constraints

### Foreign Key Constraints

```sql
-- Personal access tokens
ALTER TABLE personal_access_tokens 
ADD CONSTRAINT fk_tokens_tokenable 
FOREIGN KEY (tokenable_id) REFERENCES users(id) ON DELETE CASCADE;

-- Sessions
ALTER TABLE sessions 
ADD CONSTRAINT fk_sessions_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Model has roles
ALTER TABLE model_has_roles 
ADD CONSTRAINT fk_model_roles_role 
FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE;

-- Model has permissions
ALTER TABLE model_has_permissions 
ADD CONSTRAINT fk_model_permissions_permission 
FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE;

-- Role has permissions
ALTER TABLE role_has_permissions 
ADD CONSTRAINT fk_role_permissions_role 
FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_role_permissions_permission 
FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE;
```

### Check Constraints

```sql
-- Ensure provider and provider_id are both set or both null
ALTER TABLE users 
ADD CONSTRAINT chk_provider_consistency 
CHECK (
    (provider IS NULL AND provider_id IS NULL) OR 
    (provider IS NOT NULL AND provider_id IS NOT NULL)
);

-- Ensure two-factor fields consistency
ALTER TABLE users 
ADD CONSTRAINT chk_two_factor_consistency 
CHECK (
    (two_factor_secret IS NULL AND two_factor_recovery_codes IS NULL AND two_factor_confirmed_at IS NULL) OR
    (two_factor_secret IS NOT NULL)
);
```

## Storage Requirements

### Estimated Table Sizes (for 10,000 users)

| Table | Estimated Size | Notes |
|-------|---------------|-------|
| users | ~2 MB | Including all auth fields |
| personal_access_tokens | ~500 KB | Assuming 2 tokens per user |
| sessions | ~1 MB | Active sessions only |
| model_has_roles | ~100 KB | User-role assignments |
| roles | ~1 KB | 5 default roles |
| permissions | ~2 KB | ~25 permissions |
| role_has_permissions | ~5 KB | Role-permission mappings |
| password_reset_tokens | ~50 KB | Temporary storage |

**Total estimated size**: ~4 MB for 10,000 users

## Backup Considerations

### Critical Tables (Must backup)
- `users` - User accounts and authentication data
- `roles` - Role definitions
- `permissions` - Permission definitions
- `role_has_permissions` - Role-permission mappings
- `model_has_roles` - User role assignments

### Temporary Tables (Can skip in backups)
- `sessions` - Regenerated on login
- `password_reset_tokens` - Temporary tokens
- `cache` - Regenerated as needed
- `jobs` - Queue data

### Sensitive Data
- `two_factor_secret` - Encrypted TOTP secrets
- `two_factor_recovery_codes` - Encrypted recovery codes
- `personal_access_tokens.token` - Hashed API tokens
- `password_reset_tokens.token` - Temporary reset tokens

## Related Documentation

- [Authentication Architecture](Authentication-Architecture.md) - Component overview
- [Developer Guide](Developer-Guide.md) - Implementation details
- [API Reference](API-Reference.md) - API documentation
- [Testing Strategy](Testing-Strategy.md) - Testing procedures
