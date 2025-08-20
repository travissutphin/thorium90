# Thorium90 Boilerplate

Production-ready Laravel boilerplate with AEO integration, multi-role authentication, CMS, and rapid deployment tools.

## Features

✅ **Multi-Role Authentication** - Super Admin, Admin, Editor, Author, Subscriber roles  
✅ **AEO Integration** - Schema.org structured data with automatic generation  
✅ **Content Management** - Full CMS with pages, SEO optimization, and media management  
✅ **Admin Dashboard** - Modern React-based admin panel with Inertia.js  
✅ **Production Ready** - Comprehensive error handling, logging, and security  
✅ **Test Coverage** - Full test suite with feature, unit, and integration tests  
✅ **API Ready** - Laravel Sanctum with role-based API access control  
✅ **Deployment Tools** - Environment templates and setup automation  

## Quick Start

### Installation

```bash
composer create-project thorium90/boilerplate your-project-name
cd your-project-name
```

The setup wizard will run automatically and guide you through configuration.

### Manual Setup

If you need to run setup again:

```bash
php artisan thorium90:setup --interactive
```

### Development

```bash
# Start development environment
composer run dev

# Run tests
php artisan test

# Access admin panel
http://localhost:8000/admin
```

## Project Presets

Choose from pre-configured project types:

### Default Website
- Basic CMS with pages and user management
- Perfect for business websites and portfolios

### E-Commerce Platform  
- Full e-commerce functionality
- Products, cart, orders, and payment integration ready

### Blog Platform
- Content-focused blog system
- Posts, comments, categories, and tags

### SaaS Application
- Multi-tenant architecture
- Subscriptions, teams, and billing ready

## Configuration

### Environment Templates

- `.env.boilerplate` - Master template with placeholders
- `.env.production.example` - Production-optimized settings
- `.env.development.example` - Development settings

### Feature Modules

Control which features are enabled:

```php
// config/thorium90.php
'modules' => [
    'pages' => true,
    'blog' => false,
    'ecommerce' => false,
    'api' => true,
    '2fa' => true,
    'social_login' => false,
]
```

## Architecture

### Core Components

- **Laravel 12** - Latest framework version
- **Inertia.js + React** - Modern admin interface  
- **Spatie Permissions** - Role-based access control
- **Laravel Fortify** - Authentication features
- **Schema Validation Service** - AEO optimization
- **Tailwind CSS** - Utility-first styling

### Database Design

- Multi-role user system
- Flexible page management
- Schema.org integration
- AEO-optimized structure

## Commands

### Setup & Configuration

```bash
php artisan thorium90:setup          # Interactive setup wizard
php artisan thorium90:setup --preset=blog --name="My Blog"
php artisan thorium90:rebrand        # Update branding
php artisan thorium90:docs           # Generate documentation
```

### Development

```bash
composer run dev                     # Full development environment
composer run test                    # Run test suite
php artisan serve                    # Start server only
```

## Deployment

### Quick Deploy

```bash
# Production setup
cp .env.production.example .env
php artisan key:generate
php artisan migrate --force
php artisan config:cache
```

### Environment Configuration

The boilerplate includes optimized configurations for:

- **Development** - Debug enabled, local storage, email logs
- **Staging** - Production-like with debugging, separate database
- **Production** - Optimized for performance and security

## Customization

### Branding

Update branding through environment variables or the rebrand command:

```env
BRAND_PRIMARY=#your-color
BRAND_SECONDARY=#your-secondary
APP_LOGO=/path/to/logo.svg
FOOTER_TEXT="Your Company Name"
```

### Adding Features

1. Enable modules in `config/thorium90.php`
2. Run migrations if needed
3. Update navigation and permissions

## Testing

Comprehensive test suite included:

```bash
# Run all tests
php artisan test

# Run specific test groups  
php artisan test --filter="Auth"
php artisan test --filter="AEO"
php artisan test --filter="Admin"
```

## API Documentation

When API module is enabled:

- **Authentication**: Laravel Sanctum tokens
- **Endpoints**: `/api/v1/*`  
- **Documentation**: Auto-generated OpenAPI docs

## Support & Documentation

- [Setup Guide](docs/client/SETUP.md)
- [API Documentation](docs/client/API.md)  
- [User Manual](docs/client/MANUAL.md)
- [GitHub Issues](https://github.com/thorium90/boilerplate/issues)

## License

MIT License. See [LICENSE](LICENSE) file for details.

---

**Built with Thorium90 Boilerplate** - Rapid development platform for modern web applications.