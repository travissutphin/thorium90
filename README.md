# Thorium90 - Laravel 12 Rapid Development Framework

![Thorium90 Logo](public/images/logos/header.png)

## Overview

Thorium90 is a professional Laravel 12 rapid development framework designed for agencies and developers. Build exceptional client projects with unprecedented speed and quality using modern tools and best practices.

## 🚀 Key Features

### Modern Technology Stack
- **Laravel 12** with latest PHP 8.4 support
- **React 19** with TypeScript for type-safe frontend development  
- **Inertia.js** for seamless SPA experience
- **Tailwind CSS** with shadcn/ui components
- **Vite** for lightning-fast development builds

### Comprehensive Authentication System
- **Multi-Role RBAC**: 5 user roles with 21 granular permissions
- **Two-Factor Authentication**: Mandatory for Admin/Super Admin roles
- **Social Login**: GitHub, Google, and extensible provider system
- **Email Verification**: Complete email workflow with customizable templates

### Content Management System
- **Dynamic Page System**: Create and manage pages with flexible templates
- **Schema.org Integration**: Built-in SEO and structured data support
- **Template System**: Flexible, plugin-ready architecture
- **AEO Optimization**: Answer Engine Optimization for AI search engines

### Developer Experience
- **Setup Wizard**: Interactive installation with `php artisan thorium90:setup`
- **Plugin Architecture**: Extensible plugin system for custom features
- **Testing Suite**: Comprehensive test coverage with regression testing
- **Documentation**: Complete development guides and API references

## 📦 Quick Installation

### Option 1: Setup Wizard (Recommended)
```bash
# Clone the repository
git clone https://github.com/travissutphin/thorium90.git your-project-name
cd your-project-name

# Install dependencies
composer install && npm install

# Run interactive setup wizard
php artisan thorium90:setup --interactive
```

### Option 2: Manual Installation
```bash
# Clone and install
git clone https://github.com/travissutphin/thorium90.git your-project-name
cd your-project-name
composer install && npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Start development
php artisan serve
npm run dev
```

## 🎯 Framework Architecture

### Boilerplate Philosophy
Thorium90 serves as a **professional boilerplate** for client projects:

- **Clone per project**: Each client gets their own instance
- **Rapid customization**: Modify templates without touching core code
- **Updateable core**: Pull framework updates while preserving customizations
- **Production ready**: Built-in security, performance, and SEO optimizations

### Role-Based Access Control

| Role | User Management | Content Management | Media Management | System Admin |
|------|----------------|-------------------|------------------|--------------|
| Super Admin | ✅ Full Access | ✅ Full Access | ✅ Full Access | ✅ Full Access |
| Admin | ✅ Full Access | ✅ Full Access | ✅ Full Access | ✅ Limited |
| Editor | ❌ None | ✅ Full Access | ✅ Full Access | ❌ None |
| Author | ❌ None | ✅ Own Content | ✅ Upload Only | ❌ None |
| Subscriber | ❌ None | ❌ None | ❌ None | ❌ None |

## 📖 Documentation

### Essential Reading
- **[Installation Guide](docs/installation.md)** - Complete setup instructions
- **[Development Workflow](docs/development/BOILERPLATE-WORKFLOW.md)** - MANDATORY consistency process
- **[Authentication System](docs/authentication/README.md)** - User management and security
- **[Template System](docs/features/templates.md)** - Flexible page templates
- **[Testing Strategy](docs/testing/TESTING.md)** - Quality assurance procedures

### Feature Documentation  
- **[Schema Validation](docs/features/schema-validation.md)** - SEO and structured data
- **[Plugin System](docs/features/hybrid-system.md)** - Extensible architecture
- **[CMS Pages](docs/features/cms/pages-guide.md)** - Content management

### Development Resources
- **[API Reference](docs/api/README.md)** - Backend API documentation
- **[Frontend Integration](docs/development/frontend-integration.md)** - React components and hooks
- **[Permissions Guide](docs/development/permissions-guide.md)** - RBAC implementation

## 🧪 Testing & Quality Assurance

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter="Authentication"
php artisan test --filter="Critical|Auth|Permission"

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- ✅ Authentication and authorization
- ✅ Role-based access control
- ✅ Page management and templates
- ✅ Schema validation and SEO
- ✅ Plugin system integration
- ✅ Database integrity and performance

### Quality Tools
```bash
# Code quality checks
composer lint
composer test
composer stan

# Frontend testing
npm run test
npm run type-check
```

## 🔧 Configuration

### Key Configuration Files
- `config/thorium90.php` - Framework-specific settings
- `config/permission.php` - RBAC configuration  
- `config/schema.php` - Schema.org validation rules
- `config/features.php` - Feature flag management

### Environment Variables
```env
# Core Application
APP_NAME="Your Project Name"
APP_ENV=production
APP_DEBUG=false

# Database Configuration  
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database

# Authentication
THORIUM90_2FA_REQUIRED=true
THORIUM90_SETUP_COMPLETED=true

# Social Login (Optional)
GITHUB_CLIENT_ID=your_github_client_id
GOOGLE_CLIENT_ID=your_google_client_id
```

## 🚀 Deployment

### Pre-deployment Checklist
- [ ] Run full test suite: `php artisan test`
- [ ] Check code quality: `composer lint && composer stan`
- [ ] Build assets: `npm run build`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Run migrations: `php artisan migrate --force`

### Production Optimization
```bash
# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contributing

### Development Workflow
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Follow coding standards and write tests
4. Run the test suite: `php artisan test`
5. Submit a pull request

### Coding Standards
- **PSR-12** for PHP code style
- **ESLint + Prettier** for JavaScript/TypeScript
- **Comprehensive testing** for all new features
- **TypeScript** for type safety

## 📄 License & Support

### License
Thorium90 is open-source software licensed under the [MIT License](LICENSE).

### Getting Help
- **Documentation**: Complete guides in `/docs/`
- **GitHub Issues**: Bug reports and feature requests
- **Community**: Join our Discord community
- **Professional Support**: Available for enterprise clients

### Version Compatibility
- **PHP**: ^8.2 minimum, 8.4 recommended
- **Laravel**: 12.x LTS
- **Node.js**: ^18.0 minimum, 20.x recommended
- **MySQL**: ^8.0 or PostgreSQL ^14.0

---

**Built with ❤️ by the Thorium90 Team**

*Empowering developers to build exceptional web applications with speed and confidence.*
