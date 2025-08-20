<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Thorium90Setup extends Command
{
    protected $signature = 'thorium90:setup 
                            {--interactive : Run interactive setup wizard}
                            {--preset=default : Setup preset (default|ecommerce|blog|saas)}
                            {--name= : Project name}
                            {--domain= : Primary domain}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}';

    protected $description = 'Setup Thorium90 boilerplate for a new project';

    protected $presets = [
        'default' => [
            'name' => 'Default Website',
            'description' => 'Basic CMS with pages and user management',
            'modules' => ['pages', 'users', 'auth', 'api']
        ],
        'ecommerce' => [
            'name' => 'E-Commerce Platform',
            'description' => 'Full e-commerce with products, cart, and payments',
            'modules' => ['pages', 'users', 'auth', 'products', 'cart', 'orders', 'payments']
        ],
        'blog' => [
            'name' => 'Blog Platform',
            'description' => 'Content-focused blog with posts and comments',
            'modules' => ['pages', 'users', 'auth', 'posts', 'comments', 'categories', 'tags']
        ],
        'saas' => [
            'name' => 'SaaS Application',
            'description' => 'Multi-tenant SaaS with subscriptions and teams',
            'modules' => ['pages', 'users', 'auth', 'subscriptions', 'teams', 'billing', 'api']
        ]
    ];

    public function handle()
    {
        $this->info('🚀 Welcome to Thorium90 Boilerplate Setup!');
        $this->newLine();

        if ($this->option('interactive')) {
            $this->runInteractiveSetup();
        } else {
            $this->runQuickSetup();
        }

        $this->info('✅ Thorium90 setup completed successfully!');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('• Run: php artisan serve');
        $this->info('• Visit: http://localhost:8000');
        $this->info('• Login with your admin credentials');
        $this->newLine();
    }

    protected function runInteractiveSetup()
    {
        $this->info('📋 Interactive Setup Wizard');
        $this->line('Answer a few questions to customize your Thorium90 installation.');
        $this->newLine();

        // Project Information
        $projectName = $this->ask('Project Name', 'My Thorium90 Site');
        $domain = $this->ask('Primary Domain (optional)', '');
        $adminEmail = $this->ask('Admin Email', 'admin@example.com');
        $adminPassword = $this->secret('Admin Password (min 8 chars)');

        // Preset Selection
        $this->info('Available Presets:');
        foreach ($this->presets as $key => $preset) {
            $this->line("  <info>{$key}</info>: {$preset['name']} - {$preset['description']}");
        }
        $preset = $this->choice('Choose a preset', array_keys($this->presets), 'default');

        $this->setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset);
    }

    protected function runQuickSetup()
    {
        $projectName = $this->option('name') ?: 'Thorium90 Site';
        $domain = $this->option('domain') ?: '';
        $adminEmail = $this->option('admin-email') ?: 'admin@example.com';
        $adminPassword = $this->option('admin-password') ?: 'password123';
        $preset = $this->option('preset') ?: 'default';

        $this->setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset);
    }

    protected function setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset)
    {
        $this->line('⚙️  Setting up your project...');

        // Update environment file
        $this->updateEnvironment($projectName, $domain);

        // Configure features based on preset
        $this->configureFeatures($preset);

        // Run migrations and seeders
        $this->runMigrations();

        // Create admin user
        $this->createAdminUser($adminEmail, $adminPassword);

        // Generate documentation
        $this->generateDocs($projectName, $preset);

        $this->info("✅ Project '{$projectName}' configured with '{$preset}' preset");
    }

    protected function updateEnvironment($projectName, $domain)
    {
        $this->line('📝 Updating environment configuration...');

        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $env = File::get($envPath);

            // Update app name
            $env = preg_replace('/APP_NAME=.*/', "APP_NAME=\"{$projectName}\"", $env);

            // Update app URL if domain provided
            if ($domain) {
                $env = preg_replace('/APP_URL=.*/', "APP_URL=https://{$domain}", $env);
            }

            File::put($envPath, $env);
        }
    }

    protected function configureFeatures($preset)
    {
        $this->line('🔧 Configuring features...');
        
        $modules = $this->presets[$preset]['modules'] ?? ['pages', 'users', 'auth'];
        
        // Create feature configuration file
        $featureConfig = [
            'preset' => $preset,
            'modules' => $modules,
            'enabled_features' => array_fill_keys($modules, true)
        ];

        $configPath = config_path('thorium90.php');
        $configContent = "<?php\n\nreturn " . var_export($featureConfig, true) . ";\n";
        File::put($configPath, $configContent);
    }

    protected function runMigrations()
    {
        $this->line('🗄️  Running database migrations...');
        
        $this->call('migrate', ['--force' => true]);
        $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--force' => true]);
        $this->call('db:seed', ['--class' => 'HomePageSeeder', '--force' => true]);
    }

    protected function createAdminUser($email, $password)
    {
        $this->line('👤 Creating admin user...');

        $user = \App\Models\User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin User',
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]
        );

        // Assign Super Admin role
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin']);
            $user->assignRole($adminRole);
        }

        $this->info("Admin user created: {$email}");
    }

    protected function generateDocs($projectName, $preset)
    {
        $this->line('📚 Generating project documentation...');

        $readmeContent = $this->generateReadme($projectName, $preset);
        File::put(base_path('README.md'), $readmeContent);

        $docsDir = base_path('docs/client');
        if (!File::exists($docsDir)) {
            File::makeDirectory($docsDir, 0755, true);
        }

        $setupGuide = $this->generateSetupGuide($projectName);
        File::put($docsDir . '/SETUP.md', $setupGuide);
    }

    protected function generateReadme($projectName, $preset)
    {
        $presetInfo = $this->presets[$preset];
        
        return "# {$projectName}

Built with [Thorium90 Boilerplate](https://github.com/thorium90/boilerplate)

## Project Configuration
- **Preset**: {$presetInfo['name']}
- **Description**: {$presetInfo['description']}
- **Modules**: " . implode(', ', $presetInfo['modules']) . "

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Setup database
php artisan migrate
php artisan db:seed

# Start development server
php artisan serve
```

## Features Included

✅ Multi-role authentication system
✅ AEO-optimized page management
✅ Admin dashboard with Inertia.js
✅ Schema.org structured data
✅ Production-ready configuration
✅ Comprehensive test suite

## Documentation

- [Setup Guide](docs/client/SETUP.md)
- [API Documentation](docs/client/API.md)
- [User Manual](docs/client/MANUAL.md)

## Support

For issues and questions, refer to the [Thorium90 Documentation](https://thorium90.com/docs).

---
*Generated by Thorium90 Setup on " . now()->format('Y-m-d H:i:s') . "*
";
    }

    protected function generateSetupGuide($projectName)
    {
        return "# {$projectName} - Setup Guide

## Initial Setup Complete ✅

Your Thorium90 project has been configured automatically. Here's what was set up:

### Environment
- Project name configured
- Database connection established  
- Admin user created

### Next Steps

1. **Start Development Server**
   ```bash
   php artisan serve
   ```

2. **Access Admin Panel**
   - URL: http://localhost:8000/admin
   - Use the admin credentials you provided during setup

3. **Customize Your Site**
   - Edit pages in the admin panel
   - Configure site settings
   - Upload your logo and branding

4. **Development Commands**
   ```bash
   # Run tests
   php artisan test
   
   # Clear caches
   php artisan cache:clear
   
   # Run with queue processing
   composer run dev
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
";
    }
}