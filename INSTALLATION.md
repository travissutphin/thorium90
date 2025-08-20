# Thorium90 Boilerplate Installation Guide

## Quick Installation

### Method 1: Composer Create Project (Recommended)

```bash
composer create-project thorium90/boilerplate my-new-project
cd my-new-project
```

The setup wizard will run automatically during installation.

### Method 2: Clone and Setup

```bash
git clone https://github.com/your-username/thorium90.git my-new-project
cd my-new-project
composer install
npm install
php artisan thorium90:setup --interactive
```

### Method 3: Use Creation Script

**Windows:**
```bash
.\scripts\create-boilerplate.bat
```

**Linux/Mac:**
```bash
./scripts/create-boilerplate.sh
```

## Manual Setup Process

If you need to run setup manually:

### 1. Environment Configuration

```bash
# Copy environment template
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Database Setup

```bash
# Create SQLite database (default)
touch database/database.sqlite

# Or configure MySQL/PostgreSQL in .env
# DB_CONNECTION=mysql
# DB_DATABASE=your_database
# DB_USERNAME=your_username
# DB_PASSWORD=your_password
```

### 3. Run Thorium90 Setup

```bash
# Interactive setup with all options
php artisan thorium90:setup --interactive

# Quick setup with preset
php artisan thorium90:setup --preset=blog --name="My Blog" --admin-email=admin@example.com

# Available presets: default, ecommerce, blog, saas
```

### 4. Install Frontend Dependencies

```bash
npm install
npm run build
```

### 5. Start Development Server

```bash
# Simple server
php artisan serve

# Full development stack (recommended)
composer run dev
```

## Setup Options

### Interactive Setup

The interactive wizard will ask for:

- **Project Name** - Displayed throughout the application
- **Domain** - Primary domain for production (optional)
- **Admin Email** - First admin user email
- **Admin Password** - Secure password for admin user
- **Preset** - Choose from available project types

### Command Line Options

```bash
php artisan thorium90:setup [options]

Options:
  --interactive         Run interactive setup wizard
  --preset=TYPE         Choose preset (default|ecommerce|blog|saas)
  --name=NAME           Project name
  --domain=DOMAIN       Primary domain
  --admin-email=EMAIL   Admin user email
  --admin-password=PWD  Admin user password
```

### Available Presets

#### Default Website
```bash
--preset=default
```
- Basic CMS with pages and user management
- Perfect for business websites and portfolios
- Modules: pages, users, auth, api

#### E-Commerce Platform
```bash
--preset=ecommerce
```
- Full e-commerce functionality ready
- Products, cart, orders, payment integration
- Modules: pages, users, auth, products, cart, orders, payments

#### Blog Platform
```bash
--preset=blog
```
- Content-focused blog system
- Posts, comments, categories, tags
- Modules: pages, users, auth, posts, comments, categories, tags

#### SaaS Application
```bash
--preset=saas
```
- Multi-tenant architecture ready
- Subscriptions, teams, billing
- Modules: pages, users, auth, subscriptions, teams, billing, api

## Environment Templates

### Development
```bash
cp .env.development.example .env
```
- Debug enabled
- Local storage
- Email logging
- SQLite database

### Staging
```bash
cp .env.staging.example .env
```
- Production-like settings
- Debug enabled
- Separate database
- Email testing

### Production
```bash
cp .env.production.example .env
```
- Optimized for performance
- Redis caching
- S3 storage
- SMTP email

## Post-Installation

### 1. Access Your Application

```bash
# Start the server
php artisan serve

# Visit your application
http://localhost:8000

# Admin panel
http://localhost:8000/admin
```

### 2. Default Login

Use the admin credentials you set during setup, or:

- **Email**: admin@example.com
- **Password**: password123

### 3. Customize Your Site

1. **Update Branding**
   ```bash
   php artisan thorium90:rebrand
   ```

2. **Create Content**
   - Add pages through the admin panel
   - Configure site settings
   - Upload logo and media

3. **Configure Features**
   - Enable/disable modules in `config/thorium90.php`
   - Update environment variables

## Troubleshooting

### Common Issues

#### Permission Denied
```bash
# Fix storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

#### Database Connection
```bash
# Check database configuration in .env
php artisan config:clear
php artisan migrate --force
```

#### Frontend Assets
```bash
# Clear and rebuild
npm run build
php artisan optimize:clear
```

#### Command Not Found
```bash
# Clear application cache
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

### Getting Help

1. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Run Diagnostics**
   ```bash
   php artisan about
   php artisan config:show
   ```

3. **Re-run Setup**
   ```bash
   php artisan thorium90:setup --interactive
   ```

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- Database (MySQL, PostgreSQL, or SQLite)

### Optional
- Redis (for caching and queues)
- Supervisor (for queue workers)

## Next Steps

After installation:

1. Read [README.md](README.md) for feature overview
2. Check [docs/client/](docs/client/) for detailed guides
3. Customize your project configuration
4. Deploy to your preferred hosting platform

## Support

- [GitHub Issues](https://github.com/thorium90/boilerplate/issues)
- [Documentation](docs/)
- [Community Forum](https://thorium90.com/community)