# Thorium90 - Laravel + React Starter Kit

## Introduction

Our React starter kit provides a robust, modern starting point for building Laravel applications with a React frontend using [Inertia](https://inertiajs.com).

Inertia allows you to build modern, single-page React applications using classic server-side routing and controllers. This lets you enjoy the frontend power of React combined with the incredible backend productivity of Laravel and lightning-fast Vite compilation.

This React starter kit utilizes React 19, TypeScript, Tailwind, and the [shadcn/ui](https://ui.shadcn.com) and [radix-ui](https://www.radix-ui.com) component libraries.

## 🚀 Features

### Multi-Role User Authentication System
This application includes a comprehensive role-based access control (RBAC) system built with Spatie Laravel Permission package.

#### Key Features:
- **5 User Roles**: Super Admin, Admin, Editor, Author, Subscriber
- **21 Granular Permissions**: Covering user management, content creation, media handling, and system administration
- **Frontend Integration**: Seamless integration with Inertia.js for real-time permission checking
- **Computed Properties**: Easy-to-use boolean flags for common permission checks

#### Roles & Permissions Matrix:

| Role | User Management | Content Management | Media Management | System Admin |
|------|----------------|-------------------|------------------|--------------|
| Super Admin | ✅ All | ✅ All | ✅ All | ✅ All |
| Admin | ✅ All | ✅ All | ✅ All | ✅ Limited |
| Editor | ❌ None | ✅ All | ✅ All | ❌ None |
| Author | ❌ None | ✅ Own Content | ✅ Upload | ❌ None |
| Subscriber | ❌ None | ❌ None | ❌ None | ❌ None |

### Technical Stack
- **Backend**: Laravel 11 with Spatie Laravel Permission v6.21
- **Frontend**: React 19, TypeScript, Inertia.js
- **Styling**: Tailwind CSS, shadcn/ui components
- **Testing**: PHPUnit with comprehensive test coverage

## 📖 Documentation

### Quick Start
1. **Installation**: `composer install && npm install`
2. **Environment**: Copy `.env.example` to `.env` and configure
3. **Database**: `php artisan migrate`
4. **Development**: `npm run dev` and `php artisan serve`

### Authentication System
- [Authentication Guide](docs/authentication/README.md) - Complete system overview
- [API Documentation](docs/authentication/api.md) - Endpoint documentation
- [Troubleshooting](docs/authentication/troubleshooting.md) - Common issues and solutions
- [Deployment Guide](docs/authentication/deployment.md) - Production deployment checklist

### Development
- [Testing Guide](docs/testing/authentication-tests.md) - How to run and write tests
- [Permissions Guide](docs/development/permissions-guide.md) - Working with roles and permissions
- [Frontend Integration](docs/development/frontend-integration.md) - Using permissions in React components

### Usage Examples

#### Backend Permission Checking
```php
// Check if user can perform action
if ($user->can('create posts')) {
    // Allow post creation
}

// Check if user has specific role
if ($user->hasRole('Admin')) {
    // Admin-specific logic
}
```

#### Frontend Permission Checking
```typescript
// In React components
const { auth } = usePage<SharedData>().props;

if (auth.user.can('create posts')) {
    // Show create post button
}

if (auth.user.is_admin) {
    // Show admin panel
}
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
php artisan test

# Run authentication tests only
php artisan test --filter=UIPermissionTest

# Run specific test
php artisan test --filter=test_inertia_shares_user_data_correctly
```

### Test Coverage
- ✅ Role-based user creation
- ✅ Permission inheritance
- ✅ Frontend data sharing
- ✅ Inertia.js integration
- ✅ Computed properties validation

## 📄 Configuration

### Permission Configuration
File: `config/permission.php`

Key settings:
- **Guard**: 'web' (default Laravel guard)
- **Cache**: Enabled for performance
- **Display Names**: Customizable permission display names

### Available Permissions
```php
// User Management
'view users', 'create users', 'edit users', 'delete users', 'manage user roles'

// Content Management  
'view posts', 'create posts', 'edit posts', 'delete posts', 'publish posts'
'edit own posts', 'delete own posts'

// System Administration
'manage settings', 'manage roles', 'manage permissions'

// Media Management
'upload media', 'manage media', 'delete media'

// Comment Management
'view comments', 'moderate comments', 'delete comments'
```

## 🚀 Deployment

### Pre-deployment Checklist
- [ ] Run migrations: `php artisan migrate`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Run tests: `php artisan test --filter=UIPermissionTest`
- [ ] Verify permission configuration

### Post-deployment Verification
- [ ] Verify role assignments
- [ ] Test permission inheritance
- [ ] Validate frontend integration
- [ ] Check admin panel access

## 🤝 Contributing

Thank you for considering contributing to our starter kit! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

### Development Workflow
1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## 📖 Official Documentation

Documentation for all Laravel starter kits can be found on the [Laravel website](https://laravel.com/docs/starter-kits).

## 📋 Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## 📄 License

The Laravel + React starter kit is open-sourced software licensed under the MIT license.
