<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseConfigurationService;
use Exception;

class Thorium90Setup extends Command
{
    protected $signature = 'thorium90:setup 
                            {--interactive : Run interactive setup wizard}
                            {--preset=default : Setup preset (default|ecommerce|blog|saas)}
                            {--name= : Project name}
                            {--domain= : Primary domain}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}
                            {--resolve-conflicts-only : Only resolve migration conflicts, skip full setup}
                            {--force : Skip system validation (advanced users only)}';

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
        $this->info('🚀 Welcome to Thorium90 Setup!');
        $this->newLine();

        // Handle conflicts-only mode
        if ($this->option('resolve-conflicts-only')) {
            $this->info('🔧 Running migration conflict resolution only...');
            $this->resolveMigrationConflicts();
            $this->info('✅ Migration conflicts resolved!');
            $this->line('You can now run: php artisan migrate --force');
            return;
        }

        // Validate system requirements first (unless forced or conflicts-only)
        if (!$this->option('force') && !$this->validateSystemRequirements()) {
            $this->displayRecoveryInstructions();
            return Command::FAILURE;
        }

        if ($this->option('interactive')) {
            $this->runInteractiveSetup();
        } else {
            $this->runQuickSetup();
        }

        $this->info('🎉 Thorium90 setup completed successfully!');
        $this->newLine();
        
        // Simple URL guidance
        $this->info('💡 IMPORTANT: If media files don\'t load, check APP_URL in your .env file');
        $this->line('Common fix: Change https://localhost to http://localhost for local development');
        $this->newLine();
        
        // Show setup summary
        $this->displaySetupSummary();
        
        $this->info('📋 NEXT STEPS:');
        $this->line('1. Run: npm run build (build frontend assets)');
        $this->line('2. Run: php artisan serve (start Laravel server)');
        $this->line('3. Open another terminal and run: npm run dev (for live reloading)');
        $this->line('4. Visit: http://localhost:8000');
        $this->line('5. Login with your admin credentials');
        $this->newLine();
        
        $this->info('🚀 DEVELOPMENT COMMANDS:');
        $this->line('• composer run dev (all services at once)');
        $this->line('• npm run health-check (system diagnostics)');
        $this->line('• php artisan test (run tests)');
        $this->newLine();
    }

    protected function runInteractiveSetup()
    {
        $this->info('📋 Interactive Setup Wizard');
        $this->line('Answer a few questions to customize your Thorium90 installation.');
        $this->newLine();

        // Project Information
        $projectName = $this->ask('Project Name', 'My Thorium90 Site');
        $domain = $this->ask('Primary Domain - if local leave blank', '');

        // Database Configuration
        $databaseConfig = $this->setupDatabase();

        $adminEmail = $this->ask('Admin Email', 'admin@example.com');
        $adminPassword = $this->secret('Admin Password (min 8 chars)');

        // Preset Selection
        $this->info('Available Presets:');
        foreach ($this->presets as $key => $preset) {
            $this->line("  <info>{$key}</info>: {$preset['name']} - {$preset['description']}");
        }
        $preset = $this->choice('Choose a preset', array_keys($this->presets), 'default');

        $this->setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset, $databaseConfig);
    }

    protected function runDefaultSetup()
    {
        $this->info('⚡ Running default setup with smart defaults...');
        $this->newLine();
        
        // Force SQLite for rapid local development
        $this->line('📊 Using SQLite for local development (zero configuration)');
        $this->ensureSQLiteDatabase();
        $databaseConfig = ['type' => 'sqlite'];
        
        // Smart defaults - no questions asked
        $projectName = $this->option('name') ?: basename(getcwd());
        $domain = $this->option('domain') ?: 'http://127.0.0.1:8000';
        $adminEmail = $this->option('admin-email') ?: 'admin@example.com';
        $adminPassword = $this->option('admin-password') ?: 'password123';
        $preset = $this->option('preset') ?: 'default';
        
        $this->info("📝 Project: {$projectName}");
        $this->info("👤 Admin: {$adminEmail}");
        $this->info("🎯 Preset: {$preset}");
        $this->newLine();
        
        $this->setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset, $databaseConfig);
        
        $this->info('🎉 Default setup completed! Perfect for rapid development.');
        $this->warn('💡 Tip: Use this for local dev, deploy to production for MySQL.');
    }

    protected function runQuickSetup()
    {
        $this->info('🚀 Running quick setup...');
        $this->newLine();
        
        // Force SQLite for rapid local development  
        $this->line('📊 Using SQLite for local development (zero configuration)');
        $this->ensureSQLiteDatabase();
        $databaseConfig = ['type' => 'sqlite'];
        
        $projectName = $this->option('name') ?: 'Thorium90 Site';
        $domain = $this->option('domain') ?: 'http://127.0.0.1:8000';
        $adminEmail = $this->option('admin-email') ?: 'admin@example.com';
        $adminPassword = $this->option('admin-password') ?: 'password123';
        $preset = $this->option('preset') ?: 'default';

        $this->setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset, $databaseConfig);
    }

    protected function setupDatabase()
    {
        $this->info('📊 Database Configuration');
        $this->line('Choose your database type for this project.');
        $this->newLine();

        // Display options with descriptions
        $this->line('  <info>mysql</info>: MySQL (Recommended for production)');
        $this->line('  <info>sqlite</info>: SQLite (Quick setup for development)');
        $this->line('  <info>pgsql</info>: PostgreSQL (Advanced features)');
        $this->newLine();

        $databaseType = $this->choice(
            'Which database would you like to use?',
            ['mysql', 'sqlite', 'pgsql'],
            'mysql'
        );

        if ($databaseType === 'sqlite') {
            $this->warn('⚠️  SQLite is only recommended for development and small projects.');
            if (!$this->confirm('Continue with SQLite?', true)) {
                return $this->setupDatabase(); // Ask again
            }
            
            // Ensure SQLite database file exists
            $this->ensureSQLiteDatabase();
            
            return ['type' => 'sqlite'];
        }

        return $this->setupProductionDatabase($databaseType);
    }

    protected function setupProductionDatabase($databaseType)
    {
        $this->line("Setting up {$databaseType} database...");
        
        $config = ['type' => $databaseType];
        
        // Get database connection details
        $config['host'] = $this->ask('Database Host', '127.0.0.1');
        $config['port'] = $this->ask('Database Port', $databaseType === 'mysql' ? '3306' : '5432');
        
        $config['database'] = $this->ask('Database Name', 'thorium90_' . strtolower(Str::random(6)));
        $config['username'] = $this->ask('Database Username', $databaseType === 'mysql' ? 'root' : 'postgres');
        $config['password'] = $this->secret('Database Password (leave empty if none)');

        // Test connection and offer to create database
        if ($this->testDatabaseConnection($config)) {
            $this->info('✅ Database connection successful!');
        } else {
            $this->error('❌ Could not connect to database.');
            
            if ($this->confirm('Would you like to try different settings?', true)) {
                return $this->setupProductionDatabase($databaseType);
            }
            
            $this->warn('Continuing with current settings. You may need to configure manually.');
        }

        return $config;
    }

    protected function testDatabaseConnection($config)
    {
        try {
            $this->line('🔍 Testing database connection...');
            
            // Temporarily update database config
            config(['database.connections.temp' => [
                'driver' => $config['type'],
                'host' => $config['host'],
                'port' => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
                'charset' => $config['type'] === 'mysql' ? 'utf8mb4' : 'utf8',
                'collation' => $config['type'] === 'mysql' ? 'utf8mb4_unicode_ci' : null,
            ]]);

            // Test connection
            DB::connection('temp')->getPdo();
            
            return true;
        } catch (Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            
            // Offer to create database if it doesn't exist
            if (str_contains($e->getMessage(), 'Unknown database') || str_contains($e->getMessage(), 'database') && str_contains($e->getMessage(), 'does not exist')) {
                if ($this->confirm("Database '{$config['database']}' doesn't exist. Would you like me to create it?", true)) {
                    return $this->createDatabase($config);
                }
            }
            
            return false;
        }
    }

    protected function createDatabase($config)
    {
        try {
            $this->line('🔨 Creating database...');
            
            // Connect without specifying database
            $tempConfig = $config;
            $tempConfig['database'] = $config['type'] === 'mysql' ? '' : 'postgres';
            
            config(['database.connections.temp_create' => [
                'driver' => $tempConfig['type'],
                'host' => $tempConfig['host'],
                'port' => $tempConfig['port'],
                'database' => $tempConfig['database'],
                'username' => $tempConfig['username'],
                'password' => $tempConfig['password'],
            ]]);

            $pdo = DB::connection('temp_create')->getPdo();
            
            if ($config['type'] === 'mysql') {
                $pdo->exec("CREATE DATABASE `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } else {
                $pdo->exec("CREATE DATABASE \"{$config['database']}\" WITH ENCODING 'UTF8'");
            }
            
            $this->info("✅ Database '{$config['database']}' created successfully!");
            return true;
            
        } catch (Exception $e) {
            $this->error("Failed to create database: " . $e->getMessage());
            return false;
        }
    }

    protected function setupProject($projectName, $domain, $adminEmail, $adminPassword, $preset, $databaseConfig = null)
    {
        $this->line('⚙️  Setting up your project...');

        // Update environment file
        $this->updateEnvironment($projectName, $domain, $databaseConfig);

        // Configure features based on preset
        $this->configureFeatures($preset);

        // Run migrations and seeders
        $this->runMigrations();

        // Create admin user
        $this->createAdminUser($adminEmail, $adminPassword);

        // Generate documentation
        $this->generateDocs($projectName, $preset);
        
        // Mark setup as complete
        $this->markSetupComplete($projectName, $preset, $adminEmail);

        $this->info("✅ Project '{$projectName}' configured with '{$preset}' preset");
    }

    protected function updateEnvironment($projectName, $domain, $databaseConfig = null)
    {
        $this->line('📝 Updating environment configuration...');

        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');
        
        // Copy .env.example to .env if .env doesn't exist
        if (!File::exists($envPath) && File::exists($envExamplePath)) {
            File::copy($envExamplePath, $envPath);
        }

        if (File::exists($envPath)) {
            $env = File::get($envPath);

            // Update app name
            $env = preg_replace('/APP_NAME=.*/', "APP_NAME=\"{$projectName}\"", $env);

            // Update app URL if domain provided
            if ($domain) {
                $env = preg_replace('/APP_URL=.*/', "APP_URL=https://{$domain}", $env);
            }

            // Update database configuration if provided
            if ($databaseConfig) {
                $env = $this->updateDatabaseEnvironment($env, $databaseConfig);
            }

            // Generate application key if not present
            if (!str_contains($env, 'APP_KEY=base64:')) {
                $this->call('key:generate', ['--force' => true]);
                $env = File::get($envPath); // Reload after key generation
            }

            File::put($envPath, $env);
            
            // Refresh Laravel configuration to use updated environment
            if ($databaseConfig) {
                $this->refreshDatabaseConfiguration();
            }
        }
    }

    protected function updateDatabaseEnvironment($env, $config)
    {
        $this->line('🔧 Configuring database environment...');
        $this->info("Selected database type: {$config['type']}");

        if ($config['type'] === 'sqlite') {
            // Update DB_CONNECTION to sqlite
            $env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=sqlite', $env);
            
            // Add or update DB_DATABASE for SQLite - check if line exists
            if (preg_match('/^# ?DB_DATABASE=.*/m', $env)) {
                // Replace existing DB_DATABASE line (commented or not)
                $env = preg_replace('/^# ?DB_DATABASE=.*/m', 'DB_DATABASE=' . database_path('database.sqlite'), $env);
            } elseif (preg_match('/^DB_DATABASE=.*/m', $env)) {
                // Replace existing uncommented DB_DATABASE line
                $env = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . database_path('database.sqlite'), $env);
            } else {
                // Add DB_DATABASE line after DB_CONNECTION
                $env = preg_replace('/^(DB_CONNECTION=sqlite)$/m', "$1\nDB_DATABASE=" . database_path('database.sqlite'), $env);
            }
            
            // Comment out MySQL/PostgreSQL settings that don't apply to SQLite
            $env = preg_replace('/^DB_HOST=.*/m', '# DB_HOST=127.0.0.1', $env);
            $env = preg_replace('/^DB_PORT=.*/m', '# DB_PORT=3306', $env);
            $env = preg_replace('/^DB_USERNAME=.*/m', '# DB_USERNAME=root', $env);
            $env = preg_replace('/^DB_PASSWORD=.*/m', '# DB_PASSWORD=', $env);
            
            $this->info('✅ Environment configured for SQLite');
            $this->info('SQLite database path: ' . database_path('database.sqlite'));
        } else {
            // Update database connection settings for MySQL/PostgreSQL
            $env = preg_replace('/^# ?DB_CONNECTION=.*/m', "DB_CONNECTION={$config['type']}", $env);
            $env = preg_replace('/^# ?DB_HOST=.*/m', "DB_HOST={$config['host']}", $env);
            $env = preg_replace('/^# ?DB_PORT=.*/m', "DB_PORT={$config['port']}", $env);
            $env = preg_replace('/^# ?DB_DATABASE=.*/m', "DB_DATABASE={$config['database']}", $env);
            $env = preg_replace('/^# ?DB_USERNAME=.*/m', "DB_USERNAME={$config['username']}", $env);
            $env = preg_replace('/^# ?DB_PASSWORD=.*/m', "DB_PASSWORD={$config['password']}", $env);
            
            $this->info("✅ Environment configured for {$config['type']}");
        }

        return $env;
    }

    protected function refreshDatabaseConfiguration()
    {
        $this->line('🔄 Refreshing database configuration...');
        
        // Clear configuration cache first  
        $this->call('config:clear');
        
        // Parse the .env file to get new DB_CONNECTION and DB_DATABASE
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $env = File::get($envPath);
            if (preg_match('/^DB_CONNECTION=(.*)$/m', $env, $matches)) {
                $dbConnection = trim($matches[1]);
                
                // Update environment variables in memory
                $_ENV['DB_CONNECTION'] = $dbConnection;
                putenv("DB_CONNECTION={$dbConnection}");
                
                // Also update DB_DATABASE if SQLite
                if ($dbConnection === 'sqlite') {
                    if (preg_match('/^DB_DATABASE=(.*)$/m', $env, $dbMatches)) {
                        $dbPath = trim($dbMatches[1]);
                        $_ENV['DB_DATABASE'] = $dbPath;
                        putenv("DB_DATABASE={$dbPath}");
                        $this->info("SQLite database path set to: {$dbPath}");
                    }
                }
                
                // Update Laravel's runtime configuration
                config(['database.default' => $dbConnection]);
                
                // Completely purge database manager and connections
                app()->forgetInstance('db');
                app('db')->purge();
                
                $this->info("✅ Database configuration refreshed to: {$dbConnection}");
                
                // Only clear cache if it's not database-stored or we can safely connect
                $cacheStore = config('cache.default');
                if ($cacheStore !== 'database') {
                    $this->call('cache:clear');
                    $this->info('✅ Cache cleared');
                } else {
                    // For database cache during driver switch, skip cache:clear to avoid connection errors
                    $this->warn('⚠️  Skipped cache:clear (database cache with driver switch)');
                    $this->line('Cache will be recreated automatically as needed.');
                }
                
                // Verify the change took effect
                $currentDriver = config('database.default');
                if ($currentDriver !== $dbConnection) {
                    $this->warn("⚠️  Runtime config still shows: {$currentDriver}");
                    // Force set it again
                    app('config')->set('database.default', $dbConnection);
                }
            } else {
                $this->error("❌ Could not find DB_CONNECTION in .env file");
            }
        } else {
            $this->error("❌ .env file not found");
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
        
        try {
            // Show current database configuration
            $driver = config('database.default');
            $this->info("📊 Using database driver: {$driver}");
            
            // Check for and resolve migration conflicts
            $this->resolveMigrationConflicts();
            
            // For SQLite, ensure database file exists and is writable
            if ($driver === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (!File::exists($dbPath)) {
                    $this->error("❌ SQLite database file not found: {$dbPath}");
                    throw new Exception('SQLite database file does not exist');
                }
                if (!is_writable($dbPath)) {
                    $this->error("❌ SQLite database file is not writable: {$dbPath}");
                    throw new Exception('SQLite database file is not writable');
                }
                $this->info("✅ SQLite database verified: {$dbPath}");
            }
            
            // Validate database configuration first
            $service = new DatabaseConfigurationService();
            $validation = $service->validateConfiguration();
            
            if (!$validation['valid']) {
                $this->error('❌ Database configuration validation failed:');
                foreach ($validation['errors'] as $error) {
                    $this->line("  • {$error}");
                }
                throw new Exception('Database configuration is invalid');
            }

            if (!empty($validation['warnings'])) {
                $this->warn('⚠️  Database configuration warnings:');
                foreach ($validation['warnings'] as $warning) {
                    $this->line("  • {$warning}");
                }
            }

            $this->info('🔄 Running migrations...');
            $this->call('migrate', ['--force' => true]);
            
            $this->info('🌱 Running seeders...');
            $this->call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'BlogPermissionSeeder', '--force' => true]);
            $this->call('db:seed', ['--class' => 'Thorium90DefaultPagesSeeder', '--force' => true]);
            
            $this->info('✅ Database setup completed successfully');
            
        } catch (Exception $e) {
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->line('Please check your database configuration and try again.');
            throw $e;
        }
    }

    /**
     * Detect and resolve migration conflicts that occur during deployment.
     * This prevents the common issue where old Laravel base migrations
     * conflict with enhanced Thorium90 migrations.
     */
    protected function resolveMigrationConflicts()
    {
        $this->line('🔍 Checking for migration conflicts...');
        
        $conflictingMigrations = [
            // Old Laravel base migrations that conflict with Thorium90 enhanced versions
            '2014_10_12_000000_create_users_table.php' => '0001_01_01_000000_create_users_table.php',
            '2014_10_12_100000_create_password_reset_tokens_table.php' => '0001_01_01_000001_create_cache_table.php',
            '2019_08_19_000000_create_failed_jobs_table.php' => '0001_01_01_000002_create_jobs_table.php',
            '2019_12_14_000001_create_personal_access_tokens_table.php' => '2025_08_03_200836_create_personal_access_tokens_table.php'
        ];
        
        $conflictsFound = [];
        $migrationsPath = database_path('migrations');
        
        foreach ($conflictingMigrations as $oldMigration => $newMigration) {
            $oldPath = $migrationsPath . '/' . $oldMigration;
            $newPath = $migrationsPath . '/' . $newMigration;
            
            // Check if both old and new migration exist
            if (File::exists($oldPath) && File::exists($newPath)) {
                $conflictsFound[] = [
                    'old' => $oldMigration,
                    'new' => $newMigration,
                    'old_path' => $oldPath
                ];
            }
        }
        
        if (empty($conflictsFound)) {
            $this->info('✅ No migration conflicts detected');
            return;
        }
        
        $this->warn('⚠️  Found ' . count($conflictsFound) . ' migration conflict(s)');
        
        // Create conflicts directory if it doesn't exist
        $conflictsDir = $migrationsPath . '/conflicts';
        if (!File::exists($conflictsDir)) {
            File::makeDirectory($conflictsDir, 0755, true);
        }
        
        foreach ($conflictsFound as $conflict) {
            $this->line("  • Moving {$conflict['old']} → conflicts/");
            
            try {
                // Move conflicting migration to conflicts directory
                $conflictPath = $conflictsDir . '/' . $conflict['old'];
                File::move($conflict['old_path'], $conflictPath);
                
                $this->info("    ✅ Resolved: Using enhanced {$conflict['new']} instead");
                
            } catch (Exception $e) {
                $this->error("    ❌ Failed to move {$conflict['old']}: " . $e->getMessage());
            }
        }
        
        // Create README in conflicts directory explaining the situation
        $readmeContent = "# Migration Conflicts - Resolved Automatically\n\n";
        $readmeContent .= "These migrations were moved here to resolve conflicts during deployment.\n\n";
        $readmeContent .= "## What Happened\n\n";
        $readmeContent .= "Thorium90 uses enhanced migrations that replace the standard Laravel base migrations.\n";
        $readmeContent .= "These old migrations were conflicting with the enhanced versions and have been moved here.\n\n";
        $readmeContent .= "## Conflicts Resolved\n\n";
        
        foreach ($conflictsFound as $conflict) {
            $readmeContent .= "- `{$conflict['old']}` → Now using enhanced `{$conflict['new']}`\n";
        }
        
        $readmeContent .= "\n## Safe to Delete\n\n";
        $readmeContent .= "These files are safe to delete as their functionality is included in the enhanced migrations.\n";
        $readmeContent .= "They are kept here temporarily for reference during the deployment process.\n\n";
        $readmeContent .= "*Generated by Thorium90 Setup on " . now()->format('Y-m-d H:i:s') . "*\n";
        
        File::put($conflictsDir . '/README.md', $readmeContent);
        
        $this->info('✅ Migration conflicts resolved successfully');
        $this->line('   Old migrations moved to database/migrations/conflicts/');
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

Built with [Thorium90](https://github.com/travissutphin/thorium90)

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

    /**
     * Display guided recovery instructions when setup fails
     */
    protected function displayRecoveryInstructions(): void
    {
        $this->newLine();
        $this->error('🚨 SETUP BLOCKED - System validation failed');
        $this->newLine();
        
        $this->info('🔧 RECOVERY STEPS:');
        $this->line('1. Fix the issues listed above');
        $this->line('2. Run: npm run health-check (to verify fixes)');
        $this->line('3. When all green, run: php artisan thorium90:setup --interactive');
        $this->newLine();
        
        $this->info('💡 COMMON FIXES:');
        $this->line('• Update PHP: https://www.php.net/downloads');
        $this->line('• Install missing extensions with your package manager');
        $this->line('• Fix file permissions: chmod -R 755 storage bootstrap/cache');
        $this->line('• Install Node.js: https://nodejs.org');
        $this->line('• Install Composer: https://getcomposer.org');
        $this->newLine();
        
        $this->info('🆘 NEED HELP?');
        $this->line('• Run: composer run health-check (detailed diagnostics)');
        $this->line('• Check DEPLOYMENT.md for troubleshooting');
        $this->line('• Advanced users can bypass with --force flag');
        $this->newLine();
    }

    /**
     * Validate system requirements before setup
     */
    protected function validateSystemRequirements(): bool
    {
        $this->info('🔍 Validating system requirements...');
        $this->newLine();
        
        $allValid = true;
        $warnings = [];
        
        // 1. Check PHP Version
        $phpVersion = PHP_VERSION;
        $minPhpVersion = '8.2.0';
        
        if (version_compare($phpVersion, $minPhpVersion, '>=')) {
            $this->info("✅ PHP Version: {$phpVersion} (>= {$minPhpVersion})");
        } else {
            $this->error("❌ PHP Version: {$phpVersion} (requires >= {$minPhpVersion})");
            $this->line('   Please upgrade PHP to version 8.2 or higher');
            $allValid = false;
        }
        
        // 2. Check Required PHP Extensions
        $requiredExtensions = [
            'mbstring' => 'String handling',
            'xml' => 'XML processing',
            'ctype' => 'Character type checking',
            'json' => 'JSON processing',
            'bcmath' => 'Arbitrary precision mathematics',
            'fileinfo' => 'File information',
            'tokenizer' => 'PHP tokenizer',
            'sqlite3' => 'SQLite database support',
            'pdo' => 'Database abstraction layer',
            'openssl' => 'SSL/TLS support',
            'curl' => 'HTTP client support'
        ];
        
        $this->line('📦 Checking PHP Extensions:');
        foreach ($requiredExtensions as $extension => $description) {
            $hasExtension = extension_loaded($extension);
            
            // Special case: For SQLite, accept either sqlite3 OR pdo_sqlite
            if ($extension === 'sqlite3' && !$hasExtension) {
                $hasExtension = extension_loaded('pdo_sqlite');
                if ($hasExtension) {
                    $this->info("  ✅ {$extension} ({$description}) - using pdo_sqlite");
                }
            }
            
            if ($hasExtension) {
                if ($extension !== 'sqlite3' || !extension_loaded('pdo_sqlite')) {
                    $this->info("  ✅ {$extension} ({$description})");
                }
            } else {
                $this->error("  ❌ {$extension} - {$description}");
                $this->line("     Install with: php extension or enable in php.ini");
                $allValid = false;
            }
        }
        
        // 3. Check Composer
        $composerVersion = $this->getComposerVersion();
        if ($composerVersion) {
            $this->info("✅ Composer: {$composerVersion}");
        } else {
            $this->error('❌ Composer not found or not accessible');
            $this->line('   Please install Composer: https://getcomposer.org');
            $allValid = false;
        }
        
        // 4. Check Node.js and npm
        $nodeVersion = $this->getNodeVersion();
        $npmVersion = $this->getNpmVersion();
        
        if ($nodeVersion) {
            $minNodeVersion = '16.0.0';
            if (version_compare($nodeVersion, $minNodeVersion, '>=')) {
                $this->info("✅ Node.js: {$nodeVersion} (>= {$minNodeVersion})");
            } else {
                $this->error("❌ Node.js: {$nodeVersion} (requires >= {$minNodeVersion})");
                $this->line('   Please upgrade Node.js to version 16 or higher');
                $allValid = false;
            }
        } else {
            $this->error('❌ Node.js not found');
            $this->line('   Please install Node.js: https://nodejs.org');
            $allValid = false;
        }
        
        if ($npmVersion) {
            $this->info("✅ npm: {$npmVersion}");
        } else {
            $this->error('❌ npm not found');
            $this->line('   npm should be installed with Node.js');
            $allValid = false;
        }
        
        // 5. Check Directory Permissions
        $this->line('📁 Checking directory permissions:');
        $directories = [
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'database' => database_path(),
            '.env' => base_path('.env')
        ];
        
        foreach ($directories as $name => $path) {
            if ($name === '.env') {
                // Check if .env exists or if we can create it
                if (File::exists($path)) {
                    if (is_writable($path)) {
                        $this->info("  ✅ {$name} (writable)");
                    } else {
                        $this->error("  ❌ {$name} (not writable)");
                        $allValid = false;
                    }
                } else {
                    // Check if we can create .env in the parent directory
                    $parentDir = dirname($path);
                    if (is_writable($parentDir)) {
                        $this->info("  ✅ {$name} (can be created)");
                    } else {
                        $this->error("  ❌ {$name} (cannot be created - parent dir not writable)");
                        $allValid = false;
                    }
                }
            } else {
                if (File::exists($path) && is_writable($path)) {
                    $this->info("  ✅ {$name} (writable)");
                } else {
                    if (!File::exists($path)) {
                        // Try to create the directory
                        try {
                            File::makeDirectory($path, 0755, true);
                            $this->info("  ✅ {$name} (created)");
                        } catch (Exception $e) {
                            $this->error("  ❌ {$name} (cannot create: {$e->getMessage()})");
                            $allValid = false;
                        }
                    } else {
                        $this->error("  ❌ {$name} (not writable)");
                        $this->line("     Run: chmod -R 755 " . basename($path));
                        $allValid = false;
                    }
                }
            }
        }
        
        // 6. Check Memory Limit
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->convertToBytes($memoryLimit);
        $recommendedMemory = $this->convertToBytes('256M');
        
        if ($memoryLimitBytes === -1) {
            $this->info('✅ Memory Limit: Unlimited');
        } elseif ($memoryLimitBytes >= $recommendedMemory) {
            $this->info("✅ Memory Limit: {$memoryLimit} (>= 256M)");
        } else {
            $warnings[] = "Memory Limit: {$memoryLimit} (recommended: >= 256M)";
            $this->warn("⚠️  Memory Limit: {$memoryLimit} - Recommended: >= 256M");
        }
        
        // 7. Check Max Execution Time
        $maxExecutionTime = ini_get('max_execution_time');
        if ($maxExecutionTime == 0) {
            $this->info('✅ Max Execution Time: Unlimited');
        } elseif ($maxExecutionTime >= 120) {
            $this->info("✅ Max Execution Time: {$maxExecutionTime}s (>= 120s)");
        } else {
            $warnings[] = "Max Execution Time: {$maxExecutionTime}s (recommended: >= 120s)";
            $this->warn("⚠️  Max Execution Time: {$maxExecutionTime}s - Recommended: >= 120s");
        }
        
        // 8. Check if SQLite database can be created
        $this->line('🗄️  Testing SQLite database creation:');
        try {
            $testDbPath = database_path('test_' . time() . '.sqlite');
            File::put($testDbPath, '');
            
            // Try to open with PDO
            $pdo = new \PDO("sqlite:{$testDbPath}");
            $pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY)');
            $pdo->exec('DROP TABLE test');
            $pdo = null;
            
            // Clean up
            File::delete($testDbPath);
            
            $this->info('  ✅ SQLite database creation successful');
        } catch (Exception $e) {
            $this->error("  ❌ SQLite database test failed: " . $e->getMessage());
            $allValid = false;
        }
        
        $this->newLine();
        
        // Display warnings if any
        if (!empty($warnings)) {
            $this->warn('⚠️  Warnings (non-critical):');
            foreach ($warnings as $warning) {
                $this->line("   • {$warning}");
            }
            $this->newLine();
        }
        
        // Final result
        if ($allValid) {
            $this->info('✅ All system requirements validated successfully!');
            $this->newLine();
        } else {
            $this->newLine();
            $this->error('❌ System validation failed. Please resolve the issues above.');
            $this->line('');
            $this->line('💡 Quick fixes:');
            $this->line('   • Update PHP: https://www.php.net/downloads');
            $this->line('   • Install missing extensions with your package manager');
            $this->line('   • Fix file permissions: chmod -R 755 storage bootstrap/cache');
            $this->line('   • Install Node.js: https://nodejs.org');
            $this->newLine();
        }
        
        return $allValid;
    }
    
    /**
     * Get Composer version
     */
    protected function getComposerVersion(): ?string
    {
        // Try multiple Composer detection methods for cross-platform compatibility
        $commands = [
            'composer --version',
            'php composer.phar --version'
        ];
        
        foreach ($commands as $command) {
            try {
                // Use Process for better Windows compatibility
                $process = new \Symfony\Component\Process\Process(explode(' ', $command));
                $process->run();
                
                if ($process->isSuccessful()) {
                    $output = $process->getOutput();
                    if ($output && preg_match('/Composer version ([\d\.]+)/', $output, $matches)) {
                        return $matches[1];
                    }
                }
            } catch (Exception $e) {
                // Try next method
                continue;
            }
        }
        
        // Fallback to shell_exec with Windows-compatible error handling
        try {
            $nullDevice = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
            $output = shell_exec("composer --version {$nullDevice}");
            if ($output && preg_match('/Composer version ([\d\.]+)/', $output, $matches)) {
                return $matches[1];
            }
        } catch (Exception $e) {
            // Ignore
        }
        
        return null;
    }
    
    /**
     * Get Node.js version
     */
    protected function getNodeVersion(): ?string
    {
        // Try Process first for better Windows compatibility
        try {
            $process = new \Symfony\Component\Process\Process(['node', '--version']);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = trim($process->getOutput());
                return str_replace('v', '', $output);
            }
        } catch (Exception $e) {
            // Try fallback
        }
        
        // Fallback to shell_exec with Windows-compatible error handling
        try {
            $nullDevice = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
            $output = shell_exec("node --version {$nullDevice}");
            if ($output) {
                return trim(str_replace('v', '', $output));
            }
        } catch (Exception $e) {
            // Ignore
        }
        
        return null;
    }
    
    /**
     * Get npm version
     */
    protected function getNpmVersion(): ?string
    {
        // Try Process first for better Windows compatibility
        try {
            $process = new \Symfony\Component\Process\Process(['npm', '--version']);
            $process->run();
            
            if ($process->isSuccessful()) {
                return trim($process->getOutput());
            }
        } catch (Exception $e) {
            // Try fallback
        }
        
        // Fallback to shell_exec with Windows-compatible error handling
        try {
            $nullDevice = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
            $output = shell_exec("npm --version {$nullDevice}");
            if ($output) {
                return trim($output);
            }
        } catch (Exception $e) {
            // Ignore
        }
        
        return null;
    }
    
    /**
     * Convert memory limit string to bytes
     */
    protected function convertToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '-1') {
            return -1;
        }
        
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Mark setup as complete and store metadata
     */
    protected function markSetupComplete(string $projectName, string $preset, string $adminEmail): void
    {
        $setupData = [
            'project_name' => $projectName,
            'preset' => $preset,
            'admin_email' => $adminEmail,
            'completed_at' => now()->toISOString(),
            'version' => '2.0.1',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_type' => config('database.default'),
        ];
        
        File::put(base_path('.thorium90-setup'), json_encode($setupData, JSON_PRETTY_PRINT));
        $this->line('📋 Setup metadata saved to .thorium90-setup');
    }
    
    /**
     * Display setup completion summary
     */
    protected function displaySetupSummary(): void
    {
        if (!File::exists(base_path('.thorium90-setup'))) {
            return;
        }
        
        try {
            $setupData = json_decode(File::get(base_path('.thorium90-setup')), true);
            
            $this->info('📊 SETUP SUMMARY:');
            $this->line("• Project: {$setupData['project_name']}");
            $this->line("• Preset: {$setupData['preset']}");
            $this->line("• Admin Email: {$setupData['admin_email']}");
            $this->line("• Database: {$setupData['database_type']}");
            $this->line("• Laravel: {$setupData['laravel_version']}");
            $this->line("• Completed: {$setupData['completed_at']}");
            $this->newLine();
        } catch (Exception $e) {
            // Silently continue if summary can't be displayed
        }
    }

    /**
     * Ensure SQLite database file exists
     */
    protected function ensureSQLiteDatabase()
    {
        $this->line('🗄️  Setting up SQLite database...');
        
        $databasePath = database_path('database.sqlite');
        $databaseDir = dirname($databasePath);
        
        // Ensure database directory exists
        if (!File::exists($databaseDir)) {
            File::makeDirectory($databaseDir, 0755, true);
            $this->info("✅ Created database directory: {$databaseDir}");
        }
        
        // Create database file if it doesn't exist
        if (!File::exists($databasePath)) {
            try {
                File::put($databasePath, '');
                
                // Verify the file was created and is writable
                if (File::exists($databasePath) && is_writable($databasePath)) {
                    $this->info("✅ Created SQLite database: {$databasePath}");
                } else {
                    throw new Exception("Database file created but not writable");
                }
            } catch (Exception $e) {
                $this->error("❌ Failed to create SQLite database: " . $e->getMessage());
                throw $e;
            }
        } else {
            // Verify existing database is writable
            if (is_writable($databasePath)) {
                $this->info("✅ SQLite database already exists: {$databasePath}");
            } else {
                $this->warn("⚠️  SQLite database exists but is not writable: {$databasePath}");
                $this->line("Please check file permissions.");
            }
        }
        
        // Display full path for clarity
        $this->line("Database location: " . realpath($databasePath));
    }
}