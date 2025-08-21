# Thorium90 - Setup Guide

## Prerequisites

### Database Requirements
- **Production**: MySQL 8.0+ (recommended)
- **Development**: SQLite (default) or MySQL
- **Alternative**: PostgreSQL 13+

### System Requirements
- PHP 8.2+
- Composer 2.0+
- Node.js 18+ & npm

## Installation Methods

### Option 1: Interactive Setup (Recommended)
```bash
# Clone repository
git clone https://github.com/travissutphin/thorium90.git
cd thorium90

# Install dependencies
composer install
npm install

# Run interactive setup wizard
php artisan thorium90:setup --interactive
```

The setup wizard will guide you through:
1. **Database Selection**: Choose MySQL, SQLite, or PostgreSQL
2. **Database Configuration**: Connection details and testing
3. **Environment Setup**: Project name, URL, locale
4. **Admin User Creation**: Credentials for admin access
5. **Feature Selection**: Enable/disable modules

### Option 2: Manual Setup

#### 1. Clone and Environment Configuration
```bash
# Clone repository
git clone https://github.com/travissutphin/thorium90.git
cd thorium90

# Install dependencies
composer install && npm install

# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 2. Database Setup

**For MySQL (Production):**
```bash
# Edit .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**For SQLite (Development):**
```bash
# Edit .env file
DB_CONNECTION=sqlite
# Database file will be created automatically
```

#### 3. Database Migration and Setup
```bash
# Create database tables
php artisan migrate --force

# Seed initial data (creates roles, permissions, and test users)
php artisan db:seed --class=DatabaseSeeder --force
```

**Note**: The DatabaseSeeder creates default admin users:
- **Super Admin**: `test@example.com` / `password`
- **Admin**: `admin@example.com` / `password`
- **Editor**: `editor@example.com` / `password`
- **Author**: `author@example.com` / `password`
- **Subscriber**: `subscriber@example.com` / `password`

## Post-Installation

### Development Server
```bash
php artisan serve
# Access: http://localhost:8000
```

### Admin Access
- URL: http://localhost:8000/admin
- Use credentials created during setup

### Development Commands
```bash
# Run tests
php artisan test

# Clear caches
php artisan cache:clear

# Frontend development
npm run dev

# Production build
npm run build
```

## Configuration Files

- **Environment**: `.env`
- **Features**: `config/thorium90.php`
- **Database**: `config/database.php`

## Available Artisan Commands

```bash
php artisan thorium90:setup       # Re-run setup
php artisan thorium90:docs        # Generate documentation
php artisan thorium90:rebrand     # Update branding
```

---
*Need help? Check the [Thorium90 Documentation](https://thorium90.com/docs)*
