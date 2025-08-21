# Thorium90 Boilerplate

![Thorium90 Logo](public/images/logos/header.png)

**Laravel 12 CMS boilerplate with production-ready features**

Built with [Thorium90 Framework](https://github.com/travissutphin/thorium90)

## System Requirements

- **PHP**: 8.2+
- **Database**: MySQL 8.0+ (production), SQLite (development)
- **Node.js**: 18+ with npm
- **Composer**: 2.0+

## Quick Start

### Interactive Setup (Recommended)
```bash
# Clone and install
git clone https://github.com/travissutphin/thorium90.git [project name]

# Change directory
cd [project name]

# Install composer
composer install

# Install Node
npm install

# Run setup wizard
php artisan thorium90:setup --interactive
```

### Manual Setup
```bash
# Clone and install
git clone https://github.com/travissutphin/thorium90.git [project name]
cd [project name]
composer install 
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database (choose one)
# SQLite (development):
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

# MySQL (production):
# Edit .env with MySQL credentials, then:
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

# Start development
php artisan serve
```

**Default Admin Users Created:**
- **Super Admin**: `test@example.com` / `password`
- **Admin**: `admin@example.com` / `password`

## Production Features

✅ **Authentication**: Multi-role system with 2FA support  
✅ **CMS**: Page management with AEO optimization  
✅ **Database**: MySQL-optimized with migration compatibility  
✅ **Frontend**: Inertia.js admin + Blade public templates  
✅ **SEO**: Schema.org structured data integration  
✅ **Security**: CSRF protection, rate limiting, secure headers  
✅ **Testing**: Comprehensive PHPUnit test suite  

## Database Support

- **MySQL 8.0+**: Production recommended, optimized InnoDB tables
- **SQLite**: Development default, zero configuration
- **PostgreSQL 13+**: Alternative production option

## Documentation

- **[Setup Guide](docs/client/SETUP.md)**: Installation and configuration
- **[API Documentation](docs/client/API.md)**: REST API reference
- **[User Manual](docs/client/MANUAL.md)**: Admin interface guide
- **[Deployment](docs/deployment/)**: Production deployment guides

## Development

```bash
# Frontend development
npm run dev

# Run tests
php artisan test

# Clear caches
php artisan cache:clear && php artisan config:clear
```

## Support

For issues and questions: [Thorium90 Documentation](https://thorium90.com/docs)

---

**Built with ❤️ by the Thorium90 Team**

*Empowering developers to build exceptional web applications with speed and confidence.*
